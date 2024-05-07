<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Request;
use LogicException;

/**
 * Simple parser for HTTP requests
 */
class RequestParser
{
    public static function makeFromGlobals(): Request
    {
        if (!isset($_SERVER, $_GET)) {
            throw new LogicException('HTTP variables not set');
        }

        return new Request(
            $_SERVER['REQUEST_METHOD'] ?? Request::METHOD_GET,
            $_GET,
            $_POST ?? null,
        );
    }
}
