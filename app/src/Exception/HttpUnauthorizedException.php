<?php

namespace App\Exception;

use App\Contract\HttpExceptionInterface;
use Exception;
use Throwable;

class HttpUnauthorizedException extends Exception implements HttpExceptionInterface
{
    public function getStatusText(): string
    {
        return 'Unauthorized';
    }

    public function getStatusCode(): int
    {
        return 401;
    }
}
