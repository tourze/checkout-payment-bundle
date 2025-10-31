<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\Controller\Payment;

use CheckoutPaymentBundle\Service\PaymentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class RefundPaymentController extends AbstractController
{
    public function __construct(
        private readonly PaymentService $paymentService,
    ) {
    }

    #[Route(path: '/payments/{paymentId}/refund', methods: ['POST'])]
    public function __invoke(string $paymentId, Request $request): JsonResponse
    {
        try {
            /** @var array<string, mixed>|false|null */
            $data = json_decode($request->getContent(), true);

            if (!is_array($data)) {
                $data = [];
            }

            $amount = isset($data['amount']) && is_numeric($data['amount']) ? (float) $data['amount'] : null;
            $reference = isset($data['reference']) && is_string($data['reference']) ? $data['reference'] : null;

            $refund = $this->paymentService->refundPayment($paymentId, $amount, $reference);

            return new JsonResponse([
                'refund_id' => $refund->getId(),
                'payment_id' => $refund->getPaymentId(),
                'amount' => $refund->getAmount(),
                'status' => $refund->getStatus(),
            ], 201);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}
