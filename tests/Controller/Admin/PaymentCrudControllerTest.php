<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\Tests\Controller\Admin;

use CheckoutPaymentBundle\Controller\Admin\PaymentCrudController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * @internal
 */
#[CoversClass(PaymentCrudController::class)]
#[RunTestsInSeparateProcesses]
class PaymentCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): PaymentCrudController
    {
        return self::getService(PaymentCrudController::class);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID' => ['ID'];
        yield '支付ID' => ['支付ID'];
        yield '支付会话' => ['支付会话'];
        yield '支付金额' => ['支付金额'];
        yield '已退款金额' => ['已退款金额'];
        yield '订单参考号' => ['订单参考号'];
        yield '支付状态' => ['支付状态'];
        yield '3DS验证' => ['3DS验证'];
        yield '已批准' => ['已批准'];
        yield '退款记录' => ['退款记录'];
        yield '处理时间' => ['处理时间'];
        yield '创建时间' => ['创建时间'];
        yield '更新时间' => ['更新时间'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    /**
     * @return iterable<string, array{string}>
     */
    public static function provideNewPageFields(): iterable
    {
        // Payment 禁用了NEW操作，但DataProvider不能为空，提供字段名
        yield 'paymentId' => ['paymentId'];
        yield 'reference' => ['reference'];
        yield 'amount' => ['amount'];
    }

    #[DataProvider('provideEditPageFields')]
    public static function provideEditPageFields(): iterable
    {
        // Payment 禁用了EDIT操作，但DataProvider不能为空，提供字段名
        yield 'paymentId' => ['paymentId'];
        yield 'reference' => ['reference'];
        yield 'amount' => ['amount'];
    }
}
