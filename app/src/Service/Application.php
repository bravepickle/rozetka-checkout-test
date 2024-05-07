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
            $this->initSession();
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

    /**
     * @return void
     */
    public function initSession(): void
    {
        session_start(); // init session for web apps always

        // add some tracking info to keep more info on user actions
        $_SESSION['time'] = time(); // save something to session

        // track number of calls done within single session by a user
        if (!isset($_SESSION['api_calls_total'])) {
            $_SESSION['api_calls_total'] = 0;
        }

        ++$_SESSION['api_calls_total'];

        session_write_close(); // close session ASAP to prevent errors with concurrency
    }
}
