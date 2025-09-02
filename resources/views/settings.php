<?php

use SmartPay\Modules\Admin\Setting;

$settings_tabs  = Setting::settings_tabs();
$all_settings   = Setting::get_registered_settings_sections();
$active_tab     = sanitize_text_field($_GET['tab'] ?? null);
$active_tab     = array_key_exists($active_tab, $settings_tabs) ? $active_tab : 'general';
$sections       = Setting::settings_tab_sections($active_tab);
$key            = !empty($sections) ? key($sections) : 'main';
$section        = isset($_GET['section']) && !empty($sections) && array_key_exists($_GET['section'], $sections) ? sanitize_text_field($_GET['section']) : $key;

$has_main_settings = (bool) $all_settings[$active_tab]['main']; // true if not empty, else false

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
    <div class="wrap container">
        <h1 class="wp-heading-inline"></h1>
    </div>
    <div class="container">
        <div class="d-flex align-items-center justify-content-between py-1 px-4 mt-3 text-white bg-dark rounded-top">
            <div class="lh-100">
                <h2 class="text-white"><?php esc_html_e('SmartPay Settings', 'smartpay'); ?></h2>
            </div>
            <div>
                <a class="btn btn-dark btn-sm" href="https://wpsmartpay.com/changelog/" target="_blank">v<?php echo esc_html(SMARTPAY_VERSION); ?></a>
            </div>
        </div>

        <div class="card border-light shadow-sm mt-0">
            <div class="card-header rounded-0">
                <ul class="nav nav-tabs card-header-tabs">
                    <?php
                    foreach ($settings_tabs as $tab_id => $tab_name) {
                        $tab_url = add_query_arg(array(
                            'settings-updated' => false,
                            'tab'              => $tab_id,
                        ));

                        // Remove the section from the tabs so we always end up at the main section
                        $tab_url = remove_query_arg('section', $tab_url);

                        $active = $active_tab == $tab_id ? ' active' : '';
                        echo '<li class="nav-item">';
                        echo '<a href="' . esc_url($tab_url) . '" class="nav-link' . esc_attr($active) . '">';
                        echo esc_html($tab_name);
                        echo '</a>';
                        echo '</li>';
                    }
                    ?>
                </ul>
            </div>

            <div class="card-body">
                <?php
                $number_of_sections = count($sections);
                $number = 0;
                if ($number_of_sections > 1) {
                    echo '<ul class="d-flex bd-highlight nav nav-pills border-bottom px-3 pb-2 mt-n2 mx-n4">';
                    foreach ($sections as $section_id => $section_name) {
                        echo '<li class="bd-highlight nav-item m-0">';
                        $number++;
                        $tab_url = add_query_arg(array(
                            'settings-updated' => false,
                            'tab' => $active_tab,
                            'section' => $section_id
                        ));
                        $class = 'nav-link text-decoration-none py-1';
                        if ($section == $section_id) {
                            $class .= ' active bg-secondary';
                        }
                        echo '<a class="' . esc_attr($class) . '" href="' . esc_url($tab_url) . '">' . esc_html($section_name) . '</a>';

                        echo '</li>';
                    }

                    $payment_mode = smartpay_is_test_mode() ? __('Test Mode', 'smartpay') : __('Live Mode', 'smartpay');

                    $test_mode_svg = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="12" fill="currentColor" class="bi bi-info-circle-fill" viewBox="0 0 16 16">
                                        <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/>
                                        </svg>';
                    echo '<li class="ml-auto bd-highlight nav-item m-0">';
                    echo '<span class="btn-sm btn-secondary disabled">' . wp_kses_post($test_mode_svg) . wp_kses_post($payment_mode) . '</span>';
                    echo '</li>';
                    echo '</ul>';
                }
                ?>
                <form method="POST" action="options.php">
                    <table class="form-table">
                        <?php
                        settings_fields('smartpay_settings');
                        do_settings_sections('smartpay_settings_' . $active_tab . '_' . $section);

                        // If the main section was empty and we overrode the view with the next subsection, prepare the section for saving.
                        if (true === $override) :
                        ?>
                            <input type="hidden" name="smartpay_section_override" value="<?php echo esc_attr($section); ?>" />
                        <?php endif; ?>
                    </table>
                    <?php if ($active_tab !== 'debug_log') : ?>
                        <?php submit_button(__('Save Changes', 'smartpay'), 'btn btn-primary'); ?>
                    <?php endif; ?>
                    <?php if ($active_tab === 'debug_log') : ?>
                        <button class="btn btn-primary smartpay-clear-debug-log"><?php echo esc_html__('Clear Log', 'smartpay') ?></button>
                    <?php endif; ?>
                </form>
            </div>
        </div>

    </div>
    <!-- container end -->
</div>
<?php echo ob_get_clean();
