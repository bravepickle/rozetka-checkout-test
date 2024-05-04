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

    /**
     * @param array|null $data
     * @return array<int,int>
     * @throws HttpBadRequestException
     */
    protected function parseInput(?array $data): array
    {
        if (empty($data['items'])) {
            throw new HttpBadRequestException('Malformed purchase order request');
        }

        $products = [];
        foreach ($data['items'] as $item) {
            $count = (int)$item['count'];

            if ($count <= 0) {
                throw new HttpBadRequestException('Ordered items count must be greater than 0');
            }

            $products[(int)$item['product_id']] = $count;
        }

        if (!$products) {
            throw new HttpBadRequestException('No items to buy were added to order');
        }

        return $products;
    }
}
