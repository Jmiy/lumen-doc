<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => env('SES_REGION', 'us-east-1'),
        'options' => [//如果您在执行 SES 时需要包含 附加选项 SendRawEmail 请求，您可以在 ses 配置中定义 options 数组：
            'ConfigurationSetName' => 'MyConfigurationSet',
            'Tags' => [
                [
                    'Name' => 'foo',
                    'Value' => 'bar',
                ],
            ],
        ],
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
        'options' => [
            'endpoint' => 'https://api.eu.sparkpost.com/api/v1/transmissions',
        ],
    ],

    'stripe' => [
        'model' => App\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook' => [
            'secret' => env('STRIPE_WEBHOOK_SECRET'),
            'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
        ],
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT'),
    ],

    'twitter' => [
        'client_id' => env('TWITTER_CLIENT_ID'),
        'client_secret' => env('TWITTER_CLIENT_SECRET'),
        'redirect' => env('TWITTER_REDIRECT'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT'),
    ],
    'github' => [
        'client_id' => env('GITHUB_CLIENT_ID'),
        'client_secret' => env('GITHUB_CLIENT_SECRET'),
        'redirect' => 'http://your-callback-url',
    ],

    'payment' => [
        'paypal' => [
            'client_id' => 'ARH5Dbvlm6F7_L9GXow9ZWLQ2TptLq4vD8NGARJA7VsORXO5gUvYXHnULIZDtnLoa_XfB-7tqq5QCJRf',
            'client_secret' => 'ECR-HDFE0R5JBnTJQxukFLh_prrYa__8EYigCdv1uaK0j4vOm3z7055TvrA48UbrYXiJ7GI8oZBRccTd',
            'currency' => 'USD',
            'callback_uri' => 'https://testapidev.patozon.net/api/payment/paypal/callback',
            'mode' => 'sandbox',
        ],
//        'paypal' => [
//            'client_id' => 'AdRfiW3Hm97MBMYqnb_BuJfshs7mFzElw11H1RYL8mp5603bC2V4pxEilJIipcCjy_yIoafZ5Xk5Q3ji',
//            'client_secret' => 'EEzHwkjUcrlVHJM96Dhc9AhvnXtOd3XpsxtaLzvk9ZkC7lCWgzKTRQDs3VYNKC-v7QIiaVQwWyKAAQ0H',
//            'currency' => 'USD',
//            'callback_uri' => 'https://testapidev.patozon.net/api/payment/paypal/callback',
//            'mode' => 'live',
//        ],
    ],

    'psc' => [
        'localhost' => 'http://172.16.6.92',
        'dev' => 'http://172.16.6.92',
        'test' => 'http://pmsystem.k8s.test',
        'pre-release' => 'https://pmpre.patozon.net',//https://pmpre.patozon.net
        'production' => 'https://pm.patozon.net',
    ]
];
