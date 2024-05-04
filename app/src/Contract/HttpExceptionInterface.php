<?php

namespace App\Contract;

interface HttpExceptionInterface
{
    /**
     * Get HTTP status code
     * @return int
     */
    public function getStatusCode(): int;

    /**
     * Get HTTP status header text, e.g. Bad Request
     * @return string
     */
    public function getStatusText(): string;
}
