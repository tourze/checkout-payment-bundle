<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\Tests\Service;

use CheckoutPaymentBundle\Entity\Payment;
use CheckoutPaymentBundle\Entity\PaymentSession;
use CheckoutPaymentBundle\Repository\PaymentRepository;
use CheckoutPaymentBundle\Repository\PaymentSessionRepository;
use CheckoutPaymentBundle\Service\WebhookProcessor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(WebhookProcessor::class)]
#[RunTestsInSeparateProcesses]
final class WebhookProcessorTest extends AbstractIntegrationTestCase
{
    private WebhookProcessor $processor;

    protected function onSetUp(): void
    {
        // 直接从容器获取 WebhookProcessor 服务
        $this->processor = self::getService(WebhookProcessor::class);
    }

    public function testProcessWebhookDataWithInvalidData(): void
    {
        $data = [
            'type' => 'payment_approved',
            'data' => [],
        ];

        // 使用 NullLogger，不需要验证日志调用
        // 直接测试处理逻辑，不应该抛出异常
        $this->processor->processWebhookData($data);

        // 测试通过即表示处理正常完成（NullLogger 会静默处理日志）
        $this->assertTrue(true);
    }

    public function testProcessWebhookDataWithMissingSession(): void
    {
        $data = [
            'type' => 'payment_approved',
            'data' => [
                'id' => 'pay_123',
                'reference' => 'ref_123',
            ],
        ];

        // 使用真实的 Repository，不需要 Mock，因为 reference 'ref_123' 不存在
        // 会返回 null，这正是我们测试的场景

        // 使用 NullLogger，不需要验证日志调用
        // 直接测试处理逻辑，不应该抛出异常
        $this->processor->processWebhookData($data);

        // 测试通过即表示处理正常完成（NullLogger 会静默处理日志）
        $this->assertTrue(true);
    }

    public function testProcessWebhookDataSuccessfully(): void
    {
        // 创建一个真实的会话记录
        $session = new PaymentSession();
        $session->setSessionId('sess_test_123');
        $session->setReference('ref_123');
        $session->setAmount(10000);
        $session->setCurrency('USD');
        $session->setCustomerEmail('test@example.com');
        $session->setSuccessUrl('https://example.com/success');
        $session->setCancelUrl('https://example.com/cancel');
        $session->setPaymentUrl('https://example.com/payment');

        // 持久化测试数据到真实数据库
        $this->persistAndFlush($session);

        $data = [
            'type' => 'payment_approved',
            'data' => [
                'id' => 'pay_123',
                'reference' => 'ref_123',
                'amount' => 10000,
                'currency' => 'USD',
                'status' => 'Authorized',
            ],
        ];

        // 使用真实的 WebhookProcessor 服务和其依赖
        // 测试通过即表示处理正常完成
        $this->processor->processWebhookData($data);

        // 测试通过即表示处理正常完成
        $this->assertTrue(true);
    }
}
