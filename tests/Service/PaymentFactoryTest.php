<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\Tests\Service;

use CheckoutPaymentBundle\Entity\Payment;
use CheckoutPaymentBundle\Entity\PaymentSession;
use CheckoutPaymentBundle\Service\PaymentFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(PaymentFactory::class)]
#[RunTestsInSeparateProcesses]
final class PaymentFactoryTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // 集成测试不需要特殊设置
    }

    public function testCreateFromApiResponse(): void
    {
        $factory = self::getService(PaymentFactory::class);

        $session = new PaymentSession();
        $session->setSessionId('session_123');

        $apiResponse = [
            'id' => 'pay_123',
            'amount' => 10000,
            'currency' => 'USD',
            'status' => 'Authorized',
            'response_summary' => 'Approved',
            'response_code' => '10000',
        ];

        $payment = $factory->createFromApiResponse($apiResponse, $session, 'ref_123');

        $this->assertSame('pay_123', $payment->getPaymentId());
        $this->assertSame($session, $payment->getSession());
        $this->assertSame('ref_123', $payment->getReference());
        $this->assertSame(10000, $payment->getAmount());
        $this->assertSame('USD', $payment->getCurrency());
        $this->assertSame('Authorized', $payment->getStatus());
    }

    public function testUpdateFromWebhookData(): void
    {
        $factory = self::getService(PaymentFactory::class);

        $payment = new Payment();

        $webhookData = [
            'amount' => 5000,
            'currency' => 'EUR',
            'status' => 'Captured',
            'response_summary' => 'Payment captured',
        ];

        $factory->updateFromWebhookData($payment, $webhookData);

        $this->assertSame(5000, $payment->getAmount());
        $this->assertSame('EUR', $payment->getCurrency());
        $this->assertSame('Captured', $payment->getStatus());
        $this->assertSame('Payment captured', $payment->getResponseSummary());
    }
}
