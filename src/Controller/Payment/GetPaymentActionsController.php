<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\Controller\Payment;

use CheckoutPaymentBundle\Service\PaymentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class GetPaymentActionsController extends AbstractController
{
    public function __construct(
        private readonly PaymentService $paymentService,
    ) {
    }

    #[Route(path: '/payments/{paymentId}/actions', methods: ['GET'])]
    public function __invoke(string $paymentId): JsonResponse
    {
        try {
            $actions = $this->paymentService->getPaymentActions($paymentId);

            return new JsonResponse(['actions' => $actions]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}
