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

        $stmt = $container->db()->prepare(
            "INSERT INTO `orders` (price_total, payload, items_count, created_at, updated_at) " .
            "VALUES (:price, :payload, :count, NOW(), NOW())"
        );

        $stmt->execute(['price' => '100.00', 'payload' => json_encode($input), 'count' => count($input['items'] ?? [])]);

        var_dump($input);
        var_export($input);
//        var_dump(json_encode($input, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
//        var_dump($request);
        die(implode(':', [__METHOD__, __FILE__, __LINE__]) . PHP_EOL);

        return sprintf('Authenticated Successfully: %s', $request->body['username'] ?? 'N/A');
    }
}
