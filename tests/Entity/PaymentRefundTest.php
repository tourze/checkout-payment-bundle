<?php

namespace CheckoutPaymentBundle\Tests\Entity;

use CheckoutPaymentBundle\Entity\Payment;
use CheckoutPaymentBundle\Entity\PaymentRefund;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(PaymentRefund::class)]
final class PaymentRefundTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new PaymentRefund();
    }

    public function testPaymentRefundEntity(): void
    {
        $payment = new Payment();
        $payment->setPaymentId('pay_test123');
        $payment->setAmount(1000);
        $payment->setCurrency('USD');

        $refund = new PaymentRefund();

        $this->assertNull($refund->getId());
        $this->assertNull($refund->getRefundId());
        $this->assertNull($refund->getPayment());
        $this->assertNull($refund->getAmount());
        $this->assertNull($refund->getCurrency());
        $this->assertNull($refund->getReference());
        $this->assertEquals('pending', $refund->getStatus());
        $this->assertNull($refund->getResponseSummary());
        $this->assertNull($refund->getResponseCode());
        $this->assertNull($refund->getReason());
        $this->assertNull($refund->getMetadata());
        $this->assertNull($refund->getCreateTime());
        $this->assertNull($refund->getUpdateTime());
        $this->assertNull($refund->getProcessedAt());

        $refund->setRefundId('ref_test123');
        $refund->setPayment($payment);
        $refund->setAmount(500);
        $refund->setCurrency('USD');
        $refund->setReference('refund_123');
        $refund->setStatus('Approved');
        $refund->setResponseSummary('Refunded');
        $refund->setResponseCode('10000');
        $refund->setReason('Customer request');
        $refund->setMetadata(['order_id' => '123']);

        $this->assertEquals('ref_test123', $refund->getRefundId());
        $this->assertSame($payment, $refund->getPayment());
        $this->assertEquals(500, $refund->getAmount());
        $this->assertEquals('USD', $refund->getCurrency());
        $this->assertEquals('refund_123', $refund->getReference());
        $this->assertEquals('Approved', $refund->getStatus());
        $this->assertEquals('Refunded', $refund->getResponseSummary());
        $this->assertEquals('10000', $refund->getResponseCode());
        $this->assertEquals('Customer request', $refund->getReason());
        $this->assertEquals(['order_id' => '123'], $refund->getMetadata());
    }

    public function testStatusUpdateTimestamps(): void
    {
        $refund = new PaymentRefund();
        $this->assertNull($refund->getProcessedAt());

        // Test setting and getting create time
        $createTime = new \DateTimeImmutable();
        $refund->setCreateTime($createTime);
        $this->assertEquals($createTime, $refund->getCreateTime());

        // Test setting and getting update time
        $updateTime = new \DateTimeImmutable();
        $refund->setUpdateTime($updateTime);
        $this->assertEquals($updateTime, $refund->getUpdateTime());

        sleep(1);
        $refund->setStatus('Approved');
        $this->assertInstanceOf(\DateTimeImmutable::class, $refund->getProcessedAt());
        $this->assertGreaterThan($createTime, $refund->getProcessedAt());
    }

    public function testStatusMethods(): void
    {
        $refund = new PaymentRefund();

        $this->assertTrue($refund->isPending());
        $this->assertFalse($refund->isApproved());
        $this->assertFalse($refund->isFailed());

        $refund->setStatus('Approved');
        $this->assertTrue($refund->isApproved());
        $this->assertFalse($refund->isPending());
        $this->assertFalse($refund->isFailed());

        $refund->setStatus('Failed');
        $this->assertTrue($refund->isFailed());
        $this->assertFalse($refund->isPending());
        $this->assertFalse($refund->isApproved());
    }

    /** @return iterable<string, array{string, mixed}> */
    public static function propertiesProvider(): iterable
    {
        $payment = new Payment();
        $payment->setPaymentId('pay_test123');
        $payment->setAmount(1000);
        $payment->setCurrency('USD');

        return [
            'refundId' => ['refundId', 'ref_test123'],
            'payment' => ['payment', $payment],
            'amount' => ['amount', 500],
            'currency' => ['currency', 'USD'],
            'reference' => ['reference', 'refund_123'],
            'status' => ['status', 'Approved'],
            'responseSummary' => ['responseSummary', 'Refunded'],
            'responseCode' => ['responseCode', '10000'],
            'reason' => ['reason', 'Customer request'],
            'metadata' => ['metadata', ['order_id' => '123']],
        ];
    }
}
