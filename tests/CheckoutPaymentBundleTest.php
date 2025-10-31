<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\Tests;

use CheckoutPaymentBundle\CheckoutPaymentBundle;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;

/**
 * @internal
 * @phpstan-ignore symplify.forbiddenExtendOfNonAbstractClass
 */
#[CoversClass(CheckoutPaymentBundle::class)]
#[RunTestsInSeparateProcesses]
final class CheckoutPaymentBundleTest extends AbstractBundleTestCase
{
}
