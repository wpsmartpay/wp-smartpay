<?php

namespace SmartPay;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

final class Session
{
    /**
     * The single instance of this class
     */
    private static $instance = null;

    /**
     * Holds our session data
     *
     * @var array
     * @access private
     * @since 1.5
     */
    private $session;

    /**
     * Session index prefix
     *
     * @var string
     * @access private
     * @since 2.3
     */
    private $session_key = 'SMARTPAY_SESSION';

    /**
     * Construct Session class.
     *
     * @since 0.1
     * @access private
     */
    private function __construct()
    {
        $this->init_session();
    }

    /**
     * Set a session variable
     *
     * @since 1.5
     *
     * @param string $key Session key
     * @param int|string|array $value Session variable
     * @return mixed Session variable
     */
    public function set($key, $value)
    {
        $key = sanitize_key($key);

        if (is_array($value) || is_object($value)) {
            $this->session[$key] = wp_json_encode($value);
        } else {
            $this->session[$key] = esc_attr($value);
        }

        $_SESSION[$this->session_key] = $this->session;

        return $this->session[$key];
    }

    /**
     * Retrieve a session variable
     *
     * @since 1.5
     * @param string $key Session key
     * @return mixed Session variable
     */
    public function get($key)
    {
        $key    = sanitize_key($key);
        $return = false;

        if (isset($this->session[$key]) && !empty($this->session[$key])) {

            preg_match('/[oO]\s*:\s*\d+\s*:\s*"\s*(?!(?i)(stdClass))/', $this->session[$key], $matches);
            if (!empty($matches)) {
                $this->set($key, null);
                return false;
            }

            if (is_numeric($this->session[$key])) {
                $return = $this->session[$key];
            } else {

                $maybe_json = json_decode($this->session[$key]);

                // Since json_last_error is PHP 5.3+, we have to rely on a `null` value for failing to parse JSON.
                if (is_null($maybe_json)) {
                    $is_serialized = is_serialized($this->session[$key]);
                    if ($is_serialized) {
                        $value = @unserialize($this->session[$key]);
                        $this->set($key, (array) $value);
                        $return = $value;
                    } else {
                        $return = $this->session[$key];
                    }
                } else {
                    $return = json_decode($this->session[$key], true);
                }
            }
        }

        return $return;
    }

    // TODO: Implement unset

    public function set_payment_id($payment_id)
    {
        return $this->set('smartpay_payment_id', $payment_id);
    }

    public function get_payment_id()
    {
        return $this->get('smartpay_payment_id');
    }

    public function set_payment_data($payment_data)
    {
        return $this->set('smartpay_payment_data', $payment_data);
    }

    public function get_payment_data()
    {
        return $this->get('smartpay_payment_data');
    }

    /**
     * Main Session Instance.
     *
     * Ensures that only one instance of Session exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since 0.1
     * @return object|Session
     * @access public
     */
    public static function instance()
    {
        if (!isset(self::$instance) && !(self::$instance instanceof Session)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function init_session()
    {
        if (!session_id()) {
            session_start();
        }

        if (is_multisite()) {
            $this->session_key = 'SMARTPAY_SESSION_' . strtoupper(get_current_blog_id());
        }

        $this->session = isset($_SESSION[$this->session_key]) && is_array($_SESSION[$this->session_key]) ? $_SESSION[$this->session_key] : [];
    }
}