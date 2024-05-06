<?php

namespace App\PurchaseProcessor;

use App\Exception\HttpExhaustedException;
use Redis;
use RedisException;
use Throwable;

class RedisPurchaseProcessor extends AbstractPurchaseProcessor
{
    /**
     * @inheritDoc
     * @throws RedisException
     * @throws Throwable
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
                $updateData[$productKeyMap[$productId]] = $inputProducts[$productId];
                $foundKeyValues[$productKeyMap[$productId]] = (int)$count;
            }
        }

        if ($missingProductIds) {
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
                throw new HttpExhaustedException('Missing enough items in inventory to fulfill your order');
            }
        }

        $result = $this->save($redis, $updateData);

        // did we exceed inventory capacity due to high concurrency operations?
        if (min($result) < 0) {
            $this->rollbackRedisTransaction($redis, $updateData);
            // TODO: notify system worker that we reached below zero

            throw new HttpExhaustedException('Missing enough items in inventory to fulfill your order');
        }

        $redis->close(); // close connection ASAP

        // TODO: add cronjob to sync from redis to db and visa versa

        return 'Processed order successfully in redis mode';
    }

    protected function save(Redis $redis, array $updateData): array
    {
        // Redis transaction
        $multi = $redis->pipeline();
        foreach ($updateData as $productKey => $count) {
            $multi->decrBy($productKey, $count); // highly concurrent processes values taken from db cannot be used
        }

        return $multi->exec();
    }

    protected function rollbackRedisTransaction(Redis $redis, array $updateData): array
    {
        $multi = $redis->pipeline();
        foreach ($updateData as $productKey => $count) {
            $multi->incrBy($productKey, $count); // compensate previous decrement
        }

        return $multi->exec();
    }
}
