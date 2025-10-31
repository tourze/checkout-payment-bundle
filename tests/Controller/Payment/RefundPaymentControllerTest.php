<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\Tests\Controller\Payment;

use CheckoutPaymentBundle\Controller\Payment\RefundPaymentController;
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
#[CoversClass(RefundPaymentController::class)]
#[RunTestsInSeparateProcesses]
final class RefundPaymentControllerTest extends AbstractWebTestCase
{
    protected function onSetUp(): void
    {
        $_ENV['CHECKOUT_WEBHOOK_SECRET'] = 'test_webhook_secret';
        $_ENV['CHECKOUT_CONFIG_NAME'] = 'test';
    }

    public function testRefundPaymentPost(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        // 创建支付会话和支付记录
        $session = new PaymentSession();
        $session->setSessionId('hps_test123');
        $session->setReference('order_123');
        $session->setAmount(10000);
        $session->setCurrency('USD');
        $session->setStatus('Captured');
        $session->setCustomerEmail('test@example.com');
        $session->setSuccessUrl('https://example.com/success');
        $session->setCancelUrl('https://example.com/cancel');
        $session->setPaymentUrl('https://example.com/payment');

        $payment = new Payment();
        $payment->setPaymentId('pay_test123');
        $payment->setSession($session);
        $payment->setAmount(10000);
        $payment->setCurrency('USD');
        $payment->setStatus('Captured');
        $payment->setApproved(true);
        $payment->setCapturedAt(new \DateTimeImmutable());
        $payment->setReference('order_123');

        $entityManager = self::getService(EntityManagerInterface::class);
        $entityManager->persist($session);
        $entityManager->persist($payment);
        $entityManager->flush();

        $jsonContent = $jsonContent = false !== json_encode([
            'amount' => 5000,
            'reference' => 'refund-test-123',
        ]) ? json_encode([
            'amount' => 5000,
            'reference' => 'refund-test-123',
        ]) : '{}';
        self::assertIsString($jsonContent);
        $client->request('POST', '/payments/pay_test123/refund', [], [], ['CONTENT_TYPE' => 'application/json'], $jsonContent);

        $this->assertSame(201, $client->getResponse()->getStatusCode());

        $content = false !== $client->getResponse()->getContent() ? $client->getResponse()->getContent() : '';
        $responseData = json_decode($content, true);
        self::assertIsArray($responseData);
        $this->assertArrayHasKey('refund_id', $responseData);
        $this->assertArrayHasKey('payment_id', $responseData);
        $this->assertArrayHasKey('amount', $responseData);
        $this->assertArrayHasKey('status', $responseData);
        $this->assertEquals(5000, $responseData['amount']);
    }

    public function testRefundPaymentPostFullRefund(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        // 创建支付会话和支付记录
        $session = new PaymentSession();
        $session->setSessionId('hps_test456');
        $session->setReference('order_456');
        $session->setAmount(10000);
        $session->setCurrency('USD');
        $session->setStatus('Captured');
        $session->setCustomerEmail('test@example.com');
        $session->setSuccessUrl('https://example.com/success');
        $session->setCancelUrl('https://example.com/cancel');
        $session->setPaymentUrl('https://example.com/payment');

        $payment = new Payment();
        $payment->setPaymentId('pay_test456');
        $payment->setSession($session);
        $payment->setAmount(10000);
        $payment->setCurrency('USD');
        $payment->setStatus('Captured');
        $payment->setApproved(true);
        $payment->setCapturedAt(new \DateTimeImmutable());
        $payment->setReference('order_456');

        $entityManager = self::getService(EntityManagerInterface::class);
        $entityManager->persist($session);
        $entityManager->persist($payment);
        $entityManager->flush();

        $jsonContent = false !== json_encode([
            'reference' => 'full-refund-test',
        ]) ? json_encode([
            'reference' => 'full-refund-test',
        ]) : '{}';
        self::assertIsString($jsonContent);
        $client->request('POST', '/payments/pay_test456/refund', [], [], ['CONTENT_TYPE' => 'application/json'], $jsonContent);

        $this->assertSame(201, $client->getResponse()->getStatusCode());

        $content = false !== $client->getResponse()->getContent() ? $client->getResponse()->getContent() : '';
        $responseData = json_decode($content, true);
        self::assertIsArray($responseData);
        $this->assertArrayHasKey('refund_id', $responseData);
        $this->assertArrayHasKey('payment_id', $responseData);
        $this->assertArrayHasKey('amount', $responseData);
        $this->assertArrayHasKey('status', $responseData);
    }

    public function testRefundPaymentPostWithoutReference(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        // 创建支付会话和支付记录
        $session = new PaymentSession();
        $session->setSessionId('hps_test789');
        $session->setReference('order_789');
        $session->setAmount(10000);
        $session->setCurrency('USD');
        $session->setStatus('Captured');
        $session->setCustomerEmail('test@example.com');
        $session->setSuccessUrl('https://example.com/success');
        $session->setCancelUrl('https://example.com/cancel');
        $session->setPaymentUrl('https://example.com/payment');

        $payment = new Payment();
        $payment->setPaymentId('pay_test789');
        $payment->setSession($session);
        $payment->setAmount(10000);
        $payment->setCurrency('USD');
        $payment->setStatus('Captured');
        $payment->setApproved(true);
        $payment->setCapturedAt(new \DateTimeImmutable());
        $payment->setReference('order_789');

        $entityManager = self::getService(EntityManagerInterface::class);
        $entityManager->persist($session);
        $entityManager->persist($payment);
        $entityManager->flush();

        $jsonContent = false !== json_encode([
            'amount' => 3000,
        ]) ? json_encode([
            'amount' => 3000,
        ]) : '{}';
        self::assertIsString($jsonContent);
        $client->request('POST', '/payments/pay_test789/refund', [], [], ['CONTENT_TYPE' => 'application/json'], $jsonContent);

        $this->assertSame(201, $client->getResponse()->getStatusCode());

        $content = false !== $client->getResponse()->getContent() ? $client->getResponse()->getContent() : '';
        $responseData = json_decode($content, true);
        self::assertIsArray($responseData);
        $this->assertArrayHasKey('refund_id', $responseData);
        $this->assertArrayHasKey('payment_id', $responseData);
        $this->assertArrayHasKey('amount', $responseData);
        $this->assertArrayHasKey('status', $responseData);
        $this->assertEquals(3000, $responseData['amount']);
    }

    public function testRefundPaymentPostEmptyBody(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        // 创建支付会话和支付记录
        $session = new PaymentSession();
        $session->setSessionId('hps_test101');
        $session->setReference('order_101');
        $session->setAmount(10000);
        $session->setCurrency('USD');
        $session->setStatus('Captured');
        $session->setCustomerEmail('test@example.com');
        $session->setSuccessUrl('https://example.com/success');
        $session->setCancelUrl('https://example.com/cancel');
        $session->setPaymentUrl('https://example.com/payment');

        $payment = new Payment();
        $payment->setPaymentId('pay_test101');
        $payment->setSession($session);
        $payment->setAmount(10000);
        $payment->setCurrency('USD');
        $payment->setStatus('Captured');
        $payment->setApproved(true);
        $payment->setCapturedAt(new \DateTimeImmutable());
        $payment->setReference('order_101');

        $entityManager = self::getService(EntityManagerInterface::class);
        $entityManager->persist($session);
        $entityManager->persist($payment);
        $entityManager->flush();

        $jsonContent = false !== json_encode([]) ? json_encode([]) : '{}';
        self::assertIsString($jsonContent);
        $client->request('POST', '/payments/pay_test101/refund', [], [], ['CONTENT_TYPE' => 'application/json'], $jsonContent);

        $this->assertSame(201, $client->getResponse()->getStatusCode());

        $content = false !== $client->getResponse()->getContent() ? $client->getResponse()->getContent() : '';
        $responseData = json_decode($content, true);
        self::assertIsArray($responseData);
        $this->assertArrayHasKey('refund_id', $responseData);
        $this->assertArrayHasKey('payment_id', $responseData);
        $this->assertArrayHasKey('amount', $responseData);
        $this->assertArrayHasKey('status', $responseData);
    }

    public function testRefundPaymentPostWithException(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $jsonContent = false !== json_encode([
            'amount' => 5000,
            'reference' => 'refund-test-123',
        ]) ? json_encode([
            'amount' => 5000,
            'reference' => 'refund-test-123',
        ]) : '{}';
        self::assertIsString($jsonContent);
        $client->request('POST', '/payments/nonexistent/refund', [], [], ['CONTENT_TYPE' => 'application/json'], $jsonContent);

        $this->assertSame(500, $client->getResponse()->getStatusCode());

        $content = false !== $client->getResponse()->getContent() ? $client->getResponse()->getContent() : '';
        $responseData = json_decode($content, true);
        self::assertIsArray($responseData);
        $this->assertArrayHasKey('error', $responseData);
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $client->catchExceptions(false);
        $this->expectException(MethodNotAllowedHttpException::class);

        $client->request($method, '/payments/pay_test123/refund');
    }
}
