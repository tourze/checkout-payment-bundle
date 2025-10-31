<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * 测试环境编译器传递
 * 在测试环境中，将CheckoutApiClient的httpClient设置为null以使用模拟响应
 */
class TestEnvironmentCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        // 只在测试环境中处理
        if ($container->getParameter('kernel.environment') !== 'test') {
            return;
        }

        // 修改CheckoutApiClient的配置，将httpClient设置为null
        if ($container->hasDefinition('CheckoutPaymentBundle\Service\CheckoutApiClient')) {
            $definition = $container->getDefinition('CheckoutPaymentBundle\Service\CheckoutApiClient');

            // 将第一个参数（$httpClient）设置为null
            $arguments = $definition->getArguments();
            $arguments[0] = null;
            $definition->setArguments($arguments);
        }
    }
}