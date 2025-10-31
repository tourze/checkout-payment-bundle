<?php

namespace CheckoutPaymentBundle\Tests\Service;

use CheckoutPaymentBundle\Service\AttributeControllerLoader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Routing\RouteCollection;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(AttributeControllerLoader::class)]
#[RunTestsInSeparateProcesses]
final class AttributeControllerLoaderTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // 这个测试类不需要特殊的setUp逻辑
    }

    public function testLoad(): void
    {
        $loader = self::getService(AttributeControllerLoader::class);

        $container = new ContainerBuilder();

        $loader->load($container);

        $this->assertInstanceOf(ContainerBuilder::class, $container);
    }

    public function testAutoload(): void
    {
        $loader = self::getService(AttributeControllerLoader::class);

        $collection = $loader->autoload();

        $this->assertInstanceOf(RouteCollection::class, $collection);
        $this->assertGreaterThan(0, $collection->count());
    }

    public function testSupports(): void
    {
        $loader = self::getService(AttributeControllerLoader::class);

        $this->assertFalse($loader->supports('test_resource'));
        $this->assertFalse($loader->supports('test_resource', 'test_type'));
    }
}
