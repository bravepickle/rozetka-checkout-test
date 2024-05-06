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
//define('APP_LOG',env('APP_LOG', 'php://stdout'));
define('APP_LOG',env('APP_LOG', null));

define('REDIS_HOST', env('REDIS_HOST', 'redis-db'));
define('REDIS_PORT', (int)env('REDIS_PORT', 6379));
define('REDIS_PASS', env('REDIS_PASS', null));

define('DB_DSN', env('REDIS_HOST', 'mysql:dbname=main_db;host=my-db'));
define('DB_USER', env('REDIS_HOST', 'root'));
define('DB_PASS', env('REDIS_HOST', 'Dfoij3FlFLvm?2'));

// Postmark product ID
define('MARK_PRODUCT_ID', (int)env('MARK_PRODUCT_ID', 100));
define('MARK_PRODUCT_COUNT', (int)env('MARK_PRODUCT_COUNT', 10000));

// populate fixtures
define('FIXTURE_ORDERS_SIZE', (int)env('FIXTURE_ORDERS_SIZE', 10000000));
define('FIXTURE_PRODUCTS_SIZE', (int)env('FIXTURE_PRODUCTS_SIZE', 5000000));
define('FIXTURE_BATCH_SIZE', (int)env('FIXTURE_BATCH_SIZE', 10000));

// Redis Streams settings
define('STREAM_NAME', 'product:orders');
define('STREAM_GROUP', 'purchase');
define('STREAM_CONSUMER', 'worker');
