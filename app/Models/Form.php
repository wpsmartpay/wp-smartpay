<?php

namespace SmartPay\Models;

use SmartPay\Framework\Database\Eloquent\Model;

class Form extends Model
{
    protected $table = 'smartpay_forms';

    protected $fillable = [
        'title',
        'body',
        'fields',
        'status',
    ];

    const PUBLISH = 'publish';
    const DRAFT   = 'draft';
    const PENDING = 'pending';

    public static function boot()
    {
        static::creating(function ($form) {
            $form->amounts    = $form->amounts ?: [];
            $form->fields     = $form->fields ?: [];
            $form->settings   = $form->settings ?: [];
            $form->created_by = $form->created_by ?: get_current_user_id();
        });

        static::created(function ($form) {
            $pageArr = [
                'post_title'    => $form->title ?? 'Untitled form',
                'post_status'   => 'publish',
                'post_content'  => '<!-- wp:shortcode -->[smartpay_form id="'.$form->id.'" behavior="embedded" label=""]<!-- /wp:shortcode -->',
                'post_type'     => 'page'
            ];
    
            $pageId = wp_insert_post( $pageArr );
            if( is_wp_error( $pageId ) ) {
                return;
            }
            $form->extra = ['form_preview_page_id' => $pageId,'form_preview_page_permalink' => get_permalink($pageId)];
            $form->save();
        });

        static::updated(function($form){
            $extraFields = $form->extra ?? null;
            if( is_array($extraFields) && array_key_exists('form_preview_page_id',$extraFields) ) {
                return;
            }

            $pageArr = [
                'post_title'    => $form->title ?? 'Untitled form',
                'post_status'   => 'publish',
                'post_content'  => '<!-- wp:shortcode -->[smartpay_form id="'.$form->id.'" behavior="embedded" label=""]<!-- /wp:shortcode -->',
                'post_type'     => 'page'
            ];
    
            $pageId = wp_insert_post( $pageArr );
            if( is_wp_error( $pageId ) ) {
                return;
            }
            $form->extra = ['form_preview_page_id' => $pageId,'form_preview_page_permalink' => get_permalink($pageId)];
            $form->save();
        });

        static::deleting(function($form) {
            $extraFields = $form->extra ?? null;
            if( is_array($extraFields) && array_key_exists('form_preview_page_id',$extraFields) ) {
                wp_delete_post( $extraFields['form_preview_page_id'] );
            }     
        });
        
    }

    public function getAmountsAttribute($amounts)
    {
        return \json_decode($amounts, true);
    }

    public function setAmountsAttribute($amounts)
    {
        $this->attributes['amounts'] = \json_encode($amounts);
    }

    public function getFieldsAttribute($fields)
    {
        return \json_decode($fields, true);
    }

    public function setFieldsAttribute($fields)
    {
        $this->attributes['fields'] = \json_encode($fields);
    }

    public function getSettingsAttribute($settings)
    {
        return \json_decode($settings, true);
    }

    public function setSettingsAttribute($settings)
    {
        $this->attributes['settings'] = \json_encode($settings);
    }

    public function getExtraAttribute($settings)
    {
        return \json_decode($settings, true);
    }

    public function setExtraAttribute($settings)
    {
        $this->attributes['extra'] = \json_encode($settings);
    }
}