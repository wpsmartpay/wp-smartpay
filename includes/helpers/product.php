<?php

function smartpay_get_product($product_id)
{
    if (!$product_id) return;

    return SmartPay()->product->get_product($product_id);
}

function smartpay_get_product_variation($variation_id)
{
    if (!$variation_id) return;

    return SmartPay()->product->get_product_variation($variation_id);
}

// TODO: Set/Get product meta