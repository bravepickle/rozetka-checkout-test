<?php
declare(strict_types=1);

namespace App\Service;

use PDO;
use Redis;

/**
 * Service locator container
 */
class Container
{
    /**
     * @var Redis|null Redis connections
     */
    protected ?Redis $redis;

    /**
     * @var Router Web router
     */
    protected Router $router;

    /**
     * @var PDO DB connection
     */
    protected PDO $db;

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

    /**
     * Get MySQL DB connection
     * @return PDO
     * @const DB_DSN
     */
    public function db(): PDO
    {
        if (!isset($this->db)) {
            $this->db = new PDO(DB_DSN, DB_USER, DB_PASS);
        }

        return $this->db;
    }
}
