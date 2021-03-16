<?php

namespace SmartPay\Modules\Admin;

class Logger {
    private $filename;
    private $file;
    public $is_writable = true;
    
    public function __construct() {
        //add_filter('smartpay_settings_sections_extensions',[$this,'debugLogSection']);
        //add_filter('smartpay_settings_debug_log',[$this,'debugLogSettings']);
        $this->setup_log_file();
    }

    public function debugLogSection( array $sections ) : array {
        $sections['debug_log'] = __('Debug Log','smartpay-pro');
        return $sections;
    }

    public function debugLogSettings( array $settings ) : array {
        $debug_log_settings = [
            [
                'id'    => 'smartpay_debug_log',
                'name'  => __('Debug Log', 'smartpay'),
                'type'  => 'text'
            ]
        ];
        
        return array_merge($settings, ['debug_log' => $debug_log_settings]);
    }   

    public function setup_log_file() {
        $upload_dir       = wp_upload_dir();
		$this->filename   = wp_hash( home_url( '/' ) ) . '-smartpay-debug.log';
		$this->file       = trailingslashit( $upload_dir['basedir'] ) . $this->filename;

		if ( ! is_writeable( $upload_dir['basedir'] ) ) {
			$this->is_writable = false;
		}
    }

    public function get_file_contents() {
		return $this->get_file();
	}

    public function log_to_file( $message = '' ) {
		$message = date( 'Y-n-d H:i:s' ) . ' - ' . $message . "\r\n";
		$this->write_to_log( $message );
	}

    protected function write_to_log( $message = '' ) {
		$file = $this->get_file();
		$file .= $message;
		@file_put_contents( $this->file, $file );
	}

    protected function get_file() {
		$file = '';

		if ( @file_exists( $this->file ) ) {

			if ( ! is_writeable( $this->file ) ) {
				$this->is_writable = false;
			}

			$file = @file_get_contents( $this->file );

		} else {
			@file_put_contents( $this->file, '' );
			@chmod( $this->file, 0664 );
		}

		return $file;
	}
}