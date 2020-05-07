<?php

namespace SmartPay;

/**
 * A helper class for outputting common HTML elements
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

class HTML_Elements
{
    /**
     * Renders an HTML Text field
     *
     * @since 0.1
     *
     * @param array $args Arguments for the text field
     * @return string Text field
     */
    public function text($args = array())
    {
        // Backwards compatibility
        if (func_num_args() > 1) {
            $args = func_get_args();

            $name  = $args[0];
            $value = isset($args[1]) ? $args[1] : '';
            $label = isset($args[2]) ? $args[2] : '';
            $desc  = isset($args[3]) ? $args[3] : '';
        }

        $defaults = array(
            'id'           => '',
            'name'         => isset($name)  ? $name  : 'text',
            'value'        => isset($value) ? $value : null,
            'label'        => isset($label) ? $label : null,
            'desc'         => isset($desc)  ? $desc  : null,
            'placeholder'  => '',
            'class'        => 'regular-text',
            'disabled'     => false,
            'autocomplete' => '',
            'data'         => false
        );

        $args = wp_parse_args($args, $defaults);

        $class = implode(' ', array_map('sanitize_html_class', explode(' ', $args['class'])));
        $disabled = '';
        if ($args['disabled']) {
            $disabled = ' disabled="disabled"';
        }

        $data = '';
        if (!empty($args['data'])) {
            foreach ($args['data'] as $key => $value) {
                $data .= 'data-' . smartpay_sanitize_key($key) . '="' . esc_attr($value) . '" ';
            }
        }

        $output = '<span id="smartpay-' . smartpay_sanitize_key($args['name']) . '-wrap">';
        if (!empty($args['label'])) {
            $output .= '<label class="smartpay-label" for="' . smartpay_sanitize_key($args['id']) . '">' . esc_html($args['label']) . '</label>';
        }

        if (!empty($args['desc'])) {
            $output .= '<span class="smartpay-description">' . esc_html($args['desc']) . '</span>';
        }

        $output .= '<input type="text" name="' . esc_attr($args['name']) . '" id="' . esc_attr($args['id'])  . '" autocomplete="' . esc_attr($args['autocomplete'])  . '" value="' . esc_attr($args['value']) . '" placeholder="' . esc_attr($args['placeholder']) . '" class="' . $class . '" ' . $data . '' . $disabled . '/>';

        $output .= '</span>';

        return $output;
    }

    /**
     * Renders an Bootstrap Switch element
     *
     * @since 1.9
     *
     * @param array $args
     *
     * @return string Switch HTML code
     */
    public function switch($args = array())
    {
        $defaults = array(
            'name'     => null,
            'current'  => null,
            'class'    => 'custom-control-input',
            'label'    => '',
            'options'  => array(
                'disabled' => false,
                'readonly' => false
            )
        );

        $args = wp_parse_args($args, $defaults);

        $class = implode(' ', array_map('sanitize_html_class', explode(' ', $args['class'])));
        $options = '';
        if (!empty($args['options']['disabled'])) {
            $options .= ' disabled="disabled"';
        } elseif (!empty($args['options']['readonly'])) {
            $options .= ' readonly';
        }

        $output = '<div class="custom-control custom-switch">';
        $output .= '<input type="checkbox"' . $options . ' name="' . esc_attr($args['name']) . '" id="' . esc_attr($args['name']) . '_' . esc_attr($args['current']) . '" class="' . $class . ' ' . esc_attr($args['name']) . '" ' . checked(1, $args['current'], false) . ' />';
        $output .= '<label class="custom-control-label" for="' . esc_attr($args['name']) . '_' . esc_attr($args['current']) . '">' . esc_attr($args['label']) . '</label>';
        $output .= '</div>';

        return $output;
    }

    /**
     * Renders an HTML Dropdown
     *
     * @since 1.6
     *
     * @param array $args
     *
     * @return string
     */
    public function select($args = array())
    {
        $defaults = array(
            'options'          => array(),
            'name'             => null,
            'class'            => '',
            'id'               => '',
            'selected'         => array(),
            'chosen'           => false,
            'placeholder'      => null,
            'multiple'         => false,
            'show_option_all'  => _x('All', 'all dropdown items', 'smartpay'),
            'show_option_none' => _x('None', 'no dropdown items', 'smartpay'),
            'data'             => array(),
            'readonly'         => false,
            'disabled'         => false,
        );

        $args = wp_parse_args($args, $defaults);

        $data_elements = '';
        foreach ($args['data'] as $key => $value) {
            $data_elements .= ' data-' . esc_attr($key) . '="' . esc_attr($value) . '"';
        }

        if ($args['multiple']) {
            $multiple = ' MULTIPLE';
        } else {
            $multiple = '';
        }

        if ($args['chosen']) {
            $args['class'] .= ' edd-select-chosen';
            if (is_rtl()) {
                $args['class'] .= ' chosen-rtl';
            }
        }

        if ($args['placeholder']) {
            $placeholder = $args['placeholder'];
        } else {
            $placeholder = '';
        }

        if (isset($args['readonly']) && $args['readonly']) {
            $readonly = ' readonly="readonly"';
        } else {
            $readonly = '';
        }

        if (isset($args['disabled']) && $args['disabled']) {
            $disabled = ' disabled="disabled"';
        } else {
            $disabled = '';
        }

        $class  = implode(' ', array_map('sanitize_html_class', explode(' ', $args['class'])));
        $output = '<select' . $disabled . $readonly . ' name="' . esc_attr($args['name']) . '" id="' . esc_attr(smartpay_sanitize_key(str_replace('-', '_', $args['id']))) . '" class="edd-select ' . $class . '"' . $multiple . ' data-placeholder="' . $placeholder . '"' . $data_elements . '>';

        if (!isset($args['selected']) || (is_array($args['selected']) && empty($args['selected'])) || !$args['selected']) {
            $selected = "";
        }

        if ($args['show_option_all']) {
            if ($args['multiple'] && !empty($args['selected'])) {
                $selected = selected(true, in_array(0, $args['selected']), false);
            } else {
                $selected = selected($args['selected'], 0, false);
            }
            $output .= '<option value="all"' . $selected . '>' . esc_html($args['show_option_all']) . '</option>';
        }

        if (!empty($args['options'])) {
            if ($args['show_option_none']) {
                if ($args['multiple']) {
                    $selected = selected(true, in_array(-1, $args['selected']), false);
                } elseif (isset($args['selected']) && !is_array($args['selected']) && !empty($args['selected'])) {
                    $selected = selected($args['selected'], -1, false);
                }
                $output .= '<option value="-1"' . $selected . '>' . esc_html($args['show_option_none']) . '</option>';
            }

            foreach ($args['options'] as $key => $option) {
                if ($args['multiple'] && is_array($args['selected'])) {
                    $selected = selected(true, in_array((string) $key, $args['selected']), false);
                } elseif (isset($args['selected']) && !is_array($args['selected'])) {
                    $selected = selected($args['selected'], $key, false);
                }

                $output .= '<option value="' . esc_attr($key) . '"' . $selected . '>' . esc_html($option) . '</option>';
            }
        }

        $output .= '</select>';

        return $output;
    }
}
