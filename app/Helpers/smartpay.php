<?php

require_once __DIR__ . '/integration.php';

use SmartPay\Modules\Gateway\Gateway;
use SmartPay\Modules\Payment\Payment;

function smartpay_svg_icon()
{
    return 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pg0KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDE5LjAuMCwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPg0KPHN2ZyB2ZXJzaW9uPSIxLjEiIGlkPSJDYXBhXzEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4Ig0KCSB2aWV3Qm94PSIwIDAgNTEyIDUxMiIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgNTEyIDUxMjsiIHhtbDpzcGFjZT0icHJlc2VydmUiPg0KPGc+DQoJPGc+DQoJCTxwYXRoIGQ9Ik0yMjQsMTU5Ljk5MnYtMzJIMzJjLTE3LjYzMiwwLTMyLDE0LjM2OC0zMiwzMnY2NGgyMzAuNzUyQzIyNi4zMDQsMjA0LjQ0LDIyNCwxODMuMzg0LDIyNCwxNTkuOTkyeiIvPg0KCTwvZz4NCjwvZz4NCjxnPg0KCTxnPg0KCQk8cGF0aCBkPSJNNTEwLjY4OCwyODcuOTkyYy0yMS44MjQsMzMuNjMyLTU1LjEwNCw2Mi4yNC0xMDIuNzg0LDg5LjYzMmMtNy4zMjgsNC4xOTItMTUuNTg0LDYuMzY4LTIzLjkwNCw2LjM2OA0KCQkJcy0xNi41NzYtMi4xNzYtMjMuODA4LTYuMzA0Yy00Ny42OC0yNy40NTYtODAuOTYtNTYuMDk2LTEwMi44MTYtODkuNjk2SDB2MTYwYzAsMTcuNjY0LDE0LjM2OCwzMiwzMiwzMmg0NDgNCgkJCWMxNy42NjQsMCwzMi0xNC4zMzYsMzItMzJ2LTE2MEg1MTAuNjg4eiBNMTQ0LDM4My45OTJIODBjLTguODMyLDAtMTYtNy4xNjgtMTYtMTZjMC04LjgzMiw3LjE2OC0xNiwxNi0xNmg2NA0KCQkJYzguODMyLDAsMTYsNy4xNjgsMTYsMTZDMTYwLDM3Ni44MjQsMTUyLjgzMiwzODMuOTkyLDE0NCwzODMuOTkyeiIvPg0KCTwvZz4NCjwvZz4NCjxnPg0KCTxnPg0KCQk8cGF0aCBkPSJNNTAyLjMwNCw4MS4zMDRsLTExMi00OGMtNC4wNjQtMS43MjgtOC41NzYtMS43MjgtMTIuNjQsMGwtMTEyLDQ4QzI1OS44MDgsODMuOCwyNTYsODkuNTkyLDI1Niw5NS45OTJ2NjQNCgkJCWMwLDg4LjAzMiwzMi41NDQsMTM5LjQ4OCwxMjAuMDMyLDE4OS44ODhjMi40NjQsMS40MDgsNS4yMTYsMi4xMTIsNy45NjgsMi4xMTJzNS41MDQtMC43MDQsNy45NjgtMi4xMTINCgkJCUM0NzkuNDU2LDI5OS42MDgsNTEyLDI0OC4xNTIsNTEyLDE1OS45OTJ2LTY0QzUxMiw4OS41OTIsNTA4LjE5Miw4My44LDUwMi4zMDQsODEuMzA0eiBNNDQ0LjUxMiwxNTQuMDA4bC02NCw4MA0KCQkJYy0zLjA3MiwzLjc3Ni03LjY4LDUuOTg0LTEyLjUxMiw1Ljk4NGMtMC4yMjQsMC0wLjQ4LDAtMC42NzIsMGMtNS4wODgtMC4yMjQtOS43OTItMi44NDgtMTIuNjQtNy4xMDRsLTMyLTQ4DQoJCQljLTQuODk2LTcuMzYtMi45MTItMTcuMjgsNC40NDgtMjIuMTc2YzcuMjk2LTQuODY0LDE3LjI0OC0yLjk0NCwyMi4xNzYsNC40NDhsMTkuODcyLDI5Ljc5Mmw1MC4zMDQtNjIuOTEyDQoJCQljNS41MzYtNi44OCwxNS42MTYtNy45NjgsMjIuNDk2LTIuNDk2QzQ0OC44OTYsMTM3LjAxNiw0NDkuOTg0LDE0Ny4wOTYsNDQ0LjUxMiwxNTQuMDA4eiIvPg0KCTwvZz4NCjwvZz4NCjwvc3ZnPg0K';
}

function smartpay_amount_format($amount, $currency = '')
{
    if (empty($currency)) {
        $currency = smartpay_get_option('currency', 'USD');
    }

    $symbol = smartpay_get_currency_symbol($currency);

    $position = smartpay_get_option('currency_position', 'before');

    $amount = abs($amount);

    if ($position == 'before') {
        switch ($currency) {
            case 'GBP':
            case 'BRL':
            case 'EUR':
            case 'USD':
            case 'AUD':
            case 'CAD':
            case 'HKD':
            case 'MXN':
            case 'NZD':
            case 'SGD':
            case 'JPY':
            case 'BDT':
                $formatted = $symbol . $amount;
                break;
            default:
                $formatted = $currency . ' ' . $amount;
                break;
        }
    } else {
        switch ($currency) {
            case 'GBP':
            case 'BRL':
            case 'EUR':
            case 'USD':
            case 'AUD':
            case 'CAD':
            case 'HKD':
            case 'MXN':
            case 'SGD':
            case 'JPY':
            case 'BDT':
                $formatted = $amount . $symbol;
                break;
            default:
                $formatted = $amount . ' ' . $currency;
                break;
        }
    }

    return $formatted;
}

function smartpay_get_option($key = '', $default = false)
{
    global $smartpay_options;
    $value = !empty($smartpay_options[$key]) ? $smartpay_options[$key] : $default;
    return $value;
}

function smartpay_get_currency_symbol($currency = '')
{
    if (empty($currency)) {
        $currency = smartpay_get_currency();
    }

    $currencies = smartpay_get_currencies();

    if (array_key_exists($currency, $currencies)) {
        return $currencies[$currency]['symbol'] ?? '&#36;';
    } else {
        return $currencies['USD']['symbol'] ?? '&#36;';
    }
}

function smartpay_get_currency()
{
    $currency = smartpay_get_option('currency', 'USD');
    return $currency;
}

function smartpay_get_currencies()
{
    static $currencies;

    if (!isset($currencies)) {
        $currencies = apply_filters(
            'smartpay_currencies',
            [
                'AED' => [
                    'name'   => __('United Arab Emirates dirham', 'smartpay'),
                    'symbol' => '&#x62f;.&#x625;'
                ],
                'AFN' => [
                    'name'   => __('Afghan afghani', 'smartpay'),
                    'symbol' => '&#x60b;'
                ],
                'ALL' => [
                    'name'   => __('Albanian lek', 'smartpay'),
                    'symbol' => 'L',
                ],
                'AMD' => [
                    'name'   => __('Armenian dram', 'smartpay'),
                    'symbol' => 'AMD',
                ],
                'ANG' => [
                    'name'   => __('Netherlands Antillean guilder', 'smartpay'),
                    'symbol' => '&fnof;',
                ],
                'AOA' => [
                    'name'   => __('Angolan kwanza', 'smartpay'),
                    'symbol' => 'Kz',
                ],
                'ARS' => [
                    'name'   => __('Argentine peso', 'smartpay'),
                    'symbol' => '&#36;',
                ],
                'AUD' => [
                    'name'   => __('Australian dollar', 'smartpay'),
                    'symbol' => '&#36;',
                ],
                'AWG' => [
                    'name'   => __('Aruban florin', 'smartpay'),
                    'symbol' => 'Afl.',
                ],
                'AZN' => [
                    'name'   => __('Azerbaijani manat', 'smartpay'),
                    'symbol' => 'AZN',
                ],
                'BAM' => [
                    'name'   => __('Bosnia and Herzegovina convertible mark', 'smartpay'),
                    'symbol' => 'KM',
                ],
                'BBD' => [
                    'name'   => __('Barbadian dollar', 'smartpay'),
                    'symbol' => '&#36;',
                ],
                'BDT' => [
                    'name'   => __('Bangladeshi taka', 'smartpay'),
                    'symbol' => '&#2547;&nbsp;',
                ],
                'BGN' => [
                    'name'   => __('Bulgarian lev', 'smartpay'),
                    'symbol' => '&#1083;&#1074;.',
                ],
                'BHD' => [
                    'name'   => __('Bahraini dinar', 'smartpay'),
                    'symbol' => '.&#x62f;.&#x628;',
                ],
                'BIF' => [
                    'name'   => __('Burundian franc', 'smartpay'),
                    'symbol' => 'Fr',
                ],
                'BMD' => [
                    'name'   => __('Bermudian dollar', 'smartpay'),
                    'symbol' => '&#36;',
                ],
                'BND' => [
                    'name'   => __('Brunei dollar', 'smartpay'),
                    'symbol' => '&#36;',
                ],
                'BOB' => [
                    'name'   => __('Bolivian boliviano', 'smartpay'),
                    'symbol' => 'Bs.',
                ],
                'BRL' => [
                    'name'   => __('Brazilian real', 'smartpay'),
                    'symbol' => '&#82;&#36;',
                ],
                'BSD' => [
                    'name'   => __('Bahamian dollar', 'smartpay'),
                    'symbol' => '&#36;',
                ],
                'BTC' => [
                    'name'   => __('Bitcoin', 'smartpay'),
                    'symbol' => '&#3647;',
                ],
                'BTN' => [
                    'name'   => __('Bhutanese ngultrum', 'smartpay'),
                    'symbol' => 'Nu.',
                ],
                'BWP' => [
                    'name'   => __('Botswana pula', 'smartpay'),
                    'symbol' => 'P',
                ],
                'BYR' => [
                    'name'   => __('Belarusian ruble (old)', 'smartpay'),
                    'symbol' => 'Br',
                ],
                'BYN' => [
                    'name'   => __('Belarusian ruble', 'smartpay'),
                    'symbol' => 'Br',
                ],
                'BZD' => [
                    'name'   => __('Belize dollar', 'smartpay'),
                    'symbol' => '&#36;',
                ],
                'CAD' => [
                    'name'   => __('Canadian dollar', 'smartpay'),
                    'symbol' => '&#36;',
                ],
                'CDF' => [
                    'name'   => __('Congolese franc', 'smartpay'),
                    'symbol' => 'Fr',
                ],
                'CHF' => [
                    'name'   => __('Swiss franc', 'smartpay'),
                    'symbol' => '&#67;&#72;&#70;',
                ],
                'CLP' => [
                    'name'   => __('Chilean peso', 'smartpay'),
                    'symbol' => '&#36;',
                ],
                'CNY' => [
                    'name'   => __('Chinese yuan', 'smartpay'),
                    'symbol' => '&yen;',
                ],
                'COP' => [
                    'name'   => __('Colombian peso', 'smartpay'),
                    'symbol' => '&#36;',
                ],
                'CRC' => [
                    'name'   => __('Costa Rican col&oacute;n', 'smartpay'),
                    'symbol' => '&#x20a1;',
                ],
                'CUC' => [
                    'name'   => __('Cuban convertible peso', 'smartpay'),
                    'symbol' => '&#36;',
                ],
                'CUP' => [
                    'name'   => __('Cuban peso', 'smartpay'),
                    'symbol' => '&#36;',
                ],
                'CVE' => [
                    'name'   => __('Cape Verdean escudo', 'smartpay'),
                    'symbol' => '&#36;',
                ],
                'CZK' => [
                    'name'   => __('Czech koruna', 'smartpay'),
                    'symbol' => '&#75;&#269;',
                ],
                'DJF' => [
                    'name'   => __('Djiboutian franc', 'smartpay'),
                    'symbol' => 'Fr',
                ],
                'DKK' => [
                    'name'   => __('Danish krone', 'smartpay'),
                    'symbol' => 'DKK',
                ],
                'DOP' => [
                    'name'   => __('Dominican peso', 'smartpay'),
                    'symbol' => 'RD&#36;',
                ],
                'DZD' => [
                    'name'   => __('Algerian dinar', 'smartpay'),
                    'symbol' => '&#x62f;.&#x62c;',
                ],
                'EGP' => [
                    'name'   => __('Egyptian pound', 'smartpay'),
                    'symbol' => 'EGP',
                ],
                'ERN' => [
                    'name'   => __('Eritrean nakfa', 'smartpay'),
                    'symbol' => 'Nfk',
                ],
                'ETB' => [
                    'name'   => __('Ethiopian birr', 'smartpay'),
                    'symbol' => 'Br',
                ],
                'EUR' => [
                    'name'   => __('Euro', 'smartpay'),
                    'symbol' => '&euro;',
                ],
                'FJD' => [
                    'name'   => __('Fijian dollar', 'smartpay'),
                    'symbol' => '&#36;',
                ],
                'FKP' => [
                    'name'   => __('Falkland Islands pound', 'smartpay'),
                    'symbol' => '&pound;',
                ],
                'GBP' => [
                    'name'   => __('Pound sterling', 'smartpay'),
                    'symbol' => '&pound;',
                ],
                'GEL' => [
                    'name'   => __('Georgian lari', 'smartpay'),
                    'symbol' => '&#x20be;',
                ],
                'GGP' => [
                    'name'   => __('Guernsey pound', 'smartpay'),
                    'symbol' => '&pound;',
                ],
                'GHS' => [
                    'name'   => __('Ghana cedi', 'smartpay'),
                    'symbol' => '&#x20b5;',
                ],
                'GIP' => [
                    'name'   => __('Gibraltar pound', 'smartpay'),
                    'symbol' => '&pound;',
                ],
                'GMD' => [
                    'name'   => __('Gambian dalasi', 'smartpay'),
                    'symbol' => 'D',
                ],
                'GNF' => [
                    'name'   => __('Guinean franc', 'smartpay'),
                    'symbol' => 'Fr',
                ],
                'GTQ' => [
                    'name'   => __('Guatemalan quetzal', 'smartpay'),
                    'symbol' => 'Q',
                ],
                'GYD' => [
                    'name'   => __('Guyanese dollar', 'smartpay'),
                    'symbol' => '&#36;',
                ],
                'HKD' => [
                    'name'   => __('Hong Kong dollar', 'smartpay'),
                    'symbol' => '&#36;',
                ],
                'HNL' => [
                    'name'   => __('Honduran lempira', 'smartpay'),
                    'symbol' => 'L',
                ],
                'HRK' => [
                    'name'   => __('Croatian kuna', 'smartpay'),
                    'symbol' => 'kn',
                ],
                'HTG' => [
                    'name'   => __('Haitian gourde', 'smartpay'),
                    'symbol' => 'G',
                ],
                'HUF' => [
                    'name'   => __('Hungarian forint', 'smartpay'),
                    'symbol' => '&#70;&#116;',
                ],
                'IDR' => [
                    'name'   => __('Indonesian rupiah', 'smartpay'),
                    'symbol' => 'Rp',
                ],
                'ILS' => [
                    'name'   => __('Israeli new shekel', 'smartpay'),
                    'symbol' => '&#8362;',
                ],
                'IMP' => [
                    'name'   => __('Manx pound', 'smartpay'),
                    'symbol' => '&pound;',
                ],
                'INR' => [
                    'name'   => __('Indian rupee', 'smartpay'),
                    'symbol' => '&#8377;',
                ],
                'IQD' => [
                    'name'   => __('Iraqi dinar', 'smartpay'),
                    'symbol' => '&#x639;.&#x62f;',
                ],
                'IRR' => [
                    'name'   => __('Iranian rial', 'smartpay'),
                    'symbol' => '&#xfdfc;',
                ],
                'IRT' => [
                    'name'   => __('Iranian toman', 'smartpay'),
                    'symbol' => '&#x062A;&#x0648;&#x0645;&#x0627;&#x0646;',
                ],
                'ISK' => [
                    'name'   => __('Icelandic kr&oacute;na', 'smartpay'),
                    'symbol' => 'kr.',
                ],
                'JEP' => [
                    'name'   => __('Jersey pound', 'smartpay'),
                    'symbol' => '&pound;',
                ],
                'JMD' => [
                    'name'   => __('Jamaican dollar', 'smartpay'),
                    'symbol' => '&#36;',
                ],
                'JOD' => [
                    'name'   => __('Jordanian dinar', 'smartpay'),
                    'symbol' => '&#x62f;.&#x627;',
                ],
                'JPY' => [
                    'name'   => __('Japanese yen', 'smartpay'),
                    'symbol' => '&yen;',
                ],
                'KES' => [
                    'name'   => __('Kenyan shilling', 'smartpay'),
                    'symbol' => 'KSh',
                ],
                'KGS' => [
                    'name'   => __('Kyrgyzstani som', 'smartpay'),
                    'symbol' => '&#x441;&#x43e;&#x43c;',
                ],
                'KHR' => [
                    'name'   => __('Cambodian riel', 'smartpay'),
                    'symbol' => '&#x17db;',
                ],
                'KMF' => [
                    'name'   => __('Comorian franc', 'smartpay'),
                    'symbol' => 'Fr',
                ],
                'KPW' => [
                    'name'   => __('North Korean won', 'smartpay'),
                    'symbol' => '&#x20a9;',
                ],
                'KRW' => [
                    'name'   => __('South Korean won', 'smartpay'),
                    'symbol' => '&#8361;',
                ],
                'KWD' => [
                    'name'   => __('Kuwaiti dinar', 'smartpay'),
                    'symbol' => '&#x62f;.&#x643;',
                ],
                'KYD' => [
                    'name'   => __('Cayman Islands dollar', 'smartpay'),
                    'symbol' => '&#36;',
                ],
                'KZT' => [
                    'name'   => __('Kazakhstani tenge', 'smartpay'),
                    'symbol' => '&#8376;',
                ],
                'LAK' => [
                    'name'   => __('Lao kip', 'smartpay'),
                    'symbol' => '&#8365;',
                ],
                'LBP' => [
                    'name'   => __('Lebanese pound', 'smartpay'),
                    'symbol' => '&#x644;.&#x644;',
                ],
                'LKR' => [
                    'name'   => __('Sri Lankan rupee', 'smartpay'),
                    'symbol' => '&#xdbb;&#xdd4;',
                ],
                'LRD' => [
                    'name'   => __('Liberian dollar', 'smartpay'),
                    'symbol' => '&#36;',
                ],
                'LSL' => [
                    'name'   => __('Lesotho loti', 'smartpay'),
                    'symbol' => 'L',
                ],
                'LYD' => [
                    'name'   => __('Libyan dinar', 'smartpay'),
                    'symbol' => '&#x644;.&#x62f;',
                ],
                'MAD' => [
                    'name'   => __('Moroccan dirham', 'smartpay'),
                    'symbol' => '&#x62f;.&#x645;.',
                ],
                'MDL' => [
                    'name'   => __('Moldovan leu', 'smartpay'),
                    'symbol' => 'MDL',
                ],
                'MGA' => [
                    'name'   => __('Malagasy ariary', 'smartpay'),
                    'symbol' => 'Ar',
                ],
                'MKD' => [
                    'name'   => __('Macedonian denar', 'smartpay'),
                    'symbol' => '&#x434;&#x435;&#x43d;',
                ],
                'MMK' => [
                    'name'   => __('Burmese kyat', 'smartpay'),
                    'symbol' => 'Ks',
                ],
                'MNT' => [
                    'name'   => __('Mongolian t&ouml;gr&ouml;g', 'smartpay'),
                    'symbol' => '&#x20ae;',
                ],
                'MOP' => [
                    'name'   => __('Macanese pataca', 'smartpay'),
                    'symbol' => 'P',
                ],
                'MRU' => [
                    'name'   => __('Mauritanian ouguiya', 'smartpay'),
                    'symbol' => 'UM',
                ],
                'MUR' => [
                    'name'   => __('Mauritian rupee', 'smartpay'),
                    'symbol' => '&#x20a8;',
                ],
                'MVR' => [
                    'name'   => __('Maldivian rufiyaa', 'smartpay'),
                    'symbol' => '.&#x783;',
                ],
                'MWK' => [
                    'name'   => __('Malawian kwacha', 'smartpay'),
                    'symbol' => 'MK',
                ],
                'MXN' => [
                    'name'   => __('Mexican peso', 'smartpay'),
                    'symbol' => '&#36;',
                ],
                'MYR' => [
                    'name'   => __('Malaysian ringgit', 'smartpay'),
                    'symbol' => '&#82;&#77;',
                ],
                'MZN' => [
                    'name'   => __('Mozambican metical', 'smartpay'),
                    'symbol' => 'MT',
                ],
                'NAD' => [
                    'name'   => __('Namibian dollar', 'smartpay'),
                    'symbol' => 'N&#36;',
                ],
                'NGN' => [
                    'name'   => __('Nigerian naira', 'smartpay'),
                    'symbol' => '&#8358;',
                ],
                'NIO' => [
                    'name'   => __('Nicaraguan c&oacute;rdoba', 'smartpay'),
                    'symbol' => 'C&#36;',
                ],
                'NOK' => [
                    'name'   => __('Norwegian krone', 'smartpay'),
                    'symbol' => '&#107;&#114;',
                ],
                'NPR' => [
                    'name'   => __('Nepalese rupee', 'smartpay'),
                    'symbol' => '&#8360;',
                ],
                'NZD' => [
                    'name'   => __('New Zealand dollar', 'smartpay'),
                    'symbol' => '&#36;',
                ],
                'OMR' => [
                    'name'   => __('Omani rial', 'smartpay'),
                    'symbol' => '&#x631;.&#x639;.',
                ],
                'PAB' => [
                    'name'   => __('Panamanian balboa', 'smartpay'),
                    'symbol' => 'B/.',
                ],
                'PEN' => [
                    'name'   => __('Sol', 'smartpay'),
                    'symbol' => 'S/',
                ],
                'PGK' => [
                    'name'   => __('Papua New Guinean kina', 'smartpay'),
                    'symbol' => 'K',
                ],
                'PHP' => [
                    'name'   => __('Philippine peso', 'smartpay'),
                    'symbol' => '&#8369;',
                ],
                'PKR' => [
                    'name'   => __('Pakistani rupee', 'smartpay'),
                    'symbol' => '&#8360;',
                ],
                'PLN' => [
                    'name'   => __('Polish z&#x142;oty', 'smartpay'),
                    'symbol' => '&#122;&#322;',
                ],
                'PRB' => [
                    'name'   => __('Transnistrian ruble', 'smartpay'),
                    'symbol' => '&#x440;.',
                ],
                'PYG' => [
                    'name'   => __('Paraguayan guaran&iacute;', 'smartpay'),
                    'symbol' => '&#8370;',
                ],
                'QAR' => [
                    'name'   => __('Qatari riyal', 'smartpay'),
                    'symbol' => '&#x631;.&#x642;',
                ],
                'RMB' => [
                    'name'   => __('Renminbi', 'smartpay'),
                    'symbol' => '&yen;',
                ],
                'RON' => [
                    'name'   => __('Romanian leu', 'smartpay'),
                    'symbol' => 'lei',
                ],
                'RSD' => [
                    'name'   => __('Serbian dinar', 'smartpay'),
                    'symbol' => '&#1088;&#1089;&#1076;',
                ],
                'RUB' => [
                    'name'   => __('Russian ruble', 'smartpay'),
                    'symbol' => '&#8381;',
                ],
                'RWF' => [
                    'name'   => __('Rwandan franc', 'smartpay'),
                    'symbol' => 'Fr',
                ],
                'SAR' => [
                    'name'   => __('Saudi riyal', 'smartpay'),
                    'symbol' => '&#x631;.&#x633;',
                ],
                'SBD' => [
                    'name'   => __('Solomon Islands dollar', 'smartpay'),
                    'symbol' => '&#36;',
                ],
                'SCR' => [
                    'name'   => __('Seychellois rupee', 'smartpay'),
                    'symbol' => '&#x20a8;',
                ],
                'SDG' => [
                    'name'   => __('Sudanese pound', 'smartpay'),
                    'symbol' => '&#x62c;.&#x633;.',
                ],
                'SEK' => [
                    'name'   => __('Swedish krona', 'smartpay'),
                    'symbol' => '&#107;&#114;',
                ],
                'SGD' => [
                    'name'   => __('Singapore dollar', 'smartpay'),
                    'symbol' => '&#36;',
                ],
                'SHP' => [
                    'name'   => __('Saint Helena pound', 'smartpay'),
                    'symbol' => '&pound;',
                ],
                'SLL' => [
                    'name'   => __('Sierra Leonean leone', 'smartpay'),
                    'symbol' => 'Le',
                ],
                'SOS' => [
                    'name'   => __('Somali shilling', 'smartpay'),
                    'symbol' => 'Sh',
                ],
                'SRD' => [
                    'name'   => __('Surinamese dollar', 'smartpay'),
                    'symbol' => '&#36;',
                ],
                'SSP' => [
                    'name'   => __('South Sudanese pound', 'smartpay'),
                    'symbol' => '&pound;',
                ],
                'STN' => [
                    'name'   => __('S&atilde;o Tom&eacute; and Pr&iacute;ncipe dobra', 'smartpay'),
                    'symbol' => 'Db',
                ],
                'SYP' => [
                    'name'   => __('Syrian pound', 'smartpay'),
                    'symbol' => '&#x644;.&#x633;',
                ],
                'SZL' => [
                    'name'   => __('Swazi lilangeni', 'smartpay'),
                    'symbol' => 'L',
                ],
                'THB' => [
                    'name'   => __('Thai baht', 'smartpay'),
                    'symbol' => '&#3647;',
                ],
                'TJS' => [
                    'name'   => __('Tajikistani somoni', 'smartpay'),
                    'symbol' => '&#x405;&#x41c;',
                ],
                'TMT' => [
                    'name'   => __('Turkmenistan manat', 'smartpay'),
                    'symbol' => 'm',
                ],
                'TND' => [
                    'name'   => __('Tunisian dinar', 'smartpay'),
                    'symbol' => '&#x62f;.&#x62a;',
                ],
                'TOP' => [
                    'name'   => __('Tongan pa&#x2bb;anga', 'smartpay'),
                    'symbol' => 'T&#36;',
                ],
                'TRY' => [
                    'name'   => __('Turkish lira', 'smartpay'),
                    'symbol' => '&#8378;',
                ],
                'TTD' => [
                    'name'   => __('Trinidad and Tobago dollar', 'smartpay'),
                    'symbol' => '&#36;',
                ],
                'TWD' => [
                    'name'   => __('New Taiwan dollar', 'smartpay'),
                    'symbol' => '&#78;&#84;&#36;',
                ],
                'TZS' => [
                    'name'   => __('Tanzanian shilling', 'smartpay'),
                    'symbol' => 'Sh',
                ],
                'UAH' => [
                    'name'   => __('Ukrainian hryvnia', 'smartpay'),
                    'symbol' => '&#8372;',
                ],
                'UGX' => [
                    'name'   => __('Ugandan shilling', 'smartpay'),
                    'symbol' => 'UGX',
                ],
                'USD' => [
                    'name'   => __('United States (US) dollar', 'smartpay'),
                    'symbol' => '&#36;',
                ],
                'UYU' => [
                    'name'   => __('Uruguayan peso', 'smartpay'),
                    'symbol' => '&#36;',
                ],
                'UZS' => [
                    'name'   => __('Uzbekistani som', 'smartpay'),
                    'symbol' => 'UZS',
                ],
                'VEF' => [
                    'name'   => __('Venezuelan bol&iacute;var', 'smartpay'),
                    'symbol' => 'Bs F',
                ],
                'VES' => [
                    'name'   => __('Bol&iacute;var soberano', 'smartpay'),
                    'symbol' => 'Bs.S',
                ],
                'VND' => [
                    'name'   => __('Vietnamese &#x111;&#x1ed3;ng', 'smartpay'),
                    'symbol' => '&#8363;',
                ],
                'VUV' => [
                    'name'   => __('Vanuatu vatu', 'smartpay'),
                    'symbol' => 'Vt',
                ],
                'WST' => [
                    'name'   => __('Samoan t&#x101;l&#x101;', 'smartpay'),
                    'symbol' => 'T',
                ],
                'XAF' => [
                    'name'   => __('Central African CFA franc', 'smartpay'),
                    'symbol' => 'CFA',
                ],
                'XCD' => [
                    'name'   => __('East Caribbean dollar', 'smartpay'),
                    'symbol' => '&#36;',
                ],
                'XOF' => [
                    'name'   => __('West African CFA franc', 'smartpay'),
                    'symbol' => 'CFA',
                ],
                'XPF' => [
                    'name'   => __('CFP franc', 'smartpay'),
                    'symbol' => 'Fr',
                ],
                'YER' => [
                    'name'   => __('Yemeni rial', 'smartpay'),
                    'symbol' => '&#xfdfc;',
                ],
                'ZAR' => [
                    'name'   => __('South African rand', 'smartpay'),
                    'symbol' => '&#82;',
                ],
                'ZMW' => [
                    'name'   => __('Zambian kwacha', 'smartpay'),
                    'symbol' => 'ZK',
                ],
            ]
        );
    }

    return $currencies;
}

function smartpay_sanitize_key($key)
{
    $key = preg_replace('/[^a-zA-Z0-9_\-\.\:\/]/', '', $key);
    return $key;
}

function smartpay_payment_gateways()
{
    // Default, built-in gateways
    return apply_filters('smartpay_gateways', Gateway::gateways());
}

function smartpay_get_enabled_payment_gateways($sort = false)
{
    $gateways = smartpay_payment_gateways();

    $enabled  = (array) smartpay_get_option('gateways', false);

    $gateway_list = array();

    foreach ($gateways as $key => $gateway) {
        if (isset($enabled[$key]) && $enabled[$key] == 1) {
            $gateway_list[$key] = $gateway;
        }
    }

    if (true === $sort) {
        // Reorder our gateways so the default is first
        $default_gateway_id = smartpay_get_default_gateway();

        if (smartpay_is_gateway_active($default_gateway_id)) {
            $default_gateway    = array($default_gateway_id => $gateway_list[$default_gateway_id]);
            unset($gateway_list[$default_gateway_id]);

            $gateway_list = array_merge($default_gateway, $gateway_list);
        }
    }

    return $gateway_list;
}

function smartpay_is_gateway_active($gateway)
{
    $gateways = smartpay_get_enabled_payment_gateways();

    if (!is_array($gateways) || !count($gateways)) {
        return;
    }

    $is_active = array_key_exists($gateway, $gateways);
    return $is_active;
}

function smartpay_get_default_gateway()
{
    $default = smartpay_get_option('default_gateway', 'paddle');

    if (!smartpay_is_gateway_active($default)) {
        $gateways = smartpay_get_enabled_payment_gateways();
        $gateways = array_keys($gateways);
        $default  = reset($gateways);
    }

    return $default;
}


function smartpay_get_settings()
{
    $settings = get_option('smartpay_settings');

    if (empty($settings)) {
        $general_settings = get_option('smartpay_settings_general') ? get_option('smartpay_settings_general') : [];
        $gateway_settings = get_option('smartpay_settings_gateways') ? get_option('smartpay_settings_gateways') : [];
        $email_settings   = get_option('smartpay_settings_emails') ? get_option('smartpay_settings_emails') : [];
        $license_settings = get_option('smartpay_settings_licenses') ? get_option('smartpay_settings_licenses') : [];

        $settings = array_merge($general_settings, $gateway_settings, $email_settings, $license_settings);
        update_option('smartpay_settings', $settings);
    }
    return apply_filters('smartpay_get_settings', $settings);
}

function smartpay_get_payment_page_uri($query_string = null)
{
    $page_id = absint(smartpay_get_option('payment_page', 0));

    return smartpay_get_page_uri($page_id, $query_string);
}

function smartpay_get_page_uri($page_id, $query_string = null)
{
    $page_uri = get_permalink($page_id);

    if ($query_string) {
        $page_uri .= $query_string;
    }

    return $page_uri;
}

function smartpay_insert_payment($paymentData)
{
    return smartpay()->get(Payment::class)->insertPayment($paymentData);
}

function smartpay_is_test_mode()
{
    $is_test_mode = smartpay_get_option('test_mode', false);
    return (bool) $is_test_mode;
}

function smartpay_get_payment_success_page_uri($query_string = null)
{
    $page_id = absint(smartpay_get_option('payment_success_page', 0));

    return smartpay_get_page_uri($page_id, $query_string);
}

function smartpay_get_payment_failure_page_uri($query_string = null)
{
    $page_id = absint(smartpay_get_option('payment_failure_page', 0));

    return smartpay_get_page_uri($page_id, $query_string);
}

function smartpay_update_settings(array $settings)
{
    $old_settings = get_option('smartpay_settings');

    if (!($old_settings === $settings)) {
        update_option('smartpay_settings', $settings);
    }
}