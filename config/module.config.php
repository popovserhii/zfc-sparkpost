<?php
namespace Popov\ZfcSparkPost;

return [
    'service_manager' => [
        'aliases' => [
            'SparkpostTransport' => Transport\SparkPost::class,
        ],
        'factories' => [
            Transport\SparkPost::class => Transport\Factory\SparkPostFactory::class
        ],
    ],

];
