<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\DataFixtures;

use CheckoutPaymentBundle\DataFixtures\PaymentSessionFixtures;
use CheckoutPaymentBundle\Entity\Payment;
use CheckoutPaymentBundle\Entity\PaymentSession;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PaymentFixtures extends Fixture implements DependentFixtureInterface
{
    public const PAYMENT_1_REFERENCE = 'payment-1';
    public const PAYMENT_2_REFERENCE = 'payment-2';
    public const PAYMENT_3_REFERENCE = 'payment-3';

    public function load(ObjectManager $manager): void
    {
        $payment1 = new Payment();
        $payment1->setPaymentId('pay_test_001');
        $payment1->setReference('REF-001');
        $payment1->setAmount(10000);
        $payment1->setCurrency('USD');
        $payment1->setStatus('Authorized');
        $payment1->setApproved(true);
        $payment1->setSession($this->getReference(PaymentSessionFixtures::SESSION_1_REFERENCE, PaymentSession::class));
        $payment1->setRisk(['level' => 'low']);
        $payment1->setProcessedOn(new \DateTimeImmutable('2024-01-15 10:30:00'));
        $payment1->setResponseCode('10000');
        $payment1->setResponseSummary('Approved');
        $payment1->setSource(['type' => 'card', 'last4' => '4242']);
        $payment1->setCustomer(['email' => 'customer1@test.local']);
        $payment1->setMetadata(['order_id' => 'ORD-001']);
        $manager->persist($payment1);
        $this->addReference(self::PAYMENT_1_REFERENCE, $payment1);

        $payment2 = new Payment();
        $payment2->setPaymentId('pay_test_002');
        $payment2->setReference('REF-002');
        $payment2->setAmount(25000);
        $payment2->setCurrency('EUR');
        $payment2->setStatus('Captured');
        $payment2->setApproved(true);
        $payment2->setSession($this->getReference(PaymentSessionFixtures::SESSION_2_REFERENCE, PaymentSession::class));
        $payment2->setRisk(['level' => 'medium']);
        $payment2->setProcessedOn(new \DateTimeImmutable('2024-01-20 14:45:00'));
        $payment2->setResponseCode('10000');
        $payment2->setResponseSummary('Approved');
        $payment2->setSource(['type' => 'card', 'last4' => '5555']);
        $payment2->setCustomer(['email' => 'customer2@test.local']);
        $payment2->setMetadata(['order_id' => 'ORD-002']);
        $manager->persist($payment2);
        $this->addReference(self::PAYMENT_2_REFERENCE, $payment2);

        $payment3 = new Payment();
        $payment3->setPaymentId('pay_test_003');
        $payment3->setReference('REF-003');
        $payment3->setAmount(5000);
        $payment3->setCurrency('GBP');
        $payment3->setStatus('Pending');
        $payment3->setApproved(false);
        $payment3->setSession($this->getReference(PaymentSessionFixtures::SESSION_3_REFERENCE, PaymentSession::class));
        $payment3->setRisk(['level' => 'high']);
        $payment3->setProcessedOn(new \DateTimeImmutable('2024-02-01 09:15:00'));
        $payment3->setResponseCode('20005');
        $payment3->setResponseSummary('Declined - Do not honor');
        $payment3->setSource(['type' => 'card', 'last4' => '0002']);
        $payment3->setCustomer(['email' => 'customer3@test.local']);
        $payment3->setMetadata(['order_id' => 'ORD-003']);
        $manager->persist($payment3);
        $this->addReference(self::PAYMENT_3_REFERENCE, $payment3);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            PaymentSessionFixtures::class,
        ];
    }
}
