<?php

namespace App\Command;

use App\Exception\HttpExhaustedException;
use App\Service\Container;
use JsonException;
use PDO;
use Redis;
use RedisException;

class OrderRequestsWorker
{
    protected const int BATCH_SIZE = 100;
//    protected const int BLOCK_TIME = 1000;
    protected const int BLOCK_TIME = 0;


    public function __construct(private Container $container)
    {
    }


    protected function info(string $message): void
    {
        // TODO: use logger. Its for debug only
        echo '[INFO] ' . $message . PHP_EOL;
    }

    public function run(string $stream, string $group, string $consumer): void
    {
//        > XGROUP CREATE race:italy italy_riders $ MKSTREAM
//OK
        $redis = $this->container->redis();
        $db = $this->container->db();

//        $redis->xGroup('CREATE', $stream, $group, $consumer, true, '$');
        $redis->xGroup('CREATE', $stream, $group, '$', true);
//        $redis->rawCommand("XGROUP CREATE $stream $group $ MKSTREAM");

        while (true) {
            try {
//            xreadgroup group france_riders John count 1 block 0 streams race:france >
                $data = $redis->xReadGroup($group, $consumer, [$stream => '>'], self::BATCH_SIZE, self::BLOCK_TIME);

                if (!$data) {
                    echo '.';
                    continue;
                }

                $this->processResults($redis, $db, $group, $data);

            } catch (\Throwable $e) {
                echo '[ERROR] ' . $e->getMessage() . PHP_EOL;
                log_error($e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            }
        }

        die(implode(':', [__METHOD__, __FILE__, __LINE__]) . PHP_EOL);

    }

    /**
     * @param Redis $redis
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
        var_dump($data);

        // TODO: check if enough inventory

//        $insertOrdersQuery = "INSERT INTO `orders` (price_total, payload, items_count, created_at, updated_at) ";
        $insertStmt = $db->prepare(
            "INSERT INTO `orders` (price_total, payload, items_count, created_at, updated_at) " .
            "VALUES (:price, :payload, :count, NOW(), NOW())"
        );

        foreach ($data as $selStream => $items) {
            $savedKeys = [];
//            $productUpdates = [];
//            $orderId = null;
//            $productOrderMap = [];

            foreach ($items as $id => $item) {
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

//                var_dump(['order id' => $orderId]);
//                die(implode(':', [__METHOD__, __FILE__, __LINE__]) . PHP_EOL);


//                $this->makePurchase($payload, $productUpdates);

                $products = $this->parseProducts($payload);
                $productUpdates = [];

                // combine results
                foreach ($products as $prodId => $count) {
                    $productUpdates['p:' . $prodId] = $count;
//                    $productOrderMap[$prodId] = $orderId;
//                    if (!isset($productUpdates[$prodId])) {
//                        $productUpdates[$prodId] = $count;
//                    } else {
//                        $productUpdates[$prodId] += $count;
//                    }
                }

//                $this->makePurchase($payload, $productUpdates);

                $result = $this->saveRemainders($redis, $productUpdates);

//                var_dump(['incremented' => $result]);
//            die(implode(':', [__METHOD__, __FILE__, __LINE__]) . PHP_EOL);


                // did we exceed inventory capacity due to high concurrency operations?
                if (min($result) < 0) {

//                    var_dump($result);
//                    die(implode(':', [__METHOD__, __FILE__, __LINE__]) . PHP_EOL);

                    $this->rollbackRedisTransaction($redis, $productUpdates);

                    if ($orderId) {
                        $db->exec(
                            'UPDATE `orders` SET status = "cancelled", ' .
                            'updated_at = NOW() WHERE id = ' . (int)$orderId
                        );
                    }
                    // TODO: notify system worker that we reached below zero
                }

                // TODO: validate counts available and non zero

                var_dump([$id => $item, 'parsed' => $payload, 'products' => $products]);

                $savedKeys[] = $id;
            }

//            $result = $this->saveRemainders($redis, $productUpdates);
//
//            var_dump(['incremented' => $result]);
////            die(implode(':', [__METHOD__, __FILE__, __LINE__]) . PHP_EOL);
//
//
//            // did we exceed inventory capacity due to high concurrency operations?
//            if (min($result) < 0) {
//                $this->rollbackRedisTransaction($redis, $productUpdates);
//
//                if ($orderId) {
//                    $db->exec(
//                            'UPDATE `orders` SET status ="cancelled", ' .
//                            'updated_at = NOW() WHERE id = ' . (int)$orderId
//                    );
//                }
//                // TODO: notify system worker that we reached below zero
//            }

            // make batch product updates grouped by product

            echo "now check pending!" . PHP_EOL;

            sleep(30);

            $redis->xAck($selStream, $group, $savedKeys);

        }

        die(implode(':', [__METHOD__, __FILE__, __LINE__]) . PHP_EOL);
    }

    protected function calcPriceTotal(array $data): string
    {
        return '100.00'; // stub. No real data provided
    }

    protected function makePurchase(array $data, array &$productUpdates): void
    {
        $products = $this->parseProducts($data);
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
        // TODO: compare to MySQL saves
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
