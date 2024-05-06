<?php

namespace App\PurchaseProcessor;

use App\Exception\HttpExhaustedException;
use LogicException;
use RedisException;
use Throwable;

class StreamPurchaseProcessor extends AbstractPurchaseProcessor
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
        $productIds = [];
        $productKeyMap = [];
        foreach ($inputProducts as $productId => $count) {
            $productKeyMap[$productId] = 'p:' . $productId;
            $productIds[] = $productId;
        }

        $redis = $this->container->redis();
        $keyCounts = $redis->mGet($productKeyMap);
        foreach ($keyCounts as $index => $count) {
            if ($count === false) {
                throw new LogicException('Processing missing product ids in Redis is not implemented');
            }

            $productId = $productIds[$index];
            if ($count <= 0 || $count < $inputProducts[$productId]) {
                throw new HttpExhaustedException('Missing enough items in inventory to fulfill your order');
            }
        }

        $redis->xAdd(
            STREAM_NAME,
            '*',
            [
                'uid' => $_SESSION['username'],
                'payload' => json_encode(
                    $data,
                    JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES
                ),
            ]
        );

        $redis->close(); // close ASAP

        // TODO: add cronjob to sync from redis to db and visa versa

        return 'Processed order successfully in stream mode';
    }
}
