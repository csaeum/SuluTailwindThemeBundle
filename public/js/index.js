// @flow
import {fieldRegistry} from 'sulu-admin-bundle/containers';
import {listToolbarActionRegistry} from 'sulu-admin-bundle/views/List';
import {formToolbarActionRegistry} from 'sulu-admin-bundle/views/Form';
import initializer from 'sulu-admin-bundle/services/initializer';
import VariantPicker from './components/VariantPicker/VariantPicker';
import StylePicker from './components/StylePicker/StylePicker';
import MarginSelector from './components/MarginSelector/MarginSelector';
import ColorTokenEditor from './components/ColorTokenEditor/ColorTokenEditor';
import FontPicker from './components/FontPicker/FontPicker';
import RadiusSelector from './components/RadiusSelector/RadiusSelector';
import ButtonStylePicker from './components/ButtonStylePicker/ButtonStylePicker';
import collapsibleSections from './components/CollapsibleSections/CollapsibleSections';
import ActivateToolbarAction from './components/ActivateToolbarAction/ActivateToolbarAction';
import SaveWithConfigReloadAction from './components/SaveWithConfigReloadAction/SaveWithConfigReloadAction';

/**
 * Register all custom field types for the SuluTailwindThemeBundle admin interface.
 *
 * Receives config data from ThemeAdmin::getConfig() containing:
 * - variants: block variant definitions from the active theme
 * - blockStyles: available layout styles per block type
 */
initializer.addUpdateConfigHook('iw_sulu_tailwind_theme', (config: Object, initialized: boolean) => {
    if (config) {
        // Pass active theme data to components via static properties.
        // This data is refreshed on each admin config reload.
        VariantPicker.themeVariants = config.variants || [];
        StylePicker.blockStyles = config.blockStyles || {};
        ButtonStylePicker.themeButtons = config.buttons || {};
        ColorTokenEditor.themePalette = config.palette || {};
        collapsibleSections.init(config.collapsibleSections || {});
        FontPicker.hasApiKey = config.hasApiKey || false;
    }

    if (initialized) {
        return;
    }

    listToolbarActionRegistry.add('iw_sulu_tailwind_theme.activate', ActivateToolbarAction);
    formToolbarActionRegistry.add('iw_sulu_tailwind_theme.save', SaveWithConfigReloadAction);

    fieldRegistry.add(
        'iw_theme_variant_picker',
        VariantPicker
    );

    fieldRegistry.add(
        'iw_theme_style_picker',
        StylePicker
    );

    fieldRegistry.add(
        'iw_theme_margin_selector',
        MarginSelector
    );

    fieldRegistry.add(
        'iw_theme_color_token_editor',
        ColorTokenEditor
    );

    fieldRegistry.add(
        'iw_theme_font_picker',
        FontPicker
    );

    fieldRegistry.add(
        'iw_theme_radius_selector',
        RadiusSelector
    );

    fieldRegistry.add(
        'iw_theme_button_style_picker',
        ButtonStylePicker
    );
});
