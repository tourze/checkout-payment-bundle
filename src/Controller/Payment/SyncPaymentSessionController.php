<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\Controller\Payment;

use CheckoutPaymentBundle\Service\PaymentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class SyncPaymentSessionController extends AbstractController
{
    public function __construct(
        private readonly PaymentService $paymentService,
    ) {
    }

    #[Route(path: '/sessions/{sessionId}/sync', methods: ['POST'])]
    public function __invoke(string $sessionId): JsonResponse
    {
        try {
            $session = $this->paymentService->syncPaymentSession($sessionId);

            return new JsonResponse(['status' => 'synced', 'session' => $session]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}
