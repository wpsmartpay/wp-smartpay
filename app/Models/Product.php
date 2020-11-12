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
        'covers',
        'files',
        'parent',
        'status',
    ];

    const PUBLISH = 'publish';

    public function setFilesAttribute($files)
    {
        $this->attributes['files'] = \json_encode($files);
    }

    public function setCoversAttribute($covers)
    {
        $this->attributes['covers'] = \json_encode($covers);
    }

    public function getFilesAttribute($files)
    {
        return \json_decode($files);
    }

    public function getCoversAttribute($covers)
    {
        return \json_decode($covers);
    }

    public function getPriceAttribute()
    {
        return ($this->sale_price > -1) ? $this->sale_price : $this->base_price;
    }

    public function variations()
    {
        return $this->hasMany(Product::class, 'parent', 'id');
    }
}
