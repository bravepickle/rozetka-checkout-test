<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$request = \App\Service\RequestParser::makeFromGlobals();



$fh = fopen('php://stdout', 'a+');
//$fh = fopen(__DIR__ . '/out.log', 'a+');
//fwrite($fh, print_r($request, true));
fwrite($fh, print_r(['parsedRequest' => $request, 'raw_server' => $_SERVER, '_POST' => $_POST], true));

//file_put_contents(__DIR__ . '/out.log', print_r($request, true));

fclose($fh);

echo '<pre>';
print_r($_SERVER);
print_r($request);
die(implode(':', [__METHOD__, __FILE__, __LINE__]) . PHP_EOL);

