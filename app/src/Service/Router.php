<?php
declare(strict_types=1);

namespace App\Service;

use App\Contract\ControllerInterface;
use App\Controller\AuthController;
use App\Controller\CheckoutController;
use App\Controller\DefaultController;
use App\Model\Request;
use Exception;
use RuntimeException;

class Router
{
    /**
     * @var ControllerInterface[]
     */
    protected array $controllers = [
        AuthController::class,
        CheckoutController::class,
        DefaultController::class,
    ];

    /**
     * Resolve routing for the request
     *
     * @param Request $request
     * @param Container $container
     * @return string
     * @throws Exception
     */
    public function resolve(Request $request, Container $container): string
    {
        foreach ($this->controllers as $class) {
            $controller = new $class();
            if ($controller->supports($request)) {
                $response = $controller->handle($request, $container);

                if ($response === null) {
                    throw new Exception('Failed to handle request');
                }

                return $response;
            }
        }

        throw new RuntimeException('Not found');
    }
}
