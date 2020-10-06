<?php

use SmartPay\Integrations;

function smartpay_integrations()
{
    return apply_filters('smartpay_integrations', Integrations::integrations());
}
