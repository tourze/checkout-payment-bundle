<?php

namespace CheckoutPaymentBundle\Tests\Controller\Payment;

use CheckoutPaymentBundle\Controller\Payment\CapturePaymentController;
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
#[CoversClass(CapturePaymentController::class)]
#[RunTestsInSeparateProcesses]
class CapturePaymentControllerTest extends AbstractWebTestCase
{
    protected function onSetUp(): void
    {
        $_ENV['CHECKOUT_WEBHOOK_SECRET'] = 'test_webhook_secret';
        $_ENV['CHECKOUT_CONFIG_NAME'] = 'test';
    }

    public function testCapturePaymentPost(): void
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

        $content = false !== json_encode(['amount' => 10000]) ? json_encode(['amount' => 10000]) : '{}';
        $client->request('POST', '/payments/pay_test123/capture', [], [], ['CONTENT_TYPE' => 'application/json'], $content);

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $responseContent = $client->getResponse()->getContent();
        self::assertIsString($responseContent);
        $responseData = json_decode($responseContent, true);
        self::assertIsArray($responseData);
        $this->assertArrayHasKey('payment_id', $responseData);
        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('captured', $responseData);
        $this->assertTrue($responseData['captured']);
    }

    public function testCapturePaymentPostWithPartialAmount(): void
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
        $payment->setPaymentId('pay_test456');
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

        $content = false !== json_encode(['amount' => 5000]) ? json_encode(['amount' => 5000]) : '{}';
        $client->request('POST', '/payments/pay_test456/capture', [], [], ['CONTENT_TYPE' => 'application/json'], $content);

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $responseContent = $client->getResponse()->getContent();
        self::assertIsString($responseContent);
        $responseData = json_decode($responseContent, true);
        self::assertIsArray($responseData);
        $this->assertArrayHasKey('payment_id', $responseData);
        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('captured', $responseData);
        $this->assertTrue($responseData['captured']);
    }

    public function testCapturePaymentPostWithoutAmount(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        // 创建支付会话和支付记录
        $session = new PaymentSession();
        $session->setSessionId('hps_test789');
        $session->setReference('order_789');
        $session->setAmount(10000);
        $session->setCurrency('USD');
        $session->setStatus('Authorized');
        $session->setCustomerEmail('test@example.com');
        $session->setSuccessUrl('https://example.com/success');
        $session->setCancelUrl('https://example.com/cancel');
        $session->setPaymentUrl('https://example.com/payment');

        $payment = new Payment();
        $payment->setPaymentId('pay_test789');
        $payment->setSession($session);
        $payment->setAmount(10000);
        $payment->setCurrency('USD');
        $payment->setStatus('Authorized');
        $payment->setApproved(true);
        $payment->setReference('order_789');

        $entityManager = self::getService(EntityManagerInterface::class);
        $entityManager->persist($session);
        $entityManager->persist($payment);
        $entityManager->flush();

        $content = false !== json_encode([]) ? json_encode([]) : '{}';
        $client->request('POST', '/payments/pay_test789/capture', [], [], ['CONTENT_TYPE' => 'application/json'], $content);

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $responseContent = $client->getResponse()->getContent();
        self::assertIsString($responseContent);
        $responseData = json_decode($responseContent, true);
        self::assertIsArray($responseData);
        $this->assertArrayHasKey('payment_id', $responseData);
        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('captured', $responseData);
        $this->assertTrue($responseData['captured']);
    }

    public function testCapturePaymentPostWithException(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $content = false !== json_encode(['amount' => 10000]) ? json_encode(['amount' => 10000]) : '{}';
        $client->request('POST', '/payments/nonexistent/capture', [], [], ['CONTENT_TYPE' => 'application/json'], $content);

        $this->assertSame(500, $client->getResponse()->getStatusCode());

        $responseContent = $client->getResponse()->getContent();
        self::assertIsString($responseContent);
        $responseData = json_decode($responseContent, true);
        self::assertIsArray($responseData);
        $this->assertArrayHasKey('error', $responseData);
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        if ('INVALID' === $method) {
            self::markTestSkipped('INVALID is not a real HTTP method');
        }

        $client->catchExceptions(false);
        $this->expectException(MethodNotAllowedHttpException::class);

        $client->request($method, '/payments/pay_test123/capture');
    }
}
