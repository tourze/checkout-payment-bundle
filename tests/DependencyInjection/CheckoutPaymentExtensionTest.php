<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\Tests\DependencyInjection;

use CheckoutPaymentBundle\DependencyInjection\CheckoutPaymentExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;

/**
 * @internal
 */
#[CoversClass(CheckoutPaymentExtension::class)]
final class CheckoutPaymentExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
    public function testExtensionAlias(): void
    {
        $extension = new CheckoutPaymentExtension();
        $this->assertEquals('checkout_payment', $extension->getAlias());
    }
}
