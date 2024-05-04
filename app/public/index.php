<?php
declare(strict_types=1);

use App\Contract\HttpExceptionInterface;
use App\Service\Application;
use App\Service\RequestParser;

require __DIR__ . '/../config/bootstrap.php';

$container = (new Application())->boot()->container();

$request = RequestParser::makeFromGlobals();

$_SESSION['time'] = time(); // save something to session

// track number of calls done within single session by a user
if (!isset($_SESSION['api_calls_total'])) {
    $_SESSION['api_calls_total'] = 0;
}

++$_SESSION['api_calls_total'];

//$_SESSION['action'] = isset($_SESSION['action']) ?
//    array_unique(array_merge((array)$_SESSION['action'], [$request->query('action')])) : [$request->query('action')];


try {
    $response = $container->router()->resolve($request, $container);

    header('HTTP/1.1 200 OK');
    header('Content-Type: text/plain; charset=utf-8');

    echo $response . PHP_EOL;
} catch (HttpExceptionInterface $exception) {
    $statusText = $exception->getStatusText();
    if ($exception->getStatusCode() > 0 && $statusText) {
        header(sprintf('HTTP/1.1 %d %s', $exception->getStatusCode(), $statusText));
    } else {
        header('HTTP/1.1 500 Internal Server Error');
    }

    header('Content-Type: text/plain; charset=utf-8');

    echo $exception->getMessage() ?: $exception->getStatusText() . PHP_EOL;
} catch (\Throwable $exception) {
    header('HTTP/1.1 500 Internal Server Error');
    header('Content-Type: text/plain; charset=utf-8');

    echo $exception->getMessage() . PHP_EOL . $exception->getFile() . ':' . $exception->getLine() . PHP_EOL .
        PHP_EOL . $exception->getTraceAsString();
}
