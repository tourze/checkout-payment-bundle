<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\Service;

use CheckoutPaymentBundle\Entity\Payment;
use CheckoutPaymentBundle\Entity\PaymentSession;
use CheckoutPaymentBundle\Exception\PaymentException;
use CheckoutPaymentBundle\Repository\PaymentRepository;
use CheckoutPaymentBundle\Repository\PaymentSessionRepository;
use Psr\Log\LoggerInterface;

readonly class WebhookProcessor
{
    public function __construct(
        private PaymentSessionRepository $sessionRepository,
        private PaymentRepository $paymentRepository,
        private PaymentFactory $paymentFactory,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function processWebhookData(array $data): void
    {
        $eventType = is_string($data['type'] ?? null) ? $data['type'] : '';
        $paymentData = is_array($data['data'] ?? null) ? $data['data'] : [];

        /** @var array<string, mixed> $validatedPaymentData */
        $validatedPaymentData = $this->ensureStringKeyArray($paymentData);

        if (!$this->isValidWebhookData($validatedPaymentData, $eventType)) {
            return;
        }

        $paymentId = is_string($validatedPaymentData['id']) ? $validatedPaymentData['id'] : '';
        $reference = is_string($validatedPaymentData['reference']) ? $validatedPaymentData['reference'] : '';

        $session = $this->findSessionForWebhook($reference, $paymentId);
        if (null === $session) {
            return;
        }

        $payment = $this->findOrCreatePayment($paymentId, $session, $reference);
        $this->paymentFactory->updateFromWebhookData($payment, $validatedPaymentData);
        $this->paymentRepository->save($payment, true);
        $this->updateSessionStatus($session, $payment, $eventType);
        $this->logWebhookProcessed($eventType, $paymentId, $reference, $payment->getStatus());
    }

    /**
     * @param array<string, mixed> $paymentData
     */
    private function isValidWebhookData(array $paymentData, string $eventType): bool
    {
        if (!isset($paymentData['id']) || !isset($paymentData['reference'])) {
            $this->logger->warning('Invalid webhook data structure', [
                'event_type' => $eventType,
                'payment_data' => $paymentData,
            ]);

            return false;
        }

        return true;
    }

    private function findSessionForWebhook(string $reference, string $paymentId): ?PaymentSession
    {
        $session = $this->sessionRepository->findByReference($reference);
        if (null === $session) {
            $this->logger->warning('Payment session not found for webhook', [
                'reference' => $reference,
                'payment_id' => $paymentId,
            ]);
        }

        return $session;
    }

    private function findOrCreatePayment(string $paymentId, PaymentSession $session, string $reference): Payment
    {
        $payment = $this->paymentRepository->findByPaymentId($paymentId);
        if (null === $payment) {
            $payment = new Payment();
            $payment->setPaymentId($paymentId);
            $payment->setSession($session);
            $payment->setReference($reference);
        }

        return $payment;
    }

    private function updateSessionStatus(PaymentSession $session, Payment $payment, string $eventType): void
    {
        $statusMap = [
            'payment_approved' => 'paid',
            'payment_declined' => 'failed',
            'payment_captured' => 'captured',
            'payment_refunded' => 'refunded',
            'payment_voided' => 'cancelled',
            'payment_expired' => 'expired',
        ];

        if (isset($statusMap[$eventType])) {
            $session->setStatus($statusMap[$eventType]);
            $this->sessionRepository->save($session, true);
        }
    }

    private function logWebhookProcessed(string $eventType, string $paymentId, string $reference, ?string $status): void
    {
        $this->logger->info('Webhook processed successfully', [
            'event_type' => $eventType,
            'payment_id' => $paymentId,
            'reference' => $reference,
            'status' => $status,
        ]);
    }

    /**
     * @param array<mixed> $array
     * @return array<string, mixed>
     */
    private function ensureStringKeyArray(array $array): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            if (is_string($key)) {
                $result[$key] = $value;
            } elseif (is_int($key)) {
                $result[(string) $key] = $value;
            }
        }

        /** @var array<string, mixed> */
        return $result;
    }
}
