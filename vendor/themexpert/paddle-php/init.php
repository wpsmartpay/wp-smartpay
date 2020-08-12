<?php

// Paddle
require(dirname(__FILE__) . '/src/Paddle.php');

// HttpClient
require(dirname(__FILE__) . '/src/HttpClient/ClientInterface.php');
require(dirname(__FILE__) . '/src/HttpClient/CurlClient.php');

// Utilities
require(dirname(__FILE__) . '/src/Util/Util.php');
require(dirname(__FILE__) . '/src/Util/ErrorCodes.php');
require(dirname(__FILE__) . '/src/Util/Currency.php');
require(dirname(__FILE__) . '/src/Util/Price.php');
require(dirname(__FILE__) . '/src/Util/Url.php');

// API Exceptions
require(dirname(__FILE__) . '/src/Exception/ArgumentException.php');

// API Resources
require(dirname(__FILE__) . '/src/ApiResource.php');

// Verify Webhook Token
require(dirname(__FILE__) . '/src/Verify.php');

// Checkout
require(dirname(__FILE__) . '/src/Paddle/Checkout/Order.php');
require(dirname(__FILE__) . '/src/Paddle/Checkout/Price.php');
require(dirname(__FILE__) . '/src/Paddle/Checkout/User.php');

// Product
require(dirname(__FILE__) . '/src/Paddle/Product/Product.php');
require(dirname(__FILE__) . '/src/Paddle/Product/Coupon.php');
require(dirname(__FILE__) . '/src/Paddle/Product/License.php');
require(dirname(__FILE__) . '/src/Paddle/Product/PayLink.php');
require(dirname(__FILE__) . '/src/Paddle/Product/Transaction.php');
require(dirname(__FILE__) . '/src/Paddle/Product/Payment.php');

// Subscription
require(dirname(__FILE__) . '/src/Paddle/Subscription/Plan.php');
require(dirname(__FILE__) . '/src/Paddle/Subscription/User.php');
require(dirname(__FILE__) . '/src/Paddle/Subscription/Modifier.php');
require(dirname(__FILE__) . '/src/Paddle/Subscription/Payment.php');
require(dirname(__FILE__) . '/src/Paddle/Subscription/OneOffCharge.php');

// Alert
require(dirname(__FILE__) . '/src/Paddle/Alert/Webhook.php');