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

    public static function boot()
    {
        static::creating(function ($form) {
            $form->created_by = $form->created_by ?: get_current_user_id();
        });
    }

    public function getFieldsAttribute($fields)
    {
        return \json_decode($fields, true);
    }

    public function setFieldsAttribute($fields)
    {
        $this->attributes['fields'] = \json_encode($fields);
    }
}