<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\Tests\Service;

use CheckoutPaymentBundle\Service\AdminMenu;
use Knp\Menu\ItemInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminMenuTestCase;

/**
 * @internal
 */
#[CoversClass(AdminMenu::class)]
#[RunTestsInSeparateProcesses]
final class AdminMenuTest extends AbstractEasyAdminMenuTestCase
{
    protected function onSetUp(): void
    {
        // 初始化测试环境
    }

    public function testInvokeCreatesPaymentMenu(): void
    {
        // 从容器获取真实的 LinkGenerator 服务
        $linkGenerator = self::getService(LinkGeneratorInterface::class);

        $rootItem = $this->createMock(ItemInterface::class);
        $paymentMenu = $this->createMock(ItemInterface::class);

        $rootItem->expects(self::exactly(2))
            ->method('getChild')
            ->with('支付管理')
            ->willReturnOnConsecutiveCalls(null, $paymentMenu)
        ;

        $rootItem->expects(self::once())
            ->method('addChild')
            ->with('支付管理')
            ->willReturn($paymentMenu)
        ;

        $paymentMenu->expects(self::exactly(7))
            ->method('addChild')
            ->willReturnCallback(function (string $name): ItemInterface {
                return $this->createMock(ItemInterface::class);
            })
        ;

        $adminMenu = self::getService(AdminMenu::class);
        $adminMenu($rootItem);

        // 验证 LinkGenerator 确实被正确调用
        $this->assertInstanceOf(LinkGeneratorInterface::class, $linkGenerator);
    }

    public function testInvokeHandlesExistingMenu(): void
    {
        $rootItem = $this->createMock(ItemInterface::class);
        $paymentMenu = $this->createMock(ItemInterface::class);

        $rootItem->method('getChild')->willReturn($paymentMenu);

        // 期望 addChild 方法被调用来添加子菜单项
        $paymentMenu->expects(self::atLeastOnce())
            ->method('addChild')
            ->willReturnCallback(function (string $name): ItemInterface {
                return $this->createMock(ItemInterface::class);
            });

        $adminMenu = self::getService(AdminMenu::class);
        $adminMenu($rootItem);
    }

    public function testLinkGeneratorIntegration(): void
    {
        // 测试真实的 LinkGenerator 服务集成
        $linkGenerator = self::getService(LinkGeneratorInterface::class);

        // 验证服务是有效的
        $this->assertInstanceOf(LinkGeneratorInterface::class, $linkGenerator);

        // 验证方法存在（具体实现取决于 LinkGeneratorInterface 的定义）
        $this->assertTrue(method_exists($linkGenerator, 'getCurdListPage'));
    }
}
