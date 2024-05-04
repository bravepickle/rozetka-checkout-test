<?php
declare(strict_types=1);

namespace App\Contract;

use App\Model\Request;
use App\Service\Container;

/**
 * Controller Interface
 */
interface ControllerInterface
{
    /**
     * Check if supports requests handling
     * @param Request $request
     * @return bool
     */
    public function supports(Request $request): bool;

    /**
     * Handle request
     * @param Request $request
     * @param Container $container
     * @return string|null
     */
    public function handle(Request $request, Container $container): ?string;
}
