<?php
declare(strict_types=1);

namespace App\Service;

use Redis;

/**
 * Service locator container
 */
class Container
{
    protected ?Redis $redis;

    protected Router $router;

    /**
     * Get Redis connection
     * @return Redis
     */
    public function redis(): Redis
    {
        if (!isset($this->redis)) {
            $this->redis = new Redis(['host' => REDIS_HOST]);
        }

        return $this->redis;
    }

    /**
     * Get router
     * @return Router
     */
    public function router(): Router
    {
        if (!isset($this->router)) {
            $this->router = new Router();
        }

        return $this->router;
    }
}
