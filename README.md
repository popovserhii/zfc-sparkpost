# Zend SparkPost adapter
Adapter for sending messages through SparkPost service

## Configuration
In your local.php add/change next config
```
[
    'mail' => [
        'type' => 'sparkpost',
        'from' => 'ex@example.com',
        'options' => [
            'api_key' => 'your-api-key',
            'async' => false,
            'trackOpens' => false,
            'trackClicks' => false,
            'sandbox' => false,
            'inlineCss' => false,
            'transactional' => true,
        ]
    ],
]
```