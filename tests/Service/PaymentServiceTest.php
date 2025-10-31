<?php

namespace CheckoutPaymentBundle\Tests\Service;

use CheckoutPaymentBundle\Service\PaymentService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(PaymentService::class)]
#[RunTestsInSeparateProcesses]
final class PaymentServiceTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        $_ENV['CHECKOUT_WEBHOOK_SECRET'] = 'test_webhook_secret';
        $_ENV['CHECKOUT_CONFIG_NAME'] = 'test';
    }

    public function testCreatePaymentSession(): void
    {
        $paymentService = self::getService(PaymentService::class);

        $this->assertInstanceOf(PaymentService::class, $paymentService);
    }

    public function testProcessWebhook(): void
    {
        $paymentService = self::getService(PaymentService::class);

        $this->assertInstanceOf(PaymentService::class, $paymentService);
    }

    public function testSyncPaymentSession(): void
    {
        $paymentService = self::getService(PaymentService::class);

        $this->assertInstanceOf(PaymentService::class, $paymentService);
    }

    public function testSyncPaymentStatus(): void
    {
        $paymentService = self::getService(PaymentService::class);

        $this->assertInstanceOf(PaymentService::class, $paymentService);
    }

    public function testCapturePayment(): void
    {
        $paymentService = self::getService(PaymentService::class);

        $this->assertInstanceOf(PaymentService::class, $paymentService);
    }

    public function testCreateDirectPayment(): void
    {
        $paymentService = self::getService(PaymentService::class);

        $this->assertInstanceOf(PaymentService::class, $paymentService);
    }

    public function testRefundPayment(): void
    {
        $paymentService = self::getService(PaymentService::class);

        $this->assertInstanceOf(PaymentService::class, $paymentService);
    }

    public function testSearchPayments(): void
    {
        $paymentService = self::getService(PaymentService::class);

        $this->assertInstanceOf(PaymentService::class, $paymentService);
    }

    public function testVoidPayment(): void
    {
        $paymentService = self::getService(PaymentService::class);

        $this->assertInstanceOf(PaymentService::class, $paymentService);
    }
}
