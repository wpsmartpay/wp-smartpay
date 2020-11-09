<?php

namespace SmartPay\Modules\Form;

use SmartPay\Http\Controllers\Rest\FormController as FormRestController;
use WP_REST_Server;

class Form
{
    protected $app;

    public function __construct($app)
    {
        $this->app = $app;

        $this->app->addAction('admin_enqueue_scripts', [$this, 'adminScripts']);
        $this->app->addAction('rest_api_init', [$this, 'registerRestRoutes']);
    }

    public function adminScripts($hook)
    {
        if ('smartpay_page_smartpay-forms' !== $hook) {
            return;
        }

        global $current_screen;

        $current_screen->is_block_editor(true);

        wp_enqueue_script(
            'smartpay-form',
            SMARTPAY_PLUGIN_ASSETS . '/block-editor/index.js',
            ['lodash', 'wp-block-editor', 'wp-block-library', 'wp-blocks', 'wp-components', 'wp-data', 'wp-dom-ready', 'wp-editor', 'wp-element', 'wp-format-library', 'wp-i18n', 'wp-media-utils', 'wp-plugins', 'wp-polyfill', 'wp-primitives'],
            SMARTPAY_VERSION,
        );

        // Inline the Editor Settings.
        wp_add_inline_script(
            'smartpay-form',
            'window.smartPayBlockEditorSettings = ' . wp_json_encode($this->block_editor_settings()) . ';'
        );

        // Preload server-registered block schemas.
        wp_add_inline_script(
            'wp-blocks',
            'wp.blocks.unstable__bootstrapServerSideBlockDefinitions(' . wp_json_encode(get_block_editor_server_block_settings()) . ');'
        );

        // Editor default styles.
        wp_enqueue_script('wp-format-library');
        wp_enqueue_style('wp-format-library');

        // Styles.
        wp_enqueue_style(
            'smartpay-form',
            SMARTPAY_PLUGIN_ASSETS . '/block-editor/index.css',
            ['wp-edit-blocks'],
            SMARTPAY_VERSION
        );
    }

    public function block_editor_settings()
    {
        $settings = array(
            'disableCustomColors'    => get_theme_support('disable-custom-colors'),
            'disableCustomFontSizes' => get_theme_support('disable-custom-font-sizes'),
            // 'imageSizes'             => $available_image_sizes,
            'isRTL'                  => is_rtl(),
            // 'maxUploadFileSize'      => $max_upload_size,
            '__experimentalBlockPatterns' => []
        );
        list($color_palette,) = (array) get_theme_support('editor-color-palette');
        list($font_sizes,)    = (array) get_theme_support('editor-font-sizes');
        if (false !== $color_palette) {
            $settings['colors'] = $color_palette;
        }
        if (false !== $font_sizes) {
            $settings['fontSizes'] = $font_sizes;
        }

        return $settings;
    }

    public function registerRestRoutes()
    {
        $formController = $this->app->make(FormRestController::class);

        register_rest_route('smartpay/v1/', 'forms', [
            [
                'methods'   => WP_REST_Server::READABLE,
                'callback'  => [$formController, 'index'],
                'permission_callback' => [$formController, 'middleware'],
            ],
            [
                'methods'   => WP_REST_Server::CREATABLE,
                'callback'  => [$formController, 'store'],
                'permission_callback' => [$formController, 'middleware'],
            ],
        ]);

        register_rest_route('smartpay/v1/', 'forms/(?P<id>[\d]+)', [
            [
                'methods'   => WP_REST_Server::READABLE,
                'callback'  => [$formController, 'view'],
                'permission_callback' => [$formController, 'middleware'],
            ],
            [
                'methods'   => 'PUT, PATCH',
                'callback'  => [$formController, 'update'],
                'permission_callback' => [$formController, 'middleware'],
            ],
            [
                'methods'   => WP_REST_Server::DELETABLE,
                'callback'  => [$formController, 'delete'],
                'permission_callback' => [$formController, 'middleware'],
            ],
        ]);
    }
}