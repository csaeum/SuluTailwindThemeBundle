// @flow
import {reaction} from 'mobx';
import {translate} from 'sulu-admin-bundle/utils';
import {Requester} from 'sulu-admin-bundle/services';
import Config from 'sulu-admin-bundle/services/Config';
import initializer from 'sulu-admin-bundle/services/initializer';
import AbstractFormToolbarAction from 'sulu-admin-bundle/views/Form/toolbarActions/AbstractFormToolbarAction';

/**
 * Custom save toolbar action that reloads the theme config after saving.
 *
 * This ensures that palette colors, button previews, and variant data
 * reflect the latest theme state across all tabs and components.
 *
 * Instead of calling initializer.initialize() (which reloads the entire
 * admin config including navigation and breaks navigation item IDs),
 * this fetches only the config endpoint and re-runs our bundle's
 * update config hooks.
 */
export default class SaveWithConfigReloadAction extends AbstractFormToolbarAction {
    /** @type {Function|null} Disposer for the save reaction */
    _saveDisposer: ?Function = null;

    getToolbarItemConfig() {
        const {dirty, saving} = this.resourceFormStore;

        return {
            disabled: !dirty,
            icon: 'su-save',
            label: translate('sulu_admin.save'),
            loading: saving,
            onClick: () => {
                // Watch for saving to complete (true → false)
                this._saveDisposer = reaction(
                    () => this.resourceFormStore.saving,
                    (isSaving: boolean) => {
                        if (!isSaving) {
                            // Save completed — reload only the bundle config
                            Requester.get(Config.endpoints.config).then((config) => {
                                const bundleConfig = config['iw_sulu_tailwind_theme'];
                                if (bundleConfig && initializer.updateConfigHooks['iw_sulu_tailwind_theme']) {
                                    initializer.updateConfigHooks['iw_sulu_tailwind_theme'].forEach((hook) => {
                                        hook(bundleConfig, true);
                                    });
                                }
                            });

                            if (this._saveDisposer) {
                                this._saveDisposer();
                                this._saveDisposer = null;
                            }
                        }
                    }
                );

                this.form.submit();
            },
            type: 'button',
        };
    }
}
