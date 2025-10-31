<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\Tests\Controller\Payment;

use CheckoutPaymentBundle\Controller\Payment\PaymentFailureController;
use CheckoutPaymentBundle\Entity\PaymentSession;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;

/**
 * @internal
 */
#[CoversClass(PaymentFailureController::class)]
#[RunTestsInSeparateProcesses]
final class PaymentFailureControllerTest extends AbstractWebTestCase
{
    protected function onSetUp(): void
    {
        $_ENV['CHECKOUT_WEBHOOK_SECRET'] = 'test_webhook_secret';
        $_ENV['CHECKOUT_CONFIG_NAME'] = 'test';
    }

    public function testPaymentFailure(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        // 创建支付会话
        $session = new PaymentSession();
        $session->setSessionId('test_session_id');
        $session->setReference('order_123');
        $session->setAmount(10000);
        $session->setCurrency('USD');
        $session->setStatus('Failed');
        $session->setCustomerEmail('test@example.com');
        $session->setSuccessUrl('https://example.com/success');
        $session->setCancelUrl('https://example.com/cancel');
        $session->setPaymentUrl('https://example.com/payment');

        $entityManager = self::getService(EntityManagerInterface::class);
        $entityManager->persist($session);
        $entityManager->flush();

        $client->request('GET', '/checkout-payment/failure?sessionId=test_session_id');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    public function testPaymentFailureWithMissingSessionId(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $client->request('GET', '/checkout-payment/failure');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    public function testPaymentFailureWithSessionNotFound(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $client->request('GET', '/checkout-payment/failure?sessionId=nonexistent_session');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    public function testPaymentFailurePost(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('POST', '/checkout-payment/failure');
    }

    public function testPaymentFailurePut(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PUT', '/checkout-payment/failure');
    }

    public function testPaymentFailureDelete(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('DELETE', '/checkout-payment/failure');
    }

    public function testPaymentFailurePatch(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PATCH', '/checkout-payment/failure');
    }

    public function testPaymentFailureOptions(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('OPTIONS', '/checkout-payment/failure');
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $client->catchExceptions(false);
        $this->expectException(MethodNotAllowedHttpException::class);

        $client->request($method, '/checkout-payment/failure');
    }
}
