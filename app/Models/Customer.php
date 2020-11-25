<?php

namespace SmartPay\Models;

use SmartPay\Framework\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'smartpay_customers';

    protected $fillable = [];

    public function getFullNameAttribute()
    {
        if ($this->last_name) {
            return "$this->first_name $this->last_name";
        }

        return "$this->first_name";
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'customer_id', 'id');
    }
}
