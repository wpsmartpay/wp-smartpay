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
        'parent_id',
        'status',
    ];

    const PUBLISH = 'publish';

    public static function boot()
    {
        static::creating(function ($form) {
            $form->created_by = $form->created_by ?: get_current_user_id();
        });
    }

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
        return \json_decode($files, true);
    }

    public function getCoversAttribute($covers)
    {
        return \json_decode($covers, true);
    }

    public function getPriceAttribute()
    {
        return ($this->sale_price > -1) ? $this->sale_price : $this->base_price;
    }

    public function getFormattedTitleAttribute()
    {
        if ($this->isVariation()) {
            return "{$this->parent->title} - $this->title";
        }

        return $this->title;
    }

    public function parent()
    {
        return $this->hasOne(Product::class, 'id', 'parent_id');
    }

    public function variations()
    {
        return $this->hasMany(Product::class, 'parent_id', 'id');
    }

    /**
     * Check if product has variations
     *
     * @return boolean
     */
    public function hasVariations(): bool
    {
        if (!property_exists($this, 'variations')) {
            $this->load('variations');
        }

        return !!count($this->variations);
    }

    /**
     * Check if product is variation
     *
     * @return boolean
     */
    public function isVariation(): bool
    {
        if (!property_exists($this, 'parent')) {
            $this->load('parent');
        }

        return !!$this->parent;
    }

    /**
     * Check if product is parent
     *
     * @return boolean
     */
    public function isParent(): bool
    {
        return $this->hasVariations() || !$this->isVariation();
    }

    /**
     * Checks if the product can be purchased
     *
     * @since  0.0.1
     * @return bool
     */
    public function isPurchasable()
    {
        $isPurchasable = true;

        if (self::PUBLISH !== $this->attributes['status']) {
            $isPurchasable = false;
        }

        return (bool) apply_filters('smartpay_product_is_purchasable', $isPurchasable, $this);
    }
}