<?php

namespace SmartPay\Modules\Admin;

class Logger
{
    private $filename;
    private $file;
    public $is_writable = true;

    public function __construct()
    {
        $this->setup_log_file();
    }

    public function debugLogSection(array $sections): array
    {
        $sections['debug_log'] = __('Debug Log', 'smartpay');
        return $sections;
    }

    public function debugLogSettings(array $settings): array
    {
        $debug_log_settings = [
            [
                'id'    => 'smartpay_debug_log',
                'name'  => __('Debug Log', 'smartpay'),
                'type'  => 'text'
            ]
        ];

        return array_merge($settings, ['debug_log' => $debug_log_settings]);
    }

    public function setup_log_file()
    {
        $upload_dir       = wp_upload_dir();
        $this->filename   = wp_hash(home_url('/')) . '-smartpay-debug.log';
        $this->file       = trailingslashit($upload_dir['basedir']) . $this->filename;

        if (!is_writeable($upload_dir['basedir'])) {
            $this->is_writable = false;
        }
    }

    public function get_file_contents()
    {
        return $this->get_file();
    }

    public function log_to_file($message = '')
    {
        $message = gmdate('Y-n-d H:i:s') . ' - ' . $message . "\r\n";
        $this->write_to_log($message);
    }

    protected function write_to_log($message = '')
    {
        $file = $this->get_file();
        $file .= $message;
        @file_put_contents($this->file, $file);
    }

    protected function get_file()
    {
        $file = '';

        if (@file_exists($this->file)) {

            if (!is_writeable($this->file)) {
                $this->is_writable = false;
            }

            $file = @file_get_contents($this->file);
        } else {
            @file_put_contents($this->file, '');
            @chmod($this->file, 0664);
        }

        return $file;
    }

    public function clear_log_file()
    {
        @unlink($this->file);

        if (
            file_exists($this->file)
        ) {

            // it's still there, so maybe server doesn't have delete rights
            chmod($this->file, 0664); // Try to give the server delete rights
            @unlink($this->file);

            // See if it's still there
            if (
                @file_exists($this->file)
            ) {

                /*
				 * Remove all contents of the log file if we cannot delete it
				 */
                if (is_writeable($this->file)) {

                    file_put_contents($this->file, '');
                } else {

                    return false;
                }
            }
        }

        $this->file = '';
        return true;
    }
}
