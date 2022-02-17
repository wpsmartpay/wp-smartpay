<?php

namespace SmartPay\Models;

use SmartPay\Framework\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'smartpay_customers';

    protected $fillable = [
        'first_name',
        'last_name',
        'email_name',
        'notes',
        'extra',
    ];

    public function getFullNameAttribute()
    {
        if ($this->last_name) {
            return "$this->first_name $this->last_name";
        }

        return "$this->first_name";
    }

    public function updateCustomerNotes(array $arr)
    {
        $notes = $arr;
        $this->notes = json_encode($notes);
        $this->save();
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'customer_id', 'id');
    }
}
