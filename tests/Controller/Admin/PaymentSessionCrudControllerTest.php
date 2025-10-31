<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\Tests\Controller\Admin;

use CheckoutPaymentBundle\Controller\Admin\PaymentSessionCrudController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * @internal
 */
#[CoversClass(PaymentSessionCrudController::class)]
#[RunTestsInSeparateProcesses]
class PaymentSessionCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): PaymentSessionCrudController
    {
        return self::getService(PaymentSessionCrudController::class);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID' => ['ID'];
        yield '会话ID' => ['会话ID'];
        yield '订单参考号' => ['订单参考号'];
        yield '支付金额' => ['支付金额'];
        yield '货币' => ['货币'];
        yield '客户邮箱' => ['客户邮箱'];
        yield '客户姓名' => ['客户姓名'];
        yield '会话状态' => ['会话状态'];
        yield '关联支付' => ['关联支付'];
        yield '过期时间' => ['过期时间'];
        yield '创建时间' => ['创建时间'];
        yield '更新时间' => ['更新时间'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideNewPageFields(): iterable
    {
        // PaymentSession 允许NEW操作，提供实际的字段
        yield 'sessionId' => ['sessionId'];
        yield 'reference' => ['reference'];
        yield 'amount' => ['amount'];
        yield 'currency' => ['currency'];
        yield 'description' => ['description'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    #[DataProvider('provideEditPageFields')]
    public static function provideEditPageFields(): iterable
    {
        yield 'session_id' => ['sessionId'];
        yield 'reference' => ['reference'];
        yield 'amount' => ['amount'];
        yield 'currency' => ['currency'];
        yield 'description' => ['description'];
        yield 'customer_email' => ['customerEmail'];
        yield 'customer_name' => ['customerName'];
        yield 'billing_address' => ['billingAddress'];
        yield 'success_url' => ['successUrl'];
        yield 'cancel_url' => ['cancelUrl'];
        yield 'failure_url' => ['failureUrl'];
        yield 'payment_url' => ['paymentUrl'];
        yield 'metadata' => ['metadata'];
        yield 'expires_at' => ['expiresAt'];
    }
}
