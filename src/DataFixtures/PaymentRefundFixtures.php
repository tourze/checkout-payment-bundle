<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\DataFixtures;

use CheckoutPaymentBundle\Entity\Payment;
use CheckoutPaymentBundle\Entity\PaymentRefund;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PaymentRefundFixtures extends Fixture implements DependentFixtureInterface
{
    public const REFUND_1_REFERENCE = 'refund-1';
    public const REFUND_2_REFERENCE = 'refund-2';

    public function load(ObjectManager $manager): void
    {
        $refund1 = new PaymentRefund();
        $refund1->setRefundId('ref_test_001');
        $refund1->setPaymentId('pay_test_002');
        $refund1->setReference('REFUND-001');
        $refund1->setAmount(5000);
        $refund1->setCurrency('EUR');
        $refund1->setStatus('Approved');
        $refund1->setApproved(true);
        $refund1->setResponseCode('10000');
        $refund1->setResponseSummary('Approved');
        $refund1->setPayment($this->getReference(PaymentFixtures::PAYMENT_2_REFERENCE, Payment::class));
        $refund1->setReason('Customer request');
        $refund1->setMetadata(['original_order' => 'ORD-002']);
        $manager->persist($refund1);
        $this->addReference(self::REFUND_1_REFERENCE, $refund1);

        $refund2 = new PaymentRefund();
        $refund2->setRefundId('ref_test_002');
        $refund2->setPaymentId('pay_test_002');
        $refund2->setReference('REFUND-002');
        $refund2->setAmount(10000);
        $refund2->setCurrency('EUR');
        $refund2->setStatus('Pending');
        $refund2->setApproved(false);
        $refund2->setResponseCode('20001');
        $refund2->setResponseSummary('Processing');
        $refund2->setPayment($this->getReference(PaymentFixtures::PAYMENT_2_REFERENCE, Payment::class));
        $refund2->setReason('Product defect');
        $refund2->setMetadata(['original_order' => 'ORD-002']);
        $manager->persist($refund2);
        $this->addReference(self::REFUND_2_REFERENCE, $refund2);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            PaymentFixtures::class,
        ];
    }
}
