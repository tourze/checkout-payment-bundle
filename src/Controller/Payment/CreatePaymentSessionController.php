<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\Controller\Payment;

use CheckoutPaymentBundle\Service\PaymentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class CreatePaymentSessionController extends AbstractController
{
    public function __construct(
        private readonly PaymentService $paymentService,
    ) {
    }

    #[Route(path: '/sessions', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!is_array($data)) {
                return new JsonResponse(['error' => 'Invalid JSON'], 400);
            }

            /** @var array<string, mixed> $validatedData */
            $validatedData = $data;

            $requiredFields = ['reference', 'amount', 'currency', 'customer_email', 'success_url', 'cancel_url'];
            foreach ($requiredFields as $field) {
                if (!isset($validatedData[$field]) || '' === $validatedData[$field]) {
                    return new JsonResponse(['error' => "Missing required field: {$field}"], 400);
                }
            }

            $session = $this->paymentService->createPaymentSession($validatedData);

            return new JsonResponse([
                'session_id' => $session->getSessionId(),
                'payment_url' => $session->getPaymentUrl(),
                'reference' => $session->getReference(),
                'amount' => $session->getAmount(),
                'currency' => $session->getCurrency(),
                'status' => $session->getStatus(),
            ], 201);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}
