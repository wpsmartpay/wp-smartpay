<?php

use SmartPay\Framework\Container\Container;

if (!function_exists('smartpay')) {
    /**
     * Get the available container instance.
     *
     * @param  string|null  $make
     * @param  array  $parameters
     * @return mixed|\SmartPay\Framework\Application
     */
    function smartpay($make = null, array $parameters = [])
    {
        if (is_null($make)) {
            return Container::getInstance();
        }

        return Container::getInstance()->make($make, $parameters);
    }
}

if (!function_exists('base_path')) {
    /**
     * Get the path to the base of the install.
     *
     * @param  string  $path
     * @return string
     */
    function base_path($path = '')
    {
        return smartpay()->basePath() . ($path ? '/' . $path : $path);
    }
}

if (!function_exists('smartpay_view')) {
    /**
     * Get the evaluated view contents for the given view.
     *
     * @param  string  $view
     * @param  array  $data
     * @param  array  $mergeData
     * @return \Illuminate\View\View
     */
    function smartpay_view($view = null, $data = [], $mergeData = [])
    {
        $factory = smartpay('view');

        if (func_num_args() === 0) {
            return $factory;
        }

        return $factory->make($view, $data, $mergeData);
    }
}

if (!function_exists('validator')) {
    /**
     * Create a new Validator instance.
     *
     * @param  array  $data
     * @param  array  $rules
     * @param  array  $messages
     * @param  array  $customAttributes
     * @return \Illuminate\Contracts\Validation\Validator
     */
    function validator($data = [], array $rules = [], array $messages = [], array $customAttributes = [])
    {
        $factory = smartpay('validator');

        if (func_num_args() === 0) {
            return $factory;
        }

        return $factory->make($data, $rules, $messages, $customAttributes);
    }
}

if (!function_exists('dd')) {
    function dd($data)
    {
        foreach (func_get_args() as $arg) {
            echo "<pre>";
            print_r($arg);
            echo "</pre>";
        }
        die;
    }
}
