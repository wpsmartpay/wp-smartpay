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
            $form->created_by = $form->created_by ?: get_current_user_id();
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
}