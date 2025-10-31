<?php

namespace CheckoutPaymentBundle\Tests\Controller\Payment;

use CheckoutPaymentBundle\Controller\Payment\CreateDirectPaymentController;
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
#[CoversClass(CreateDirectPaymentController::class)]
#[RunTestsInSeparateProcesses]
class CreateDirectPaymentControllerTest extends AbstractWebTestCase
{
    protected function onSetUp(): void
    {
        $_ENV['CHECKOUT_WEBHOOK_SECRET'] = 'test_webhook_secret';
        $_ENV['CHECKOUT_CONFIG_NAME'] = 'test';
    }

    public function testCreateDirectPaymentPost(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        // 首先创建一个PaymentSession用于关联
        $session = new PaymentSession();
        $session->setSessionId('hps_test_direct_123');
        $session->setReference('TEST-DIRECT-123');
        $session->setAmount(10000);
        $session->setCurrency('USD');
        $session->setStatus('created');
        $session->setCustomerEmail('test@example.com');
        $session->setSuccessUrl('https://example.com/success');
        $session->setCancelUrl('https://example.com/cancel');
        $session->setPaymentUrl('https://example.com/payment');

        $entityManager = self::getService(EntityManagerInterface::class);
        $entityManager->persist($session);
        $entityManager->flush();

        $data = [
            'reference' => 'TEST-DIRECT-123',
            'amount' => 10000,
            'currency' => 'USD',
            'source' => ['type' => 'card'],
        ];

        $jsonContent = false !== json_encode($data) ? json_encode($data) : '{}';
        self::assertIsString($jsonContent);
        $client->request('POST', '/payments', [], [], ['CONTENT_TYPE' => 'application/json'], $jsonContent);

        $this->assertSame(201, $client->getResponse()->getStatusCode());

        $content = false !== $client->getResponse()->getContent() ? $client->getResponse()->getContent() : '';
        $responseData = json_decode($content, true);
        self::assertIsArray($responseData);
        $this->assertArrayHasKey('payment_id', $responseData);
        $this->assertArrayHasKey('reference', $responseData);
        $this->assertArrayHasKey('amount', $responseData);
        $this->assertArrayHasKey('currency', $responseData);
        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('approved', $responseData);
        $this->assertEquals($data['reference'], $responseData['reference']);
        $this->assertEquals($data['amount'], $responseData['amount']);
        $this->assertEquals($data['currency'], $responseData['currency']);
    }

    public function testCreateDirectPaymentPostInvalidJson(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $client->request('POST', '/payments', [], [], ['CONTENT_TYPE' => 'application/json'], 'invalid json');

        $this->assertSame(400, $client->getResponse()->getStatusCode());

        $content = false !== $client->getResponse()->getContent() ? $client->getResponse()->getContent() : '';
        $responseData = json_decode($content, true);
        self::assertIsArray($responseData);
        $this->assertArrayHasKey('error', $responseData);
        self::assertIsString($responseData['error']);
        $this->assertEquals('Invalid JSON', $responseData['error']);
    }

    public function testCreateDirectPaymentPostMissingRequiredFields(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $data = [
            'amount' => 10000,
            'currency' => 'USD',
            'source' => ['type' => 'card'],
        ];

        $jsonContent = false !== json_encode($data) ? json_encode($data) : '{}';
        self::assertIsString($jsonContent);
        $client->request('POST', '/payments', [], [], ['CONTENT_TYPE' => 'application/json'], $jsonContent);

        $this->assertSame(400, $client->getResponse()->getStatusCode());

        $content = false !== $client->getResponse()->getContent() ? $client->getResponse()->getContent() : '';
        $responseData = json_decode($content, true);
        self::assertIsArray($responseData);
        $this->assertArrayHasKey('error', $responseData);
        self::assertIsString($responseData['error']);
        $this->assertStringContainsString('Missing required field: reference', $responseData['error']);
    }

    public function testCreateDirectPaymentPostMissingAmount(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $data = [
            'reference' => 'TEST-REF-123',
            'currency' => 'USD',
            'source' => ['type' => 'card'],
        ];

        $jsonContent = false !== json_encode($data) ? json_encode($data) : '{}';
        self::assertIsString($jsonContent);
        $client->request('POST', '/payments', [], [], ['CONTENT_TYPE' => 'application/json'], $jsonContent);

        $this->assertSame(400, $client->getResponse()->getStatusCode());

        $content = false !== $client->getResponse()->getContent() ? $client->getResponse()->getContent() : '';
        $responseData = json_decode($content, true);
        self::assertIsArray($responseData);
        $this->assertArrayHasKey('error', $responseData);
        self::assertIsString($responseData['error']);
        $this->assertStringContainsString('Missing required field: amount', $responseData['error']);
    }

    public function testCreateDirectPaymentPostMissingCurrency(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $data = [
            'reference' => 'TEST-REF-123',
            'amount' => 10000,
            'source' => ['type' => 'card'],
        ];

        $jsonContent = false !== json_encode($data) ? json_encode($data) : '{}';
        self::assertIsString($jsonContent);
        $client->request('POST', '/payments', [], [], ['CONTENT_TYPE' => 'application/json'], $jsonContent);

        $this->assertSame(400, $client->getResponse()->getStatusCode());

        $content = false !== $client->getResponse()->getContent() ? $client->getResponse()->getContent() : '';
        $responseData = json_decode($content, true);
        self::assertIsArray($responseData);
        $this->assertArrayHasKey('error', $responseData);
        self::assertIsString($responseData['error']);
        $this->assertStringContainsString('Missing required field: currency', $responseData['error']);
    }

    public function testCreateDirectPaymentPostMissingSource(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $data = [
            'reference' => 'TEST-REF-123',
            'amount' => 10000,
            'currency' => 'USD',
        ];

        $jsonContent = false !== json_encode($data) ? json_encode($data) : '{}';
        self::assertIsString($jsonContent);
        $client->request('POST', '/payments', [], [], ['CONTENT_TYPE' => 'application/json'], $jsonContent);

        $this->assertSame(400, $client->getResponse()->getStatusCode());

        $content = false !== $client->getResponse()->getContent() ? $client->getResponse()->getContent() : '';
        $responseData = json_decode($content, true);
        self::assertIsArray($responseData);
        $this->assertArrayHasKey('error', $responseData);
        self::assertIsString($responseData['error']);
        $this->assertStringContainsString('Missing required field: source', $responseData['error']);
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

        $client->request($method, '/payments');
    }
}
