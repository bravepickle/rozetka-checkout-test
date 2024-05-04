<?php

namespace App\PurchaseProcessor;

use App\Exception\HttpBadRequestException;

class StreamPurchaseProcessor extends AbstractPurchaseProcessor
{
    /**
     * @inheritDoc
     */
    #[\Override]
    public function process(?array $data): string
    {
        throw new \LogicException('TO BE DEFINED!');
    }
}
