// @flow
import {fieldRegistry} from 'sulu-admin-bundle/containers';
import initializer from 'sulu-admin-bundle/services/initializer';
import VariantPicker from './components/VariantPicker/VariantPicker';
import StylePicker from './components/StylePicker/StylePicker';
import MarginSelector from './components/MarginSelector/MarginSelector';
import ColorTokenEditor from './components/ColorTokenEditor/ColorTokenEditor';
import FontSelector from './components/FontSelector/FontSelector';
import RadiusSelector from './components/RadiusSelector/RadiusSelector';

/**
 * Register all custom field types for the SuluThemeBundle admin interface.
 *
 * Receives config data from ThemeAdmin::getConfig() containing:
 * - variants: block variant definitions from the active theme
 * - blockStyles: available layout styles per block type
 */
initializer.addUpdateConfigHook('iw_sulu_theme', (config: Object, initialized: boolean) => {
    if (config) {
        // Pass active theme data to components via static properties.
        // This data is refreshed on each admin config reload.
        VariantPicker.themeVariants = config.variants || [];
        StylePicker.blockStyles = config.blockStyles || {};
    }

    if (initialized) {
        return;
    }

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
        'iw_theme_font_selector',
        FontSelector
    );

    fieldRegistry.add(
        'iw_theme_radius_selector',
        RadiusSelector
    );
});
