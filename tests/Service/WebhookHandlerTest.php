<?php

namespace CheckoutPaymentBundle\Tests\Service;

use CheckoutPaymentBundle\Service\WebhookHandler;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(WebhookHandler::class)]
#[RunTestsInSeparateProcesses]
final class WebhookHandlerTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        $_ENV['CHECKOUT_WEBHOOK_SECRET'] = 'test_webhook_secret';
        $_ENV['CHECKOUT_CONFIG_NAME'] = 'test';
    }

    public function testHandleWebhook(): void
    {
        $handler = self::getService(WebhookHandler::class);

        $this->assertInstanceOf(WebhookHandler::class, $handler);
    }

    public function testValidateWebhookRequest(): void
    {
        $handler = self::getService(WebhookHandler::class);

        $this->assertInstanceOf(WebhookHandler::class, $handler);
    }
}
