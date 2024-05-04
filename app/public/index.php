<?php
declare(strict_types=1);

use App\Service\Application;
use App\Service\RequestParser;

require __DIR__ . '/../config/bootstrap.php';

$container = (new Application())->boot()->container();

$request = RequestParser::makeFromGlobals();

$_SESSION['time'] = time(); // save something to session
$_SESSION['action'] = $request->query('action');

try {
    $response = $container->router()->resolve($request, $container);

    header('HTTP/1.1 200 OK');
    header('Content-Type: text/plain; charset=utf-8');

    echo $response . PHP_EOL;
} catch (\Throwable $exception) {
    header('HTTP/1.0 500 Internal Server Error');
    header('Content-Type: text/plain; charset=utf-8');

    echo $exception->getMessage() . PHP_EOL;
}
