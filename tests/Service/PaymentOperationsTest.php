<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\Tests\Service;

use CheckoutPaymentBundle\Entity\Payment;
use CheckoutPaymentBundle\Exception\PaymentException;
use CheckoutPaymentBundle\Repository\PaymentRefundRepository;
use CheckoutPaymentBundle\Repository\PaymentRepository;
use CheckoutPaymentBundle\Service\CheckoutApiClient;
use CheckoutPaymentBundle\Service\PaymentOperations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @internal
 */
#[CoversClass(PaymentOperations::class)]
final class PaymentOperationsTest extends TestCase
{
    /** @var CheckoutApiClient&MockObject */
    private CheckoutApiClient $apiClient;

    /** @var PaymentRepository&MockObject */
    private PaymentRepository $paymentRepository;

    /** @var PaymentRefundRepository&MockObject */
    private PaymentRefundRepository $refundRepository;

    /** @var LoggerInterface&MockObject */
    private LoggerInterface $logger;

    private PaymentOperations $operations;

    protected function setUp(): void
    {
        $this->apiClient = $this->createMock(CheckoutApiClient::class);
        $this->paymentRepository = $this->createMock(PaymentRepository::class);
        $this->refundRepository = $this->createMock(PaymentRefundRepository::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->operations = new PaymentOperations(
            $this->apiClient,
            $this->paymentRepository,
            $this->refundRepository,
            $this->logger
        );
    }

    public function testCapturePaymentThrowsExceptionWhenPaymentNotFound(): void
    {
        $this->paymentRepository
            ->method('findByPaymentId')
            ->with('pay_123')
            ->willReturn(null)
        ;

        $this->expectException(PaymentException::class);
        $this->operations->capturePayment('pay_123');
    }

    public function testVoidPaymentThrowsExceptionWhenPaymentNotFound(): void
    {
        $this->paymentRepository
            ->method('findByPaymentId')
            ->with('pay_123')
            ->willReturn(null)
        ;

        $this->expectException(PaymentException::class);
        $this->operations->voidPayment('pay_123');
    }

    public function testRefundPaymentThrowsExceptionWhenPaymentNotFound(): void
    {
        $this->paymentRepository
            ->method('findByPaymentId')
            ->with('pay_123')
            ->willReturn(null)
        ;

        $this->expectException(PaymentException::class);
        $this->operations->refundPayment('pay_123');
    }
}
