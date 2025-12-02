# Smartpay Admin Settings
## smartpay_settings_saved

- Type: Action
- Fires: After SmartPay settings are saved on a tab/section.
- File: `app/Modules/Admin/Setting.php:446`

Parameters:

- `$output` (array) — Final sanitized options saved.
- `$input` (array) — Raw submitted input.
- `$tab` (string) — Current tab slug.
- `$section` (string) — Current section slug.

```php
add_action('smartpay_settings_saved', function( $output, $input, $tab, $section ) {
    // React to settings changes.
}, 10, 4);
```

## smartpay_settings_* family (filters)

The SmartPay settings system provides a comprehensive set of filters for customizing the admin settings interface. These filters allow you to add new settings tabs, sections, fields, and control how data is sanitized and displayed.

### Core Settings Structure Filters

#### `smartpay_settings_tabs`
- **Type:** Filter
- **Purpose:** Add or modify the main settings tabs
- **Parameters:** `$tabs` (array) - Array of tab slugs => tab labels
- **Location:** `app/Modules/Admin/Setting.php:347`

```php
add_filter('smartpay_settings_tabs', function( $tabs ) {
    $tabs['my_custom_tab'] = __('My Custom Tab', 'smartpay');
    return $tabs;
});
```

#### `smartpay_settings_sections_{tab}`
- **Type:** Filter
- **Purpose:** Define sections within a specific tab
- **Parameters:** `$sections` (array) - Array of section slugs => section labels
- **Location:** `app/Modules/Admin/Setting.php:317-333`

```php
add_filter('smartpay_settings_sections_general', function( $sections ) {
    $sections['advanced'] = __('Advanced Options', 'smartpay');
    return $sections;
});
```

#### `smartpay_settings_sections`
- **Type:** Filter
- **Purpose:** Filter all sections across all tabs
- **Parameters:** `$sections` (array) - Complete sections array
- **Location:** `app/Modules/Admin/Setting.php:335`

### Settings Registration Filters

#### `smartpay_settings_{tab}`
- **Type:** Filter
- **Purpose:** Register settings fields for specific tabs
- **Parameters:** `$settings` (array) - Settings array for the tab
- **Available tabs:** `general`, `gateways`, `emails`, `licenses`, `extensions`, `debug_log`
- **Location:** `app/Modules/Admin/Setting.php:94-278`

```php
add_filter('smartpay_settings_general', function( $settings ) {
    $settings['main']['my_custom_setting'] = array(
        'id'   => 'my_custom_setting',
        'name' => __('My Custom Setting', 'smartpay'),
        'desc' => __('Description of my setting', 'smartpay'),
        'type' => 'text',
    );
    return $settings;
});
```

#### `smartpay_settings`
- **Type:** Filter
- **Purpose:** Filter the complete assembled settings structure
- **Parameters:** `$smartpay_settings` (array) - Complete settings array
- **Location:** `app/Modules/Admin/Setting.php:278`

```php
add_filter('smartpay_settings', function( $settings ) {
    // Modify the entire settings structure
    return $settings;
});
```

### Sanitization Filters

#### `smartpay_settings_{tab}-{section}_sanitize`
- **Type:** Filter
- **Purpose:** Sanitize raw input for a specific tab-section combination
- **Parameters:** `$input` (array) - Raw submitted input
- **Location:** `app/Modules/Admin/Setting.php:391`

```php
add_filter('smartpay_settings_general-main_sanitize', function( $input ) {
    // Custom sanitization for general tab, main section
    if (isset($input['my_field'])) {
        $input['my_field'] = sanitize_text_field($input['my_field']);
    }
    return $input;
});
```

#### `smartpay_settings_sanitize_{type}`
- **Type:** Filter
- **Purpose:** Sanitize values by field type
- **Parameters:** `$value` (mixed), `$key` (string) - Field value and key
- **Location:** `app/Modules/Admin/Setting.php:412`

```php
add_filter('smartpay_settings_sanitize_text', function( $value, $key ) {
    // Custom sanitization for text fields
    return sanitize_text_field($value);
}, 10, 2);
```

#### `smartpay_settings_sanitize`
- **Type:** Filter
- **Purpose:** General sanitization filter for all settings
- **Parameters:** `$value` (mixed), `$key` (string) - Field value and key
- **Location:** `app/Modules/Admin/Setting.php:413`

```php
add_filter('smartpay_settings_sanitize', function( $value, $key ) {
    // Apply custom sanitization to all settings
    if ($key === 'special_field') {
        return wp_kses_post($value);
    }
    return $value;
}, 10, 2);
```

### Field Type and Display Filters

#### `smartpay_non_setting_types`
- **Type:** Filter
- **Purpose:** Declare field types that are not persistent settings
- **Parameters:** `$types` (array) - Array of non-setting field types
- **Location:** `app/Modules/Admin/Setting.php:403`

```php
add_filter('smartpay_non_setting_types', function( $types ) {
    $types[] = 'custom_display_only';
    return $types;
});
```

#### `smartpay_after_setting_output`
- **Type:** Filter
- **Purpose:** Modify generated field HTML before output
- **Parameters:** `$html` (string), `$args` (array) - Generated HTML and field arguments
- **Location:** `app/Modules/Admin/Setting.php:620,645`

```php
add_filter('smartpay_after_setting_output', function( $html, $args ) {
    if ($args['id'] === 'special_field') {
        $html .= '<div class="custom-help">Additional help text</div>';
    }
    return $html;
}, 10, 2);
```

### Complete Example: Adding a Custom Tab

```php
// 1. Add the tab
add_filter('smartpay_settings_tabs', function( $tabs ) {
    $tabs['my_plugin'] = __('My Plugin', 'smartpay');
    return $tabs;
});

// 2. Add sections to the tab
add_filter('smartpay_settings_sections_my_plugin', function( $sections ) {
    $sections['main'] = __('Main Settings', 'smartpay');
    $sections['advanced'] = __('Advanced Settings', 'smartpay');
    return $sections;
});

// 3. Add settings to the tab
add_filter('smartpay_settings_my_plugin', function( $settings ) {
    $settings['main'] = array(
        'my_text_field' => array(
            'id'   => 'my_text_field',
            'name' => __('Text Field', 'smartpay'),
            'desc' => __('Enter some text', 'smartpay'),
            'type' => 'text',
        ),
        'my_checkbox' => array(
            'id'   => 'my_checkbox',
            'name' => __('Enable Feature', 'smartpay'),
            'desc' => __('Check to enable this feature', 'smartpay'),
            'type' => 'checkbox',
        ),
    );

    $settings['advanced'] = array(
        'my_select' => array(
            'id'      => 'my_select',
            'name'    => __('Select Option', 'smartpay'),
            'desc'    => __('Choose an option', 'smartpay'),
            'type'    => 'select',
            'options' => array(
                'option1' => __('Option 1', 'smartpay'),
                'option2' => __('Option 2', 'smartpay'),
            ),
        ),
    );

    return $settings;
});

// 4. Add custom sanitization
add_filter('smartpay_settings_my_plugin-main_sanitize', function( $input ) {
    if (isset($input['my_text_field'])) {
        $input['my_text_field'] = sanitize_text_field($input['my_text_field']);
    }
    return $input;
});
```

## smartpay_admin_add_menu_items

- Type: Action
- Fires: After SmartPay registers default admin menus; add custom submenu pages.
- File: `app/Modules/Admin/Admin.php:140`

Parameters: none

```php
add_action('smartpay_admin_add_menu_items', function() {
    add_submenu_page('smartpay', 'Reports', 'Reports', 'manage_options', 'smartpay-reports', function() {
        echo '<div class="wrap"><h1>Reports</h1></div>';
    });
}, 20);
```


## smartpay_get_settings

- Type: Filter
- Fires: When SmartPay loads the combined settings array.
- File: `app/Helpers/smartpay.php:867`

Parameters:

- `$settings` (array)

```php
add_filter('smartpay_get_settings', function( $settings ) {
    $settings['feature_flag'] = true;
    return $settings;
});
```
