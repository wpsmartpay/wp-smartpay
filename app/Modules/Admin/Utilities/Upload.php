<?php

namespace SmartPay\Modules\Admin\Utilities;

defined('ABSPATH') || exit;

class Upload
{
    /**
     * Construct Upload class.
     *
     * @since 0.0.4
     * @access private
     */
    public function __construct()
    {
        //
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
    public function protectDirectory($force = false)
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