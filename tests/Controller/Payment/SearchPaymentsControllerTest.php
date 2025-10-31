<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\Tests\Controller\Payment;

use CheckoutPaymentBundle\Controller\Payment\SearchPaymentsController;
use CheckoutPaymentBundle\Entity\Payment;
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
#[CoversClass(SearchPaymentsController::class)]
#[RunTestsInSeparateProcesses]
final class SearchPaymentsControllerTest extends AbstractWebTestCase
{
    protected function onSetUp(): void
    {
        $_ENV['CHECKOUT_WEBHOOK_SECRET'] = 'test_webhook_secret';
        $_ENV['CHECKOUT_CONFIG_NAME'] = 'test';
    }

    public function testSearchPayments(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        // 创建支付会话和支付记录
        $session = new PaymentSession();
        $session->setSessionId('hps_test123');
        $session->setReference('test_ref');
        $session->setAmount(10000);
        $session->setCurrency('USD');
        $session->setStatus('Completed');
        $session->setCustomerEmail('test@example.com');
        $session->setSuccessUrl('https://example.com/success');
        $session->setCancelUrl('https://example.com/cancel');
        $session->setPaymentUrl('https://example.com/payment');

        $payment = new Payment();
        $payment->setPaymentId('pay_test123');
        $payment->setSession($session);
        $payment->setAmount(10000);
        $payment->setCurrency('USD');
        $payment->setStatus('Completed');
        $payment->setReference('test_ref');

        $entityManager = self::getService(EntityManagerInterface::class);
        $entityManager->persist($session);
        $entityManager->persist($payment);
        $entityManager->flush();

        $client->request('GET', '/payments/search', [
            'reference' => 'test_ref',
            'from' => '2023-01-01',
            'to' => '2023-12-31',
            'status' => 'completed',
        ]);

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $content = false !== $client->getResponse()->getContent() ? $client->getResponse()->getContent() : '';
        $responseData = json_decode($content, true);
        self::assertIsArray($responseData);
        $this->assertArrayHasKey('payments', $responseData);
    }

    public function testSearchPaymentsWithNoFilters(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        // 创建支付会话和支付记录
        $session = new PaymentSession();
        $session->setSessionId('hps_test456');
        $session->setReference('order_456');
        $session->setAmount(5000);
        $session->setCurrency('USD');
        $session->setStatus('Pending');
        $session->setCustomerEmail('test@example.com');
        $session->setSuccessUrl('https://example.com/success');
        $session->setCancelUrl('https://example.com/cancel');
        $session->setPaymentUrl('https://example.com/payment');

        $payment = new Payment();
        $payment->setPaymentId('pay_test456');
        $payment->setSession($session);
        $payment->setAmount(5000);
        $payment->setCurrency('USD');
        $payment->setStatus('Pending');
        $payment->setReference('order_456');

        $entityManager = self::getService(EntityManagerInterface::class);
        $entityManager->persist($session);
        $entityManager->persist($payment);
        $entityManager->flush();

        $client->request('GET', '/payments/search');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $content = false !== $client->getResponse()->getContent() ? $client->getResponse()->getContent() : '';
        $responseData = json_decode($content, true);
        self::assertIsArray($responseData);
        $this->assertArrayHasKey('payments', $responseData);
    }

    public function testSearchPaymentsWithException(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $client->request('GET', '/payments/search', [
            'reference' => 'invalid_ref',
        ]);

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $content = false !== $client->getResponse()->getContent() ? $client->getResponse()->getContent() : '';
        $responseData = json_decode($content, true);
        self::assertIsArray($responseData);
        $this->assertArrayHasKey('payments', $responseData);
    }

    public function testSearchPaymentsPost(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('POST', '/payments/search');
    }

    public function testSearchPaymentsPut(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PUT', '/payments/search');
    }

    public function testSearchPaymentsDelete(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('DELETE', '/payments/search');
    }

    public function testSearchPaymentsPatch(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PATCH', '/payments/search');
    }

    public function testSearchPaymentsOptions(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('OPTIONS', '/payments/search');
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $client->catchExceptions(false);
        $this->expectException(MethodNotAllowedHttpException::class);

        $client->request($method, '/payments/search');
    }
}
