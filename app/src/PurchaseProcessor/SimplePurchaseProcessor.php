<?php

namespace App\PurchaseProcessor;

use App\Exception\HttpBadRequestException;
use App\Exception\HttpExhaustedException;
use PDO;
use Throwable;

class SimplePurchaseProcessor extends AbstractPurchaseProcessor
{
    /**
     * @inheritDoc
     * @throws Throwable
     */
    #[\Override]
    public function process(?array $data): string
    {
        $products = $this->parseInput($data);
        $db = $this->container->db();
        $found = $db->query(
            sprintf(
                'SELECT product_id, items_count from product_remainders WHERE product_id IN (%s)',
                implode(', ', array_keys($products))
            )
        )->fetchAll(PDO::FETCH_ASSOC);

        if (!$found || count($found) < count($products)) {
            throw new HttpBadRequestException('Failed to find some products in the order');
        }

        /** @var array{product_id: int, items_count: int} $productRemainder */
        foreach ($found as $productRemainder) {
            if ($productRemainder['items_count'] <= 0 ||
                $productRemainder['items_count'] < $products[$productRemainder['product_id']]) {
                throw new HttpExhaustedException('Missing enough items in inventory to fulfill your order');
            }
        }

        $this->save($db, $products, $data);

        return 'Processed order successfully in simple mode';
    }

    /**
     * @param PDO $db
     * @param array<int, int> $products
     * @param array $data
     * @return void
     * @throws Throwable
     */
    public function save(PDO $db, array $products, array $data): void
    {
        try {
            $db->beginTransaction();

            $productId = $count = null;
            $stmt = $db->prepare(
                "UPDATE product_remainders SET items_count = items_count - ? WHERE product_id = ? LIMIT 1",
            );

            $stmt->bindParam(1, $count, PDO::PARAM_INT);
            $stmt->bindParam(2, $productId, PDO::PARAM_INT);

            foreach ($products as $productId => $count) {
                $stmt->execute();
            }

            $stmt = $db->prepare(
                "INSERT INTO `orders` (price_total, payload, items_count, created_at, updated_at) " .
                "VALUES (:price, :payload, :count, NOW(), NOW())"
            );

            $stmt->execute(['price' => '100.00', 'payload' => json_encode($data), 'count' => count($products)]);

            $db->commit();
        } catch (Throwable $e) {
            $db->rollBack();

            throw $e;
        }
    }
}
