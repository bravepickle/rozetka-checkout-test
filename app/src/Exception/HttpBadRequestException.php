<?php

namespace App\Exception;

use App\Contract\HttpExceptionInterface;
use Exception;
use Throwable;

class HttpBadRequestException extends Exception implements HttpExceptionInterface
{
    public function getStatusText(): string
    {
        return 'Bad Request';
    }

    public function getStatusCode(): int
    {
        return 400;
    }
}
