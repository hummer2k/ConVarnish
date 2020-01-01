# ConVarnish

Master:
[![Build Status](https://travis-ci.org/hummer2k/ConVarnish.svg?branch=master)](https://travis-ci.org/hummer2k/ConVarnish)
[![Coverage Status](https://coveralls.io/repos/hummer2k/ConVarnish/badge.svg?branch=master)](https://coveralls.io/r/hummer2k/ConVarnish)

## Installation

Install via composer:

`$ composer require hummerk2/convarnish:^4.0`

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

Copy `convarnish/config/con-varnish.config.php.dist` to
`config/autoload/con-varnish.global.php`
