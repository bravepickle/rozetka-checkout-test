<?php

namespace App\Command;

class OrderRequestsWorker
{
    protected function info(string $message): void
    {
        // TODO: use logger. Its for debug only
        echo '[INFO] ' . $message . PHP_EOL;
    }
    public function run(): void
    {
        die(implode(':', [__METHOD__, __FILE__, __LINE__]) . PHP_EOL);

    }
}
