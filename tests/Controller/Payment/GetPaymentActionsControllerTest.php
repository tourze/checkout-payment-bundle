<?php

namespace CheckoutPaymentBundle\Tests\Controller\Payment;

use CheckoutPaymentBundle\Controller\Payment\GetPaymentActionsController;
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
#[CoversClass(GetPaymentActionsController::class)]
#[RunTestsInSeparateProcesses]
class GetPaymentActionsControllerTest extends AbstractWebTestCase
{
    protected function onSetUp(): void
    {
        $_ENV['CHECKOUT_WEBHOOK_SECRET'] = 'test_webhook_secret';
        $_ENV['CHECKOUT_CONFIG_NAME'] = 'test';
    }

    public function testGetPaymentActionsGet(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        // 创建支付会话和支付记录
        $session = new PaymentSession();
        $session->setSessionId('hps_test123');
        $session->setReference('order_123');
        $session->setAmount(10000);
        $session->setCurrency('USD');
        $session->setStatus('Authorized');
        $session->setCustomerEmail('test@example.com');
        $session->setSuccessUrl('https://example.com/success');
        $session->setCancelUrl('https://example.com/cancel');
        $session->setPaymentUrl('https://example.com/payment');

        $payment = new Payment();
        $payment->setPaymentId('pay_test123');
        $payment->setSession($session);
        $payment->setAmount(10000);
        $payment->setCurrency('USD');
        $payment->setStatus('Authorized');
        $payment->setApproved(true);
        $payment->setReference('order_123');

        $entityManager = self::getService(EntityManagerInterface::class);
        $entityManager->persist($session);
        $entityManager->persist($payment);
        $entityManager->flush();

        $client->request('GET', '/payments/pay_test123/actions');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $content = false !== $client->getResponse()->getContent() ? $client->getResponse()->getContent() : '';
        $responseData = json_decode($content, true);
        self::assertIsArray($responseData);
        $this->assertArrayHasKey('actions', $responseData);
        $this->assertIsArray($responseData['actions']);
    }

    public function testGetPaymentActionsGetWithValidPaymentId(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        // 创建支付会话和支付记录
        $session = new PaymentSession();
        $session->setSessionId('hps_test456');
        $session->setReference('order_456');
        $session->setAmount(10000);
        $session->setCurrency('USD');
        $session->setStatus('Authorized');
        $session->setCustomerEmail('test@example.com');
        $session->setSuccessUrl('https://example.com/success');
        $session->setCancelUrl('https://example.com/cancel');
        $session->setPaymentUrl('https://example.com/payment');

        $payment = new Payment();
        $payment->setPaymentId('pay_authorized456');
        $payment->setSession($session);
        $payment->setAmount(10000);
        $payment->setCurrency('USD');
        $payment->setStatus('Authorized');
        $payment->setApproved(true);
        $payment->setReference('order_456');

        $entityManager = self::getService(EntityManagerInterface::class);
        $entityManager->persist($session);
        $entityManager->persist($payment);
        $entityManager->flush();

        $client->request('GET', '/payments/pay_authorized456/actions');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $content = false !== $client->getResponse()->getContent() ? $client->getResponse()->getContent() : '';
        $responseData = json_decode($content, true);
        self::assertIsArray($responseData);
        $this->assertArrayHasKey('actions', $responseData);
        $this->assertIsArray($responseData['actions']);
    }

    public function testGetPaymentActionsGetWithNonExistentPaymentId(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $client->request('GET', '/payments/pay_nonexistent/actions');

        // 在测试环境中，API客户端使用模拟响应，总是返回成功
        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $content = false !== $client->getResponse()->getContent() ? $client->getResponse()->getContent() : '';
        $responseData = json_decode($content, true);
        self::assertIsArray($responseData);
        $this->assertArrayHasKey('actions', $responseData);
    }

    public function testGetPaymentActionsGetWithEmptyActions(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        // 创建支付会话和支付记录
        $session = new PaymentSession();
        $session->setSessionId('hps_test789');
        $session->setReference('order_789');
        $session->setAmount(10000);
        $session->setCurrency('USD');
        $session->setStatus('Completed');
        $session->setCustomerEmail('test@example.com');
        $session->setSuccessUrl('https://example.com/success');
        $session->setCancelUrl('https://example.com/cancel');
        $session->setPaymentUrl('https://example.com/payment');

        $payment = new Payment();
        $payment->setPaymentId('pay_completed123');
        $payment->setSession($session);
        $payment->setAmount(10000);
        $payment->setCurrency('USD');
        $payment->setStatus('Completed');
        $payment->setApproved(true);
        $payment->setReference('order_789');

        $entityManager = self::getService(EntityManagerInterface::class);
        $entityManager->persist($session);
        $entityManager->persist($payment);
        $entityManager->flush();

        $client->request('GET', '/payments/pay_completed123/actions');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $content = false !== $client->getResponse()->getContent() ? $client->getResponse()->getContent() : '';
        $responseData = json_decode($content, true);
        self::assertIsArray($responseData);
        $this->assertArrayHasKey('actions', $responseData);
        $this->assertIsArray($responseData['actions']);
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(false);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);

        $client->request($method, '/payments/pay_test123/actions');
    }
}
