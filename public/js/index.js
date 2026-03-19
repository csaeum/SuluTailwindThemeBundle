// @flow
import {fieldRegistry} from 'sulu-admin-bundle/containers';
import {formToolbarActionRegistry} from 'sulu-admin-bundle/views/Form';
import {viewRegistry} from 'sulu-admin-bundle/containers';
import initializer from 'sulu-admin-bundle/services/initializer';
import themeConfigStore from './stores/themeConfigStore';
import WebspaceThemeForm from './components/WebspaceThemeForm/WebspaceThemeForm';
import VariantPicker from './components/VariantPicker/VariantPicker';
import StylePicker from './components/StylePicker/StylePicker';
import MarginSelector from './components/MarginSelector/MarginSelector';
import ColorTokenEditor from './components/ColorTokenEditor/ColorTokenEditor';
import FontPicker from './components/FontPicker/FontPicker';
import RadiusSelector from './components/RadiusSelector/RadiusSelector';
import ButtonStylePicker from './components/ButtonStylePicker/ButtonStylePicker';
import collapsibleSections from './components/CollapsibleSections/CollapsibleSections';
import SaveWithConfigReloadAction from './components/SaveWithConfigReloadAction/SaveWithConfigReloadAction';

/**
 * Register all custom field types for the SuluTailwindThemeBundle admin interface.
 *
 * Theme-specific data (variants, buttons, palette) is stored in a shared
 * MobX observable store (themeConfigStore) and loaded per-webspace via API.
 * Components decorated with @observer re-render automatically on webspace switch.
 */
initializer.addUpdateConfigHook('iw_sulu_tailwind_theme', (config: Object, initialized: boolean) => {
    if (config) {
        // Apply initial theme data to the observable store
        themeConfigStore.update(config);
        StylePicker.blockStyles = config.blockStyles || {};
        collapsibleSections.init(config.collapsibleSections || {});
        FontPicker.hasApiKey = config.hasApiKey || false;
    }

    if (initialized) {
        return;
    }

    viewRegistry.add('iw_sulu_tailwind_theme.webspace_theme_form', WebspaceThemeForm);
    formToolbarActionRegistry.add('iw_sulu_tailwind_theme.save', SaveWithConfigReloadAction);

    fieldRegistry.add('iw_theme_variant_picker', VariantPicker);
    fieldRegistry.add('iw_theme_style_picker', StylePicker);
    fieldRegistry.add('iw_theme_margin_selector', MarginSelector);
    fieldRegistry.add('iw_theme_color_token_editor', ColorTokenEditor);
    fieldRegistry.add('iw_theme_font_picker', FontPicker);
    fieldRegistry.add('iw_theme_radius_selector', RadiusSelector);
    fieldRegistry.add('iw_theme_button_style_picker', ButtonStylePicker);
});
