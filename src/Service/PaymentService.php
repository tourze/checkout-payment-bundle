<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\Service;

use CheckoutPaymentBundle\Entity\Payment;
use CheckoutPaymentBundle\Entity\PaymentRefund;
use CheckoutPaymentBundle\Entity\PaymentSession;
use CheckoutPaymentBundle\Entity\WebhookLog;
use CheckoutPaymentBundle\Exception\PaymentException;
use CheckoutPaymentBundle\Repository\PaymentRefundRepository;
use CheckoutPaymentBundle\Repository\PaymentRepository;
use CheckoutPaymentBundle\Repository\PaymentSessionRepository;
use CheckoutPaymentBundle\Repository\WebhookLogRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(public: true)]
class PaymentService
{
    public function __construct(
        private readonly CheckoutApiClient $apiClient,
        private readonly PaymentSessionRepository $sessionRepository,
        private readonly PaymentRepository $paymentRepository,
        private readonly WebhookLogRepository $webhookLogRepository,
        private readonly LoggerInterface $logger,
        private readonly PaymentSessionFactory $sessionFactory,
        private readonly PaymentFactory $paymentFactory,
        private readonly WebhookProcessor $webhookProcessor,
        private readonly PaymentOperations $paymentOperations,
        private readonly string $webhookSecret,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createPaymentSession(array $data): PaymentSession
    {
        $session = $this->sessionFactory->createFromData($data);

        try {
            $apiResponse = $this->apiClient->createHostedPaymentSession($session);
            $this->updateSessionFromApiResponse($session, $apiResponse);
            $this->sessionRepository->save($session, true);
            $this->logSessionCreated($session);

            return $session;
        } catch (\Exception $e) {
            return $this->handleSessionCreationFailure($session, $data, $e);
        }
    }

    public function getPaymentSession(string $sessionId): ?PaymentSession
    {
        return $this->sessionRepository->findBySessionId($sessionId);
    }

    public function getPaymentSessionByReference(string $reference): ?PaymentSession
    {
        return $this->sessionRepository->findByReference($reference);
    }

    public function processWebhook(string $payload, string $signature): void
    {
        $this->logger->info('Processing webhook', [
            'signature' => substr($signature, 0, 10) . '...',
        ]);

        $decodedData = json_decode($payload, true);
        if (!is_array($decodedData)) {
            throw PaymentException::webhookProcessingFailed('Invalid JSON payload');
        }

        /** @var array<string, mixed> $data */
        $data = $decodedData;

        $webhookLog = $this->createWebhookLog($data, $payload, $signature);

        if ($this->verifyWebhookSignature($payload, $signature)) {
            $this->processValidWebhook($webhookLog, $data);
        } else {
            $this->processInvalidWebhook($webhookLog);
        }

        $this->webhookLogRepository->save($webhookLog, true);
    }

    public function syncPaymentStatus(string $sessionId): void
    {
        $session = $this->getSessionOrFail($sessionId);

        try {
            $sessionData = $this->apiClient->getHostedPaymentSession($sessionId);
            $session->setStatus(is_string($sessionData['status'] ?? null) ? $sessionData['status'] : 'unknown');
            $this->sessionRepository->save($session, true);

            $this->logger->info('Payment session status synced', [
                'session_id' => $sessionId,
                'status' => $session->getStatus(),
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to sync payment session status', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
            ]);

            throw PaymentException::apiError('Failed to sync payment session status: ' . $e->getMessage());
        }
    }

    public function getPayment(string $paymentId): ?Payment
    {
        return $this->paymentRepository->findOneBy(['paymentId' => $paymentId]);
    }

    /**
     * @return array<Payment>
     */
    public function getPaymentHistory(string $reference): array
    {
        $session = $this->sessionRepository->findByReference($reference);
        if (null === $session) {
            return [];
        }

        return $this->paymentRepository->findBySession($session);
    }

    /**
     * @param array<string, mixed> $paymentData
     */
    public function createDirectPayment(array $paymentData): Payment
    {
        $reference = is_string($paymentData['reference'] ?? null) ? $paymentData['reference'] : '';
        $session = $this->findSessionForDirectPayment($reference);

        try {
            $apiResponse = $this->apiClient->createPayment($paymentData);
            $payment = $this->paymentFactory->createFromApiResponse($apiResponse, $session, $reference);
            $this->paymentRepository->save($payment, true);
            $this->logDirectPaymentCreated($payment);

            return $payment;
        } catch (\Exception $e) {
            return $this->handleDirectPaymentCreationFailure($paymentData, $e);
        }
    }

    /**
     * @param array<string, mixed> $captureData
     */
    public function capturePayment(string $paymentId, array $captureData = []): Payment
    {
        return $this->paymentOperations->capturePayment($paymentId, $captureData);
    }

    public function refundPayment(string $paymentId, ?float $amount = null, ?string $reference = null): PaymentRefund
    {
        return $this->paymentOperations->refundPayment($paymentId, $amount, $reference);
    }

    public function voidPayment(string $paymentId): Payment
    {
        return $this->paymentOperations->voidPayment($paymentId);
    }

    /**
     * @return array<string, mixed>
     */
    public function getPaymentActions(string $paymentId): array
    {
        try {
            return $this->apiClient->getPaymentActions($paymentId);
        } catch (\Exception $e) {
            $this->logger->error('Failed to get payment actions', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);

            throw PaymentException::apiError('Failed to get payment actions: ' . $e->getMessage());
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function getPaymentRefunds(string $paymentId): array
    {
        try {
            return $this->apiClient->getPaymentRefunds($paymentId);
        } catch (\Exception $e) {
            $this->logger->error('Failed to get payment refunds', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);

            throw PaymentException::apiError('Failed to get payment refunds: ' . $e->getMessage());
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function getPaymentCaptures(string $paymentId): array
    {
        try {
            return $this->apiClient->getPaymentCaptures($paymentId);
        } catch (\Exception $e) {
            $this->logger->error('Failed to get payment captures', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);

            throw PaymentException::apiError('Failed to get payment captures: ' . $e->getMessage());
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function getPaymentVoids(string $paymentId): array
    {
        try {
            return $this->apiClient->getPaymentVoids($paymentId);
        } catch (\Exception $e) {
            $this->logger->error('Failed to get payment voids', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);

            throw PaymentException::apiError('Failed to get payment voids: ' . $e->getMessage());
        }
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function searchPayments(array $filters = []): array
    {
        try {
            return $this->apiClient->getPayments($filters);
        } catch (\Exception $e) {
            $this->logger->error('Failed to search payments', [
                'filters' => $filters,
                'error' => $e->getMessage(),
            ]);

            throw PaymentException::apiError('Failed to search payments: ' . $e->getMessage());
        }
    }

    public function syncPaymentSession(string $sessionId): PaymentSession
    {
        $session = $this->getSessionOrFail($sessionId);

        try {
            $sessionData = $this->apiClient->getHostedPaymentSession($sessionId);
            $session->setStatus(is_string($sessionData['status'] ?? null) ? $sessionData['status'] : 'unknown');
            $this->sessionRepository->save($session, true);

            $this->logger->info('Payment session synced successfully', [
                'session_id' => $sessionId,
                'status' => $session->getStatus(),
            ]);

            return $session;
        } catch (\Exception $e) {
            $this->logger->error('Failed to sync payment session', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
            ]);

            throw PaymentException::apiError('Failed to sync payment session: ' . $e->getMessage());
        }
    }

    /**
     * @param array<string, mixed> $apiResponse
     */
    private function updateSessionFromApiResponse(PaymentSession $session, array $apiResponse): void
    {
        $session->setSessionId(is_string($apiResponse['id'] ?? null) ? $apiResponse['id'] : '');
        $session->setPaymentUrl($this->extractPaymentUrl($apiResponse));
        $session->setStatus('created');
    }

    /**
     * @param array<string, mixed> $apiResponse
     */
    private function extractPaymentUrl(array $apiResponse): string
    {
        if (!isset($apiResponse['links']) || !is_array($apiResponse['links'])) {
            return '';
        }

        if (!isset($apiResponse['links']['payment']) || !is_string($apiResponse['links']['payment'])) {
            return '';
        }

        return $apiResponse['links']['payment'];
    }

    private function logSessionCreated(PaymentSession $session): void
    {
        $this->logger->info('Payment session created successfully', [
            'session_id' => $session->getSessionId(),
            'reference' => $session->getReference(),
            'amount' => $session->getAmount(),
            'currency' => $session->getCurrency(),
        ]);
    }

    /**
     * @param array<string, mixed> $data
     * @throws PaymentException
     */
    private function handleSessionCreationFailure(PaymentSession $session, array $data, \Exception $e): never
    {
        $session->setStatus('failed');
        $this->sessionRepository->save($session, true);

        $this->logger->error('Failed to create payment session', [
            'reference' => $data['reference'] ?? 'unknown',
            'error' => $e->getMessage(),
        ]);

        throw PaymentException::apiError('Failed to create payment session: ' . $e->getMessage());
    }

    private function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $expectedSignature = hash_hmac('sha256', $payload, $this->webhookSecret);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function createWebhookLog(array $data, string $payload, string $signature): WebhookLog
    {
        $webhookLog = new WebhookLog();
        $webhookLog->setWebhookId(is_string($data['id'] ?? null) ? $data['id'] : 'unknown');
        $webhookLog->setEventType(is_string($data['type'] ?? null) ? $data['type'] : 'unknown');
        $webhookLog->setPayload($payload);
        $webhookLog->setSignature($signature);

        return $webhookLog;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function processValidWebhook(WebhookLog $webhookLog, array $data): void
    {
        $webhookLog->setSignatureValid(true);
        $this->webhookProcessor->processWebhookData($data);
        $webhookLog->setStatus('processed');
        $webhookLog->setProcessedData($data);
    }

    private function processInvalidWebhook(WebhookLog $webhookLog): void
    {
        $webhookLog->setSignatureValid(false);
        $webhookLog->setStatus('failed');
        $webhookLog->setErrorMessage('Invalid signature');
        $this->logger->warning('Invalid webhook signature', [
            'webhook_id' => $webhookLog->getWebhookId(),
            'event_type' => $webhookLog->getEventType(),
        ]);
    }

    private function findSessionForDirectPayment(string $reference): PaymentSession
    {
        $session = $this->sessionRepository->findByReference($reference);
        if (null === $session) {
            throw PaymentException::paymentSessionNotFound('Payment session not found for reference: ' . $reference);
        }

        return $session;
    }

    private function logDirectPaymentCreated(Payment $payment): void
    {
        $this->logger->info('Direct payment created successfully', [
            'payment_id' => $payment->getPaymentId(),
            'reference' => $payment->getReference(),
            'amount' => $payment->getAmount(),
            'currency' => $payment->getCurrency(),
            'status' => $payment->getStatus(),
        ]);
    }

    /**
     * @param array<string, mixed> $paymentData
     * @throws PaymentException
     */
    private function handleDirectPaymentCreationFailure(array $paymentData, \Exception $e): never
    {
        $this->logger->error('Failed to create direct payment', [
            'reference' => $paymentData['reference'] ?? 'unknown',
            'error' => $e->getMessage(),
        ]);

        throw PaymentException::apiError('Failed to create direct payment: ' . $e->getMessage());
    }

    private function getSessionOrFail(string $sessionId): PaymentSession
    {
        $session = $this->sessionRepository->findBySessionId($sessionId);
        if (null === $session) {
            throw PaymentException::paymentSessionNotFound($sessionId);
        }

        return $session;
    }
}
