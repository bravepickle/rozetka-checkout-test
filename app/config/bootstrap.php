<?php

require_once __DIR__ . "/../vendor/autoload.php";

define('APP_ROOT', dirname(__DIR__));;
define('REDIS_HOST', $_ENV['REDIS_HOST'] ?? 'redis-db');;
