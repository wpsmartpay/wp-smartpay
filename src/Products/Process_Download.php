<?php

namespace SmartPay\Products;

use SmartPay\Payments\SmartPay_Payment;

// Exit if accessed directly.
if (!defined('ABSPATH')) exit;
final class Process_Download
{
    /**
     * The single instance of this class
     */
    private static $instance = null;

    /**
     * Construct Process_Download class.
     *
     * @since  x.x.x
     * @access private
     */
    private function __construct()
    {
        add_action('init', [$this, 'process_download'], 100);
    }

    /**
     * Main Process_Download Instance.
     *
     * Ensures that only one instance of Process_Download exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since  x.x.x
     * @return object|Process_Download
     * @access public
     */
    public static function instance()
    {
        if (!isset(self::$instance) && !(self::$instance instanceof Process_Download)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Process Download
     *
     * Handles the file download process for product.
     *
     * @since   x.x.x
     * @return  void
     * @access  public
     */
    public function process_download()
    {
        $args = [
            'smartpay_file' => (isset($_GET['smartpay_file'])) ? $_GET['smartpay_file'] : '',
            'ttl'           => (isset($_GET['ttl'])) ? rawurldecode($_GET['ttl']) : '',
            'token'         => (isset($_GET['token'])) ? $_GET['token'] : ''
        ];

        if (empty($args['smartpay_file']) || empty($args['ttl']) || empty($args['token'])) return;

        $validation = $this->_process_signed_download_url();

        if (!is_array($validation) || !$validation['is_valid'] ?? false) {
            wp_die(__('Sorry! Maybe your token is invalid or you don\'t have the access.', 'smartpay'), __('Error', 'smartpay'), array('response' => 403));
        }

        // TODO: Check access

        extract($validation);

        $payment = new SmartPay_Payment($payment_id);

        if (!$payment_id || !$payment || 'publish' !== $payment->status) {
            wp_die(__('Sorry! Payment invalid or not completed yet.', 'smartpay'), __('Error', 'smartpay'), array('response' => 403));
        }

        $product = new SmartPay_Product($product_id);

        if (!$product_id || !$product || !$product->can_purchase()) {
            wp_die(__('Sorry! This product is invalid or don\'t have right permission.', 'smartpay'), __('Error', 'smartpay'), array('response' => 403));
        }

        $variation = new Product_Variation($variation_id);

        if ($variation_id) {
            if (!$variation || !$variation->can_purchase()) {
                wp_die(__('Sorry! This product variation is invalid or don\'t have right permission.', 'smartpay'), __('Error', 'smartpay'), array('response' => 403));
            }

            $download_files = $variation->get_downloadable_files();
        } else {

            $download_files = $product->get_files();
        }

        if (!array_key_exists($file_index, $download_files)) {
            wp_die(__('Sorry! This file doesn\'t exist or don\'t have right permission.', 'smartpay'), __('Error', 'smartpay'), array('response' => 403));
        }

        $download_file  = $download_files[$file_index];
        $requested_file = $download_file['url'];

        $method = 'direct';
        $from_attachment_id = false;
        if ($this->_is_local_file($requested_file) && $download_file['id'] && 'attachment' == get_post_type($download_file['id'])) {

            $attached_file = get_attached_file($download_file['id'], false);

            // Confirm the file exists
            if (!file_exists($attached_file)) {
                $attached_file = false;
            }

            if ($attached_file) {
                $from_attachment_id = true;
                $requested_file     = $attached_file;
            }
        }

        $file_details = parse_url($requested_file);
        $schemes      = array('http', 'https'); // Direct URL schemes

        $supported_streams = stream_get_wrappers();
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN' && isset($file_details['scheme']) && !in_array($file_details['scheme'], $supported_streams)) {
            wp_die(__('Error downloading file. Please contact support.', 'smartpay'), __('File download error', 'smartpay'), 501);
        }

        if ((!isset($file_details['scheme']) || !in_array($file_details['scheme'], $schemes)) && isset($file_details['path']) && file_exists($requested_file)) {

            /**
             * Download method is set to Redirect in settings but an absolute path was provided
             * We need to switch to a direct download in order for the file to download properly
             */
            $method = 'direct';
        }

        $file_extension = smartpay_get_file_extension($requested_file);
        $ctype          = $this->_get_file_ctype($file_extension);

        // Disable time limit
        if (!in_array('set_time_limit', explode(',',  ini_get('disable_functions')))) {
            @set_time_limit(0);
        }

        // If we're using an attachment ID to get the file, even by path, we can ignore this check.
        if (false === $from_attachment_id) {
            $file_is_in_allowed_location = $this->_local_file_location_is_allowed($file_details, $schemes, $requested_file);
            if (false === $file_is_in_allowed_location) {
                wp_die(__('Sorry, this file could not be downloaded.', 'smartpay'), __('Error Downloading File', 'smartpay'), 403);
            }
        }

        // Write session data and end session
        @session_write_close();
        if (function_exists('apache_setenv')) {
            @apache_setenv('no-gzip', 1);
        }
        @ini_set('zlib.output_compression', 'Off');

        nocache_headers();
        header("Robots: none");
        header("Content-Type: " . $ctype . "");
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=\"" . $download_file['filename'] ?? 'file' . "\"");
        header("Content-Transfer-Encoding: binary");

        // If the file isn't locally hosted, process the redirect
        if (filter_var($requested_file, FILTER_VALIDATE_URL) && !$this->_is_local_file($requested_file)) {
            $this->_deliver_download($requested_file, true);
            exit;
        }

        switch ($method) {

            case 'redirect':

                // Redirect straight to the file
                $this->_deliver_download($requested_file, true);
                break;

            case 'direct':
            default:

                $direct    = false;
                $file_path = $requested_file;

                if ((!isset($file_details['scheme']) || !in_array($file_details['scheme'], $schemes)) && isset($file_details['path']) && file_exists($requested_file)) {

                    /** This is an absolute path */
                    $direct    = true;
                    $file_path = $requested_file;
                } else if (defined('UPLOADS') && strpos($requested_file, UPLOADS) !== false) {

                    /**
                     * This is a local file given by URL so we need to figure out the path
                     * UPLOADS is always relative to ABSPATH
                     * site_url() is the URL to where WordPress is installed
                     */
                    $file_path  = str_replace(site_url(), '', $requested_file);
                    $file_path  = realpath(ABSPATH . $file_path);
                    $direct     = true;
                } else if (strpos($requested_file, content_url()) !== false) {

                    /** This is a local file given by URL so we need to figure out the path */
                    $file_path  = str_replace(content_url(), WP_CONTENT_DIR, $requested_file);
                    $file_path  = realpath($file_path);
                    $direct     = true;
                } else if (strpos($requested_file, set_url_scheme(content_url(), 'https')) !== false) {

                    /** This is a local file given by an HTTPS URL so we need to figure out the path */
                    $file_path  = str_replace(set_url_scheme(content_url(), 'https'), WP_CONTENT_DIR, $requested_file);
                    $file_path  = realpath($file_path);
                    $direct     = true;
                }

                // Set the file size header
                header("Content-Length: " . @filesize($file_path));

                // Now deliver the file based on the kind of software the server is running / has enabled
                if (stristr(getenv('SERVER_SOFTWARE'), 'lighttpd')) {

                    header("X-LIGHTTPD-send-file: $file_path");
                } elseif ($direct && (stristr(getenv('SERVER_SOFTWARE'), 'nginx') || stristr(getenv('SERVER_SOFTWARE'), 'cherokee'))) {

                    $ignore_x_accel_redirect_header = false;

                    if (!$ignore_x_accel_redirect_header) {
                        // We need a path relative to the domain
                        $file_path = str_ireplace(realpath($_SERVER['DOCUMENT_ROOT']), '', $file_path);
                        header("X-Accel-Redirect: /$file_path");
                    }
                }

                if ($direct) {

                    $this->_deliver_download($file_path);
                } else {

                    // The file supplied does not have a discoverable absolute path
                    $this->_deliver_download($requested_file, true);
                }

                break;
        }

        smartpay_die();
    }

    /**
     * Deliver the download file
     *
     * If enabled, the file is symlinked to better support large file downloads
     *
     * @since x.x.x
     * @param  string $file
     * @param  bool $redirect
     * @return void
     */
    private function _deliver_download($file = '', $redirect = false)
    {
        /*
        * If symlinks are enabled, a link to the file will be created
        * This symlink is used to hide the true location of the file, even when the file URL is revealed
        * The symlink is deleted after it is used
        */
        if ($this->_symlink_file_downloads() && $this->_is_local_file($file)) {

            $file = $this->_get_local_path_from_url($file);

            // Generate a symbolic link
            $ext       = smartpay_get_file_extension($file);
            $parts     = explode('.', $file);
            $name      = basename($parts[0]);
            $md5       = md5($file);
            $file_name = $name . '_' . substr($md5, 0, -15) . '.' . $ext;
            $path      = smartpay_get_symlink_dir() . '/' . $file_name;
            $url       = smartpay_get_symlink_url() . '/' . $file_name;

            // Set a transient to ensure this symlink is not deleted before it can be used
            set_transient(md5($file_name), '1', 30);

            // Schedule deletion of the symlink
            if (!wp_next_scheduled('smartpay_cleanup_file_symlinks')) {
                wp_schedule_single_event(current_time('timestamp') + 60, 'smartpay_cleanup_file_symlinks');
            }

            // Make sure the symlink doesn't already exist before we create it
            if (!file_exists($path)) {
                $link = @symlink(realpath($file), $path);
            } else {
                $link = true;
            }

            if ($link) {
                // Send the browser to the file
                header('Location: ' . $url);
            } else {
                $this->_readfile_chunked($file);
            }
        } elseif ($redirect) {

            header('Location: ' . $file);
        } else {

            // Read the file and deliver it in chunks
            $this->_readfile_chunked($file);
        }
    }

    /**
     * Determine if the file being requested is hosted locally or not
     *
     * @since  x.x.x
     * @param  string $requested_file The file being requested
     * @return bool If the file is hosted locally or not
     */
    private function _is_local_file($requested_file)
    {
        $home_url       = preg_replace('#^https?://#', '', home_url());
        $requested_file = preg_replace('#^(https?|file)://#', '', $requested_file);

        $is_local_url  = strpos($requested_file, $home_url) === 0;
        $is_local_path = strpos($requested_file, '/') === 0;

        return ($is_local_url || $is_local_path);
    }

    /**
     * Given the URL to a file, determine it's local path
     *
     * Used during the symlink process to determine where to make the symlink point to
     *
     * @since  x.x.x
     * @param  string $url The URL of the file requested
     * @return string If found to be locally hosted, the path to the file
     */
    private function _get_local_path_from_url($url)
    {

        $file       = $url;
        $upload_dir = wp_upload_dir();
        $upload_url = $upload_dir['baseurl'] . '/smartpay';

        if (defined('UPLOADS') && strpos($file, UPLOADS) !== false) {

            /**
             * This is a local file given by URL so we need to figure out the path
             * UPLOADS is always relative to ABSPATH
             * site_url() is the URL to where WordPress is installed
             */
            $file = str_replace(site_url(), '', $file);
        } else if (strpos($file, $upload_url) !== false) {

            /** This is a local file given by URL so we need to figure out the path */
            $file = str_replace($upload_url, smartpay_get_upload_dir(), $file);
        } else if (strpos($file, set_url_scheme($upload_url, 'https')) !== false) {

            /** This is a local file given by an HTTPS URL so we need to figure out the path */
            $file = str_replace(set_url_scheme($upload_url, 'https'), smartpay_get_upload_dir(), $file);
        } elseif (strpos($file, content_url()) !== false) {

            $file = str_replace(content_url(), WP_CONTENT_DIR, $file);
        }

        return $file;
    }

    /**
     * Get the file content type
     *
     * @since x.x.x
     * @param  string $extension file extension
     * @return string content type
     */
    private function _get_file_ctype($extension)
    {
        switch ($extension):
            case 'ac':
                $ctype = "application/pkix-attr-cert";
                break;
            case 'adp':
                $ctype = "audio/adpcm";
                break;
            case 'ai':
                $ctype = "application/postscript";
                break;
            case 'aif':
                $ctype = "audio/x-aiff";
                break;
            case 'aifc':
                $ctype = "audio/x-aiff";
                break;
            case 'aiff':
                $ctype = "audio/x-aiff";
                break;
            case 'air':
                $ctype = "application/vnd.adobe.air-application-installer-package+zip";
                break;
            case 'apk':
                $ctype = "application/vnd.android.package-archive";
                break;
            case 'asc':
                $ctype = "application/pgp-signature";
                break;
            case 'atom':
                $ctype = "application/atom+xml";
                break;
            case 'atomcat':
                $ctype = "application/atomcat+xml";
                break;
            case 'atomsvc':
                $ctype = "application/atomsvc+xml";
                break;
            case 'au':
                $ctype = "audio/basic";
                break;
            case 'aw':
                $ctype = "application/applixware";
                break;
            case 'avi':
                $ctype = "video/x-msvideo";
                break;
            case 'bcpio':
                $ctype = "application/x-bcpio";
                break;
            case 'bin':
                $ctype = "application/octet-stream";
                break;
            case 'bmp':
                $ctype = "image/bmp";
                break;
            case 'boz':
                $ctype = "application/x-bzip2";
                break;
            case 'bpk':
                $ctype = "application/octet-stream";
                break;
            case 'bz':
                $ctype = "application/x-bzip";
                break;
            case 'bz2':
                $ctype = "application/x-bzip2";
                break;
            case 'ccxml':
                $ctype = "application/ccxml+xml";
                break;
            case 'cdmia':
                $ctype = "application/cdmi-capability";
                break;
            case 'cdmic':
                $ctype = "application/cdmi-container";
                break;
            case 'cdmid':
                $ctype = "application/cdmi-domain";
                break;
            case 'cdmio':
                $ctype = "application/cdmi-object";
                break;
            case 'cdmiq':
                $ctype = "application/cdmi-queue";
                break;
            case 'cdf':
                $ctype = "application/x-netcdf";
                break;
            case 'cer':
                $ctype = "application/pkix-cert";
                break;
            case 'cgm':
                $ctype = "image/cgm";
                break;
            case 'class':
                $ctype = "application/octet-stream";
                break;
            case 'cpio':
                $ctype = "application/x-cpio";
                break;
            case 'cpt':
                $ctype = "application/mac-compactpro";
                break;
            case 'crl':
                $ctype = "application/pkix-crl";
                break;
            case 'csh':
                $ctype = "application/x-csh";
                break;
            case 'css':
                $ctype = "text/css";
                break;
            case 'cu':
                $ctype = "application/cu-seeme";
                break;
            case 'davmount':
                $ctype = "application/davmount+xml";
                break;
            case 'dbk':
                $ctype = "application/docbook+xml";
                break;
            case 'dcr':
                $ctype = "application/x-director";
                break;
            case 'deploy':
                $ctype = "application/octet-stream";
                break;
            case 'dif':
                $ctype = "video/x-dv";
                break;
            case 'dir':
                $ctype = "application/x-director";
                break;
            case 'dist':
                $ctype = "application/octet-stream";
                break;
            case 'distz':
                $ctype = "application/octet-stream";
                break;
            case 'djv':
                $ctype = "image/vnd.djvu";
                break;
            case 'djvu':
                $ctype = "image/vnd.djvu";
                break;
            case 'dll':
                $ctype = "application/octet-stream";
                break;
            case 'dmg':
                $ctype = "application/octet-stream";
                break;
            case 'dms':
                $ctype = "application/octet-stream";
                break;
            case 'doc':
                $ctype = "application/msword";
                break;
            case 'docx':
                $ctype = "application/vnd.openxmlformats-officedocument.wordprocessingml.document";
                break;
            case 'dotx':
                $ctype = "application/vnd.openxmlformats-officedocument.wordprocessingml.template";
                break;
            case 'dssc':
                $ctype = "application/dssc+der";
                break;
            case 'dtd':
                $ctype = "application/xml-dtd";
                break;
            case 'dump':
                $ctype = "application/octet-stream";
                break;
            case 'dv':
                $ctype = "video/x-dv";
                break;
            case 'dvi':
                $ctype = "application/x-dvi";
                break;
            case 'dxr':
                $ctype = "application/x-director";
                break;
            case 'ecma':
                $ctype = "application/ecmascript";
                break;
            case 'elc':
                $ctype = "application/octet-stream";
                break;
            case 'emma':
                $ctype = "application/emma+xml";
                break;
            case 'eps':
                $ctype = "application/postscript";
                break;
            case 'epub':
                $ctype = "application/epub+zip";
                break;
            case 'etx':
                $ctype = "text/x-setext";
                break;
            case 'exe':
                $ctype = "application/octet-stream";
                break;
            case 'exi':
                $ctype = "application/exi";
                break;
            case 'ez':
                $ctype = "application/andrew-inset";
                break;
            case 'f4v':
                $ctype = "video/x-f4v";
                break;
            case 'fli':
                $ctype = "video/x-fli";
                break;
            case 'flv':
                $ctype = "video/x-flv";
                break;
            case 'gif':
                $ctype = "image/gif";
                break;
            case 'gml':
                $ctype = "application/srgs";
                break;
            case 'gpx':
                $ctype = "application/gml+xml";
                break;
            case 'gram':
                $ctype = "application/gpx+xml";
                break;
            case 'grxml':
                $ctype = "application/srgs+xml";
                break;
            case 'gtar':
                $ctype = "application/x-gtar";
                break;
            case 'gxf':
                $ctype = "application/gxf";
                break;
            case 'hdf':
                $ctype = "application/x-hdf";
                break;
            case 'hqx':
                $ctype = "application/mac-binhex40";
                break;
            case 'htm':
                $ctype = "text/html";
                break;
            case 'html':
                $ctype = "text/html";
                break;
            case 'ice':
                $ctype = "x-conference/x-cooltalk";
                break;
            case 'ico':
                $ctype = "image/x-icon";
                break;
            case 'ics':
                $ctype = "text/calendar";
                break;
            case 'ief':
                $ctype = "image/ief";
                break;
            case 'ifb':
                $ctype = "text/calendar";
                break;
            case 'iges':
                $ctype = "model/iges";
                break;
            case 'igs':
                $ctype = "model/iges";
                break;
            case 'ink':
                $ctype = "application/inkml+xml";
                break;
            case 'inkml':
                $ctype = "application/inkml+xml";
                break;
            case 'ipfix':
                $ctype = "application/ipfix";
                break;
            case 'jar':
                $ctype = "application/java-archive";
                break;
            case 'jnlp':
                $ctype = "application/x-java-jnlp-file";
                break;
            case 'jp2':
                $ctype = "image/jp2";
                break;
            case 'jpe':
                $ctype = "image/jpeg";
                break;
            case 'jpeg':
                $ctype = "image/jpeg";
                break;
            case 'jpg':
                $ctype = "image/jpeg";
                break;
            case 'js':
                $ctype = "application/javascript";
                break;
            case 'json':
                $ctype = "application/json";
                break;
            case 'jsonml':
                $ctype = "application/jsonml+json";
                break;
            case 'kar':
                $ctype = "audio/midi";
                break;
            case 'latex':
                $ctype = "application/x-latex";
                break;
            case 'lha':
                $ctype = "application/octet-stream";
                break;
            case 'lrf':
                $ctype = "application/octet-stream";
                break;
            case 'lzh':
                $ctype = "application/octet-stream";
                break;
            case 'lostxml':
                $ctype = "application/lost+xml";
                break;
            case 'm3u':
                $ctype = "audio/x-mpegurl";
                break;
            case 'm4a':
                $ctype = "audio/mp4a-latm";
                break;
            case 'm4b':
                $ctype = "audio/mp4a-latm";
                break;
            case 'm4p':
                $ctype = "audio/mp4a-latm";
                break;
            case 'm4u':
                $ctype = "video/vnd.mpegurl";
                break;
            case 'm4v':
                $ctype = "video/x-m4v";
                break;
            case 'm21':
                $ctype = "application/mp21";
                break;
            case 'ma':
                $ctype = "application/mathematica";
                break;
            case 'mac':
                $ctype = "image/x-macpaint";
                break;
            case 'mads':
                $ctype = "application/mads+xml";
                break;
            case 'man':
                $ctype = "application/x-troff-man";
                break;
            case 'mar':
                $ctype = "application/octet-stream";
                break;
            case 'mathml':
                $ctype = "application/mathml+xml";
                break;
            case 'mbox':
                $ctype = "application/mbox";
                break;
            case 'me':
                $ctype = "application/x-troff-me";
                break;
            case 'mesh':
                $ctype = "model/mesh";
                break;
            case 'metalink':
                $ctype = "application/metalink+xml";
                break;
            case 'meta4':
                $ctype = "application/metalink4+xml";
                break;
            case 'mets':
                $ctype = "application/mets+xml";
                break;
            case 'mid':
                $ctype = "audio/midi";
                break;
            case 'midi':
                $ctype = "audio/midi";
                break;
            case 'mif':
                $ctype = "application/vnd.mif";
                break;
            case 'mods':
                $ctype = "application/mods+xml";
                break;
            case 'mov':
                $ctype = "video/quicktime";
                break;
            case 'movie':
                $ctype = "video/x-sgi-movie";
                break;
            case 'm1v':
                $ctype = "video/mpeg";
                break;
            case 'm2v':
                $ctype = "video/mpeg";
                break;
            case 'mp2':
                $ctype = "audio/mpeg";
                break;
            case 'mp2a':
                $ctype = "audio/mpeg";
                break;
            case 'mp21':
                $ctype = "application/mp21";
                break;
            case 'mp3':
                $ctype = "audio/mpeg";
                break;
            case 'mp3a':
                $ctype = "audio/mpeg";
                break;
            case 'mp4':
                $ctype = "video/mp4";
                break;
            case 'mp4s':
                $ctype = "application/mp4";
                break;
            case 'mpe':
                $ctype = "video/mpeg";
                break;
            case 'mpeg':
                $ctype = "video/mpeg";
                break;
            case 'mpg':
                $ctype = "video/mpeg";
                break;
            case 'mpg4':
                $ctype = "video/mpeg";
                break;
            case 'mpga':
                $ctype = "audio/mpeg";
                break;
            case 'mrc':
                $ctype = "application/marc";
                break;
            case 'mrcx':
                $ctype = "application/marcxml+xml";
                break;
            case 'ms':
                $ctype = "application/x-troff-ms";
                break;
            case 'mscml':
                $ctype = "application/mediaservercontrol+xml";
                break;
            case 'msh':
                $ctype = "model/mesh";
                break;
            case 'mxf':
                $ctype = "application/mxf";
                break;
            case 'mxu':
                $ctype = "video/vnd.mpegurl";
                break;
            case 'nc':
                $ctype = "application/x-netcdf";
                break;
            case 'oda':
                $ctype = "application/oda";
                break;
            case 'oga':
                $ctype = "application/ogg";
                break;
            case 'ogg':
                $ctype = "application/ogg";
                break;
            case 'ogx':
                $ctype = "application/ogg";
                break;
            case 'omdoc':
                $ctype = "application/omdoc+xml";
                break;
            case 'onetoc':
                $ctype = "application/onenote";
                break;
            case 'onetoc2':
                $ctype = "application/onenote";
                break;
            case 'onetmp':
                $ctype = "application/onenote";
                break;
            case 'onepkg':
                $ctype = "application/onenote";
                break;
            case 'opf':
                $ctype = "application/oebps-package+xml";
                break;
            case 'oxps':
                $ctype = "application/oxps";
                break;
            case 'p7c':
                $ctype = "application/pkcs7-mime";
                break;
            case 'p7m':
                $ctype = "application/pkcs7-mime";
                break;
            case 'p7s':
                $ctype = "application/pkcs7-signature";
                break;
            case 'p8':
                $ctype = "application/pkcs8";
                break;
            case 'p10':
                $ctype = "application/pkcs10";
                break;
            case 'pbm':
                $ctype = "image/x-portable-bitmap";
                break;
            case 'pct':
                $ctype = "image/pict";
                break;
            case 'pdb':
                $ctype = "chemical/x-pdb";
                break;
            case 'pdf':
                $ctype = "application/pdf";
                break;
            case 'pki':
                $ctype = "application/pkixcmp";
                break;
            case 'pkipath':
                $ctype = "application/pkix-pkipath";
                break;
            case 'pfr':
                $ctype = "application/font-tdpfr";
                break;
            case 'pgm':
                $ctype = "image/x-portable-graymap";
                break;
            case 'pgn':
                $ctype = "application/x-chess-pgn";
                break;
            case 'pgp':
                $ctype = "application/pgp-encrypted";
                break;
            case 'pic':
                $ctype = "image/pict";
                break;
            case 'pict':
                $ctype = "image/pict";
                break;
            case 'pkg':
                $ctype = "application/octet-stream";
                break;
            case 'png':
                $ctype = "image/png";
                break;
            case 'pnm':
                $ctype = "image/x-portable-anymap";
                break;
            case 'pnt':
                $ctype = "image/x-macpaint";
                break;
            case 'pntg':
                $ctype = "image/x-macpaint";
                break;
            case 'pot':
                $ctype = "application/vnd.ms-powerpoint";
                break;
            case 'potx':
                $ctype = "application/vnd.openxmlformats-officedocument.presentationml.template";
                break;
            case 'ppm':
                $ctype = "image/x-portable-pixmap";
                break;
            case 'pps':
                $ctype = "application/vnd.ms-powerpoint";
                break;
            case 'ppsx':
                $ctype = "application/vnd.openxmlformats-officedocument.presentationml.slideshow";
                break;
            case 'ppt':
                $ctype = "application/vnd.ms-powerpoint";
                break;
            case 'pptx':
                $ctype = "application/vnd.openxmlformats-officedocument.presentationml.presentation";
                break;
            case 'prf':
                $ctype = "application/pics-rules";
                break;
            case 'ps':
                $ctype = "application/postscript";
                break;
            case 'psd':
                $ctype = "image/photoshop";
                break;
            case 'qt':
                $ctype = "video/quicktime";
                break;
            case 'qti':
                $ctype = "image/x-quicktime";
                break;
            case 'qtif':
                $ctype = "image/x-quicktime";
                break;
            case 'ra':
                $ctype = "audio/x-pn-realaudio";
                break;
            case 'ram':
                $ctype = "audio/x-pn-realaudio";
                break;
            case 'ras':
                $ctype = "image/x-cmu-raster";
                break;
            case 'rdf':
                $ctype = "application/rdf+xml";
                break;
            case 'rgb':
                $ctype = "image/x-rgb";
                break;
            case 'rm':
                $ctype = "application/vnd.rn-realmedia";
                break;
            case 'rmi':
                $ctype = "audio/midi";
                break;
            case 'roff':
                $ctype = "application/x-troff";
                break;
            case 'rss':
                $ctype = "application/rss+xml";
                break;
            case 'rtf':
                $ctype = "text/rtf";
                break;
            case 'rtx':
                $ctype = "text/richtext";
                break;
            case 'sgm':
                $ctype = "text/sgml";
                break;
            case 'sgml':
                $ctype = "text/sgml";
                break;
            case 'sh':
                $ctype = "application/x-sh";
                break;
            case 'shar':
                $ctype = "application/x-shar";
                break;
            case 'sig':
                $ctype = "application/pgp-signature";
                break;
            case 'silo':
                $ctype = "model/mesh";
                break;
            case 'sit':
                $ctype = "application/x-stuffit";
                break;
            case 'skd':
                $ctype = "application/x-koan";
                break;
            case 'skm':
                $ctype = "application/x-koan";
                break;
            case 'skp':
                $ctype = "application/x-koan";
                break;
            case 'skt':
                $ctype = "application/x-koan";
                break;
            case 'sldx':
                $ctype = "application/vnd.openxmlformats-officedocument.presentationml.slide";
                break;
            case 'smi':
                $ctype = "application/smil";
                break;
            case 'smil':
                $ctype = "application/smil";
                break;
            case 'snd':
                $ctype = "audio/basic";
                break;
            case 'so':
                $ctype = "application/octet-stream";
                break;
            case 'spl':
                $ctype = "application/x-futuresplash";
                break;
            case 'spx':
                $ctype = "audio/ogg";
                break;
            case 'src':
                $ctype = "application/x-wais-source";
                break;
            case 'stk':
                $ctype = "application/hyperstudio";
                break;
            case 'sv4cpio':
                $ctype = "application/x-sv4cpio";
                break;
            case 'sv4crc':
                $ctype = "application/x-sv4crc";
                break;
            case 'svg':
                $ctype = "image/svg+xml";
                break;
            case 'swf':
                $ctype = "application/x-shockwave-flash";
                break;
            case 't':
                $ctype = "application/x-troff";
                break;
            case 'tar':
                $ctype = "application/x-tar";
                break;
            case 'tcl':
                $ctype = "application/x-tcl";
                break;
            case 'tex':
                $ctype = "application/x-tex";
                break;
            case 'texi':
                $ctype = "application/x-texinfo";
                break;
            case 'texinfo':
                $ctype = "application/x-texinfo";
                break;
            case 'tif':
                $ctype = "image/tiff";
                break;
            case 'tiff':
                $ctype = "image/tiff";
                break;
            case 'torrent':
                $ctype = "application/x-bittorrent";
                break;
            case 'tr':
                $ctype = "application/x-troff";
                break;
            case 'tsv':
                $ctype = "text/tab-separated-values";
                break;
            case 'txt':
                $ctype = "text/plain";
                break;
            case 'ustar':
                $ctype = "application/x-ustar";
                break;
            case 'vcd':
                $ctype = "application/x-cdlink";
                break;
            case 'vrml':
                $ctype = "model/vrml";
                break;
            case 'vsd':
                $ctype = "application/vnd.visio";
                break;
            case 'vss':
                $ctype = "application/vnd.visio";
                break;
            case 'vst':
                $ctype = "application/vnd.visio";
                break;
            case 'vsw':
                $ctype = "application/vnd.visio";
                break;
            case 'vxml':
                $ctype = "application/voicexml+xml";
                break;
            case 'wav':
                $ctype = "audio/x-wav";
                break;
            case 'wbmp':
                $ctype = "image/vnd.wap.wbmp";
                break;
            case 'wbmxl':
                $ctype = "application/vnd.wap.wbxml";
                break;
            case 'wm':
                $ctype = "video/x-ms-wm";
                break;
            case 'wml':
                $ctype = "text/vnd.wap.wml";
                break;
            case 'wmlc':
                $ctype = "application/vnd.wap.wmlc";
                break;
            case 'wmls':
                $ctype = "text/vnd.wap.wmlscript";
                break;
            case 'wmlsc':
                $ctype = "application/vnd.wap.wmlscriptc";
                break;
            case 'wmv':
                $ctype = "video/x-ms-wmv";
                break;
            case 'wmx':
                $ctype = "video/x-ms-wmx";
                break;
            case 'wrl':
                $ctype = "model/vrml";
                break;
            case 'xbm':
                $ctype = "image/x-xbitmap";
                break;
            case 'xdssc':
                $ctype = "application/dssc+xml";
                break;
            case 'xer':
                $ctype = "application/patch-ops-error+xml";
                break;
            case 'xht':
                $ctype = "application/xhtml+xml";
                break;
            case 'xhtml':
                $ctype = "application/xhtml+xml";
                break;
            case 'xla':
                $ctype = "application/vnd.ms-excel";
                break;
            case 'xlam':
                $ctype = "application/vnd.ms-excel.addin.macroEnabled.12";
                break;
            case 'xlc':
                $ctype = "application/vnd.ms-excel";
                break;
            case 'xlm':
                $ctype = "application/vnd.ms-excel";
                break;
            case 'xls':
                $ctype = "application/vnd.ms-excel";
                break;
            case 'xlsx':
                $ctype = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
                break;
            case 'xlsb':
                $ctype = "application/vnd.ms-excel.sheet.binary.macroEnabled.12";
                break;
            case 'xlt':
                $ctype = "application/vnd.ms-excel";
                break;
            case 'xltx':
                $ctype = "application/vnd.openxmlformats-officedocument.spreadsheetml.template";
                break;
            case 'xlw':
                $ctype = "application/vnd.ms-excel";
                break;
            case 'xml':
                $ctype = "application/xml";
                break;
            case 'xpm':
                $ctype = "image/x-xpixmap";
                break;
            case 'xsl':
                $ctype = "application/xml";
                break;
            case 'xslt':
                $ctype = "application/xslt+xml";
                break;
            case 'xul':
                $ctype = "application/vnd.mozilla.xul+xml";
                break;
            case 'xwd':
                $ctype = "image/x-xwindowdump";
                break;
            case 'xyz':
                $ctype = "chemical/x-xyz";
                break;
            case 'zip':
                $ctype = "application/zip";
                break;
            default:
                $ctype = "application/force-download";
        endswitch;

        if (wp_is_mobile()) {
            $ctype = 'application/octet-stream';
        }

        return $ctype;
    }

    /**
     * Reads file in chunks so big downloads are possible without changing PHP.INI
     * See http://codeigniter.com/wiki/Download_helper_for_large_files/
     *
     * @since x.x.x
     * @param   string  $file The file
     * @param   boolean $retbytes Return the bytes of file
     * @return  bool|string If string, $status || $cnt
     */
    private function _readfile_chunked($file, $retbytes = true)
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        ob_start();

        // If output buffers exist, make sure they are closed.
        if (ob_get_length()) {
            ob_clean();
        }

        $chunksize = 1024 * 1024;
        $buffer    = '';
        $cnt       = 0;
        $handle    = @fopen($file, 'rb');

        if ($size = @filesize($file)) {
            header("Content-Length: " . $size);
        }

        if (false === $handle) {
            return false;
        }

        if (isset($_SERVER['HTTP_RANGE'])) {
            list($size_unit, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
            if ('bytes' === $size_unit) {
                if (strpos(',', $range)) {
                    list($range) = explode(',', $range, 1);
                }
            } else {
                $range = '';
                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                exit;
            }
        } else {
            $range = '';
        }

        if (empty($range)) {
            $seek_start = null;
            $seek_end   = null;
        } else {
            list($seek_start, $seek_end) = explode('-', $range, 2);
        }

        $seek_end   = (empty($seek_end)) ? ($size - 1) : min(abs(intval($seek_end)), ($size - 1));
        $seek_start = (empty($seek_start) || $seek_end < abs(intval($seek_start))) ? 0 : max(abs(intval($seek_start)), 0);

        // Only send partial content header if downloading a piece of the file (IE workaround)
        if ($seek_start > 0 || $seek_end < ($size - 1)) {
            header('HTTP/1.1 206 Partial Content');
            header('Content-Range: bytes ' . $seek_start . '-' . $seek_end . '/' . $size);
            header('Content-Length: ' . ($seek_end - $seek_start + 1));
        } else {
            header("Content-Length: $size");
        }

        header('Accept-Ranges: bytes');

        // Disable time limit
        if (!in_array('set_time_limit', explode(',',  ini_get('disable_functions')))) {
            @set_time_limit(0);
        }

        fseek($handle, $seek_start);

        while (!@feof($handle)) {
            $buffer = @fread($handle, $chunksize);
            echo $buffer;
            ob_flush();

            if ($retbytes) {
                $cnt += strlen($buffer);
            }

            if (connection_status() != 0) {
                @fclose($handle);
                exit;
            }
        }

        ob_flush();

        $status = @fclose($handle);

        if ($retbytes && $status) {
            return $cnt;
        }

        return $status;
    }

    /**
     * Determines if we should use symbolic links during the file download process
     *
     * @since  x.x.x
     * @return bool
     */
    private function _symlink_file_downloads()
    {
        // TODO: Add option
        $symlink = smartpay_get_option('symlink_file_downloads', false) && function_exists('symlink');
        return $symlink;
    }

    /**
     * Determines if a file should be allowed to be downloaded by making sure it's within the wp-content directory.
     *
     * @since x.x.x
     * @param $file_details
     * @param $schemas
     * @param $requested_file
     *
     * @return boolean
     */
    private function _local_file_location_is_allowed($file_details, $schemas, $requested_file)
    {

        // If the file is an absolute path, make sure it's in the wp-content directory, to prevent store owners from accidentally allowing privileged files from being downloaded.
        if ((!isset($file_details['scheme']) || !in_array($file_details['scheme'], $schemas)) && isset($file_details['path'])) {

            /** This is an absolute path */
            $requested_file         = wp_normalize_path(realpath($requested_file));
            $normalized_abspath     = wp_normalize_path(ABSPATH);
            $normalized_content_dir = wp_normalize_path(WP_CONTENT_DIR);

            if (0 !== strpos($requested_file, $normalized_abspath) || false === strpos($requested_file, $normalized_content_dir)) {
                // If the file is not within the WP_CONTENT_DIR, it should not be able to be downloaded.
                return false;
            }
        }

        return true;
    }

    /**
     * Get file download url with tocken
     *
     * @since x.x.x
     * @param int $file_index
     * @param int $payment_id
     * @param int $product_id
     * @param int $variation_id
     * @return string
     */
    public function get_file_download_url($file_index, $payment_id, $product_id, $variation_id = 0)
    {
        $payment = new SmartPay_Payment($payment_id);

        // TODO: Check payment, product, variation and files
        if (!$payment_id || !$payment) return;

        // TODO: Add option to set max hours
        $hours = 6;

        if (!($expire = strtotime('+' . $hours . 'hours', current_time('timestamp')))) {
            $expire = 2147472000; // Highest possible date, January 19, 2038
        }

        // TODO: Replace file id to index
        $params = [
            'file'         => (int) $file_index,
            'payment_id'   => (int) $payment_id,
            'product_id'   => (int) $product_id,
            'variation_id' => (int) $variation_id,
            'ttl'          => rawurlencode($expire)
        ];

        $smartpay_file = rawurlencode(
            sprintf('%d:%d:%d:%d', $params['file'], $params['payment_id'], $params['product_id'], $params['variation_id'])
        );

        $args = [
            'smartpay_file' => $smartpay_file,
            'ttl'           => $params['ttl'],
        ];

        $args['token'] = $this->_generate_token(add_query_arg($args, untrailingslashit(site_url())));

        return add_query_arg($args, site_url('index.php'));
    }

    /**
     * Generate token
     *
     * @since x.x.x
     * @param string $url
     * @return string token
     */
    private function _generate_token($url)
    {
        $args      = [];
        $hash_algo = 'SHA256';
        $secret    = hash($hash_algo, wp_salt());

        $parts   = parse_url($url);

        $args['ip'] = smartpay_get_ip();

        $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $args['user_agent'] = rawurlencode($ua);
        $args['secret'] = $secret;
        $args['token']  = false; // Removes a token if present.

        $url   = add_query_arg($args, $url);
        $parts = parse_url($url);

        // In the event there isn't a path, set an empty one so we can MD5 the token
        if (!isset($parts['path'])) {
            $parts['path'] = '';
        }

        $token = hash_hmac('sha256', $parts['path'] . '?' . $parts['query'], wp_salt('smartpay_file_download_link'));
        return $token;
    }

    /**
     * Check token is valid and have right permission
     *
     * @since x.x.x
     * @return array
     */
    private function _process_signed_download_url()
    {
        $response = [
            'is_valid' => false
        ];

        $parts = parse_url(add_query_arg(array()));
        wp_parse_str($parts['query'], $query_args);
        $url = add_query_arg($query_args, site_url());

        $valid_token = $this->_validate_token($url);

        if (!$valid_token) return $response;

        $file_parts = explode(':', rawurldecode($_GET['smartpay_file']));

        // TODO: Implement download limit

        $response['is_valid']     = true;
        $response['file_index']   = $file_parts[0];
        $response['payment_id']   = $file_parts[1];
        $response['product_id']   = $file_parts[2];
        $response['variation_id'] = $file_parts[3];
        $response['expire']       = $file_parts[4];

        return $response;
    }

    /**
     * Check is token valid or not
     *
     * @since x.x.x
     * @param string $url
     * @return bool
     */
    private function _validate_token($url = '')
    {
        $parts = parse_url($url);

        if (!isset($parts['query'])) return false;

        wp_parse_str($parts['query'], $query_args);

        if (isset($query_args['ttl']) && current_time('timestamp') > $query_args['ttl']) {
            wp_die(__('Sorry but your download link has expired.', 'smartpay'), __('Error', 'smartpay'), array('response' => 403));
        }

        if (!isset($query_args['token']) || !hash_equals($query_args['token'], $this->_generate_token($url))) return false;

        return true;
    }
}