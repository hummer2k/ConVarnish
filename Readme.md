# ConVarnish

https://travis-ci.org/hummer2k/ConVarnish.svg?branch=master

## Master
[![Build Status](https://travis-ci.org/hummer2k/ConVarnish.svg?branch=master)](https://travis-ci.org/hummer2k/ConVarnish)

## Develop
[![Build Status](https://travis-ci.org/hummer2k/ConVarnish.svg?branch=develop)](https://travis-ci.org/hummer2k/ConVarnish)

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
