<?php

function smartpay_get_product($product_id)
{
    if (!$product_id) {
        return;
    }

    return SmartPay()->product::get_product($product_id);
}
