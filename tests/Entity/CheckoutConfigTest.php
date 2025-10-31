<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\Tests\Entity;

use CheckoutPaymentBundle\Entity\CheckoutConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(CheckoutConfig::class)]
final class CheckoutConfigTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new CheckoutConfig();
    }

    public function testCheckoutConfigEntity(): void
    {
        $config = new CheckoutConfig();

        // Test initial state
        $this->assertTrue($config->isEnabled());
        $this->assertTrue($config->isSandbox());
        $this->assertEquals(30, $config->getTimeout());
        $this->assertEquals(3, $config->getRetryAttempts());
        $this->assertFalse($config->isDefault());

        // Test setters and getters
        $config->setName('Test Config');
        $config->setDescription('Test Description');
        $config->setApiKey('test_api_key');
        $config->setEnabled(false);
        $config->setSandbox(false);
        $config->setTimeout(60);
        $config->setRetryAttempts(5);
        $config->setExtraConfig(['test' => 'value']);
        $config->setDefault(true);

        $this->assertEquals('Test Config', $config->getName());
        $this->assertEquals('Test Description', $config->getDescription());
        $this->assertEquals('test_api_key', $config->getApiKey());
        $this->assertFalse($config->isEnabled());
        $this->assertFalse($config->isSandbox());
        $this->assertEquals(60, $config->getTimeout());
        $this->assertEquals(5, $config->getRetryAttempts());
        $this->assertEquals(['test' => 'value'], $config->getExtraConfig());
        $this->assertTrue($config->isDefault());
    }

    public function testGetApiUrl(): void
    {
        $config = new CheckoutConfig();

        // Test sandbox URL
        $config->setSandbox(true);
        $this->assertEquals('https://api.sandbox.checkout.com', $config->getApiUrl());

        // Test production URL
        $config->setSandbox(false);
        $this->assertEquals('https://api.checkout.com', $config->getApiUrl());
    }

    public function testToString(): void
    {
        $config = new CheckoutConfig();
        $config->setName('Test Config');

        $this->assertEquals('Test Config', (string) $config);
    }

    /** @return iterable<string, array{string, mixed}> */
    public static function propertiesProvider(): iterable
    {
        return [
            'name' => ['name', 'Test Config'],
            'description' => ['description', 'Test Description'],
            'apiKey' => ['apiKey', 'test_api_key_123'],
            'enabled' => ['enabled', true],
            'sandbox' => ['sandbox', true],
            'timeout' => ['timeout', 30],
            'retryAttempts' => ['retryAttempts', 3],
            'extraConfig' => ['extraConfig', ['test' => 'value']],
            'default' => ['default', false],
        ];
    }
}
