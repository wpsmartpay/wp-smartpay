<?php
defined('ABSPATH') || exit;

use SmartPay\Modules\Admin\Setting;

$settings_tabs  = Setting::settings_tabs();
$all_settings   = Setting::get_registered_settings_sections();
// phpcs:ignore: WordPress.Security.NonceVerification.Recommended -- Get Request, No nonce needed
$active_tab     = isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : null;
$active_tab     = array_key_exists($active_tab, $settings_tabs) ? $active_tab : 'general';
$sections       = Setting::settings_tab_sections($active_tab);
$key            = !empty($sections) ? key($sections) : 'main';
// phpcs:ignore: WordPress.Security.NonceVerification.Recommended -- Get Request, No nonce needed
$section        = isset($_GET['section']) && !empty($sections) && array_key_exists(sanitize_text_field(wp_unslash($_GET['section'])), $sections) ? sanitize_text_field(wp_unslash($_GET['section'])) : $key;

$has_main_settings = isset($all_settings[$active_tab]) && !empty($all_settings[$active_tab]['main']);

if (!$has_main_settings) {
    foreach ($all_settings[$active_tab] as $s_id => $s_title) {
        if (is_string($s_id) && is_array($sections) && array_key_exists($s_id, $sections)) {
            continue;
        } else {
            $has_main_settings = true;
            break;
        }
    }
}

$override = false;
if (false === $has_main_settings) {
    unset($sections['main']);

    if ('main' === $section) {
        foreach ($sections as $section_key => $section_title) {
            if (!empty($all_settings[$active_tab][$section_key])) {
                $section  = $section_key;
                $override = true;
                break;
            }
        }
    }
}

ob_start();
?>
<div class="smartpay <?php echo 'settings-' . esc_attr($active_tab); ?>">
    <div class="container-full">
        <div class="wrap" style="display:none">
            <h1 class="wp-heading-inline"></h1>
        </div>

        <div class="smartpay-page-header">
            <div class="smartpay-page-header__inner">
                <div class="smartpay-page-header__text">
                    <h2 class="smartpay-page-header__title"><?php esc_html_e( 'Settings', 'smartpay' ); ?></h2>
                    <p class="smartpay-page-header__subtitle"><?php esc_html_e( 'Configure your SmartPay preferences', 'smartpay' ); ?></p>
                </div>
                <div class="smartpay-page-header__actions">
                    <div class="smartpay-page-header__logo">
                        <img src="<?php echo esc_url( SMARTPAY_PLUGIN_ASSETS . '/img/logo.png' ); ?>" alt="SmartPay" />
                    </div>
                </div>
            </div>
        </div>

        <div class="sp-content-wide">

            <div class="sp-tabs__nav flex overflow-x-auto">
                <?php foreach ($settings_tabs as $tab_id => $tab_name) :
                    $tab_url = remove_query_arg('section', add_query_arg(['settings-updated' => false, 'tab' => $tab_id]));
                ?>
                    <a href="<?php echo esc_url($tab_url); ?>"
                    class="sp-tabs__item <?php echo ($active_tab === $tab_id) ? 'sp-tabs__item--active' : ''; ?>">
                        <?php echo esc_html($tab_name); ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="bg-white border border-border sp-card--tab-connected overflow-hidden">
                <div class="p-6">

                    <?php if (count($sections) > 1) : ?>
                        <?php $payment_mode = smartpay_is_test_mode() ? __('Test Mode', 'smartpay') : __('Live Mode', 'smartpay'); ?>
                        <div class="sp-tabs__nav flex items-center mb-6">
                            <?php foreach ($sections as $section_id => $section_name) :
                                $tab_url = add_query_arg(['settings-updated' => false, 'tab' => $active_tab, 'section' => $section_id]);
                            ?>
                                <a class="sp-tabs__item <?php echo ($section === $section_id) ? 'sp-tabs__item--active' : ''; ?>"
                                href="<?php echo esc_url($tab_url); ?>">
                                    <?php echo esc_html($section_name); ?>
                                </a>
                            <?php endforeach; ?>
                            <span class="ml-auto text-xs text-muted-foreground border border-border rounded-md px-3 py-1.5 whitespace-nowrap">
                                <?php echo esc_html($payment_mode); ?>
                            </span>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="options.php">
                        <?php settings_fields('smartpay_settings'); ?>
                        <?php do_settings_sections('smartpay_settings_' . $active_tab . '_' . $section); ?>
                        <?php if (true === $override) : ?>
                            <input type="hidden" name="smartpay_section_override" value="<?php echo esc_attr($section); ?>" />
                        <?php endif; ?>

                        <?php if ($active_tab !== 'debug_log') : ?>
                            <p class="submit">
                                <input type="submit" name="submit" id="submit"
                                    class="sp-btn sp-btn--primary"
                                    value="<?php echo esc_attr__('Save Changes', 'smartpay'); ?>" />
                            </p>
                        <?php endif; ?>
                        <?php if ($active_tab === 'debug_log') : ?>
                            <button type="button" class="sp-btn sp-btn--primary smartpay-clear-debug-log">
                                <?php echo esc_html__('Clear Log', 'smartpay'); ?>
                            </button>
                        <?php endif; ?>
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>
<?php
// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- The generated output has already escaped.
echo ob_get_clean();
