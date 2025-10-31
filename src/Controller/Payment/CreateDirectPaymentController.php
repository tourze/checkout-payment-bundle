<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\Controller\Payment;

use CheckoutPaymentBundle\Service\PaymentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class CreateDirectPaymentController extends AbstractController
{
    public function __construct(
        private readonly PaymentService $paymentService,
    ) {
    }

    #[Route(path: '/payments', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!is_array($data)) {
                return new JsonResponse(['error' => 'Invalid JSON'], 400);
            }

            /** @var array<string, mixed> $validatedData */
            $validatedData = $data;

            $requiredFields = ['reference', 'amount', 'currency', 'source'];
            foreach ($requiredFields as $field) {
                if (!isset($validatedData[$field]) || '' === $validatedData[$field]) {
                    return new JsonResponse(['error' => "Missing required field: {$field}"], 400);
                }
            }

            $payment = $this->paymentService->createDirectPayment($validatedData);

            return new JsonResponse([
                'payment_id' => $payment->getId(),
                'reference' => $payment->getReference(),
                'amount' => $payment->getAmount(),
                'currency' => $payment->getCurrency(),
                'status' => $payment->getStatus(),
                'approved' => $payment->isApproved(),
            ], 201);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}
