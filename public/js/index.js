// @flow
import {fieldRegistry} from 'sulu-admin-bundle/containers';
import initializer from 'sulu-admin-bundle/services/initializer';
import VariantPicker from './components/VariantPicker/VariantPicker';
import StylePicker from './components/StylePicker/StylePicker';
import MarginSelector from './components/MarginSelector/MarginSelector';
import ColorTokenEditor from './components/ColorTokenEditor/ColorTokenEditor';
import FontSelector from './components/FontSelector/FontSelector';

/**
 * Register all custom field types for the SuluThemeBundle admin interface.
 * These field types are used in the theme configuration forms.
 */
initializer.addUpdateConfigHook('iw_sulu_theme', (config: Object, initialized: boolean) => {
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
});
