<?php

namespace SmartPay\Modules\Admin;

class Form
{
    protected $app;

    public function __construct($app)
    {
        $this->app = $app;

        $this->app->addAction('admin_enqueue_scripts',[$this,'adminScripts']);
        // add_action('rest_api_init', [$this, 'registerRestRoutes']);
    }

    public function adminScripts()
    {
        global $current_screen;
        $current_screen->is_block_editor( true );

        wp_enqueue_script( 'blockeditor-script', SMARTPAY_PLUGIN_ASSETS . '/js/block-editor.js', ['lodash', 'wp-block-editor', 'wp-block-library', 'wp-blocks', 'wp-components', 'wp-data', 'wp-dom-ready', 'wp-editor', 'wp-element', 'wp-format-library', 'wp-i18n', 'wp-media-utils', 'wp-polyfill'], '1.0' );

        $settings = [
            'disableCustomColors'    => get_theme_support( 'disable-custom-colors' ),
            'disableCustomFontSizes' => get_theme_support( 'disable-custom-font-sizes' ),
            // 'imageSizes'             => $available_image_sizes,
            'isRTL'                  => is_rtl(),
            // 'maxUploadFileSize'      => $max_upload_size,
            '__experimentalBlockPatterns' => []
        ];

        list( $color_palette, ) = (array) get_theme_support( 'editor-color-palette' );
        list( $font_sizes, )    = (array) get_theme_support( 'editor-font-sizes' );
        if ( false !== $color_palette ) {
            $settings['colors'] = $color_palette;
        }
        if ( false !== $font_sizes ) {
            $settings['fontSizes'] = $font_sizes;
        }

        wp_add_inline_script( 'blockeditor-script', 'window.getdaveSbeSettings = ' . wp_json_encode( $settings ) . ';' );

        // Preload server-registered block schemas.
        wp_add_inline_script(
            'wp-blocks',
            'wp.blocks.unstable__bootstrapServerSideBlockDefinitions(' . wp_json_encode( $settings ) . ');'
        );

        // Editor default styles.
        wp_enqueue_script( 'wp-format-library' );
        wp_enqueue_style( 'wp-format-library' );

        // Styles.
        wp_enqueue_style(
            'blockeditor-styles', // Handle.
            SMARTPAY_PLUGIN_ASSETS . '/css/block-editor.css', // Block editor CSS.
            array( 'wp-edit-blocks' ), // Dependency to include the CSS after it.
            '1.0' // Version: File modification time.
        );
    }

    // public function registerRestRoutes()
    // {
    //     $productController = $this->app->make(ProductRestController::class);

    //     register_rest_route('smartpay/v1/', 'products', [
    //         [
    //             'methods'   => 'POST',
    //             'callback'  => [$productController, 'store'],
    //             'permission_callback' => [$productController, 'middleware'],
    //         ]
    //     ]);
    // }
}