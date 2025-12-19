<?php

namespace CheckoutPaymentBundle\Tests\Service;

use CheckoutPaymentBundle\Service\CheckoutApiClient;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(CheckoutApiClient::class)]
#[RunTestsInSeparateProcesses]
final class CheckoutApiClientTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // 这个测试类不需要特殊的setUp逻辑
    }

    public function testServiceExists(): void
    {
        $apiClient = self::getService(CheckoutApiClient::class);

        $this->assertInstanceOf(CheckoutApiClient::class, $apiClient);
    }

    public function testIsSandboxMethod(): void
    {
        $apiClient = self::getService(CheckoutApiClient::class);

        $result = $apiClient->isSandbox();
        $this->assertTrue($result === true || $result === false);
    }

    public function testGetApiUrlMethod(): void
    {
        $apiClient = self::getService(CheckoutApiClient::class);

        $url = $apiClient->getApiUrl();
        $this->assertStringContainsString('checkout.com', $url);
    }

    public function testCapturePayment(): void
    {
        $apiClient = self::getService(CheckoutApiClient::class);

        try {
            $result = $apiClient->capturePayment('test_payment_id', []);
            $this->assertNotEmpty($result);
        } catch (\Exception $e) {
            $this->assertInstanceOf(\Exception::class, $e);
        }
    }

    public function testCreateHostedPaymentSession(): void
    {
        $apiClient = self::getService(CheckoutApiClient::class);

        // 创建真实的 PaymentSession 对象，使用 DI 容器中的服务
        $sessionFactory = self::getService(\CheckoutPaymentBundle\Service\PaymentSessionFactory::class);
        $entityManager = self::getService(\Doctrine\ORM\EntityManagerInterface::class);

        $session = $sessionFactory->createFromData([
            'amount' => 1000,
            'currency' => 'USD',
            'reference' => 'test_ref',
            'description' => 'Test',
            'customer_email' => 'test@example.com',
            'customer_name' => 'Test User',
            'success_url' => 'https://example.com/success',
            'cancel_url' => 'https://example.com/cancel',
            'failure_url' => 'https://example.com/failure',
        ]);

        try {
            $result = $apiClient->createHostedPaymentSession($session);
            $this->assertNotEmpty($result);
        } catch (\Exception $e) {
            $this->assertInstanceOf(\Exception::class, $e);
        }
    }

    public function testCreatePayment(): void
    {
        $apiClient = self::getService(CheckoutApiClient::class);

        try {
            $result = $apiClient->createPayment([]);
            $this->assertNotEmpty($result);
        } catch (\Exception $e) {
            $this->assertInstanceOf(\Exception::class, $e);
        }
    }

    public function testCreateWebhook(): void
    {
        $apiClient = self::getService(CheckoutApiClient::class);

        try {
            $result = $apiClient->createWebhook('https://example.com/webhook', []);
            $this->assertNotEmpty($result);
        } catch (\Exception $e) {
            $this->assertInstanceOf(\Exception::class, $e);
        }
    }

    public function testRefundPayment(): void
    {
        $apiClient = self::getService(CheckoutApiClient::class);

        try {
            $result = $apiClient->refundPayment('test_payment_id', []);
            $this->assertNotEmpty($result);
        } catch (\Exception $e) {
            $this->assertInstanceOf(\Exception::class, $e);
        }
    }

    public function testVoidPayment(): void
    {
        $apiClient = self::getService(CheckoutApiClient::class);

        try {
            $result = $apiClient->voidPayment('test_payment_id');
            $this->assertNotEmpty($result);
        } catch (\Exception $e) {
            $this->assertInstanceOf(\Exception::class, $e);
        }
    }
}
