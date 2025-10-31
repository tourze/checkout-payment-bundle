<?php

namespace CheckoutPaymentBundle\Tests\Service;

use CheckoutPaymentBundle\Service\CheckoutConfigManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(CheckoutConfigManager::class)]
#[RunTestsInSeparateProcesses]
final class CheckoutConfigManagerTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // 这个测试类不需要特殊的setUp逻辑
    }

    public function testServiceExists(): void
    {
        $configManager = self::getService(CheckoutConfigManager::class);

        $this->assertInstanceOf(CheckoutConfigManager::class, $configManager);
    }

    public function testCreateConfig(): void
    {
        $configManager = self::getService(CheckoutConfigManager::class);

        $data = [
            'name' => 'test-config',
            'description' => 'Test configuration',
            'apiKey' => 'test-api-key',
            'enabled' => true,
            'isSandbox' => true,
            'timeout' => 30,
            'retryAttempts' => 3,
            'extraConfig' => ['key' => 'value'],
            'isDefault' => false,
        ];

        $config = $configManager->createConfig($data);

        $this->assertEquals($data['name'], $config->getName());
    }

    public function testGetAllEnabledConfigs(): void
    {
        $configManager = self::getService(CheckoutConfigManager::class);

        $configs = $configManager->getAllEnabledConfigs();

        $this->assertNotNull($configs);
    }

    public function testDeleteConfig(): void
    {
        $configManager = self::getService(CheckoutConfigManager::class);

        $result = $configManager->deleteConfig('non_existent_config');

        $this->assertFalse($result);
    }

    public function testUpdateConfig(): void
    {
        $configManager = self::getService(CheckoutConfigManager::class);

        $result = $configManager->updateConfig('non_existent_config', []);

        $this->assertNull($result);
    }
}
