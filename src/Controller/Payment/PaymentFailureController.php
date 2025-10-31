<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\Controller\Payment;

use CheckoutPaymentBundle\Service\PaymentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PaymentFailureController extends AbstractController
{
    public function __construct(
        private readonly PaymentService $paymentService,
    ) {
    }

    #[Route(path: '/checkout-payment/failure', methods: ['GET'])]
    public function __invoke(Request $request): Response
    {
        $sessionId = $request->query->get('sessionId');
        if (!is_string($sessionId)) {
            return $this->render('@CheckoutPayment/payment/error.html.twig', [
                'error' => 'Missing session ID',
            ]);
        }

        $session = $this->paymentService->getPaymentSession($sessionId);
        if (null === $session) {
            return $this->render('@CheckoutPayment/payment/error.html.twig', [
                'error' => 'Session not found',
            ]);
        }

        return $this->render('@CheckoutPayment/payment/failure.html.twig', [
            'session' => $session,
        ]);
    }
}
