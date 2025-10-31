<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\Service;

use CheckoutPaymentBundle\Entity\CheckoutConfig;
use CheckoutPaymentBundle\Repository\CheckoutConfigRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(public: true)]
readonly class CheckoutConfigManager
{
    public function __construct(
        private CheckoutConfigRepository $configRepository,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
    ) {
    }

    public function getConfig(string $name): ?CheckoutConfig
    {
        return $this->configRepository->findByName($name);
    }

    public function getDefaultConfig(): ?CheckoutConfig
    {
        return $this->configRepository->findDefaultConfig();
    }

    /** @return array<CheckoutConfig> */
    public function getConfigsByEnvironment(bool $isSandbox): array
    {
        return $this->configRepository->findByEnvironment($isSandbox);
    }

    /** @return array<CheckoutConfig> */
    public function getAllEnabledConfigs(): array
    {
        return $this->configRepository->findEnabledConfigs();
    }

    /** @param array<string, mixed> $data */
    public function createConfig(array $data): CheckoutConfig
    {
        $config = new CheckoutConfig();
        $this->setBasicConfigFields($config, $data);
        $this->setAdvancedConfigFields($config, $data);
        $this->setExtraConfigData($config, $data);
        $this->handleDefaultFlag($config, $data);

        // 如果设置为默认配置，需要将其他配置设为非默认
        if ($config->isDefault()) {
            $this->clearDefaultFlags();
        }

        $this->entityManager->persist($config);
        $this->entityManager->flush();

        $this->logger->info('Checkout 配置已创建', [
            'config_id' => $config->getId(),
            'config_name' => $config->getName(),
        ]);

        return $config;
    }

    /** @param array<string, mixed> $data */
    public function updateConfig(string $name, array $data): ?CheckoutConfig
    {
        $config = $this->getConfig($name);
        if (null === $config) {
            return null;
        }

        $this->applyConfigUpdates($config, $data);
        $this->entityManager->flush();

        $this->logger->info('Checkout 配置已更新', [
            'config_id' => $config->getId(),
            'config_name' => $config->getName(),
        ]);

        return $config;
    }

    public function deleteConfig(string $name): bool
    {
        $config = $this->getConfig($name);
        if (null === $config) {
            return false;
        }

        $this->entityManager->remove($config);
        $this->entityManager->flush();

        $this->logger->info('Checkout 配置已删除', [
            'config_id' => $config->getId(),
            'config_name' => $config->getName(),
        ]);

        return true;
    }

    /** @param array<string, mixed> $data */
    private function applyConfigUpdates(CheckoutConfig $config, array $data): void
    {
        $this->updateBasicConfigFields($config, $data);
        $this->updateAdvancedConfigFields($config, $data);
        $this->updateExtraConfigData($config, $data);
        $this->updateDefaultFlag($config, $data);
    }

    /** @param array<string, mixed> $data */
    private function setBasicConfigFields(CheckoutConfig $config, array $data): void
    {
        $name = is_string($data['name'] ?? null) ? $data['name'] : '';
        $config->setName($name);

        $description = is_string($data['description'] ?? null) ? $data['description'] : '';
        $config->setDescription($description);

        $apiKey = is_string($data['apiKey'] ?? null) ? $data['apiKey'] : '';
        $config->setApiKey($apiKey);
    }

    /** @param array<string, mixed> $data */
    private function setAdvancedConfigFields(CheckoutConfig $config, array $data): void
    {
        $enabled = isset($data['enabled']) ? (bool) $data['enabled'] : true;
        $config->setEnabled($enabled);

        $isSandbox = isset($data['isSandbox']) ? (bool) $data['isSandbox'] : true;
        $config->setSandbox($isSandbox);

        $timeout = isset($data['timeout']) && is_numeric($data['timeout']) ? (int) $data['timeout'] : 30;
        $config->setTimeout($timeout);

        $retryAttempts = isset($data['retryAttempts']) && is_numeric($data['retryAttempts']) ? (int) $data['retryAttempts'] : 3;
        $config->setRetryAttempts($retryAttempts);
    }

    /** @param array<string, mixed> $data */
    private function setExtraConfigData(CheckoutConfig $config, array $data): void
    {
        $extraConfig = null;
        if (isset($data['extraConfig']) && is_array($data['extraConfig'])) {
            /** @var array<string, mixed> */
            $extraConfig = $data['extraConfig'];
        }
        $config->setExtraConfig($extraConfig);
    }

    /** @param array<string, mixed> $data */
    private function handleDefaultFlag(CheckoutConfig $config, array $data): void
    {
        $isDefault = isset($data['isDefault']) ? (bool) $data['isDefault'] : false;
        if ($isDefault) {
            $this->clearDefaultFlags();
        }
        $config->setDefault($isDefault);
    }

    /** @param array<string, mixed> $data */
    private function updateBasicConfigFields(CheckoutConfig $config, array $data): void
    {
        if (isset($data['description']) && is_string($data['description'])) {
            $config->setDescription($data['description']);
        }

        if (isset($data['apiKey']) && is_string($data['apiKey'])) {
            $config->setApiKey($data['apiKey']);
        }
    }

    /** @param array<string, mixed> $data */
    private function updateAdvancedConfigFields(CheckoutConfig $config, array $data): void
    {
        if (isset($data['enabled'])) {
            $config->setEnabled((bool) $data['enabled']);
        }

        if (isset($data['isSandbox'])) {
            $config->setSandbox((bool) $data['isSandbox']);
        }

        if (isset($data['timeout']) && is_numeric($data['timeout'])) {
            $config->setTimeout((int) $data['timeout']);
        }

        if (isset($data['retryAttempts']) && is_numeric($data['retryAttempts'])) {
            $config->setRetryAttempts((int) $data['retryAttempts']);
        }
    }

    /** @param array<string, mixed> $data */
    private function updateExtraConfigData(CheckoutConfig $config, array $data): void
    {
        if (isset($data['extraConfig']) && is_array($data['extraConfig'])) {
            /** @var array<string, mixed> */
            $extraConfig = $data['extraConfig'];
            $config->setExtraConfig($extraConfig);
        }
    }

    /** @param array<string, mixed> $data */
    private function updateDefaultFlag(CheckoutConfig $config, array $data): void
    {
        if (isset($data['isDefault'])) {
            $isDefault = (bool) $data['isDefault'];
            if ($isDefault) {
                $this->clearDefaultFlags();
            }
            $config->setDefault($isDefault);
        }
    }

    private function clearDefaultFlags(): void
    {
        $configs = $this->configRepository->findBy(['isDefault' => true]);
        foreach ($configs as $config) {
            $config->setDefault(false);
        }
    }
}
