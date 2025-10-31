<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\Controller\Webhook;

use CheckoutPaymentBundle\Service\WebhookHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class HandleWebhookController extends AbstractController
{
    public function __construct(
        private readonly WebhookHandler $webhookHandler,
    ) {
    }

    #[Route(path: '/webhooks', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        return $this->webhookHandler->handleWebhook($request);
    }
}
