<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\DataFixtures;

use CheckoutPaymentBundle\Entity\WebhookLog;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class WebhookLogFixtures extends Fixture implements DependentFixtureInterface
{
    public const WEBHOOK_1_REFERENCE = 'webhook-1';
    public const WEBHOOK_2_REFERENCE = 'webhook-2';
    public const WEBHOOK_3_REFERENCE = 'webhook-3';

    public function load(ObjectManager $manager): void
    {
        $webhook1 = new WebhookLog();
        $webhook1->setWebhookId('wh_test_001');
        $webhook1->setEventId('evt_test_001');
        $webhook1->setEventType('payment_approved');
        $webhook1->setPaymentId('pay_test_001');
        $webhook1->setProcessed(true);
        $webhook1->setRequestHeaders([
            'X-Checkout-Signature' => 'sha256=abc123',
            'Content-Type' => 'application/json',
        ]);
        $webhook1->setRequestBody((string) json_encode([
            'type' => 'payment_approved',
            'data' => [
                'id' => 'pay_test_001',
                'amount' => 10000,
                'currency' => 'USD',
            ],
        ]));
        $webhook1->setResponseStatus(200);
        $webhook1->setPayload((string) json_encode([
            'type' => 'payment_approved',
            'data' => [
                'id' => 'pay_test_001',
                'amount' => 10000,
                'currency' => 'USD',
            ],
        ]));
        $manager->persist($webhook1);
        $this->addReference(self::WEBHOOK_1_REFERENCE, $webhook1);

        $webhook2 = new WebhookLog();
        $webhook2->setWebhookId('wh_test_002');
        $webhook2->setEventId('evt_test_002');
        $webhook2->setEventType('payment_captured');
        $webhook2->setPaymentId('pay_test_002');
        $webhook2->setProcessed(true);
        $webhook2->setRequestHeaders([
            'X-Checkout-Signature' => 'sha256=def456',
            'Content-Type' => 'application/json',
        ]);
        $webhook2->setRequestBody((string) json_encode([
            'type' => 'payment_captured',
            'data' => [
                'id' => 'pay_test_002',
                'amount' => 25000,
                'currency' => 'EUR',
            ],
        ]));
        $webhook2->setResponseStatus(200);
        $webhook2->setPayload((string) json_encode([
            'type' => 'payment_captured',
            'data' => [
                'id' => 'pay_test_002',
                'amount' => 25000,
                'currency' => 'EUR',
            ],
        ]));
        $manager->persist($webhook2);
        $this->addReference(self::WEBHOOK_2_REFERENCE, $webhook2);

        $webhook3 = new WebhookLog();
        $webhook3->setWebhookId('wh_test_003');
        $webhook3->setEventId('evt_test_003');
        $webhook3->setEventType('payment_declined');
        $webhook3->setPaymentId('pay_test_003');
        $webhook3->setProcessed(false);
        $webhook3->setRequestHeaders([
            'X-Checkout-Signature' => 'sha256=ghi789',
            'Content-Type' => 'application/json',
        ]);
        $webhook3->setRequestBody((string) json_encode([
            'type' => 'payment_declined',
            'data' => [
                'id' => 'pay_test_003',
                'amount' => 5000,
                'currency' => 'GBP',
                'response_code' => '20005',
            ],
        ]));
        $webhook3->setResponseStatus(500);
        $webhook3->setPayload((string) json_encode([
            'type' => 'payment_declined',
            'data' => [
                'id' => 'pay_test_003',
                'amount' => 5000,
                'currency' => 'GBP',
                'response_code' => '20005',
            ],
        ]));
        $webhook3->setErrorMessage('Database connection failed');
        $manager->persist($webhook3);
        $this->addReference(self::WEBHOOK_3_REFERENCE, $webhook3);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            PaymentFixtures::class,
        ];
    }
}
