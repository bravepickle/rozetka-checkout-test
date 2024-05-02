<?php
declare(strict_types=1);

use App\Service\RequestParser;
use App\Service\Router;

require __DIR__ . '/../vendor/autoload.php';

session_start();

//print_r(['previous session' => $_SESSION]);

$_SESSION['time'] = time(); // save something to session

$request = RequestParser::makeFromGlobals();
$router = new Router();

try {
    header('HTTP/1.1 200 OK');
    echo $router->resolve($request) . PHP_EOL;
} catch (\Throwable $exception) {
    header('HTTP/1.0 500 Internal Server Error');
    echo $exception->getMessage();
}



//$fh = fopen('php://stdout', 'a+');
////$fh = fopen(__DIR__ . '/out.log', 'a+');
////fwrite($fh, print_r($request, true));
//fwrite($fh, print_r(['parsedRequest' => $request, 'raw_server' => $_SERVER, '_POST' => $_POST], true));
//
////file_put_contents(__DIR__ . '/out.log', print_r($request, true));
//
//fclose($fh);
//
//echo '<pre>';
//print_r($_SERVER);
//print_r($request);
//die(implode(':', [__METHOD__, __FILE__, __LINE__]) . PHP_EOL);

