<?php

use SmartPay\Modules\Admin\Setting;

$settings_tabs  = Setting::settings_tabs();
$all_settings   = Setting::get_registered_settings_sections();
$active_tab     = sanitize_text_field($_GET['tab'] ?? null);
$active_tab     = array_key_exists($active_tab, $settings_tabs) ? $active_tab : 'general';
$sections       = Setting::settings_tab_sections($active_tab);
$key            = !empty($sections) ? key($sections) : 'main';
$section        = isset($_GET['section']) && !empty($sections) && array_key_exists($_GET['section'], $sections) ? sanitize_text_field($_GET['section']) : $key;

$has_main_settings = !empty($all_settings[$active_tab]['main']) ? true : false;

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
<div class="smartpay <?php echo 'settings-' . $active_tab; ?>">
    <div class="wrap container">
        <h1 class="wp-heading-inline"></h1>
    </div>
    <div class="container">
        <div class="d-flex align-items-center justify-content-between py-1 px-4 mt-3 text-white bg-dark rounded-top">
            <div class="lh-100">
                <h2 class="text-white"><?php _e('SmartPay Settings', 'smartpay'); ?></h2>
            </div>
            <div>
                <a class="btn btn-dark btn-sm" href="https://wpsmartpay.com/changelog/" target="_blank">v<?php echo SMARTPAY_VERSION; ?></a>
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
                        echo '<a href="' . esc_url($tab_url) . '" class="nav-link' . $active . '">';
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
                    echo '<ul class="nav nav-pills border-bottom px-3 pb-2 mt-n2 mx-n4">';
                    foreach ($sections as $section_id => $section_name) {
                        echo '<li class="nav-item m-0">';
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
                        echo '<a class="' . $class . '" href="' . esc_url($tab_url) . '">' . $section_name . '</a>';

                        echo '</li>';
                    }
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
                    <button class="btn btn-primary smartpay-clear-debug-log"><?php echo __('Clear Log', 'smartpay') ?></button>
                    <?php endif; ?>
                </form>
            </div>
        </div>

    </div>
    <!-- container end -->
</div>
<?php echo ob_get_clean();