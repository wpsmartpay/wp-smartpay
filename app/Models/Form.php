<?php

namespace SmartPay\Models;

use SmartPay\Framework\Database\Eloquent\Model;

class Form extends Model
{
    protected $table = 'smartpay_forms';

    protected $fillable = [
        'title',
        'body',
        'status',
    ];

    const PUBLISH = 'publish';

    public static function boot()
    {
        static::creating(function ($form) {
            $time = date('Y-m-d h-i-s');

            $form->created_at = $time;
            $form->updated_at = $time;
            $form->created_by = $form->created_by ?: get_current_user_id();
        });
    }
}
