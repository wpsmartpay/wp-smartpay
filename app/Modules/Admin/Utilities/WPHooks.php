<?php

namespace SmartPay\Modules\Admin\Utilities;

defined('ABSPATH') || exit;

class WPHooks
{
    /**
     * Construct Hooks class.
     *
     * @since 0.0.4
     * @access private
     */
    public function __construct()
    {
        add_filter('upload_dir', [$this, 'setSmartPayUploadDir']);
    }

    /**
     * Separate upload directory for smartpay
     *
     * @since 0.0.4
     * @param $upload
     * @return void
     */
    public function setSmartPayUploadDir($upload)
    {
        if (!isset($_SERVER['HTTP_REFERER'])) {
            return $upload;
        }
        preg_match("/^.+?\?page=(.+)$/is", sanitize_text_field(wp_unslash($_SERVER['HTTP_REFERER'])), $match);

        if (isset($match[1]) && $match[1] == 'smartpay') {

            // $upload = new Upload;
            // $upload->protectDirectory();

            // If year/month organization is enabled
            if (get_option('uploads_use_yearmonth_folders')) {

                // Generate the yearly and monthly dirs
                $time = current_time('mysql');
                $y = substr($time, 0, 4);
                $m = substr($time, 5, 2);
                $upload['subdir'] = "/$y/$m";
            }

            $upload['subdir'] = '/smartpay' . $upload['subdir'];
            $upload['path']   = $upload['basedir'] . $upload['subdir'];
            $upload['url']    = $upload['baseurl'] . $upload['subdir'];
        }

        return $upload;
    }
}