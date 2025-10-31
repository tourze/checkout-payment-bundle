<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\Controller\Payment;

use CheckoutPaymentBundle\Service\PaymentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class CapturePaymentController extends AbstractController
{
    public function __construct(
        private readonly PaymentService $paymentService,
    ) {
    }

    #[Route(path: '/payments/{paymentId}/capture', methods: ['POST'])]
    public function __invoke(string $paymentId, Request $request): JsonResponse
    {
        try {
            /** @var array<string, mixed>|null */
            $data = json_decode($request->getContent(), true);

            if (!is_array($data)) {
                $data = [];
            }

            $amount = $data['amount'] ?? null;
            $captureData = null !== $amount ? ['amount' => $amount] : [];

            $payment = $this->paymentService->capturePayment($paymentId, $captureData);

            return new JsonResponse([
                'payment_id' => $payment->getId(),
                'status' => $payment->getStatus(),
                'captured' => true,
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}
