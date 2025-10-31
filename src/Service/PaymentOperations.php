<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\Service;

use CheckoutPaymentBundle\Entity\Payment;
use CheckoutPaymentBundle\Entity\PaymentRefund;
use CheckoutPaymentBundle\Exception\PaymentException;
use CheckoutPaymentBundle\Repository\PaymentRefundRepository;
use CheckoutPaymentBundle\Repository\PaymentRepository;
use Psr\Log\LoggerInterface;

readonly class PaymentOperations
{
    public function __construct(
        private CheckoutApiClient $apiClient,
        private PaymentRepository $paymentRepository,
        private PaymentRefundRepository $refundRepository,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @param array<string, mixed> $captureData
     */
    public function capturePayment(string $paymentId, array $captureData = []): Payment
    {
        $payment = $this->getPaymentOrFail($paymentId);

        if (!$payment->canCapture()) {
            throw PaymentException::invalidPaymentStatus('Authorized', $payment->getStatus() ?? 'unknown');
        }

        try {
            $apiResponse = $this->apiClient->capturePayment($paymentId, $captureData);

            $payment->setStatus('Captured');
            $payment->setCapturedAt(new \DateTimeImmutable());
            $payment->setResponseSummary(is_string($apiResponse['response_summary'] ?? null) ? $apiResponse['response_summary'] : 'Captured');

            $this->paymentRepository->save($payment, true);

            $this->logger->info('Payment captured successfully', [
                'payment_id' => $paymentId,
                'amount' => $captureData['amount'] ?? $payment->getAmount(),
            ]);

            return $payment;
        } catch (\Exception $e) {
            $this->logger->error('Failed to capture payment', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);

            throw PaymentException::apiError('Failed to capture payment: ' . $e->getMessage());
        }
    }

    public function refundPayment(string $paymentId, ?float $amount = null, ?string $reference = null): PaymentRefund
    {
        $payment = $this->getPaymentOrFail($paymentId);

        if (!$payment->canRefund()) {
            throw PaymentException::cannotRefund($payment->getStatus() ?? 'unknown');
        }

        $refundAmount = (int) ($amount ?? $payment->getAvailableRefundAmount());

        if ($refundAmount > $payment->getAvailableRefundAmount()) {
            throw PaymentException::refundAmountExceedsAvailable();
        }

        try {
            $refundData = [
                'amount' => $refundAmount,
                'reference' => $reference,
            ];

            $apiResponse = $this->apiClient->refundPayment($paymentId, $refundData);
            $refund = $this->createRefundFromApiResponse($apiResponse, $payment, $refundAmount, $reference);
            $this->updatePaymentRefundStatus($payment, $refundAmount);
            $this->logRefundSuccess($paymentId, $refund, $refundAmount, $payment->getTotalRefundedAmount());

            return $refund;
        } catch (\Exception $e) {
            $this->logger->error('Failed to refund payment', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);

            throw PaymentException::apiError('Failed to refund payment: ' . $e->getMessage());
        }
    }

    public function voidPayment(string $paymentId): Payment
    {
        $payment = $this->getPaymentOrFail($paymentId);

        if (!$payment->canVoid()) {
            throw PaymentException::invalidPaymentStatus('Authorized', $payment->getStatus() ?? 'unknown');
        }

        try {
            $this->apiClient->voidPayment($paymentId);

            $payment->setStatus('Voided');
            $payment->setVoidedAt(new \DateTimeImmutable());
            $payment->setResponseSummary('Voided');

            $this->paymentRepository->save($payment, true);

            $this->logger->info('Payment voided successfully', [
                'payment_id' => $paymentId,
            ]);

            return $payment;
        } catch (\Exception $e) {
            $this->logger->error('Failed to void payment', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);

            throw PaymentException::apiError('Failed to void payment: ' . $e->getMessage());
        }
    }

    private function getPaymentOrFail(string $paymentId): Payment
    {
        $payment = $this->paymentRepository->findByPaymentId($paymentId);
        if (null === $payment) {
            throw PaymentException::paymentNotFound($paymentId);
        }

        return $payment;
    }

    /**
     * @param array<string, mixed> $apiResponse
     */
    private function createRefundFromApiResponse(
        array $apiResponse,
        Payment $payment,
        int $refundAmount,
        ?string $reference,
    ): PaymentRefund {
        $refund = new PaymentRefund();
        $refund->setRefundId(is_string($apiResponse['id'] ?? null) ? $apiResponse['id'] : '');
        $refund->setPayment($payment);
        $refund->setAmount($refundAmount);
        $refund->setCurrency($payment->getCurrency() ?? 'USD');
        $refund->setReference($reference);
        $refund->setStatus(is_string($apiResponse['status'] ?? null) ? $apiResponse['status'] : 'Approved');
        $refund->setResponseSummary(is_string($apiResponse['response_summary'] ?? null) ? $apiResponse['response_summary'] : 'Refunded');
        $refund->setResponseCode(is_string($apiResponse['response_code'] ?? null) ? $apiResponse['response_code'] : null);

        $this->refundRepository->save($refund, true);

        return $refund;
    }

    private function updatePaymentRefundStatus(Payment $payment, int $refundAmount): void
    {
        $totalRefundedAmount = $payment->getTotalRefundedAmount() + $refundAmount;
        $payment->setRefundedAt(new \DateTimeImmutable());
        $payment->setRefundedAmount($totalRefundedAmount);

        if ($totalRefundedAmount >= $payment->getAmount()) {
            $payment->setStatus('Refunded');
            $payment->setResponseSummary('Fully Refunded');
        } else {
            $payment->setStatus('Partially Refunded');
            $payment->setResponseSummary('Partially Refunded');
        }

        $this->paymentRepository->save($payment, true);
    }

    private function logRefundSuccess(string $paymentId, PaymentRefund $refund, int $refundAmount, int $totalRefunded): void
    {
        $this->logger->info('Payment refunded successfully', [
            'payment_id' => $paymentId,
            'refund_id' => $refund->getRefundId(),
            'refund_amount' => $refundAmount,
            'total_refunded' => $totalRefunded,
        ]);
    }
}
