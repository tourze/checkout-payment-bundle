<?php

namespace CheckoutPaymentBundle\Tests\Exception;

use CheckoutPaymentBundle\Exception\PaymentException;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(PaymentException::class)]
final class PaymentExceptionTest extends AbstractExceptionTestCase
{
    public function testExceptionMessage(): void
    {
        $exception = new PaymentException('Payment failed');

        $this->assertEquals('Payment failed', $exception->getMessage());
        $this->assertInstanceOf(\Exception::class, $exception);
    }

    public function testExceptionWithCode(): void
    {
        $exception = new PaymentException('Payment declined', 402);

        $this->assertEquals('Payment declined', $exception->getMessage());
        $this->assertEquals(402, $exception->getCode());
    }

    public function testExceptionWithPrevious(): void
    {
        $previous = new \Exception('Card declined');
        $exception = new PaymentException('Payment failed', 402, $previous);

        $this->assertEquals('Payment failed', $exception->getMessage());
        $this->assertEquals(402, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testPaymentNotFound(): void
    {
        $exception = PaymentException::paymentNotFound('pay_123');

        $this->assertEquals('Payment not found: pay_123', $exception->getMessage());
        $this->assertInstanceOf(PaymentException::class, $exception);
    }

    public function testCannotRefund(): void
    {
        $exception = PaymentException::cannotRefund('captured');

        $this->assertEquals('Payment cannot be refunded. Current status: captured', $exception->getMessage());
        $this->assertInstanceOf(PaymentException::class, $exception);
    }

    public function testRefundAmountExceedsAvailable(): void
    {
        $exception = PaymentException::refundAmountExceedsAvailable();

        $this->assertEquals('Refund amount exceeds available refund amount', $exception->getMessage());
        $this->assertInstanceOf(PaymentException::class, $exception);
    }

    public function testPaymentSessionNotFound(): void
    {
        $exception = PaymentException::paymentSessionNotFound('sess_123');

        $this->assertEquals('Payment session not found: sess_123', $exception->getMessage());
        $this->assertInstanceOf(PaymentException::class, $exception);
    }

    public function testInvalidPaymentStatus(): void
    {
        $exception = PaymentException::invalidPaymentStatus('authorized', 'captured');

        $this->assertEquals('Expected payment status "authorized", got "captured"', $exception->getMessage());
        $this->assertInstanceOf(PaymentException::class, $exception);
    }

    public function testWebhookProcessingFailed(): void
    {
        $exception = PaymentException::webhookProcessingFailed('Invalid signature');

        $this->assertEquals('Webhook processing failed: Invalid signature', $exception->getMessage());
        $this->assertInstanceOf(PaymentException::class, $exception);
    }

    public function testApiError(): void
    {
        $exception = PaymentException::apiError('Network error');

        $this->assertEquals('API error: Network error', $exception->getMessage());
        $this->assertInstanceOf(PaymentException::class, $exception);
    }

    public function testInvalidWebhookSignature(): void
    {
        $exception = PaymentException::invalidWebhookSignature();

        $this->assertEquals('Invalid webhook signature', $exception->getMessage());
        $this->assertInstanceOf(PaymentException::class, $exception);
    }
}
