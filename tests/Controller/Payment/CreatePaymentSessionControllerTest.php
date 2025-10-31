<?php

namespace CheckoutPaymentBundle\Tests\Controller\Payment;

use CheckoutPaymentBundle\Controller\Payment\CreatePaymentSessionController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;

/**
 * @internal
 */
#[CoversClass(CreatePaymentSessionController::class)]
#[RunTestsInSeparateProcesses]
class CreatePaymentSessionControllerTest extends AbstractWebTestCase
{
    protected function onSetUp(): void
    {
        $_ENV['CHECKOUT_WEBHOOK_SECRET'] = 'test_webhook_secret';
        $_ENV['CHECKOUT_CONFIG_NAME'] = 'test';
    }

    public function testCreatePaymentSessionPost(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $data = [
            'reference' => 'TEST-REF-123',
            'amount' => 10000,
            'currency' => 'USD',
            'customer_email' => 'test@example.com',
            'success_url' => 'https://example.com/success',
            'cancel_url' => 'https://example.com/cancel',
        ];

        $jsonContent = false !== json_encode($data) ? json_encode($data) : '{}';
        self::assertIsString($jsonContent);
        $client->request('POST', '/sessions', [], [], ['CONTENT_TYPE' => 'application/json'], $jsonContent);

        $this->assertSame(201, $client->getResponse()->getStatusCode());

        $content = false !== $client->getResponse()->getContent() ? $client->getResponse()->getContent() : '';
        $responseData = json_decode($content, true);
        self::assertIsArray($responseData);
        $this->assertArrayHasKey('session_id', $responseData);
        $this->assertArrayHasKey('payment_url', $responseData);
        $this->assertArrayHasKey('reference', $responseData);
        $this->assertArrayHasKey('amount', $responseData);
        $this->assertArrayHasKey('currency', $responseData);
        $this->assertArrayHasKey('status', $responseData);
        $this->assertEquals($data['reference'], $responseData['reference']);
        $this->assertEquals($data['amount'], $responseData['amount']);
        $this->assertEquals($data['currency'], $responseData['currency']);
    }

    public function testCreatePaymentSessionPostInvalidJson(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $client->request('POST', '/sessions', [], [], ['CONTENT_TYPE' => 'application/json'], 'invalid json');

        $this->assertSame(400, $client->getResponse()->getStatusCode());

        $content = false !== $client->getResponse()->getContent() ? $client->getResponse()->getContent() : '';
        $responseData = json_decode($content, true);
        self::assertIsArray($responseData);
        $this->assertArrayHasKey('error', $responseData);
        self::assertIsString($responseData['error']);
        $this->assertEquals('Invalid JSON', $responseData['error']);
    }

    public function testCreatePaymentSessionPostMissingRequiredField(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $data = [
            'reference' => 'TEST-REF-123',
            'amount' => 10000,
            // Missing currency, customer_email, success_url, cancel_url
        ];

        $jsonContent = false !== json_encode($data) ? json_encode($data) : '{}';
        self::assertIsString($jsonContent);
        $client->request('POST', '/sessions', [], [], ['CONTENT_TYPE' => 'application/json'], $jsonContent);

        $this->assertSame(400, $client->getResponse()->getStatusCode());

        $content = false !== $client->getResponse()->getContent() ? $client->getResponse()->getContent() : '';
        $responseData = json_decode($content, true);
        self::assertIsArray($responseData);
        $this->assertArrayHasKey('error', $responseData);
        self::assertIsString($responseData['error']);
        $this->assertStringContainsString('Missing required field', $responseData['error']);
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $client->catchExceptions(false);

        $this->expectException(MethodNotAllowedHttpException::class);

        $client->request($method, '/sessions');
    }
}
