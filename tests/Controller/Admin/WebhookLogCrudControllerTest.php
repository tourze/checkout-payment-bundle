<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\Tests\Controller\Admin;

use CheckoutPaymentBundle\Controller\Admin\WebhookLogCrudController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * @internal
 */
#[CoversClass(WebhookLogCrudController::class)]
#[RunTestsInSeparateProcesses]
class WebhookLogCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): WebhookLogCrudController
    {
        return self::getService(WebhookLogCrudController::class);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID' => ['ID'];
        yield 'Webhook ID' => ['Webhook ID'];
        yield '事件ID' => ['事件ID'];
        yield '事件类型' => ['事件类型'];
        yield '处理状态' => ['处理状态'];
        yield '已处理' => ['已处理'];
        yield '签名有效' => ['签名有效'];
        yield '接收时间' => ['接收时间'];
        yield '处理时间' => ['处理时间'];
        yield '创建时间' => ['创建时间'];
        yield '更新时间' => ['更新时间'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideNewPageFields(): iterable
    {
        // WebhookLog 禁用了NEW操作，但DataProvider不能为空，提供字段名
        yield 'webhookId' => ['webhookId'];
        yield 'eventId' => ['eventId'];
        yield 'eventType' => ['eventType'];
    }

    #[DataProvider('provideEditPageFields')]
    public static function provideEditPageFields(): iterable
    {
        // WebhookLog 禁用了EDIT操作，但DataProvider不能为空，提供字段名
        yield 'webhookId' => ['webhookId'];
        yield 'eventId' => ['eventId'];
        yield 'eventType' => ['eventType'];
    }
}
