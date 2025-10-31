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
        $linkGenerator = $this->createMock(LinkGeneratorInterface::class);
        $linkGenerator->method('getCurdListPage')
            ->willReturnCallback(static fn (string $entityClass): string => '/admin/' . str_replace('\\', '_', $entityClass))
        ;

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
    }

    public function testInvokeHandlesExistingMenu(): void
    {
        $linkGenerator = $this->createMock(LinkGeneratorInterface::class);
        $linkGenerator->method('getCurdListPage')
            ->willReturn('/admin/test')
        ;

        $rootItem = $this->createMock(ItemInterface::class);
        $paymentMenu = $this->createMock(ItemInterface::class);

        $rootItem->method('getChild')->willReturn($paymentMenu);

        $paymentMenu->method('addChild')->willReturnCallback(function (string $name): ItemInterface {
            return $this->createMock(ItemInterface::class);
        });

        $adminMenu = self::getService(AdminMenu::class);
        $adminMenu($rootItem);

        // 测试逻辑正确，验证AdminMenu已执行完成
        self::expectNotToPerformAssertions();
    }
}
