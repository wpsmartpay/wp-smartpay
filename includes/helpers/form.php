<?php
function smartpay_get_form($form_id)
{
    if (!$form_id) return;

    return SmartPay()->form->get_form($form_id);
}
