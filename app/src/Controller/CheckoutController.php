<?php
declare(strict_types=1);

namespace App\Controller;

use App\Contract\ControllerInterface;
use App\Exception\HttpBadRequestException;
use App\Exception\HttpUnauthorizedException;
use App\Model\Request;
use App\PurchaseProcessor\RedisPurchaseProcessor;
use App\PurchaseProcessor\SimplePurchaseProcessor;
use App\PurchaseProcessor\StreamPurchaseProcessor;
use App\Service\Container;
use RedisException;
use Throwable;

class CheckoutController implements ControllerInterface
{
    public function supports(Request $request): bool
    {
        return $request->query('action') === 'purchase' && $request->isPost();
    }

    /**
     * @param Request $request
     * @param Container $container
     * @return string|null
     * @throws HttpUnauthorizedException
     * @throws HttpBadRequestException
     * @throws RedisException
     * @throws Throwable
     */
    public function handle(Request $request, Container $container): ?string
    {
        $input = $request->body;

        if (empty($_SESSION['username'])) {
            if ($request->query('skip_auth', false)) {
                $_SESSION['username'] = 'anon.'; // skip authentication process
            } elseif (!empty($input['username'])) {
                $_SESSION['username'] = $input['username']; // imitation of authentication
            } else {
                throw new HttpUnauthorizedException();
            }
        }

        session_write_close(); // close session ASAP to prevent errors with concurrency

        $mode = $request->query('mode');
        if ($mode === 'simple') {
            $processor = new SimplePurchaseProcessor($container);
        } elseif ($mode === 'redis') {
            $processor = new RedisPurchaseProcessor($container);
        } else {
            $processor = new StreamPurchaseProcessor($container);
        }

        ignore_user_abort(); // ensure that all script processing will be finished. Risky operation
        $response = $processor->process($input);

        if ($request->query('session_stop', true) && session_status() === PHP_SESSION_ACTIVE) {
            session_destroy(); // processing request finished, cleanup session resources
        }

        return $response . ': ' . $_SESSION['username'] . PHP_EOL;
    }
}
