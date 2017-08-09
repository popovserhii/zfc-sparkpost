<?php
namespace Popov\ZfcSparkPost;

return [
    'service_manager' => [
        'invokables' => [
            'SparkpostTransport' => Transport\SparkPost::class,
        ],
    ],
];
