<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\Tests\Service;

use CheckoutPaymentBundle\Entity\PaymentSession;
use CheckoutPaymentBundle\Service\PaymentSessionFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(PaymentSessionFactory::class)]
final class PaymentSessionFactoryTest extends TestCase
{
    private PaymentSessionFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new PaymentSessionFactory();
    }

    public function testCreateFromDataWithRequiredFields(): void
    {
        $data = [
            'reference' => 'REF-123',
            'amount' => 1000,
            'currency' => 'USD',
            'customer_email' => 'test@example.com',
            'success_url' => 'https://example.com/success',
            'cancel_url' => 'https://example.com/cancel',
        ];

        $session = $this->factory->createFromData($data);

        self::assertSame('REF-123', $session->getReference());
        self::assertSame(1000, $session->getAmount());
        self::assertSame('USD', $session->getCurrency());
        self::assertSame('test@example.com', $session->getCustomerEmail());
        self::assertSame('https://example.com/success', $session->getSuccessUrl());
        self::assertSame('https://example.com/cancel', $session->getCancelUrl());
    }

    public function testCreateFromDataWithOptionalFields(): void
    {
        $data = [
            'reference' => 'REF-123',
            'amount' => 1000,
            'currency' => 'EUR',
            'description' => 'Test payment',
            'customer_email' => 'test@example.com',
            'customer_name' => 'John Doe',
            'success_url' => 'https://example.com/success',
            'cancel_url' => 'https://example.com/cancel',
            'failure_url' => 'https://example.com/failure',
            'billing_address' => [
                'line1' => '123 Main St',
                'city' => 'New York',
            ],
            'metadata' => [
                'user_id' => '123',
                'order_id' => 'ORD-456',
            ],
        ];

        $session = $this->factory->createFromData($data);

        self::assertSame('Test payment', $session->getDescription());
        self::assertSame('John Doe', $session->getCustomerName());
        self::assertSame('https://example.com/failure', $session->getFailureUrl());
        self::assertIsArray($session->getBillingAddress());
        self::assertSame('123 Main St', $session->getBillingAddress()['line1']);
        self::assertIsArray($session->getMetadata());
        self::assertSame('123', $session->getMetadata()['user_id']);
    }

    public function testCreateFromDataWithDefaults(): void
    {
        $data = [];

        $session = $this->factory->createFromData($data);

        self::assertSame('', $session->getReference());
        self::assertSame(0, $session->getAmount());
        self::assertSame('USD', $session->getCurrency());
        self::assertNull($session->getDescription());
        self::assertSame('', $session->getCustomerEmail());
        self::assertNull($session->getCustomerName());
        self::assertSame('', $session->getSuccessUrl());
        self::assertSame('', $session->getCancelUrl());
        self::assertNull($session->getFailureUrl());
    }

    public function testCreateFromDataWithInvalidTypes(): void
    {
        $data = [
            'reference' => 123, // should be string
            'amount' => 'invalid', // should be numeric
            'currency' => null, // should be string
            'customer_email' => false, // should be string
        ];

        $session = $this->factory->createFromData($data);

        self::assertSame('', $session->getReference());
        self::assertSame(0, $session->getAmount());
        self::assertSame('USD', $session->getCurrency());
        self::assertSame('', $session->getCustomerEmail());
    }
}
