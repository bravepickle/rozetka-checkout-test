<?php
declare(strict_types=1);

namespace App\Controller;

use App\Contract\ControllerInterface;
use App\Model\Request;
use App\Service\Container;

class CheckoutController implements ControllerInterface
{
    public function supports(Request $request): bool
    {
        return $request->query('action') === 'purchase' && $request->isPost();
    }

    public function handle(Request $request, Container $container): ?string
    {
        // TODO: add validation body, if necessary
        $input = $request->body;
        var_dump($input);
//        var_dump($request);
        die(implode(':', [__METHOD__, __FILE__, __LINE__]) . PHP_EOL);

        return sprintf('Authenticated Successfully: %s', $request->body['username'] ?? 'N/A');
    }
}
