<?php

namespace SmartPay\Framework\Concerns;

trait WordPressAction
{

    public function addAction($tag, $function_to_add, $priority = 10, $accepted_args = 0)
    {
        add_action($tag, $function_to_add, $priority, $accepted_args);
    }
}
