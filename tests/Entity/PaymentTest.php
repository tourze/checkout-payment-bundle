<?php

namespace CheckoutPaymentBundle\Tests\Entity;

use CheckoutPaymentBundle\Entity\Payment;
use CheckoutPaymentBundle\Entity\PaymentSession;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(Payment::class)]
final class PaymentTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new Payment();
    }

    public function testPaymentEntity(): void
    {
        $session = new PaymentSession();
        $session->setReference('order_123');

        $payment = new Payment();

        $this->assertNull($payment->getId());
        $this->assertNull($payment->getPaymentId());
        $this->assertNull($payment->getSession());
        $this->assertNull($payment->getAmount());
        $this->assertNull($payment->getCurrency());
        $this->assertNull($payment->getReference());
        $this->assertNull($payment->getStatus());
        $this->assertNull($payment->getResponseSummary());
        $this->assertNull($payment->getResponseCode());
        $this->assertNull($payment->getSource());
        $this->assertNull($payment->getCustomer());
        $this->assertNull($payment->getBillingAddress());
        $this->assertNull($payment->getRisk());
        $this->assertNull($payment->getCreateTime());
        $this->assertNull($payment->getUpdateTime());
        $this->assertNull($payment->getProcessedAt());

        $payment->setPaymentId('pay_test123');
        $payment->setSession($session);
        $payment->setAmount(1000);
        $payment->setCurrency('USD');
        $payment->setReference('order_123');
        $payment->setStatus('Authorized');
        $payment->setResponseSummary('Approved');
        $payment->setResponseCode('10000');
        $payment->setSource(['type' => 'card', 'last4' => '4242']);
        $payment->setCustomer(['email' => 'test@example.com']);
        $payment->setBillingAddress(['address_line1' => '123 Main St']);
        $payment->setRisk(['flagged' => false]);

        $this->assertEquals('pay_test123', $payment->getPaymentId());
        $this->assertSame($session, $payment->getSession());
        $this->assertEquals(1000, $payment->getAmount());
        $this->assertEquals('USD', $payment->getCurrency());
        $this->assertEquals('order_123', $payment->getReference());
        $this->assertEquals('Authorized', $payment->getStatus());
        $this->assertEquals('Approved', $payment->getResponseSummary());
        $this->assertEquals('10000', $payment->getResponseCode());
        $this->assertEquals(['type' => 'card', 'last4' => '4242'], $payment->getSource());
        $this->assertEquals(['email' => 'test@example.com'], $payment->getCustomer());
        $this->assertEquals(['address_line1' => '123 Main St'], $payment->getBillingAddress());
        $this->assertEquals(['flagged' => false], $payment->getRisk());
    }

    public function testStatusUpdateTimestamps(): void
    {
        $payment = new Payment();
        $this->assertNull($payment->getProcessedAt());

        // Test setting and getting create time
        $createTime = new \DateTimeImmutable();
        $payment->setCreateTime($createTime);
        $this->assertEquals($createTime, $payment->getCreateTime());

        sleep(1);
        $payment->setStatus('Authorized');

        $this->assertInstanceOf(\DateTimeImmutable::class, $payment->getProcessedAt());
        $this->assertGreaterThan($createTime, $payment->getProcessedAt());
    }

    /** @return iterable<string, array{string, mixed}> */
    public static function propertiesProvider(): iterable
    {
        $session = new PaymentSession();
        $session->setReference('order_123');

        return [
            'paymentId' => ['paymentId', 'pay_test123'],
            'session' => ['session', $session],
            'amount' => ['amount', 1000],
            'currency' => ['currency', 'USD'],
            'reference' => ['reference', 'order_123'],
            'status' => ['status', 'Authorized'],
            'responseSummary' => ['responseSummary', 'Approved'],
            'responseCode' => ['responseCode', '10000'],
            'source' => ['source', ['type' => 'card', 'last4' => '4242']],
            'customer' => ['customer', ['email' => 'test@example.com']],
            'billingAddress' => ['billingAddress', ['address_line1' => '123 Main St']],
            'risk' => ['risk', ['flagged' => false]],
        ];
    }
}
