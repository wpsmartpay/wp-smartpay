<?php

namespace ThemeXpert\Paddle\Util;

class Price
{
    protected $amount;

    protected $currency;

    public function __construct(string $currency, string $amount)
    {
        $this->currency = new Currency($currency);
        $this->amount = $amount;
    }

    public function __toString()
    {
        return "{$this->currency}:{$this->amount}";
    }
}