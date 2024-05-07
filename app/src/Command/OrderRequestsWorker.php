<?php

namespace App\Command;

use App\Service\Container;
use JsonException;
use PDO;
use Redis;
use RedisException;

class OrderRequestsWorker
{
    protected const int BATCH_SIZE = 100;

    /**
     * @var int
     */
    protected int $processed;

    /**
     * @var int
     */
    protected int $succeeded;

    /**
     * @var int
     */
    protected int $failed;

    /**
     * Timeout 10 seconds 1000msec = 1sec
     */
    protected const int BLOCK_TIME = 10000;

    /**
     * @param Container $container
     */
    public function __construct(private readonly Container $container)
    {
    }

    /**
     * @param string $stream
     * @param string $group
     * @param string $consumer
     * @return void
     * @throws RedisException
     */
    public function run(string $stream, string $group, string $consumer): void
    {
        $this->succeeded = $this->failed = $this->processed = 0;
        $redis = $this->container->redis();
        $db = $this->container->db();

        $redis->xGroup('CREATE', $stream, $group, '$', true);

        while (true) {
            try {
                $data = $redis->xReadGroup($group, $consumer, [$stream => '>'], self::BATCH_SIZE, self::BLOCK_TIME);

                if (!$data) {
                    echo "\nWaiting...\n";
//                    echo '.';
                    continue;
                }

                $this->processResults($redis, $db, $group, $data);
                unset($data);

//                echo '+'; // processed batch successfully
            } catch (\Throwable $e) {
                //log_error($e->getMessage());

//                echo '-'; // failure
            }

            $this->showSummary();
        }
    }

    protected function showSummary(): void
    {
        echo "\rOrders: processed - $this->processed, succeeded - $this->succeeded, failed - $this->failed";
    }

    /**
     * @param Redis $redis
     * @param PDO $db
     * @param string $group
     * @param array<array{
     *      delivery: array{address: string, phone: string, email: string},
     *      items: array<array{product_id: int, count: int}>
     *  }> $data
     * @return void
     * @throws JsonException
     * @throws RedisException
     */
    protected function processResults(Redis $redis, PDO $db, string $group, array $data): void
    {
        if (!$data) {
            return;
        }

        $insertStmt = $db->prepare(
            "INSERT INTO `orders` (price_total, payload, items_count, created_at, updated_at) " .
            "VALUES (:price, :payload, :count, NOW(), NOW())"
        );

        foreach ($data as $selStream => $items) {
            $savedKeys = [];
            foreach ($items as $id => $item) {
                ++$this->processed;

                $payload = json_decode(
                    $item['payload'],
                    true,
                    flags: JSON_THROW_ON_ERROR
                );

                $insertStmt->execute([
                    'price' => $this->calcPriceTotal($item),
                    'payload' => $item['payload'],
                    'count' => count($payload['items']),
                ]);

                $orderId = $db->lastInsertId();
                $products = $this->parseProducts($payload);
                $productUpdates = [];

                // combine results
                foreach ($products as $prodId => $count) {
                    $productUpdates['p:' . $prodId] = $count;
                }

                $result = $this->saveRemainders($redis, $productUpdates);

                // did we exceed inventory capacity due to high concurrency operations?
                if (min($result) < 0) {
                    $this->rollbackRedisTransaction($redis, $productUpdates);

                    if ($orderId) {
                        $db->exec(
                            'UPDATE `orders` SET status = "cancelled", ' .
                            'updated_at = NOW() WHERE id = ' . (int)$orderId
                        );
                    }

                    ++$this->failed;
                } else {
                    ++$this->succeeded;
                }

                $savedKeys[] = $id;
                unset($productUpdates);
            }

            $redis->xAck($selStream, $group, $savedKeys);
            unset($savedKeys);
        }
    }

    protected function calcPriceTotal(array $data): string
    {
        return '100.00'; // stub. No real data provided
    }

    protected function parseProducts(array $data): array
    {
        $products = [];
        foreach ($data['items'] as $item) {
            $count = (int)$item['count'];

            if ($count <= 0) {
                throw new \LogicException('Ordered items count must be greater than 0');
            }

            $products[(int)$item['product_id']] = $count;
        }

        return $products;
    }

    protected function saveRemainders(Redis $redis, array $updateData): array
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
