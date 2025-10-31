<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\Controller\Payment;

use CheckoutPaymentBundle\Service\PaymentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class SearchPaymentsController extends AbstractController
{
    public function __construct(
        private readonly PaymentService $paymentService,
    ) {
    }

    #[Route(path: '/payments/search', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $filters = [
                'reference' => $request->query->get('reference'),
                'from' => $request->query->get('from'),
                'to' => $request->query->get('to'),
                'status' => $request->query->get('status'),
            ];

            $payments = $this->paymentService->searchPayments($filters);

            return new JsonResponse(['payments' => $payments]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}
