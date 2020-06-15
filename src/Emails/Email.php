<?php

namespace SmartPay\Emails;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

final class Email
{
    /**
     * The single instance of this class
     */
    private static $instance = null;

    /**
     * Construct Email class.
     *
     * @since 0.0.1
     * @access private
     */
    private function __construct()
    {
        add_action('phpmailer_init', [$this, 'mailtrap']);
    }

    /**
     * Main Email Instance.
     *
     * Ensures that only one instance of Email exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since 0.0.1
     * @return object|Email
     * @access public
     */
    public static function instance()
    {
        if (!isset(self::$instance) && !(self::$instance instanceof Actions)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    // Mailtrap Config
    public function mailtrap($phpmailer)
    {
        $phpmailer->isSMTP();
        $phpmailer->Host = 'smtp.mailtrap.io';
        $phpmailer->SMTPAuth = true;
        $phpmailer->Port = 2525;
        $phpmailer->Username = '0f2bafb11669af';
        $phpmailer->Password = '6379f0acbb154e';
    }
}