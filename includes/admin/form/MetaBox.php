<?php

namespace ThemesGrove\SmartPay\Admin\Form;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
final class MetaBox
{
    /**
     * The single instance of this class.
     */
    private static $instance = null;

    private $post;

    /**
     * Construct MetaBox class.
     *
     * @since 0.1
     */
    private function __construct()
    {
        // Add metabox.
        add_action('add_meta_boxes', [$this, 'add_smartpay_form_meta_box']);
        add_action('save_post', [$this, 'save_smartpay_form_meta']);
    }

    /**
     * Main MetaBox Instance.
     *
     * Ensures that only one instance of MetaBox exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since 0.1
     *
     * @return object|MetaBox
     */
    public static function instance()
    {
        if (!isset(self::$instance) && !(self::$instance instanceof MetaBox)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function add_smartpay_form_meta_box()
    {
        add_meta_box(
            'smartpay-form-metabox-data',
            'Payment Form Options',
            [$this, 'add_smartpay_form_meta_box_callback'],
            ['smartpay_form'],
            'normal',
            'high'
        );
    }

    public function add_smartpay_form_meta_box_callback($post)
    {
        $this->post = $post;

        $fields =
            apply_filters(
                'smartpay_form_meta_box_fields',
                array(
                    '_form_payment_type'    => array(
                        'id'                => '_form_payment_type',
                        'name'              => __('One-Time Amount', 'wp-smartpay'),
                        'type'              => 'radio',
                        'options'           => array(
                            'one-time'      => array(
                                'name'      => __('One-Time', 'wp-smartpay'),
                                'disabled'  => false
                            ),
                            '_'             => array(
                                'name'      => __('Recurring (Available on Pro)', 'wp-smartpay'),
                                'disabled'  => true
                            ),
                        ),
                        'std'               => 'one-time',
                    ),
                    '_form_amount'          => array(
                        'id'                => '_form_amount',
                        'name'              => __('One-Time Amount', 'wp-smartpay'),
                        'type'              => 'text',
                        'placeholder'       => 'Form Amount',
                    ),

                    // On-Page Form Display
                    '_form_payment_button_text'     => array(
                        'id'                => '_form_payment_button_text',
                        'name'              => __('Payment Button Text', 'wp-smartpay'),
                        'type'              => 'text',
                        'placeholder'       => 'Pay with Paddle',
                    ),
                    '_form_payment_button_processing_text'     => array(
                        'id'                => '_form_payment_button_processing_text',
                        'name'              => __('Payment Button Processing Text', 'wp-smartpay'),
                        'type'              => 'text',
                        'placeholder'       => 'Please wait...',
                    ),
                    '_form_payment_button_style'    => array(
                        'id'                => '_form_payment_button_style',
                        'name'              => __('Payment Button Style', 'wp-smartpay'),
                        'type'              => 'radio',
                        'options'           => array(
                            'paddle_green'      => array(
                                'name'      => __('Paddle Green', 'wp-smartpay'),
                            ),
                            'site_default'             => array(
                                'name'      => __('Site Default', 'wp-smartpay'),
                            ),
                        ),
                        'std'               => 'paddle_green',
                    ),

                    // Paddle Checkout Display
                    '_form_paddle_checkout_image'     => array(
                        'id'                => '_form_paddle_checkout_image',
                        'name'              => __('Paddle checkout image', 'wp-smartpay'),
                        'type'              => 'text',
                        'placeholder'       => 'Image link',
                    ),
                    '_form_paddle_checkout_location'    => array(
                        'id'                => '_form_paddle_checkout_location',
                        'name'              => __('Paddle checkout location', 'wp-smartpay'),
                        'type'              => 'radio',
                        'options'           => array(
                            'on_site'      => array(
                                'name'      => __('On site', 'wp-smartpay'),
                            ),
                            'paddle_checkout'             => array(
                                'name'      => __('Paddle checkout', 'wp-smartpay'),
                            ),
                        ),
                        'std'               => 'on_site',
                    ),
                )
            );

        $this->render_form($fields);

        echo '<div style="background-color: #f4f4f4;
        border: 1px solid #e5e5e5;
        padding: 10px 20px;
        margin: 30px 0px;">
        <h2 style=" font-size: 18px;
        font-weight: 600;
        margin: 10px 0;
        padding: 0;">Want to customize your payment forms even more?</h2>
        <p>
            By upgrading to WP Simple Pay Pro, you get access to powerful features such as:</p>
        
        <!-- Repeat this bulleted list in sidebar.php & generic-tab-promo.php -->
        <ul>
            <li><div class="dashicons dashicons-yes"></div> Unlimited custom fields to capture additional data</li>
            <li><div class="dashicons dashicons-yes"></div> Custom amounts - let customers enter an amount to pay</li>
            <li><div class="dashicons dashicons-yes"></div> Coupon code support</li>
            <li><div class="dashicons dashicons-yes"></div> On-site checkout (no redirect) with custom forms</li>
            <li><div class="dashicons dashicons-yes"></div> Embedded &amp; overlay form display options</li>
            <li><div class="dashicons dashicons-yes"></div> Apple Pay &amp; Google Pay support with custom forms</li>
            <li><div class="dashicons dashicons-yes"></div> Stripe Subscription support (Plus or higher license required)</li>
        </ul>
        
        
        <p>
            <a href="https://wpsmartpay.com/" class="button button-primary button-large" target="_blank">
                Click here to Upgrade	</a>
        </p>
        
        </div>';

        // return smartpay_view('admin/form/payment_form_metabox', ['post' => $post]);
    }

    public function save_smartpay_form_meta($post_id)
    {
        // die(var_dump($post_id));

        if (!isset($_POST['smartpay_form_metabox_nonce']) || !wp_verify_nonce($_POST['smartpay_form_metabox_nonce'], 'smartpay_form_metabox_nonce')) {
            return;
        }

        if (isset($_POST['_form_payment_type'])) {
            \update_post_meta($post_id, '_form_payment_type', sanitize_text_field($_POST['_form_payment_type']));
        }

        if (isset($_POST['_form_amount'])) {
            \update_post_meta($post_id, '_form_amount', sanitize_text_field($_POST['_form_amount']));
        }

        if (isset($_POST['_form_payment_button_text'])) {
            \update_post_meta($post_id, '_form_payment_button_text', sanitize_text_field($_POST['_form_payment_button_text']));
        }

        if (isset($_POST['_form_payment_button_processing_text'])) {
            \update_post_meta($post_id, '_form_payment_button_processing_text', sanitize_text_field($_POST['_form_payment_button_processing_text']));
        }

        if (isset($_POST['_form_payment_button_style'])) {
            \update_post_meta($post_id, '_form_payment_button_style', sanitize_text_field($_POST['_form_payment_button_style']));
        }

        if (isset($_POST['_form_paddle_checkout_image'])) {
            \update_post_meta($post_id, '_form_paddle_checkout_image', sanitize_text_field($_POST['_form_paddle_checkout_image']));
        }

        if (isset($_POST['_form_paddle_checkout_location'])) {
            \update_post_meta($post_id, '_form_paddle_checkout_location', sanitize_text_field($_POST['_form_paddle_checkout_location']));
        }
    }

    public function render_form($fields)
    {
        echo '<form action="" method="POST">';
        wp_nonce_field('smartpay_form_metabox_nonce', 'smartpay_form_metabox_nonce');

        echo '<table><tbody class="simpay-panel-section">';

        foreach ($fields as $field) {
            echo '<tr class=""> <td style="padding: 15px 15px 15px 0px;">';
            echo '<label for="' . esc_attr($field['id']) . '">' . esc_attr($field['name']) . '</label></td><td>';

            $func = 'metabox_fields_' . $field['type'] . '_callback';

            method_exists($this,  $func) ? $this->$func($field) : 'Method not found!';

            echo '</td></tr>';
        }

        echo '</tbody></table>';
        echo '</form>';
    }

    public function metabox_fields_text_callback($field)
    {
        $old_value = get_post_meta($this->post->ID, esc_attr($field['id']), true);

        $value =  !empty($old_value) ? $old_value : $field['std'] ?? '';

        $disabled = !empty($field['disabled']) ? ' disabled="disabled"' : '';
        $readonly = $field['readonly'] === true ? ' readonly="readonly"' : '';
        $size = (isset($field['size']) && !is_null($field['size'])) ? $field['size'] : 'regular';

        $html = '<input type="text" class="' . $field['class'] . ' ' . sanitize_html_class($size) . '" name="' . esc_attr($field['id']) . '" id="' . esc_attr($field['id']) . '" value="' . esc_attr(stripslashes($value)) . '" ' . $readonly . $disabled . ' placeholder="' . esc_attr($field['placeholder']) . '" />';
        echo $html;
    }

    function metabox_fields_radio_callback($field)
    {
        $old_value = get_post_meta($this->post->ID, esc_attr($field['id']), true);

        $readonly = $field['readonly'] === true ? ' readonly="readonly"' : '';
        $size = (isset($field['size']) && !is_null($field['size'])) ? $field['size'] : 'regular';

        $html = '';
        foreach ($field['options'] as $key => $option) {
            $checked = false;
            $disabled = $option['disabled'] ?? false;

            if ($old_value && $old_value == $key) {
                $checked = true;
            } elseif (isset($field['std']) && $field['std'] == $key && !$old_value) {
                $checked = true;
            }

            $html .= '<input type="radio" class="' . $field['class'] . ' ' . sanitize_html_class($size) . '" name="' . esc_attr($field['id']) . '" id="' . esc_attr($field['id']) . '_' . $key . '" value="' . esc_attr(stripslashes($key)) . '" ' . $readonly . $disabled . checked(true, $checked, false) . disabled(true, $disabled, false) . '" />&nbsp;';

            $html .= '<label for="' . esc_attr($field['id']) . '_' . $key . '">' . esc_html($option['name'] ?? '') . '</label>&nbsp;&nbsp;';
        }

        echo $html;
    }
}
