<?php
declare(strict_types=1);

namespace App\Controller;

use App\Contract\ControllerInterface;
use App\Model\Request;

class AuthController implements ControllerInterface
{
    public function supports(Request $request): bool
    {
        return $request->query('action') === 'auth' && $request->isPost();
    }

    public function handle(Request $request): ?string
    {
        return sprintf('Authenticated Successfully %s', $request->body['username'] ?? 'N/A');
    }
}
