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
            $form->extra      = $form->extra ?: [];
        });

        static::created(function ($form) {
            do_action('smartpay_form_created', $form);
        });

        static::updated(function($form){
            do_action('smartpay_form_updated', $form);
        });

        static::deleting(function($form) {
            do_action('smartpay_form_deleted', $form);
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
