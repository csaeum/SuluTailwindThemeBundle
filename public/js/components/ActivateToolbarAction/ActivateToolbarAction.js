// @flow
import {action, observable} from 'mobx';
import {translate} from 'sulu-admin-bundle/utils';
import {Requester} from 'sulu-admin-bundle/services';
import initializer from 'sulu-admin-bundle/services/initializer';
import AbstractListToolbarAction from 'sulu-admin-bundle/views/List/toolbarActions/AbstractListToolbarAction';

/**
 * Toolbar action to activate a selected theme from the list view.
 *
 * Sends a POST request to the activate endpoint, which deactivates all other
 * themes and activates the selected one.
 */
export default class ActivateToolbarAction extends AbstractListToolbarAction {
    @observable activating: boolean = false;

    getToolbarItemConfig() {
        // Enabled only when exactly 1 theme is selected
        const singleSelected = this.listStore.selectionIds.length === 1;

        // Disabled if the selected theme is already active
        const alreadyActive = singleSelected
            && this.listStore.selections.length === 1
            && this.listStore.selections[0].isActive;

        return {
            disabled: !singleSelected || alreadyActive,
            icon: 'su-check-circle',
            label: translate('iw_sulu_tailwind_theme.activate'),
            loading: this.activating,
            onClick: this.handleClick,
            type: 'button',
        };
    }

    @action handleClick = () => {
        const selectedId = this.listStore.selectionIds[0];
        if (!selectedId) {
            return;
        }

        this.activating = true;

        Requester.post('/admin/api/iw-theme-configs/' + selectedId + '/activate')
            .then(action(() => {
                this.activating = false;
                this.listStore.reload();
                this.listStore.clearSelection();
                // Reload admin config so that palette, buttons, variants
                // reflect the newly activated theme in all components
                initializer.initialize(true);
            }))
            .catch(action(() => {
                this.activating = false;
            }));
    };
}
