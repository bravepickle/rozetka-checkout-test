<?php
declare(strict_types=1);

namespace App\Service;

/**
 * Services container
 */
class Application
{
    protected Container $container;

    public function boot(): self
    {
        $this->container = new Container();

        if (!$this->isCli()) {
            session_start(); // init session for web apps always
        }

        return $this;
    }

    public function isCli(): bool
    {
        return php_sapi_name() === 'cli';
    }

    /**
     * Get services container
     * @return Container
     */
    public function container(): Container
    {
        return $this->container;
    }
}
