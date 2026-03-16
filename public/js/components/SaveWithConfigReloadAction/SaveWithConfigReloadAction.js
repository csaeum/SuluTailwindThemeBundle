// @flow
import {reaction} from 'mobx';
import {translate} from 'sulu-admin-bundle/utils';
import initializer from 'sulu-admin-bundle/services/initializer';
import AbstractFormToolbarAction from 'sulu-admin-bundle/views/Form/toolbarActions/AbstractFormToolbarAction';

/**
 * Custom save toolbar action that reloads the admin config after saving.
 *
 * This ensures that palette colors, button previews, and variant data
 * reflect the latest theme state across all tabs and components,
 * especially after toggling the "active" checkbox or changing colors.
 *
 * Uses a MobX reaction on resourceFormStore.saving to detect when
 * the save completes, then triggers a config reload.
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
                            // Save completed — reload config
                            initializer.initialize(true);
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
