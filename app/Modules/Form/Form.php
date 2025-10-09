<?php

namespace SmartPay\Modules\Form;

use SmartPay\Framework\Application;
use SmartPay\Http\Controllers\Rest\Admin\FormController;
use WP_REST_Server;

class Form
{
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;

        $this->app->addAction('admin_enqueue_scripts', [$this, 'adminScripts']);
        $this->app->addAction('rest_api_init', [$this, 'registerRestRoutes']);
        $this->app->addAction('smartpay_form_created', [$this, 'createFormPreviewPage']);
        $this->app->addAction('smartpay_form_updated', [$this, 'updateFormPreviewPage']);
        $this->app->addAction('smartpay_form_deleted', [$this, 'deleteFormPreviewPage']);
    }

    public function adminScripts($hook)
    {
        if ('smartpay_page_smartpay-form' === $hook) {
            $this->registerFormEditor();
            $this->registerBlocks();
        }
    }

    public function editorSettings()
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
        $formController = $this->app->make(FormController::class);

        register_rest_route('smartpay/v1', 'forms', [
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

        register_rest_route('smartpay/v1', 'forms/(?P<id>[\d]+)', [
            [
                'methods'   => WP_REST_Server::READABLE,
                'callback'  => [$formController, 'show'],
                'permission_callback' => [$formController, 'middleware'],
            ],
            [
                'methods'   => 'PUT, PATCH',
                'callback'  => [$formController, 'update'],
                'permission_callback' => [$formController, 'middleware'],
            ],
            [
                'methods'   => WP_REST_Server::DELETABLE,
                'callback'  => [$formController, 'destroy'],
                'permission_callback' => [$formController, 'middleware'],
            ],
        ]);
    }
    /**
     * Register form editor
     *
     * @return void
     */
    public function registerFormEditor()
    {
        global $current_screen;

        $current_screen->is_block_editor(true);

        // Editor
        wp_enqueue_script(
            'smartpay-form',
            SMARTPAY_PLUGIN_ASSETS . '/form-builder/index.js',
            ['lodash', 'wp-block-editor', 'wp-block-library', 'wp-blocks', 'wp-components', 'wp-data', 'wp-dom-ready', 'wp-editor', 'wp-element', 'wp-format-library', 'wp-i18n', 'wp-media-utils', 'wp-plugins', 'wp-polyfill', 'wp-primitives'],
            SMARTPAY_VERSION,
	        false
        );

        wp_localize_script(
            'smartpay-form',
            'smartpay',
            [
                'apiNonce' => wp_create_nonce('wp_rest')
            ]
        );

        // Inline the Editor Settings.
        wp_add_inline_script(
            'smartpay-form',
            'window.smartPayBlockEditorSettings = ' . wp_json_encode($this->editorSettings()) . ';'
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
            SMARTPAY_PLUGIN_ASSETS . '/form-builder/index.css',
            ['wp-edit-blocks'],
            SMARTPAY_VERSION
        );
    }

    public function registerBlocks()
    {
    }

    public function createFormPreviewPage($form)
    {
        $pageArr = [
            'post_title'    => $form->title ?? 'Untitled form',
            'post_status'   => 'publish',
            'post_content'  => '<!-- wp:shortcode -->[smartpay_form id="' . $form->id . '" behavior="embedded" label=""]<!-- /wp:shortcode -->',
            'post_type'     => 'page'
        ];

        $pageId = wp_insert_post($pageArr);
        if (is_wp_error($pageId)) {
            return;
        }
        $form->extra = array_merge($form->extra, ['form_preview_page_id' => $pageId, 'form_preview_page_permalink' => get_permalink($pageId)]);
        $form->save();
    }

    public function updateFormPreviewPage($form)
    {
        $extraFields = $form->extra;
        if (is_array($extraFields) && array_key_exists('form_preview_page_id', $extraFields)) {
            return;
        }

        $pageArr = [
            'post_title'    => $form->title ?? 'Untitled form',
            'post_status'   => 'publish',
            'post_content'  => '<!-- wp:shortcode -->[smartpay_form id="' . $form->id . '" behavior="embedded" label=""]<!-- /wp:shortcode -->',
            'post_type'     => 'page'
        ];

        $pageId = wp_insert_post($pageArr);
        if (is_wp_error($pageId)) {
            return;
        }
        $form->extra = ['form_preview_page_id' => $pageId, 'form_preview_page_permalink' => get_permalink($pageId)];
        $form->save();
    }

    public function deleteFormPreviewPage($form)
    {
        $extraFields = $form->extra;
        if (is_array($extraFields) && array_key_exists('form_preview_page_id', $extraFields)) {
            wp_delete_post($extraFields['form_preview_page_id']);
        }
    }
}
