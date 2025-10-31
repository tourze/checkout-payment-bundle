<?php

namespace CheckoutPaymentBundle\Tests\Repository;

use CheckoutPaymentBundle\Entity\CheckoutConfig;
use CheckoutPaymentBundle\Repository\CheckoutConfigRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @template TEntity of CheckoutConfig
 * @extends AbstractRepositoryTestCase<TEntity>
 * @internal
 */
#[CoversClass(CheckoutConfigRepository::class)]
#[RunTestsInSeparateProcesses]
class CheckoutConfigRepositoryTest extends AbstractRepositoryTestCase
{
    /** @return ServiceEntityRepository<CheckoutConfig> */
    protected function getRepository(): ServiceEntityRepository
    {
        return self::getService(CheckoutConfigRepository::class);
    }

    protected function onSetUp(): void
    {
        // Clear all existing data first
        /** @var CheckoutConfigRepository $repository */
        $repository = $this->getRepository();

        foreach ($repository->findAll() as $entity) {
            $repository->remove($entity, true);
        }

        // Always create one fixture for count test requirement
        // The findAll test in AbstractRepositoryTestCase will clear data again in its own implementation
        $fixture = $this->createNewEntity();
        $fixture->setName('fixture-config-for-count-test');
        $repository->save($fixture, true);
    }

    protected function createNewEntity(): CheckoutConfig
    {
        $config = new CheckoutConfig();
        $config->setName('test-config-' . uniqid());
        $config->setDescription('Test configuration');
        $config->setApiKey('test-api-key-' . uniqid());
        $config->setEnabled(true);
        $config->setSandbox(true);
        $config->setTimeout(30);
        $config->setRetryAttempts(3);
        $config->setExtraConfig(['test' => 'value']);
        $config->setDefault(false);

        return $config;
    }

    public function testSaveEntity(): void
    {
        /** @var CheckoutConfigRepository $repository */
        $repository = $this->getRepository();
        $config = $this->createNewEntity();
        $config->setName('save-test-config');

        $repository->save($config, true);

        $found = $repository->findByName('save-test-config');
        $this->assertInstanceOf(CheckoutConfig::class, $found);
        $this->assertEquals('save-test-config', $found->getName());
    }

    public function testRemoveEntity(): void
    {
        /** @var CheckoutConfigRepository $repository */
        $repository = $this->getRepository();
        $config = $this->createNewEntity();
        $config->setName('remove-test-config');

        $repository->save($config, true);
        $found = $repository->findByName('remove-test-config');
        $this->assertInstanceOf(CheckoutConfig::class, $found);

        $repository->remove($config, true);
        $found = $repository->findByName('remove-test-config');
        $this->assertNull($found);
    }

    public function testFindEnabledConfigs(): void
    {
        /** @var CheckoutConfigRepository $repository */
        $repository = $this->getRepository();

        // Create enabled config
        $enabledConfig = $this->createNewEntity();
        $enabledConfig->setName('enabled-config');
        $enabledConfig->setEnabled(true);
        $repository->save($enabledConfig, true);

        // Create disabled config
        $disabledConfig = $this->createNewEntity();
        $disabledConfig->setName('disabled-config');
        $disabledConfig->setEnabled(false);
        $repository->save($disabledConfig, true);

        $enabledConfigs = $repository->findEnabledConfigs();

        foreach ($enabledConfigs as $config) {
            $this->assertTrue($config->isEnabled());
        }
    }

    public function testFindDefaultConfig(): void
    {
        /** @var CheckoutConfigRepository $repository */
        $repository = $this->getRepository();

        $defaultConfig = $this->createNewEntity();
        $defaultConfig->setName('default-config');
        $defaultConfig->setEnabled(true);
        $defaultConfig->setDefault(true);
        $repository->save($defaultConfig, true);

        $found = $repository->findDefaultConfig();
        $this->assertInstanceOf(CheckoutConfig::class, $found);
        $this->assertTrue($found->isDefault());
        $this->assertTrue($found->isEnabled());
    }

    public function testFindDefaultConfigReturnsNull(): void
    {
        /** @var CheckoutConfigRepository $repository */
        $repository = $this->getRepository();

        // 清除所有现有的默认配置
        foreach ($repository->findAll() as $entity) {
            $repository->remove($entity, true);
        }

        // Create non-default configs only
        $config1 = $this->createNewEntity();
        $config1->setName('non-default-1');
        $config1->setDefault(false);
        $repository->save($config1, true);

        $config2 = $this->createNewEntity();
        $config2->setName('non-default-2');
        $config2->setDefault(false);
        $repository->save($config2, true);

        $found = $repository->findDefaultConfig();

        // 当没有默认配置时应该返回null
        $this->assertNull($found, 'findDefaultConfig should return null when no default config exists');

        // 验证我们确实创建了非默认配置
        $allConfigs = $repository->findAll();
        $this->assertCount(2, $allConfigs, 'Should have exactly 2 non-default configs');

        foreach ($allConfigs as $config) {
            $this->assertFalse($config->isDefault(), 'All configs should be non-default');
        }
    }

    public function testFindByName(): void
    {
        /** @var CheckoutConfigRepository $repository */
        $repository = $this->getRepository();

        $config = $this->createNewEntity();
        $config->setName('find-by-name-test');
        $repository->save($config, true);

        $found = $repository->findByName('find-by-name-test');
        $this->assertInstanceOf(CheckoutConfig::class, $found);
        $this->assertEquals('find-by-name-test', $found->getName());

        $notFound = $repository->findByName('non-existent-config');
        $this->assertNull($notFound);
    }

    public function testFindByEnvironment(): void
    {
        /** @var CheckoutConfigRepository $repository */
        $repository = $this->getRepository();

        // Create sandbox config
        $sandboxConfig = $this->createNewEntity();
        $sandboxConfig->setName('sandbox-config');
        $sandboxConfig->setEnabled(true);
        $sandboxConfig->setSandbox(true);
        $repository->save($sandboxConfig, true);

        // Create production config
        $prodConfig = $this->createNewEntity();
        $prodConfig->setName('prod-config');
        $prodConfig->setEnabled(true);
        $prodConfig->setSandbox(false);
        $repository->save($prodConfig, true);

        $sandboxConfigs = $repository->findByEnvironment(true);
        foreach ($sandboxConfigs as $config) {
            $this->assertTrue($config->isSandbox());
            $this->assertTrue($config->isEnabled());
        }

        $prodConfigs = $repository->findByEnvironment(false);
        foreach ($prodConfigs as $config) {
            $this->assertFalse($config->isSandbox());
            $this->assertTrue($config->isEnabled());
        }
    }

    public function testFindByEnvironmentWithDisabledConfigs(): void
    {
        /** @var CheckoutConfigRepository $repository */
        $repository = $this->getRepository();

        // Create disabled sandbox config
        $disabledConfig = $this->createNewEntity();
        $disabledConfig->setName('disabled-sandbox');
        $disabledConfig->setEnabled(false);
        $disabledConfig->setSandbox(true);
        $repository->save($disabledConfig, true);

        $sandboxConfigs = $repository->findByEnvironment(true);

        // Should not include disabled configs
        foreach ($sandboxConfigs as $config) {
            $this->assertTrue($config->isEnabled());
        }
    }

    public function testRepositoryOrdering(): void
    {
        /** @var CheckoutConfigRepository $repository */
        $repository = $this->getRepository();

        // Create configs with different names
        $configZ = $this->createNewEntity();
        $configZ->setName('z-config');
        $configZ->setEnabled(true);
        $repository->save($configZ, true);

        $configA = $this->createNewEntity();
        $configA->setName('a-config');
        $configA->setEnabled(true);
        $repository->save($configA, true);

        $configM = $this->createNewEntity();
        $configM->setName('m-config');
        $configM->setEnabled(true);
        $repository->save($configM, true);

        $configs = $repository->findEnabledConfigs();

        // Should be ordered by name ASC
        $names = array_map(fn ($config) => $config->getName(), $configs);
        $sortedNames = $names;
        sort($sortedNames);

        // Check if the first few match the sorted order (there might be other configs)
        $zIndex = array_search('z-config', $names, true);
        $mIndex = array_search('m-config', $names, true);
        $aIndex = array_search('a-config', $names, true);

        if (false !== $zIndex && false !== $mIndex) {
            $this->assertLessThanOrEqual($zIndex, $mIndex);
        }
        if (false !== $mIndex && false !== $aIndex) {
            $this->assertLessThanOrEqual($mIndex, $aIndex);
        }
    }
}
