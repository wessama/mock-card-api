<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class PaymentHandlerProvider
{
    public function __construct(
        #[AutowireIterator('app.payment_handler')] private readonly iterable $handlers
    ) {
    }

    public function getHandler(string $type): PaymentHandlerInterface
    {
        foreach ($this->handlers as $handler) {
            $handlerClass = get_class($handler);
            if (str_contains($handlerClass, ucfirst($type))) {
                return $handler;
            }
        }

        throw new \InvalidArgumentException("No handler found for type: {$type}");
    }
}
