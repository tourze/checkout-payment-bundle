<?php

namespace CheckoutPaymentBundle\Tests\Entity;

use CheckoutPaymentBundle\Entity\PaymentSession;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(PaymentSession::class)]
final class PaymentSessionTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new PaymentSession();
    }

    public function testPaymentSessionEntity(): void
    {
        $session = new PaymentSession();

        $this->assertNull($session->getId());
        $this->assertNull($session->getSessionId());
        $this->assertNull($session->getReference());
        $this->assertNull($session->getAmount());
        $this->assertNull($session->getCurrency());
        $this->assertNull($session->getDescription());
        $this->assertNull($session->getCustomerEmail());
        $this->assertNull($session->getCustomerName());
        $this->assertNull($session->getBillingAddress());
        $this->assertNull($session->getSuccessUrl());
        $this->assertNull($session->getCancelUrl());
        $this->assertNull($session->getFailureUrl());
        $this->assertNull($session->getPaymentUrl());
        $this->assertEquals('pending', $session->getStatus());
        $this->assertNull($session->getMetadata());
        $this->assertNull($session->getCreateTime());
        $this->assertNull($session->getUpdateTime());

        $session->setSessionId('hps_test123');
        $session->setReference('order_123');
        $session->setAmount(1000);
        $session->setCurrency('USD');
        $session->setDescription('Test payment');
        $session->setCustomerEmail('test@example.com');
        $session->setCustomerName('Test User');
        $session->setBillingAddress(['address_line1' => '123 Main St']);
        $session->setSuccessUrl('https://example.com/success');
        $session->setCancelUrl('https://example.com/cancel');
        $session->setFailureUrl('https://example.com/failure');
        $session->setPaymentUrl('https://pay.checkout.com/hosted-payments/hps_test123');
        $session->setStatus('paid');
        $session->setMetadata(['order_id' => '123']);

        $this->assertEquals('hps_test123', $session->getSessionId());
        $this->assertEquals('order_123', $session->getReference());
        $this->assertEquals(1000, $session->getAmount());
        $this->assertEquals('USD', $session->getCurrency());
        $this->assertEquals('Test payment', $session->getDescription());
        $this->assertEquals('test@example.com', $session->getCustomerEmail());
        $this->assertEquals('Test User', $session->getCustomerName());
        $this->assertEquals(['address_line1' => '123 Main St'], $session->getBillingAddress());
        $this->assertEquals('https://example.com/success', $session->getSuccessUrl());
        $this->assertEquals('https://example.com/cancel', $session->getCancelUrl());
        $this->assertEquals('https://example.com/failure', $session->getFailureUrl());
        $this->assertEquals('https://pay.checkout.com/hosted-payments/hps_test123', $session->getPaymentUrl());
        $this->assertEquals('paid', $session->getStatus());
        $this->assertEquals(['order_id' => '123'], $session->getMetadata());
    }

    public function testStatusUpdateTimestamps(): void
    {
        $session = new PaymentSession();
        $this->assertNull($session->getUpdateTime());

        // Test setting and getting create time
        $createTime = new \DateTimeImmutable();
        $session->setCreateTime($createTime);
        $this->assertEquals($createTime, $session->getCreateTime());

        // Test setting and getting update time
        $updateTime = new \DateTimeImmutable();
        $session->setUpdateTime($updateTime);
        $this->assertEquals($updateTime, $session->getUpdateTime());

        sleep(1);
        $session->setStatus('paid');
        $this->assertInstanceOf(\DateTimeImmutable::class, $session->getUpdateTime());
        $this->assertGreaterThan($createTime, $session->getUpdateTime());
    }

    /** @return iterable<string, array{string, mixed}> */
    public static function propertiesProvider(): iterable
    {
        return [
            'sessionId' => ['sessionId', 'hps_test123'],
            'reference' => ['reference', 'order_123'],
            'amount' => ['amount', 1000],
            'currency' => ['currency', 'USD'],
            'description' => ['description', 'Test payment'],
            'customerEmail' => ['customerEmail', 'test@example.com'],
            'customerName' => ['customerName', 'Test User'],
            'billingAddress' => ['billingAddress', ['address_line1' => '123 Main St']],
            'successUrl' => ['successUrl', 'https://example.com/success'],
            'cancelUrl' => ['cancelUrl', 'https://example.com/cancel'],
            'failureUrl' => ['failureUrl', 'https://example.com/failure'],
            'paymentUrl' => ['paymentUrl', 'https://pay.checkout.com/hosted-payments/hps_test123'],
            'status' => ['status', 'paid'],
            'metadata' => ['metadata', ['order_id' => '123']],
        ];
    }
}
