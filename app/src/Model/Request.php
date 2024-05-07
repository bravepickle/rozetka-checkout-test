<?php
declare(strict_types=1);

namespace App\Model;

/**
 * Simplified version of HTTP request with only required data for the test
 */
class Request
{
    public const string METHOD_POST = 'POST';
    public const string METHOD_GET = 'GET';

    /**
     * @param string|null $method
     * @param array|null $body
     * @param array|null $query
     */
    public function __construct(
        public readonly ?string $method,
        public readonly ?array $query,
        public readonly ?array $body,
    )
    {
    }

    /**
     * Check if is POST method
     * @return bool
     */
    public function isPost(): bool
    {
        return $this->method === self::METHOD_POST;
    }

    public function query($name, $default = null)
    {
        return $this->query[$name] ?? $default;
    }
}
