<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\Tests\Controller\Payment;

use CheckoutPaymentBundle\Controller\Payment\PaymentSuccessController;
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
#[CoversClass(PaymentSuccessController::class)]
#[RunTestsInSeparateProcesses]
final class PaymentSuccessControllerTest extends AbstractWebTestCase
{
    protected function onSetUp(): void
    {
        $_ENV['CHECKOUT_WEBHOOK_SECRET'] = 'test_webhook_secret';
        $_ENV['CHECKOUT_CONFIG_NAME'] = 'test';
    }

    public function testPaymentSuccess(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        // 创建支付会话
        $session = new PaymentSession();
        $session->setSessionId('test_session_id');
        $session->setReference('order_123');
        $session->setAmount(10000);
        $session->setCurrency('USD');
        $session->setStatus('Completed');
        $session->setCustomerEmail('test@example.com');
        $session->setSuccessUrl('https://example.com/success');
        $session->setCancelUrl('https://example.com/cancel');
        $session->setPaymentUrl('https://example.com/payment');

        $entityManager = self::getService(EntityManagerInterface::class);
        $entityManager->persist($session);
        $entityManager->flush();

        $client->request('GET', '/checkout-payment/success?sessionId=test_session_id');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    public function testPaymentSuccessWithMissingSessionId(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $client->request('GET', '/checkout-payment/success');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    public function testPaymentSuccessWithSessionNotFound(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $client->request('GET', '/checkout-payment/success?sessionId=nonexistent_session');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    public function testPaymentSuccessPost(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('POST', '/checkout-payment/success');
    }

    public function testPaymentSuccessPut(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PUT', '/checkout-payment/success');
    }

    public function testPaymentSuccessDelete(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('DELETE', '/checkout-payment/success');
    }

    public function testPaymentSuccessPatch(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PATCH', '/checkout-payment/success');
    }

    public function testPaymentSuccessOptions(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('OPTIONS', '/checkout-payment/success');
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $client->catchExceptions(false);
        $this->expectException(MethodNotAllowedHttpException::class);

        $client->request($method, '/checkout-payment/success');
    }
}
