<?php

namespace App\PurchaseProcessor;

use App\Exception\HttpExhaustedException;
use Redis;

class StreamPurchaseProcessor extends AbstractPurchaseProcessor
{
    /**
     * @inheritDoc
     * @throws \RedisException
     * @throws \Throwable
     */
    #[\Override]
    public function process(?array $data): string
    {
        $this->parseInput($data);

        // TODO: check if enough inventory
//        var_dump($data);
//        die(implode(':', [__METHOD__, __FILE__, __LINE__]) . PHP_EOL);

        $redis = $this->container->redis();

        // TODO: add data
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
        $multi = $redis->multi();
        foreach ($updateData as $productKey => $count) {
            $multi->incrBy($productKey, $count); // compensate previous decrement
        }

        return $multi->exec();
    }
}
