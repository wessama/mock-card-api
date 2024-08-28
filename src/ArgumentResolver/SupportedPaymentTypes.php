<?php

namespace App\ArgumentResolver;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class SupportedPaymentTypes implements ValueResolverInterface
{
    private array $supportedPaymentTypes;

    public function __construct(string $supportedPaymentTypes)
    {
        $this->supportedPaymentTypes = explode(',', $supportedPaymentTypes);
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return 'supportedPaymentTypes' === $argument->getName();
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        yield $this->supportedPaymentTypes;
    }
}
