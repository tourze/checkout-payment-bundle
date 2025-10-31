<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\DataFixtures;

use CheckoutPaymentBundle\Entity\PaymentSession;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PaymentSessionFixtures extends Fixture
{
    public const SESSION_1_REFERENCE = 'session-1';
    public const SESSION_2_REFERENCE = 'session-2';
    public const SESSION_3_REFERENCE = 'session-3';

    public function load(ObjectManager $manager): void
    {
        $session1 = new PaymentSession();
        $session1->setSessionId('cs_test_001');
        $session1->setReference('SESSION-001');
        $session1->setAmount(10000);
        $session1->setCurrency('USD');
        $session1->setStatus('complete');
        $session1->setPaymentUrl('https://checkout.com/pay/cs_test_001');
        $session1->setSuccessUrl('https://localhost/success');
        $session1->setCancelUrl('https://localhost/cancel');
        $session1->setCustomerEmail('customer1@test.local');
        $session1->setCustomerName('John Doe');
        $session1->setBillingAddress([
            'address_line1' => '123 Main St',
            'city' => 'New York',
            'zip' => '10001',
            'country' => 'US',
        ]);
        $session1->setMetadata([
            'line_items' => [
                ['name' => 'Product 1', 'quantity' => 2, 'unit_amount' => 5000],
            ],
            'order_id' => 'ORD-001',
        ]);
        $session1->setExpiresAt(new \DateTimeImmutable('+24 hours'));
        $manager->persist($session1);
        $this->addReference(self::SESSION_1_REFERENCE, $session1);

        $session2 = new PaymentSession();
        $session2->setSessionId('cs_test_002');
        $session2->setReference('SESSION-002');
        $session2->setAmount(25000);
        $session2->setCurrency('EUR');
        $session2->setStatus('complete');
        $session2->setPaymentUrl('https://checkout.com/pay/cs_test_002');
        $session2->setSuccessUrl('https://localhost/success');
        $session2->setCancelUrl('https://localhost/cancel');
        $session2->setCustomerEmail('customer2@test.local');
        $session2->setCustomerName('Jane Smith');
        $session2->setBillingAddress([
            'address_line1' => '456 Oak Ave',
            'city' => 'Paris',
            'zip' => '75001',
            'country' => 'FR',
        ]);
        $session2->setMetadata([
            'line_items' => [
                ['name' => 'Product 2', 'quantity' => 1, 'unit_amount' => 25000],
            ],
            'order_id' => 'ORD-002',
        ]);
        $session2->setExpiresAt(new \DateTimeImmutable('+24 hours'));
        $manager->persist($session2);
        $this->addReference(self::SESSION_2_REFERENCE, $session2);

        $session3 = new PaymentSession();
        $session3->setSessionId('cs_test_003');
        $session3->setReference('SESSION-003');
        $session3->setAmount(5000);
        $session3->setCurrency('GBP');
        $session3->setStatus('expired');
        $session3->setPaymentUrl('https://checkout.com/pay/cs_test_003');
        $session3->setSuccessUrl('https://localhost/success');
        $session3->setCancelUrl('https://localhost/cancel');
        $session3->setCustomerEmail('customer3@test.local');
        $session3->setCustomerName('Bob Wilson');
        $session3->setBillingAddress([
            'address_line1' => '789 Park Ln',
            'city' => 'London',
            'zip' => 'SW1A 1AA',
            'country' => 'GB',
        ]);
        $session3->setMetadata([
            'line_items' => [
                ['name' => 'Product 3', 'quantity' => 1, 'unit_amount' => 5000],
            ],
            'order_id' => 'ORD-003',
        ]);
        $session3->setExpiresAt(new \DateTimeImmutable('-1 hour'));
        $manager->persist($session3);
        $this->addReference(self::SESSION_3_REFERENCE, $session3);

        $manager->flush();
    }
}
