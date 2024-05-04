<?php
declare(strict_types=1);

use App\Contract\HttpExceptionInterface;
use App\Service\Application;
use App\Service\RequestParser;

require __DIR__ . '/../config/bootstrap.php';

$container = (new Application())->boot()->container();

$request = RequestParser::makeFromGlobals();

try {
    $response = $container->router()->resolve($request, $container);

    header('HTTP/1.1 200 OK');
    header('Content-Type: text/plain; charset=utf-8');

    echo $response . PHP_EOL;
} catch (HttpExceptionInterface $e) {
    log_error('[HTTP] ' . $e->getMessage(), ['statusCode' => $e->getStatusCode(), 'file' => $e->getFile(), 'line' => $e->getLine()]);

    $statusText = $e->getStatusText();
    if ($e->getStatusCode() > 0 && $statusText) {
        header(sprintf('HTTP/1.1 %d %s', $e->getStatusCode(), $statusText));
    } else {
        header('HTTP/1.1 500 Internal Server Error');
    }

    header('Content-Type: text/plain; charset=utf-8');

    echo $e->getMessage() ?: $e->getStatusText() . PHP_EOL;
} catch (\Throwable $e) {
    log_error($e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);

    header('HTTP/1.1 500 Internal Server Error');
    header('Content-Type: text/plain; charset=utf-8');

    echo $e->getMessage() . PHP_EOL . $e->getFile() . ':' . $e->getLine() . PHP_EOL .
        PHP_EOL . $e->getTraceAsString();
}
