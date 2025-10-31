<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\Tests\Controller\Webhook;

use CheckoutPaymentBundle\Controller\Webhook\ValidateWebhookController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;

/**
 * @internal
 */
#[CoversClass(ValidateWebhookController::class)]
#[RunTestsInSeparateProcesses]
final class ValidateWebhookControllerTest extends AbstractWebTestCase
{
    protected function onSetUp(): void
    {
        $_ENV['CHECKOUT_WEBHOOK_SECRET'] = 'test_webhook_secret';
        $_ENV['CHECKOUT_CONFIG_NAME'] = 'test';
    }

    public function testValidateWebhook(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $webhookData = [
            'event' => 'payment.completed',
            'data' => [
                'payment_id' => 'pay_test123',
                'amount' => 10000,
                'currency' => 'USD',
                'status' => 'completed',
            ],
        ];

        $jsonContent = false !== json_encode($webhookData) ? json_encode($webhookData) : '{}';
        self::assertIsString($jsonContent);
        $client->request('POST', '/webhooks/validate', [], [], ['CONTENT_TYPE' => 'application/json'], $jsonContent);

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $content = false !== $client->getResponse()->getContent() ? $client->getResponse()->getContent() : '';
        $responseData = json_decode($content, true);
        self::assertIsArray($responseData);
        $this->assertArrayHasKey('valid', $responseData);
        $this->assertArrayHasKey('event_type', $responseData);
        $this->assertArrayHasKey('payment_id', $responseData);
    }

    public function testValidateWebhookWithInvalidData(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $invalidWebhookData = [
            'invalid' => 'data',
        ];

        $jsonContent = false !== json_encode($invalidWebhookData) ? json_encode($invalidWebhookData) : '{}';
        self::assertIsString($jsonContent);
        $client->request('POST', '/webhooks/validate', [], [], ['CONTENT_TYPE' => 'application/json'], $jsonContent);

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $content = false !== $client->getResponse()->getContent() ? $client->getResponse()->getContent() : '';
        $responseData = json_decode($content, true);
        self::assertIsArray($responseData);
        $this->assertArrayHasKey('valid', $responseData);
        $this->assertArrayHasKey('event_type', $responseData);
        $this->assertArrayHasKey('payment_id', $responseData);
    }

    public function testValidateWebhookGet(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $client->catchExceptions(false);
        $this->expectException(MethodNotAllowedHttpException::class);

        $client->request('GET', '/webhooks/validate');
    }

    public function testValidateWebhookPut(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $client->catchExceptions(false);
        $this->expectException(MethodNotAllowedHttpException::class);

        $jsonContent = false !== json_encode(['event' => 'test']) ? json_encode(['event' => 'test']) : '{}';
        self::assertIsString($jsonContent);
        $client->request('PUT', '/webhooks/validate', [], [], ['CONTENT_TYPE' => 'application/json'], $jsonContent);
    }

    public function testValidateWebhookDelete(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $client->catchExceptions(false);
        $this->expectException(MethodNotAllowedHttpException::class);

        $client->request('DELETE', '/webhooks/validate');
    }

    public function testValidateWebhookPatch(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $client->catchExceptions(false);
        $this->expectException(MethodNotAllowedHttpException::class);

        $jsonContent = false !== json_encode(['event' => 'test']) ? json_encode(['event' => 'test']) : '{}';
        self::assertIsString($jsonContent);
        $client->request('PATCH', '/webhooks/validate', [], [], ['CONTENT_TYPE' => 'application/json'], $jsonContent);
    }

    public function testValidateWebhookOptions(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $client->catchExceptions(false);
        $this->expectException(MethodNotAllowedHttpException::class);

        $client->request('OPTIONS', '/webhooks/validate');
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request($method, '/webhooks/validate');
    }
}
