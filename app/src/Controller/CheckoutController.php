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
            throw new HttpUnauthorizedException();
        }
        // TODO: add validation body, if necessary
        $input = $request->body;

//        $db = $container->db();
//        $this->parseInput($input, $db);

        if ($request->query('mode') === 'simple') {
            $processor = new SimplePurchaseProcessor($container);
        } else {
            $processor = new StreamPurchaseProcessor($container);
        }

//        $db->exec("")

//        var_dump($_SESSION);
//        die(implode(':', [__METHOD__, __FILE__, __LINE__]) . PHP_EOL);


        $response = $processor->process($input);

        // TODO: compare enabled and disabled cleanup sessions
        // TODO: pass to events session id and cleanup on success?
        // TODO: expire sessions

        if ($request->query('session_stop', true)) {
            session_destroy(); // processing request finished, cleanup session resources
        }

        return $response . ': ' . $_SESSION['username'];

//        var_dump($input);
//        var_export($response);
////        var_dump(json_encode($input, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
////        var_dump($request);
//        die(implode(':', [__METHOD__, __FILE__, __LINE__]) . PHP_EOL);
//
//        return sprintf('Authenticated Successfully: %s', $request->body['username'] ?? 'N/A');
    }
}
