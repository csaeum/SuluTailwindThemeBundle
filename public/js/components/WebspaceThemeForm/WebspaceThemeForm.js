// @flow
import React from 'react';
import {observable, action, computed} from 'mobx';
import {observer} from 'mobx-react';
import {Form as FormComponent, SingleSelect} from 'sulu-admin-bundle/components';
import {withToolbar} from 'sulu-admin-bundle/containers';
import {Requester} from 'sulu-admin-bundle/services';
import {translate} from 'sulu-admin-bundle/utils';

/**
 * Custom view component for the webspace theme assignment tab.
 *
 * Loads the list of available themes and the current assignment,
 * then renders a Sulu SingleSelect dropdown for theme selection.
 *
 * Follows the SnippetAreaAdmin pattern for webspace tab integration.
 */
@observer
class WebspaceThemeForm extends React.Component<*> {
    @observable themes: Array<{id: number, label: string}> = [];
    @observable selectedThemeId: ?number = null;
    @observable originalThemeId: ?number = null;
    @observable loading: boolean = true;
    @observable saving: boolean = false;

    constructor(props: *) {
        super(props);
        this.loadData();
    }

    componentDidUpdate(prevProps: *) {
        const prevWebspace = prevProps.router.attributes.webspace;
        const currentWebspace = this.props.router.attributes.webspace;

        if (prevWebspace !== currentWebspace) {
            this.loadData();
        }
    }

    @computed get dirty(): boolean {
        return this.selectedThemeId !== this.originalThemeId;
    }

    get webspaceKey(): string {
        return this.props.router.attributes.webspace;
    }

    @action loadData = () => {
        this.loading = true;

        Promise.all([
            Requester.get('/admin/api/iw-theme-configs?limit=100&page=1'),
            Requester.get('/admin/api/iw-webspace-themes?webspace=' + this.webspaceKey),
        ]).then(action(([themesResponse, assignmentResponse]) => {
            const embedded = themesResponse._embedded || {};
            const themeList = embedded.iw_theme_configs || [];
            this.themes = themeList.map((t) => ({id: t.id, label: t.label || t.name}));
            this.selectedThemeId = assignmentResponse.theme || null;
            this.originalThemeId = this.selectedThemeId;
            this.loading = false;
        })).catch(action(() => {
            this.loading = false;
        }));
    };

    @action handleChange = (value: *) => {
        this.selectedThemeId = value || null;
    };

    @action handleSubmit = () => {
        this.saving = true;

        Requester.put(
            '/admin/api/iw-webspace-themes?webspace=' + this.webspaceKey,
            {theme: this.selectedThemeId}
        ).then(action(() => {
            this.originalThemeId = this.selectedThemeId;
            this.saving = false;
        })).catch(action(() => {
            this.saving = false;
        }));
    };

    render() {
        if (this.loading) {
            return null;
        }

        return (
            <FormComponent>
                <FormComponent.Section>
                    <FormComponent.Field
                        colSpan={6}
                        description={translate('iw_sulu_tailwind_theme.webspace_theme_info')}
                        label={translate('iw_sulu_tailwind_theme.theme')}
                    >
                        <SingleSelect
                            onChange={this.handleChange}
                            value={this.selectedThemeId}
                        >
                            <SingleSelect.Option value={null}>
                                —
                            </SingleSelect.Option>
                            {this.themes.map((theme) => (
                                <SingleSelect.Option key={theme.id} value={theme.id}>
                                    {theme.label}
                                </SingleSelect.Option>
                            ))}
                        </SingleSelect>
                    </FormComponent.Field>
                </FormComponent.Section>
            </FormComponent>
        );
    }
}

WebspaceThemeForm.getDerivedRouteAttributes = () => {
    return {};
};

const formWithToolbar = withToolbar(WebspaceThemeForm, function() {
    return {
        items: [
            {
                disabled: !this.dirty,
                icon: 'su-save',
                label: translate('sulu_admin.save'),
                loading: this.saving,
                onClick: this.handleSubmit,
                type: 'button',
            },
        ],
    };
});

export default formWithToolbar;
