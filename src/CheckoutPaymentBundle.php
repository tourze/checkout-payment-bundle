<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle;

use CheckoutPaymentBundle\DependencyInjection\Compiler\TestEnvironmentCompilerPass;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;
use Tourze\RoutingAutoLoaderBundle\RoutingAutoLoaderBundle;

class CheckoutPaymentBundle extends Bundle implements BundleDependencyInterface
{
    public static function getBundleDependencies(): array
    {
        return [
            DoctrineBundle::class => ['all' => true],
            RoutingAutoLoaderBundle::class => ['all' => true],
        ];
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        // 在测试环境中注册编译器传递
        if ($container->hasParameter('kernel.environment') && 'test' === $container->getParameter('kernel.environment')) {
            $container->prependExtensionConfig('twig', [
                'paths' => [
                    __DIR__ . '/../templates' => 'CheckoutPayment',
                ],
            ]);

            // 注册测试环境编译器传递
            $container->addCompilerPass(new TestEnvironmentCompilerPass());
        }
    }
}
