<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\DependencyInjection;

use Tourze\SymfonyDependencyServiceLoader\AutoExtension;

class CheckoutPaymentExtension extends AutoExtension
{
    public function getAlias(): string
    {
        return 'checkout_payment';
    }

    protected function getConfigDir(): string
    {
        return __DIR__ . '/../Resources/config';
    }
}
