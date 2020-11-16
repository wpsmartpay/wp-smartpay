<?php

namespace SmartPay\Models;

use SmartPay\Framework\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $table = 'smartpay_coupons';

    protected $fillable = [
        'title',
        'description',
        'discount_type',
        'discount_amount',
        'status',
        'expiry_date',
        'extra',
    ];

    const PUBLISH   = 'publish';
    const DRAFT     = 'draft';

    public static function boot()
    {
        static::creating(function ($coupon) {
            $time = date('Y-m-d h-i-s');

            $coupon->created_at = $time;
            $coupon->updated_at = $time;
            $coupon->created_by = $coupon->created_by ?: get_current_user_id();
        });
    }

    public function getExpiryDateAttribute($value)
    {
        return date('Y-m-d', strtotime($value));
    }
}