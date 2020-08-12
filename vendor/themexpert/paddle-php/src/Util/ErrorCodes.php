<?php

namespace ThemeXpert\Paddle\Util;

class ErrorCodes
{
    /*
     * 1XX - API response errors
     */

    const ERR_100 = "Unable to find requested license";
    const ERR_101 = "Bad method call";
    const ERR_102 = "Bad api key";
    const ERR_103 = "Timestamp is too old or not valid";
    const ERR_104 = "License code has already been utilized";
    const ERR_105 = "License code is not active";
    const ERR_106 = "Unable to find requested activation";
    const ERR_107 = "You don't have permission to access this resource";
    const ERR_108 = "Unable to find requested product";
    const ERR_109 = "Provided currency is not valid";
    const ERR_110 = "Unable to find requested purchase";
    const ERR_111 = "Invalid authentication token";
    const ERR_112 = "Invalid verification token";
    const ERR_113 = "Invalid padding on decrypted string";
    const ERR_114 = "Invalid or duplicated affiliate";
    const ERR_115 = "Invalid or missing affiliate commission";
    const ERR_116 = "One or more required arguments are missing";
    const ERR_117 = "Provided expiration time is incorrect";
    const ERR_118 = "Price is too low";
    const ERR_119 = "Unable to find requested subscription";
    const ERR_120 = "Request failed due to internal error";
    const ERR_121 = "Unable to find requested payment";
    const ERR_122 = "Provided date is not valid";
    const ERR_123 = "Unable to find requested modifier";
    const ERR_124 = "Modifiers that have already been paid cannot be altered or deleted";
    const ERR_125 = "Main currency price was not provided";
    const ERR_126 = "A valid email address is required, please try again";
    const ERR_127 = "The given coupon type is not recognized. The only valid types are flat and percentage.";
    const ERR_128 = "The given percentage is not valid. The percentage must be a number less than 100.";
    const ERR_129 = "The given amount is not a valid flat amount. The amount must be a number equal to or greater than 0.01.";
    const ERR_130 = "The allowed uses must be a number.";
    const ERR_131 = "The given coupon code is invalid. The code must have at least 5 characters.";
    const ERR_132 = "The given coupon code has already been used for the product.";
    const ERR_133 = "The given coupon expiration date is invalid. The expected date format is “Y-m-d”.";
    const ERR_134 = "The given coupon currency is invalid. The currency must be one of the currencies of your product.";
    const ERR_135 = "Unable to find requested coupon";
    const ERR_136 = "Allowed uses cannot be less than times used.";
    const ERR_137 = "The allowed uses must be a number greater than or equal to 0.";
    const ERR_138 = "The expires at value must be either not provided or a future date in the format of Y-m-d.";
    const ERR_139 = "The given prices format is not valid. The prices must have the format of [‘currency:amount’, ‘currency:amount’, …].";
    const ERR_140 = "The given currency code is unknown to our checkout system.";
    const ERR_141 = "Either a product ID or a plan ID should be given, not both.";
    const ERR_142 = "The given recurring prices format is not valid. The recurring prices must have the format of [‘currency:amount’, ‘currency:amount’, …].";
    const ERR_143 = "Recurring price is too low";
    const ERR_144 = "Affiliate split sum must total less than 100%";
    const ERR_145 = "Recurring affiliate split must either be not set, or set to an integer equal to or greater than 1.";
    const ERR_146 = "The current invoice of this subscription is currently being processed, and cannot be updated at this time";
    const ERR_147 = "We were unable to complete the resubscription because we could not charge the customer for the resubscription";
    const ERR_148 = "The resubscription requires immediate billing so we cannot complete your request";
    const ERR_149 = "The plan interval is invalid";
    const ERR_150 = "Initial price is too low";


    /*
     * 2XX - general errors
     */
    const ERR_200 = 'CURL error: ';
    const ERR_201 = 'Incorrect HTTP response code: ';
    const ERR_202 = 'Incorrect API response: ';
    const ERR_203 = 'Timeout must be a positive integer';
    const ERR_204 = 'Vendor credentials not provided';

    /*
     * 3XX - validation errors
     */
    const ERR_300 = '$product_id must be a positive integer';
    const ERR_301 = '$title must be a string';
    const ERR_302 = '$image_url must be a valid url';
    const ERR_303 = '$price must be a number';
    const ERR_304 = '$price must not be negative';
    const ERR_305 = '$return_url must be a valid url';
    const ERR_306 = '$paypal_cancel_url must be a valid url';
    const ERR_307 = '$expires must be a valid timestamp';
    const ERR_308 = '$expires must be in the future';
    const ERR_309 = '$parent_url must be a valid url';
    const ERR_310 = '$affiliates must be an array';
    const ERR_311 = 'provide $affiliates as key->value contained array with vendor_id->vendor_commission';
    const ERR_312 = '$stylesheets must be an array';
    const ERR_313 = 'provide $stylesheets as key->value contained array with stylesheet_type->stylesheet_code';
    const ERR_314 = '$webhook_url can only be set for custom product';
    const ERR_315 = '$webhook_url must be a valid url';
    const ERR_316 = '$discountable is not allowed for custom product';
    const ERR_317 = '$coupon_code is not allowed for custom product';
    const ERR_318 = '$product_id is not allowed for custom product';
    const ERR_319 = '$limit must be a positive integer';
    const ERR_320 = '$offset must be a non negative integer';
    const ERR_321 = '$start_timestamp must be a timestamp';
    const ERR_322 = '$end_timestamp must be a timestamp';
    const ERR_323 = '$vendor_email must be valid';
    const ERR_324 = '$application_icon_url must be a valid url';
}
