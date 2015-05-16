# ConVarnish

## Installation

Install via composer:

`$ composer require hummerk2/convarnish:dev-master`

Enable module in your application.config.php

````php
<?php
$config = [
    'modules' => [
        'ConLayout',
        'ConVarnish', // <---
        'Application',
        '...'
    ]
];
````

Copy `vendor/hummer2k/convarnish/config/con-varnish.config.php.dist` to 
`config/autoload/con-varnish.global.php`
