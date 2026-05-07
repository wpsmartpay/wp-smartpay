<?php

namespace Illuminate\Contracts\View;
defined('ABSPATH') || exit;

interface Engine
{
    /**
     * Get the evaluated contents of the view.
     *
     * @param  string  $path
     * @param  array  $data
     * @return string
     */
    public function get($path, array $data = []);
}