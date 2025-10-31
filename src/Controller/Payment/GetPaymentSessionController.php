<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\Controller\Payment;

use CheckoutPaymentBundle\Service\PaymentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class GetPaymentSessionController extends AbstractController
{
    public function __construct(
        private readonly PaymentService $paymentService,
    ) {
    }

    #[Route(path: '/sessions/{sessionId}', methods: ['GET'])]
    public function __invoke(string $sessionId): JsonResponse
    {
        try {
            $session = $this->paymentService->getPaymentSession($sessionId);

            if (null === $session) {
                return new JsonResponse(['error' => 'Session not found'], 404);
            }

            return new JsonResponse([
                'session_id' => $session->getSessionId(),
                'payment_url' => $session->getPaymentUrl(),
                'reference' => $session->getReference(),
                'amount' => $session->getAmount(),
                'currency' => $session->getCurrency(),
                'status' => $session->getStatus(),
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}
