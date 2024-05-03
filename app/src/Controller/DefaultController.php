<?php
declare(strict_types=1);

namespace App\Controller;

use App\Contract\ControllerInterface;
use App\Model\Request;

class DefaultController implements ControllerInterface
{
    public function supports(Request $request): bool
    {
        return true; // supports all
    }

    public function handle(Request $request): ?string
    {
        return 'Welcome!';
    }
}
