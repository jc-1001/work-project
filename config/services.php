<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'shipping' => [
        'fee' => env('SHIPPING_FEE', 60),
    ],

    'ecpay' => [
        'merchant_id' => env('ECPAY_MERCHANT_ID'),
        'hash_key'    => env('ECPAY_HASH_KEY'),
        'hash_iv'     => env('ECPAY_HASH_IV'),
        'payment_url' => env('ECPAY_PAYMENT_URL'),
        'notify_url'  => env('ECPAY_NOTIFY_URL'),
        'return_url'  => env('ECPAY_RETURN_URL'),
    ],

];
