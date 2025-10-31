<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\Tests\Controller\Payment;

use CheckoutPaymentBundle\Controller\Payment\PaymentCancelController;
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
#[CoversClass(PaymentCancelController::class)]
#[RunTestsInSeparateProcesses]
final class PaymentCancelControllerTest extends AbstractWebTestCase
{
    protected function onSetUp(): void
    {
        $_ENV['CHECKOUT_WEBHOOK_SECRET'] = 'test_webhook_secret';
        $_ENV['CHECKOUT_CONFIG_NAME'] = 'test';
    }

    public function testPaymentCancel(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        // 创建支付会话记录
        $session = new PaymentSession();
        $session->setSessionId('test_session_id');
        $session->setReference('order_123');
        $session->setAmount(10000);
        $session->setCurrency('USD');
        $session->setStatus('Created');
        $session->setCustomerEmail('test@example.com');
        $session->setSuccessUrl('https://example.com/success');
        $session->setCancelUrl('https://example.com/cancel');
        $session->setPaymentUrl('https://example.com/payment');

        $entityManager = self::getService(EntityManagerInterface::class);
        $entityManager->persist($session);
        $entityManager->flush();

        $client->request('GET', '/checkout-payment/cancel?sessionId=test_session_id');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $content = false !== $client->getResponse()->getContent() ? $client->getResponse()->getContent() : '';
        $this->assertStringContainsString('cancel', $content);
    }

    public function testPaymentCancelWithMissingSessionId(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $client->request('GET', '/checkout-payment/cancel');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $content = false !== $client->getResponse()->getContent() ? $client->getResponse()->getContent() : '';
        $this->assertStringContainsString('error', $content);
    }

    public function testPaymentCancelWithSessionNotFound(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $client->request('GET', '/checkout-payment/cancel?sessionId=nonexistent_session');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $content = false !== $client->getResponse()->getContent() ? $client->getResponse()->getContent() : '';
        $this->assertStringContainsString('error', $content);
    }

    public function testPaymentCancelPost(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('POST', '/checkout-payment/cancel');
    }

    public function testPaymentCancelPut(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PUT', '/checkout-payment/cancel');
    }

    public function testPaymentCancelDelete(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('DELETE', '/checkout-payment/cancel');
    }

    public function testPaymentCancelPatch(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PATCH', '/checkout-payment/cancel');
    }

    public function testPaymentCancelOptions(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('OPTIONS', '/checkout-payment/cancel');
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $client->catchExceptions(false);
        $this->expectException(MethodNotAllowedHttpException::class);

        $client->request($method, '/checkout-payment/cancel');
    }
}
