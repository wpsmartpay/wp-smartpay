# UI Guidelines — WP SmartPay Plugin Family

## Design Philosophy
Use WordPress native UI components exclusively. SmartPay should feel like a natural extension of WordPress, not a foreign app bolted on.

## Component Library (WordPress Native Only)

### Layout
- Use WordPress admin page structure: `<div class="wrap">`
- Use `<div class="card">` for content sections
- Navigation: Use WP admin tabs pattern `<nav class="nav-tab-wrapper">`
- Two-column layout: `<div class="metabox-holder columns-2">`

### Forms & Inputs
- Text inputs: Standard `<input class="regular-text">`
- Large text: `<textarea class="large-text">`
- Small number: `<input class="small-text" type="number">`
- Select: `<select>` with standard WP styling
- Toggle: Use `<input type="checkbox">` — no custom toggle switches
- Color picker: `wp-color-picker`
- Date picker: `jquery-ui-datepicker` (WP bundled)
- Media upload: Use `wp.media` for file/image selection

### Buttons
```html
<!-- Primary action -->
<button class="button button-primary">Save Changes</button>

<!-- Secondary action -->
<button class="button">Cancel</button>

<!-- Destructive action -->
<button class="button button-link-delete">Delete</button>

<!-- Disabled state -->
<button class="button button-primary" disabled>Processing...</button>
```

### Notices & Feedback
```php
// Success
echo '<div class="notice notice-success is-dismissible"><p>' .
     esc_html__( 'Settings saved.', 'smartpay' ) . '</p></div>';

// Error
echo '<div class="notice notice-error"><p>' .
     esc_html__( 'Something went wrong.', 'smartpay' ) . '</p></div>';

// Warning
echo '<div class="notice notice-warning"><p>' .
     esc_html__( 'Please review.', 'smartpay' ) . '</p></div>';

// Info
echo '<div class="notice notice-info"><p>' .
     esc_html__( 'Tip: ...', 'smartpay' ) . '</p></div>';
```

### Tables
- Use `WP_List_Table` for data tables (extend the class)
- Never build custom table markup for admin data listings
- Use `<table class="widefat striped">` for simple read-only tables
- Include bulk actions where appropriate
- Always support sortable columns

### Settings Pages
- Use `add_settings_section()` / `add_settings_field()` / `register_setting()` for WordPress settings pages

### Modals & Dialogs
- Use `wp.template` + `wp-backbone` for WordPress-style modals
- Or use `<div class="media-modal wp-core-ui">` structure
- Never use custom modal implementations or third-party modal libraries

### Icons
- Use Dashicons: `<span class="dashicons dashicons-admin-generic"></span>`
- No FontAwesome, no custom icon fonts, no SVG icon libraries

### Typography
- Never override WordPress admin typography
- Use standard WP heading hierarchy: `<h1>` page title, `<h2>` section, `<h3>` subsection

### Colors
```css
/* Use WordPress admin color scheme CSS variables */
--wp-admin-theme-color: #2271b1;
--wp-admin-theme-color-darker-10: #135e96;
--wp-admin-theme-color-darker-20: #043959;

/* Standard status colors */
.smartpay-status-active   { color: #00a32a; }
.smartpay-status-paused   { color: #dba617; }
.smartpay-status-expired  { color: #d63638; }
.smartpay-status-pending  { color: #2271b1; }
```

### Spacing
- Follow WP admin spacing: 20px page padding, 12px between elements
- Use `box-sizing: border-box` on custom components
- Card padding: 12px (matches `.card` default)

### CSS Rules
- Prefix all custom classes: `.smartpay-*`
- Never use `!important` unless absolutely necessary
- Load CSS only on pages where your plugin renders
- Never load full CSS frameworks (Bootstrap, Tailwind, etc.)

### JavaScript Rules
- Use `wp.element` (React) for complex interactive UIs
- Use jQuery (WP bundled) for simple DOM manipulation
- Use `wp.apiFetch` for AJAX calls
- Prefix all JS globals: `window.smartpayData`
- Use `wp_localize_script()` or `wp_add_inline_script()` for data passing
- Never load React/Vue/Angular from CDN — use wp-element

### Accessibility (Required)
- All form inputs have associated `<label>` elements
- All images have `alt` attributes
- Use ARIA attributes where native semantics insufficient
- Keyboard navigation must work for all interactive elements
- Color contrast meets WCAG 2.1 AA (4.5:1 for text)
- Screen reader text: `<span class="screen-reader-text">...</span>`

## Page Template Structure

### Settings Page
```php
<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

    <nav class="nav-tab-wrapper">
        <a href="?page=smartpay&tab=general"
           class="nav-tab <?php echo $active === 'general' ? 'nav-tab-active' : ''; ?>">
            <?php esc_html_e( 'General', 'smartpay' ); ?>
        </a>
        <a href="?page=smartpay&tab=gateways"
           class="nav-tab <?php echo $active === 'gateways' ? 'nav-tab-active' : ''; ?>">
            <?php esc_html_e( 'Gateways', 'smartpay' ); ?>
        </a>
    </nav>

    <div class="card">
        <form method="post" action="options.php">
            <?php
            settings_fields( 'smartpay_settings' );
            do_settings_sections( 'smartpay_settings' );
            submit_button();
            ?>
        </form>
    </div>
</div>
```

## Anti-Patterns (Never Do These)
- ❌ Custom CSS frameworks (Bootstrap, Tailwind, Bulma)
- ❌ Custom JavaScript frameworks loaded from CDN
- ❌ Custom toggle switches (use checkboxes)
- ❌ Custom modal libraries (use WP modals)
- ❌ Custom icon fonts (use Dashicons)
- ❌ Inline styles on elements
- ❌ Global admin CSS that affects other plugins
- ❌ Loading assets on every admin page
- ❌ Upsell banners that dominate the settings page
- ❌ Custom admin notices that don't use .notice pattern
