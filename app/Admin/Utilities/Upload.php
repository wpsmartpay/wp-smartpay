<?php

namespace SmartPay\Admin\Utilities;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

final class Upload
{
    /**
     * The single instance of this class
     */
    private static $instance = null;

    /**
     * Construct Upload class.
     *
     * @since 0.0.4
     * @access private
     */
    private function __construct()
    {
        add_filter('upload_dir', [$this, 'set_smartpay_upload_dir']);

        $this->protect_upload_directory();
    }

    /**
     * Main Upload Instance.
     *
     * Ensures that only one instance of Upload exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since 0.0.4
     * @return object|Upload
     * @access public
     */
    public static function instance()
    {
        if (!isset(self::$instance) && !(self::$instance instanceof Upload)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Separate upload directory for smartpay
     *
     * @since 0.0.4
     * @param $upload
     * @return void
     */
    public function set_smartpay_upload_dir($upload)
    {
        // Get the current post_id
        $id = (isset($_REQUEST['post_id']) ? $_REQUEST['post_id'] : '');

        if ('smartpay_product' == get_post_type($id)) {

            // $this->protect_upload_directory(true);

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

    /**
     * Protect upload directory for product files.
     *
     * This function runs approximately once per day in order to ensure all folders
     * have their necessary protection files
     *
     * @since 0.0.4
     * @param bool $force
     */
    public function protect_upload_directory($force = false)
    {
        if (false === get_transient('smartpay_check_protect_upload_directory') || $force) {

            $upload_path =  wp_upload_dir()['basedir'] . '/smartpay';

            // Make the /smartpay folder
            wp_mkdir_p($upload_path);

            // Top level .htaccess file
            $rules = $this->_get_htaccess_rules();

            if (file_exists($upload_path . '/.htaccess')) {
                $contents = @file_get_contents($upload_path . '/.htaccess');
                if ($contents !== $rules || !$contents) {
                    // Update the .htaccess rules if they don't match
                    @file_put_contents($upload_path . '/.htaccess', $rules);
                }
            } elseif (wp_is_writable($upload_path)) {
                // Create the file if it doesn't exist
                @file_put_contents($upload_path . '/.htaccess', $rules);
            }

            // Top level blank index.php
            if (!file_exists($upload_path . '/index.php') && wp_is_writable($upload_path)) {
                @file_put_contents($upload_path . '/index.php', '<?php' . PHP_EOL . '// SmartPay is awesome!.');
            }

            // Now place index.php files in all sub folders
            $folders = $this->_scan_folders($upload_path);
            foreach ($folders as $folder) {
                // Create index.php, if it doesn't exist
                if (!file_exists($folder . 'index.php') && wp_is_writable($folder)) {
                    @file_put_contents($folder . 'index.php', '<?php' . PHP_EOL . '// SmartPay is awesome!.');
                }
            }

            // Check for the files once per day
            set_transient('smartpay_check_protect_upload_directory', true, 3600 * 24);
        }
    }

    /**
     * Create .htaccess file
     *
     * @since 0.0.4
     * @return string
     */
    private function _get_htaccess_rules()
    {
        // Prevent directory browsing and direct access to all files, except images (must be allowed for featured images / thumbnails)
        $allowed_filetypes = array('jpg', 'jpeg', 'png', 'gif');

        $rules = "Options -Indexes\n";
        $rules .= "deny from all\n";
        $rules .= "<FilesMatch '\.(" . implode('|', $allowed_filetypes) . ")$'>\n";
        $rules .= "Order Allow,Deny\n";
        $rules .= "Allow from all\n";
        $rules .= "</FilesMatch>\n";

        return $rules;
    }

    /**
     * Scans all folders inside of /uploads/smartpay
     *
     * @since 0.0.4
     * @return array $return List of files inside directory
     */
    private function _scan_folders($path = '', $return = array())
    {
        $path = $path == '' ? dirname(__FILE__) : $path;
        $lists = @scandir($path);

        if (!empty($lists)) {
            foreach ($lists as $f) {
                if (is_dir($path . DIRECTORY_SEPARATOR . $f) && $f != "." && $f != "..") {
                    if (!in_array($path . DIRECTORY_SEPARATOR . $f, $return))
                        $return[] = trailingslashit($path . DIRECTORY_SEPARATOR . $f);

                    $this->_scan_folders($path . DIRECTORY_SEPARATOR . $f, $return);
                }
            }
        }

        return $return;
    }
}