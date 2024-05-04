<?php
declare(strict_types=1);

namespace App\Controller;

use App\Contract\ControllerInterface;
use App\Model\Request;
use App\Service\Container;

class AuthController implements ControllerInterface
{
    public function supports(Request $request): bool
    {
        return $request->query('action') === 'auth' && $request->isPost();
    }

    public function handle(Request $request, Container $container): ?string
    {
        $_SESSION['username'] = $request->body['username'] ?? 'anonymous';

//        print_r(['sess' => $_SESSION, 'cookie' => $_COOKIE, 'session_id' => session_id()]);

        return sprintf('Authenticated Successfully: %s', $_SESSION['username']);
    }
}
