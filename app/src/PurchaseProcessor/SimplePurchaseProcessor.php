<?php

namespace App\PurchaseProcessor;

use App\Exception\HttpBadRequestException;
use App\Exception\HttpExhaustedException;
use PDO;

class SimplePurchaseProcessor extends AbstractPurchaseProcessor
{
    /**
     * @inheritDoc
     */
    #[\Override]
    public function process(?array $data): string
    {
        // TODO: add HTTP query switch for handling requests in simple form and using Redis Streams
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

//        var_dump($products);
//        var_dump($data);
//        var_dump($found);
//        die(implode(':', [__METHOD__, __FILE__, __LINE__]) . PHP_EOL);
//
//
//
//        die(implode(':', [__METHOD__, __FILE__, __LINE__]) . PHP_EOL);


        $this->save($db, $products, $data);

        return 'Processed order successfully in simple mode';

//        $products = array_map('intval', array_column($input['items'], 'product_id'));
    }

    /**
     * @param PDO $db
     * @param array<int, int> $products
     * @param array $data
     * @return void
     * @throws \Throwable
     */
    public function save(PDO $db, array $products, array $data): void
    {
        try {
            $db->beginTransaction();

            $productId = $count = null;
            $stmt = $db->prepare(
                "UPDATE product_remainders SET items_count = items_count - ? WHERE product_id = ? LIMIT 1",
            );

//            echo '<pre>';
//            print_r($products);
//            die(implode(':', [__METHOD__, __FILE__, __LINE__]) . PHP_EOL);


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
        } catch (\Throwable $e) {
            $db->rollBack();

            throw $e;
        }
    }
}
