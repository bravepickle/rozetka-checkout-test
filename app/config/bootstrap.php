<?php
/**
 * Bootstrap application
 *
 * @const string REDIS_CONST
 * @const string DB_DSN
 * @const string DB_USER
 * @const string DB_PASS
 */
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/functions.php';

define('APP_ROOT', dirname(__DIR__));

define('REDIS_HOST', env('REDIS_HOST', 'redis-db'));
define('DB_DSN', env('REDIS_HOST', 'mysql:dbname=main_db;host=my-db'));
define('DB_USER', env('REDIS_HOST', 'root'));
define('DB_PASS', env('REDIS_HOST', 'Dfoij3FlFLvm?2'));
