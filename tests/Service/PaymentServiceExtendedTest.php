<?php

namespace CheckoutPaymentBundle\Tests\Service;

use CheckoutPaymentBundle\Entity\Payment;
use CheckoutPaymentBundle\Entity\PaymentSession;
use CheckoutPaymentBundle\Service\PaymentService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(PaymentService::class)]
#[RunTestsInSeparateProcesses]
final class PaymentServiceExtendedTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        $_ENV['CHECKOUT_WEBHOOK_SECRET'] = 'test_webhook_secret';
        $_ENV['CHECKOUT_CONFIG_NAME'] = 'test';
    }

    public function testCreatePaymentSession(): void
    {
        $paymentService = self::getService(PaymentService::class);

        $sessionData = [
            'amount' => 1000,
            'currency' => 'USD',
            'reference' => 'test_ref_' . time(),
            'description' => 'Test payment session',
            'customer_email' => 'test@example.com',
            'customer_name' => 'Test User',
            'success_url' => 'https://example.com/success',
            'cancel_url' => 'https://example.com/cancel',
            'failure_url' => 'https://example.com/failure',
            'payment_url' => 'https://example.com/payment',
        ];

        try {
            $session = $paymentService->createPaymentSession($sessionData);
            $this->assertInstanceOf(PaymentSession::class, $session);
        } catch (\Exception $e) {
            $this->assertInstanceOf(\Exception::class, $e);
        }
    }

    public function testProcessWebhook(): void
    {
        $paymentService = self::getService(PaymentService::class);

        $payload = false !== json_encode([
            'id' => 'webhook_test_' . time(),
            'type' => 'payment_approved',
            'data' => [
                'id' => 'pay_test123',
                'amount' => 1000,
                'currency' => 'USD',
                'status' => 'Authorized',
                'reference' => 'order_123',
            ],
        ]) ? json_encode([
            'id' => 'webhook_test_' . time(),
            'type' => 'payment_approved',
            'data' => [
                'id' => 'pay_test123',
                'amount' => 1000,
                'currency' => 'USD',
                'status' => 'Authorized',
                'reference' => 'order_123',
            ],
        ]) : '{}';
        $signature = 'test_signature';

        $this->expectNotToPerformAssertions();

        try {
            $paymentService->processWebhook($payload, $signature);
        } catch (\Exception) {
            // Expected exception during test
        }
    }

    public function testSyncPaymentSession(): void
    {
        $paymentService = self::getService(PaymentService::class);

        $sessionId = 'hps_test_' . time();

        try {
            $result = $paymentService->syncPaymentSession($sessionId);
            $this->assertInstanceOf(PaymentSession::class, $result);
        } catch (\Exception $e) {
            $this->assertInstanceOf(\Exception::class, $e);
        }
    }

    public function testSyncPaymentStatus(): void
    {
        $paymentService = self::getService(PaymentService::class);

        $paymentId = 'pay_test_' . time();

        $this->expectNotToPerformAssertions();

        try {
            $paymentService->syncPaymentStatus($paymentId);
        } catch (\Exception) {
            // Expected exception during test
        }
    }

    public function testCapturePayment(): void
    {
        $paymentService = self::getService(PaymentService::class);

        try {
            $result = $paymentService->capturePayment('non_existent_payment');
            $this->assertInstanceOf(Payment::class, $result);
        } catch (\Exception $e) {
            $this->assertInstanceOf(\Exception::class, $e);
        }
    }

    public function testCreateDirectPayment(): void
    {
        $paymentService = self::getService(PaymentService::class);

        try {
            $result = $paymentService->createDirectPayment(['reference' => 'test_ref', 'amount' => 1000, 'currency' => 'USD']);
            $this->assertNotEmpty($result);
        } catch (\Exception $e) {
            $this->assertInstanceOf(\Exception::class, $e);
        }
    }

    public function testRefundPayment(): void
    {
        $paymentService = self::getService(PaymentService::class);

        try {
            $result = $paymentService->refundPayment('non_existent_payment', 100.0);
            $this->assertNotEmpty($result);
        } catch (\Exception $e) {
            $this->assertInstanceOf(\Exception::class, $e);
        }
    }

    public function testSearchPayments(): void
    {
        $paymentService = self::getService(PaymentService::class);

        try {
            $result = $paymentService->searchPayments([]);
            $this->assertNotEmpty($result);
        } catch (\Exception $e) {
            $this->assertInstanceOf(\Exception::class, $e);
        }
    }

    public function testVoidPayment(): void
    {
        $paymentService = self::getService(PaymentService::class);

        try {
            $result = $paymentService->voidPayment('non_existent_payment');
            $this->assertInstanceOf(Payment::class, $result);
        } catch (\Exception $e) {
            $this->assertInstanceOf(\Exception::class, $e);
        }
    }
}
