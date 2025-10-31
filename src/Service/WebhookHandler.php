<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

#[Autoconfigure(public: true)]
class WebhookHandler
{
    private PaymentService $paymentService;

    private LoggerInterface $logger;

    public function __construct(
        PaymentService $paymentService,
        LoggerInterface $logger,
    ) {
        $this->paymentService = $paymentService;
        $this->logger = $logger;
    }

    public function handleWebhook(Request $request): JsonResponse
    {
        try {
            $signature = $request->headers->get('Cko-Signature');
            $payload = $request->getContent();

            if (null === $signature || '' === $signature) {
                $this->logger->warning('Webhook received without signature');

                return new JsonResponse(['error' => 'Missing signature'], 400);
            }

            if ('' === $payload) {
                $this->logger->warning('Webhook received without payload');

                return new JsonResponse(['error' => 'Missing payload'], 400);
            }

            $this->paymentService->processWebhook($payload, $signature);

            return new JsonResponse(['status' => 'success'], 200);
        } catch (\Exception $e) {
            $this->logger->error('Error processing webhook', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return new JsonResponse(['error' => 'Internal server error'], 500);
        }
    }

    public function validateWebhookRequest(Request $request): bool
    {
        return $request->headers->has('Cko-Signature') && '' !== $request->getContent();
    }

    public function getWebhookEventType(Request $request): ?string
    {
        $payload = $request->getContent();
        if ('' === $payload) {
            return null;
        }

        /** @var array<string, mixed>|null */
        $data = json_decode($payload, true);
        if (!is_array($data) || !isset($data['type'])) {
            return null;
        }

        return is_string($data['type']) ? $data['type'] : null;
    }

    public function getWebhookPaymentId(Request $request): ?string
    {
        $payload = $request->getContent();
        if ('' === $payload) {
            return null;
        }

        /** @var array<string, mixed>|null */
        $data = json_decode($payload, true);
        if (!is_array($data) || !isset($data['data']) || !is_array($data['data']) || !isset($data['data']['id'])) {
            return null;
        }

        return is_string($data['data']['id']) ? $data['data']['id'] : null;
    }
}
