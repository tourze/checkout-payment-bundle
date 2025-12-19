<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\Tests\Service;

use CheckoutPaymentBundle\Entity\Payment;
use CheckoutPaymentBundle\Exception\PaymentException;
use CheckoutPaymentBundle\Repository\PaymentRefundRepository;
use CheckoutPaymentBundle\Repository\PaymentRepository;
use CheckoutPaymentBundle\Service\CheckoutApiClient;
use CheckoutPaymentBundle\Service\PaymentOperations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(PaymentOperations::class)]
#[RunTestsInSeparateProcesses]
final class PaymentOperationsTest extends AbstractIntegrationTestCase
{
    private PaymentOperations $operations;

    protected function onSetUp(): void
    {
        // 直接从容器获取 PaymentOperations 服务
        $this->operations = self::getService(PaymentOperations::class);
    }

    public function testCapturePaymentThrowsExceptionWhenPaymentNotFound(): void
    {
        // 使用真实的 Repository，pay_123 不存在，会返回 null
        // 这正是我们测试的场景

        $this->expectException(PaymentException::class);
        $this->operations->capturePayment('pay_123');
    }

    public function testVoidPaymentThrowsExceptionWhenPaymentNotFound(): void
    {
        // 使用真实的 Repository，pay_123 不存在，会返回 null
        // 这正是我们测试的场景

        $this->expectException(PaymentException::class);
        $this->operations->voidPayment('pay_123');
    }

    public function testRefundPaymentThrowsExceptionWhenPaymentNotFound(): void
    {
        // 使用真实的 Repository，pay_123 不存在，会返回 null
        // 这正是我们测试的场景

        $this->expectException(PaymentException::class);
        $this->operations->refundPayment('pay_123');
    }
}
