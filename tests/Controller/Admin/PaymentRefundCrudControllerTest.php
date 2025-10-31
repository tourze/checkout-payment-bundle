<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\Tests\Controller\Admin;

use CheckoutPaymentBundle\Controller\Admin\PaymentRefundCrudController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * @internal
 */
#[CoversClass(PaymentRefundCrudController::class)]
#[RunTestsInSeparateProcesses]
class PaymentRefundCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): PaymentRefundCrudController
    {
        return self::getService(PaymentRefundCrudController::class);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideIndexPageHeaders(): iterable
    {
        // PaymentRefund 通常只读，提供基础字段用于索引页展示
        yield 'ID' => ['ID'];
        yield '退款ID' => ['退款ID'];
        yield '关联支付' => ['关联支付'];
        yield '退款金额' => ['退款金额'];
        yield '退款状态' => ['退款状态'];
        yield '已批准' => ['已批准'];
        yield '处理时间' => ['处理时间'];
        yield '创建时间' => ['创建时间'];
        yield '更新时间' => ['更新时间'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideNewPageFields(): iterable
    {
        // PaymentRefund 禁用了NEW操作，但DataProvider不能为空，提供字段名
        yield 'refundId' => ['refundId'];
        yield 'paymentId' => ['paymentId'];
        yield 'amount' => ['amount'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideEditPageFields(): iterable
    {
        // PaymentRefund 禁用了EDIT操作，但DataProvider不能为空，提供字段名
        yield 'refundId' => ['refundId'];
        yield 'paymentId' => ['paymentId'];
        yield 'amount' => ['amount'];
    }
}
