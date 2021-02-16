<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Documentation Route
    |--------------------------------------------------------------------------
    |
    | Route which will return documentation
    */

    'route' => '/',

    /*
    |--------------------------------------------------------------------------
    | Info block
    |--------------------------------------------------------------------------
    |
    | Information fields
    */

    'info' => [

        /*
        |--------------------------------------------------------------------------
        | Documentation Template
        |--------------------------------------------------------------------------
        |
        | You can use your custom documentation view
        | Define your blade view in 'description' to describe the documentation
        | and it will be render automatically by this library
        */

        'description' => 'swagger-description',

        'version' => '0.0.1',
        'title' => 'Name of Your Application',
        'termsOfService' => '',
        'contact' => [
            'email' => 'your@email.com'
        ],
        'license' => [
            'name' => '',
            'url' => ''
        ]
    ],

    'openapi' => [
        'version' => '3.0.0'
    ],

    'servers' => [
        [
            "url" => 'https://127.0.0.1',
            "description" => 'This is a sample server description'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Base API path
    |--------------------------------------------------------------------------
    |
    | Base path for API routes. If config is set, all routes which starts from
    | this value will be grouped.
    */

    'basePath' => '/',

    /*
    |--------------------------------------------------------------------------
    | Security Library
    |--------------------------------------------------------------------------
    |
    | Library name, which used to secure the project.
    | Available values: "bearerAuth", "basicAuth", "ApiKeyAuth", ""
    */

    'security' => '',
    'defaults' => [

        /*
        |--------------------------------------------------------------------------
        | Default descriptions of code statuses
        |--------------------------------------------------------------------------
        */

        'code-descriptions' => [
            '200' => 'Operation successfully done',
            '201' => 'Created',
            '204' => 'Operation successfully done, no content',
            '401' => 'Unauthorized',
            '404' => 'This entity not found',
            '405' => 'Method Not Allowed',
            '422' => 'The given data was invalid'
        ],

        /*
        |--------------------------------------------------------------------------
        | Default headers for add to each requests
        |--------------------------------------------------------------------------
        */

        'headers' => [
            'X-Requested-With' => 'XMLHttpRequest'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Collector Class
    |--------------------------------------------------------------------------
    |
    | Class of data collector, which will collect and save documentation
    | It can be your own data collector class which should be inherited from
    | Apoplavs\Support\AutoDoc\Interfaces\DataCollectorInterface interface,
    | or our data collectors from next packages:
    |
    | if you want to use YAMLDataCollector you must install yaml extension
    | in your PHP
    |
    */

    'data_collector' => \Apoplavs\Support\AutoDoc\DataCollectors\JsonDataCollector::class,
    //'data_collector' => \Apoplavs\Support\AutoDoc\DataCollectors\YAMLDataCollector::class,

    'enabled'         => env('AUTODOC_ENABLED', false),
    'production_path' => env('LOCAL_DATA_COLLECTOR_PROD_PATH', '/tmp/documentation.json')
];
