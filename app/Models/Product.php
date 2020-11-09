<?php

namespace SmartPay\Models;

use SmartPay\Framework\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'smartpay_products';

    protected $fillable = [
        'title',
        'description',
        'base_price',
        'sale_price',
        'files',
        'parent',
        'status',
    ];

    const PUBLISH = 'Published';

    public function setFilesAttribute($files)
    {
        $this->attributes['files'] = \json_encode($files);
    }

    public function getFilesAttribute($files)
    {
        return \json_decode($files);
    }

    public function variations()
    {
        return $this->hasMany(Product::class, 'parent', 'id');
    }
}