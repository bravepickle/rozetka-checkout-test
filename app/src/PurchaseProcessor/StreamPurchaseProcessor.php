<?php

namespace App\PurchaseProcessor;

use App\Exception\HttpBadRequestException;
use App\Exception\HttpExhaustedException;

class StreamPurchaseProcessor extends AbstractPurchaseProcessor
{
    /**
     * @inheritDoc
     */
    #[\Override]
    public function process(?array $data): string
    {
        $inputProducts = $this->parseInput($data);

        $redis = $this->container->redis();

        $keyProductMap = [];
        $productKeyMap = [];
        $productIds = [];
        foreach ($inputProducts as $productId => $count) {
            $productKeyMap[$productId] = 'p:' . $productId;
            $keyProductMap['p:' . $productId] = (int)$productId;
            $productIds[] = $productId;
        }

        $found = $redis->mGet($productKeyMap);

        $updateData = [];
        $missingProductIds = [];
        $foundKeyValues = [];
        foreach ($found as $index => $count) {
            $productId = $productIds[$index];
            if ($count === false) {
                $missingProductIds[] = $productId;
            } else {
//                $updateData[$productKeyMap[$productId]] = $inputProducts[$productId];
                $updateData[$productKeyMap[$productId]] = 1009910;
                $foundKeyValues[$productKeyMap[$productId]] = (int)$count;
//                $foundKeyValues[$productKeyMap[$productId]] = 1009910;
            }
        }

        if ($missingProductIds) {
            echo '<pre>';
            var_dump(['missing product ids' => $missingProductIds, 'found' => $found]);
            die(implode(':', [__METHOD__, __FILE__, __LINE__]) . PHP_EOL);

            // FIXME: send to queue missing products in redis
            throw new \LogicException('Processing missing product ids in Redis is not implemented');

            // TODO: try reading from mysql db - as a temporary fallback
//        $db = $this->container->db();
//
//        $found = $db->query(
//            sprintf(
//                'SELECT product_id, items_count from product_remainders WHERE product_id IN (%s)',
//                implode(', ', array_keys($products))
//            )
//        )->fetchAll(PDO::FETCH_ASSOC);
        }



        if (!$updateData) {
            throw new \LogicException('Failed to process products in the order');
        }

        foreach ($updateData as $productKey => $count) {
            if ($count <= 0 || $foundKeyValues[$productKey] < $count) {
//                throw new HttpExhaustedException('Missing enough items in inventory to fulfill your order');
            }
        }

        // Redis transaction
        $multi = $redis->multi();
        foreach ($updateData as $productKey => $count) {
            $multi->decrBy($productKey, $count); // highly concurrent processes values taken from db cannot be used
        }
        $result = $multi->exec();

        // TODO: if by results we have any value below 0 then we must rollback all operations by compensating numbers
        //  and notify system + user on reaching limit, unable complete purchase request

        // did we exceed inventory capacity due to high concurrency operations?
        if (min($result) < 0) {
            // TODO: notify system worker that we reached below zero

            // rollback transaction by compensating changes
            $multi = $redis->multi();
            foreach ($updateData as $productKey => $count) {
//                $multi->incrBy($productKey, $count); // highly concurrent processes values taken from db cannot be used
                $multi->incrBy($productKey, $count); // highly concurrent processes values taken from db cannot be used
            }
            $result = $multi->exec();

            var_dump($result);
            die(implode(':', [__METHOD__, __FILE__, __LINE__]) . PHP_EOL);


            throw new HttpExhaustedException('2 Missing enough items in inventory to fulfill your order');
        }

        var_dump($found);
        var_dump($updateData);
        var_dump($result);
        var_dump(['reverted' => $redis->mget(array_keys($updateData))]);
        die(implode(':', [__METHOD__, __FILE__, __LINE__]) . PHP_EOL);


        // TODO: send to queue missing product_ids or reached 0?

        var_dump(['map' => $productKeyMap, 'missing' => $missingProductIds, 'found' => $updateData]);
        die(implode(':', [__METHOD__, __FILE__, __LINE__]) . PHP_EOL);




        if (!$found || count($found) < count($inputProducts)) {
            throw new HttpBadRequestException('Failed to find some products in the order');
        }

        /** @var array{product_id: int, items_count: int} $productRemainder */
        foreach ($found as $productRemainder) {
            if ($productRemainder['items_count'] <= 0 ||
                $productRemainder['items_count'] < $inputProducts[$productRemainder['product_id']]) {
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


        $this->save($db, $inputProducts, $data);

        return 'Processed order successfully in stream mode';

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
