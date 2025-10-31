<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\Tests\DependencyInjection\Compiler;

use CheckoutPaymentBundle\DependencyInjection\Compiler\TestEnvironmentCompilerPass;
use CheckoutPaymentBundle\Service\CheckoutApiClient;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @internal
 */
#[CoversClass(TestEnvironmentCompilerPass::class)]
final class TestEnvironmentCompilerPassTest extends TestCase
{
    private TestEnvironmentCompilerPass $compilerPass;

    protected function setUp(): void
    {
        parent::setUp();
        $this->compilerPass = new TestEnvironmentCompilerPass();
    }

    public function testProcessInTestEnvironment(): void
    {
        $container = new ContainerBuilder();

        // 设置为测试环境
        $container->setParameter('kernel.environment', 'test');

        // 创建 CheckoutApiClient 的服务定义
        $checkoutApiClientDef = new Definition(CheckoutApiClient::class);
        // 设置初始参数： httpClient 不为 null
        $checkoutApiClientDef->setArguments([
            new Reference('some_http_client'),
            new Reference('checkout_config_manager'),
            new Reference('logger'),
            'default',
        ]);

        $container->setDefinition('CheckoutPaymentBundle\Service\CheckoutApiClient', $checkoutApiClientDef);

        // 执行编译器传递
        $this->compilerPass->process($container);

        // 验证第一个参数（httpClient）被设置为 null
        $arguments = $checkoutApiClientDef->getArguments();
        $this->assertNull($arguments[0], 'In test environment, httpClient should be set to null');

        // 验证其他参数保持不变
        $this->assertInstanceOf(Reference::class, $arguments[1]);
        $this->assertInstanceOf(Reference::class, $arguments[2]);
        $this->assertEquals('default', $arguments[3]);
    }

    public function testProcessInProductionEnvironment(): void
    {
        $container = new ContainerBuilder();

        // 设置为生产环境
        $container->setParameter('kernel.environment', 'prod');

        // 创建 CheckoutApiClient 的服务定义
        $checkoutApiClientDef = new Definition(CheckoutApiClient::class);
        $originalArguments = [
            new Reference('some_http_client'),
            new Reference('checkout_config_manager'),
            new Reference('logger'),
            'default',
        ];
        $checkoutApiClientDef->setArguments($originalArguments);

        $container->setDefinition('CheckoutPaymentBundle\Service\CheckoutApiClient', $checkoutApiClientDef);

        // 执行编译器传递
        $this->compilerPass->process($container);

        // 验证参数保持不变
        $arguments = $checkoutApiClientDef->getArguments();
        $this->assertEquals($originalArguments, $arguments, 'In non-test environment, arguments should remain unchanged');
    }

    public function testProcessInDevelopmentEnvironment(): void
    {
        $container = new ContainerBuilder();

        // 设置为开发环境
        $container->setParameter('kernel.environment', 'dev');

        // 创建 CheckoutApiClient 的服务定义
        $checkoutApiClientDef = new Definition(CheckoutApiClient::class);
        $originalArguments = [
            new Reference('some_http_client'),
            new Reference('checkout_config_manager'),
            new Reference('logger'),
            'default',
        ];
        $checkoutApiClientDef->setArguments($originalArguments);

        $container->setDefinition('CheckoutPaymentBundle\Service\CheckoutApiClient', $checkoutApiClientDef);

        // 执行编译器传递
        $this->compilerPass->process($container);

        // 验证参数保持不变
        $arguments = $checkoutApiClientDef->getArguments();
        $this->assertEquals($originalArguments, $arguments, 'In development environment, arguments should remain unchanged');
    }

    public function testProcessWithoutCheckoutApiClientDefinition(): void
    {
        $container = new ContainerBuilder();

        // 设置为测试环境
        $container->setParameter('kernel.environment', 'test');

        // 不创建 CheckoutApiClient 的服务定义

        // 执行编译器传递 - 应该不会抛出异常
        $this->compilerPass->process($container);

        // 验证容器状态
        $this->assertFalse($container->hasDefinition('CheckoutPaymentBundle\Service\CheckoutApiClient'));
    }

    public function testProcessWithEmptyArguments(): void
    {
        $container = new ContainerBuilder();

        // 设置为测试环境
        $container->setParameter('kernel.environment', 'test');

        // 创建 CheckoutApiClient 的服务定义，但不设置参数
        $checkoutApiClientDef = new Definition(CheckoutApiClient::class);
        $checkoutApiClientDef->setArguments([]);

        $container->setDefinition('CheckoutPaymentBundle\Service\CheckoutApiClient', $checkoutApiClientDef);

        // 执行编译器传递 - 应该不会抛出异常
        $this->compilerPass->process($container);

        // 验证参数数组包含一个 null 元素（因为编译器设置了第0个参数为null）
        $arguments = $checkoutApiClientDef->getArguments();
        $this->assertCount(1, $arguments, 'Arguments array should have one element after processing');
        $this->assertNull($arguments[0], 'First argument should be set to null when array was initially empty');
    }

    public function testProcessWithAlreadyNullHttpClient(): void
    {
        $container = new ContainerBuilder();

        // 设置为测试环境
        $container->setParameter('kernel.environment', 'test');

        // 创建 CheckoutApiClient 的服务定义，httpClient 已经为 null
        $checkoutApiClientDef = new Definition(CheckoutApiClient::class);
        $checkoutApiClientDef->setArguments([
            null,
            new Reference('checkout_config_manager'),
            new Reference('logger'),
            'default',
        ]);

        $container->setDefinition('CheckoutPaymentBundle\Service\CheckoutApiClient', $checkoutApiClientDef);

        // 执行编译器传递
        $this->compilerPass->process($container);

        // 验证第一个参数仍然为 null
        $arguments = $checkoutApiClientDef->getArguments();
        $this->assertNull($arguments[0], 'HttpClient should remain null when already null');
    }
}