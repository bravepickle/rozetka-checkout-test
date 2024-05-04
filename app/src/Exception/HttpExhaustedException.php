<?php

namespace App\Exception;

use App\Contract\HttpExceptionInterface;
use Exception;

class HttpExhaustedException extends Exception implements HttpExceptionInterface
{
    public function getStatusText(): string
    {
        return 'Inventory is exhausted';
    }

    public function getStatusCode(): int
    {
        return 418;
    }
}
