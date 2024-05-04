<?php

namespace App\PurchaseProcessor;

use App\Exception\HttpBadRequestException;
use App\Service\Container;

/**
 * Strategy to process purchase requests
 */
abstract class AbstractPurchaseProcessor
{

    public function __construct(protected Container $container) {
    }

    /**
     * @param array{
     *     delivery: array{address: string, phone: string, email: string},
     *     items: array<array{product_id: int, count: int}>
     * }|null $data
     * @return string
     * @throws HttpBadRequestException
     */
    abstract public function process(?array $data): string;
}
