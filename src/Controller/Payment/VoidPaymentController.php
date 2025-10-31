<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\Controller\Payment;

use CheckoutPaymentBundle\Service\PaymentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class VoidPaymentController extends AbstractController
{
    public function __construct(
        private readonly PaymentService $paymentService,
    ) {
    }

    #[Route(path: '/payments/{paymentId}/void', methods: ['POST'])]
    public function __invoke(string $paymentId): JsonResponse
    {
        try {
            $payment = $this->paymentService->voidPayment($paymentId);

            return new JsonResponse([
                'payment_id' => $payment->getId(),
                'status' => $payment->getStatus(),
                'voided' => true,
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}
