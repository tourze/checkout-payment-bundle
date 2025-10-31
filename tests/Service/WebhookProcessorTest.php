<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\Tests\Service;

use CheckoutPaymentBundle\Entity\PaymentSession;
use CheckoutPaymentBundle\Repository\PaymentRepository;
use CheckoutPaymentBundle\Repository\PaymentSessionRepository;
use CheckoutPaymentBundle\Service\PaymentFactory;
use CheckoutPaymentBundle\Service\WebhookProcessor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @internal
 */
#[CoversClass(WebhookProcessor::class)]
final class WebhookProcessorTest extends TestCase
{
    /** @var PaymentSessionRepository&MockObject */
    private PaymentSessionRepository $sessionRepository;

    /** @var PaymentRepository&MockObject */
    private PaymentRepository $paymentRepository;

    /** @var PaymentFactory&MockObject */
    private PaymentFactory $paymentFactory;

    /** @var LoggerInterface&MockObject */
    private LoggerInterface $logger;

    private WebhookProcessor $processor;

    protected function setUp(): void
    {
        $this->sessionRepository = $this->createMock(PaymentSessionRepository::class);
        $this->paymentRepository = $this->createMock(PaymentRepository::class);
        $this->paymentFactory = $this->createMock(PaymentFactory::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->processor = new WebhookProcessor(
            $this->sessionRepository,
            $this->paymentRepository,
            $this->paymentFactory,
            $this->logger
        );
    }

    public function testProcessWebhookDataWithInvalidData(): void
    {
        $data = [
            'type' => 'payment_approved',
            'data' => [],
        ];

        $this->logger
            ->expects($this->once())
            ->method('warning')
            ->with('Invalid webhook data structure')
        ;

        $this->processor->processWebhookData($data);
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

        $this->sessionRepository
            ->method('findByReference')
            ->with('ref_123')
            ->willReturn(null)
        ;

        $this->logger
            ->expects($this->once())
            ->method('warning')
            ->with('Payment session not found for webhook')
        ;

        $this->processor->processWebhookData($data);
    }

    public function testProcessWebhookDataSuccessfully(): void
    {
        $session = new PaymentSession();
        $session->setReference('ref_123');

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

        $this->sessionRepository
            ->method('findByReference')
            ->with('ref_123')
            ->willReturn($session)
        ;

        $this->paymentRepository
            ->method('findByPaymentId')
            ->with('pay_123')
            ->willReturn(null)
        ;

        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with('Webhook processed successfully')
        ;

        $this->processor->processWebhookData($data);
    }
}
