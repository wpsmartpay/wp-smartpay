<?php

namespace SmartPay\Models;

use SmartPay\Framework\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'smartpay_customers';

    protected $fillable = [];

    public function payments()
    {
        return $this->hasMany(Payment::class, 'customer_id', 'id');
    }
}