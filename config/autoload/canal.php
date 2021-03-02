<?php
declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/11/3
 * Time: 17:47
 */
return [
    'canal_host' => env('CANAL_HOST', ''),
    'canal_port' => env('CANAL_PORT', ''),
    'canal_client_id' => env('CANAL_CLIENT_ID', ''),
    'canal_destination' => env('CANAL_DESTINATION', ''),

    'listen_tables' => [
        env('DB_DATABASE_ERP'). '.product_details',

    ],
];

