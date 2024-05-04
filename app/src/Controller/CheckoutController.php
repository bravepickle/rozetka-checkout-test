<?php
declare(strict_types=1);

namespace App\Controller;

use App\Contract\ControllerInterface;
use App\Exception\HttpUnauthorizedException;
use App\Model\Request;
use App\PurchaseProcessor\SimplePurchaseProcessor;
use App\PurchaseProcessor\StreamPurchaseProcessor;
use App\Service\Container;

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
     */
    public function handle(Request $request, Container $container): ?string
    {
        if (empty($_SESSION['username'])) {
            if ($request->query('skip_auth', false)) {
                $_SESSION['username'] = 'anon.';
            } else {
                throw new HttpUnauthorizedException();
            }
        }
        // TODO: add validation body, if necessary
        $input = $request->body;

        if ($request->query('mode') === 'simple') {
            $processor = new SimplePurchaseProcessor($container);
        } else {
            $processor = new StreamPurchaseProcessor($container);
        }

        ignore_user_abort(); // ensure that all script processing will be finished. Risky operation
        $response = $processor->process($input);

        // TODO: compare enabled and disabled cleanup sessions
        // TODO: pass to events session id and cleanup on success?
        // TODO: expire sessions

        if ($request->query('session_stop', true) && session_status() === PHP_SESSION_ACTIVE) {
            session_destroy(); // processing request finished, cleanup session resources
        }

        return $response . ': ' . $_SESSION['username'] . PHP_EOL;
    }
}
