<?php

namespace App\Tests\Unit\Service\Gateway;

use App\Service\PaymentHandlerInterface;
use PHPUnit\Framework\TestCase;

class PaymentHandlersTest extends TestCase
{
    public function testPaymentHandlersExistForEnabledSystems(): void
    {
        // Get the supported payment types from the environment variable
        $supportedPaymentTypes = explode(',', getenv('SUPPORTED_PAYMENT_TYPES'));

        foreach ($supportedPaymentTypes as $type) {
            $handlerClass = sprintf('App\\Service\\Gateway\\%sHandler', ucfirst($type));

            // Check if the handler class exists
            $this->assertTrue(class_exists($handlerClass), sprintf('Handler class "%s" does not exist.', $handlerClass));

            // Check if the handler class implements PaymentHandlerInterface
            $this->assertTrue(
                is_subclass_of($handlerClass, PaymentHandlerInterface::class),
                sprintf('Handler class "%s" does not implement PaymentHandlerInterface.', $handlerClass)
            );
        }
    }

    public function testNoHandlersInOtherNamespaces(): void
    {
        $handlerDirectory = __DIR__.'/../../../../src/Service/Gateway';

        // Get all PHP files in the Service/Gateway directory
        $handlerFiles = glob($handlerDirectory.'/*Handler.php');

        foreach ($handlerFiles as $file) {
            // Derive the class name from the file path
            $handlerClass = 'App\\Service\\Gateway\\'.basename($file, '.php');

            // Check if the class exists
            $this->assertTrue(class_exists($handlerClass), sprintf('Handler class "%s" does not exist.', $handlerClass));

            // Check if it implements PaymentHandlerInterface
            $this->assertTrue(
                is_subclass_of($handlerClass, PaymentHandlerInterface::class),
                sprintf('Handler class "%s" does not implement PaymentHandlerInterface.', $handlerClass)
            );
        }
    }
}
