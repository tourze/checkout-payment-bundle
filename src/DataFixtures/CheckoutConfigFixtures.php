<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\DataFixtures;

use CheckoutPaymentBundle\Entity\CheckoutConfig;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Checkout 配置数据填充
 */
class CheckoutConfigFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // 创建测试配置
        $config = new CheckoutConfig();
        $config->setName('Test Checkout Config');
        $config->setDescription('Test configuration for development');
        $config->setApiKey('test_api_key_123');
        $config->setEnabled(true);
        $config->setSandbox(true);
        $config->setTimeout(30);
        $config->setRetryAttempts(3);
        $config->setDefault(true);

        $manager->persist($config);
        $manager->flush();
    }
}
