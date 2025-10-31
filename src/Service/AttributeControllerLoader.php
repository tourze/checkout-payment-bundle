<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\Service;

use CheckoutPaymentBundle\Controller\Payment\CapturePaymentController;
use CheckoutPaymentBundle\Controller\Payment\CreateDirectPaymentController;
use CheckoutPaymentBundle\Controller\Payment\CreatePaymentSessionController;
use CheckoutPaymentBundle\Controller\Payment\GetPaymentActionsController;
use CheckoutPaymentBundle\Controller\Payment\GetPaymentRefundsController;
use CheckoutPaymentBundle\Controller\Payment\GetPaymentSessionController;
use CheckoutPaymentBundle\Controller\Payment\PaymentCancelController;
use CheckoutPaymentBundle\Controller\Payment\PaymentFailureController;
use CheckoutPaymentBundle\Controller\Payment\PaymentSuccessController;
use CheckoutPaymentBundle\Controller\Payment\RefundPaymentController;
use CheckoutPaymentBundle\Controller\Payment\SearchPaymentsController;
use CheckoutPaymentBundle\Controller\Payment\SyncPaymentSessionController;
use CheckoutPaymentBundle\Controller\Payment\VoidPaymentController;
use CheckoutPaymentBundle\Controller\Webhook\HandleWebhookController;
use CheckoutPaymentBundle\Controller\Webhook\ValidateWebhookController;
use Symfony\Bundle\FrameworkBundle\Routing\AttributeRouteControllerLoader;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Routing\RouteCollection;
use Tourze\RoutingAutoLoaderBundle\Service\RoutingAutoLoaderInterface;

#[AutoconfigureTag(name: 'routing.auto.loader')]
class AttributeControllerLoader extends Loader implements RoutingAutoLoaderInterface
{
    private AttributeRouteControllerLoader $controllerLoader;

    public function __construct()
    {
        parent::__construct();
        $this->controllerLoader = new AttributeRouteControllerLoader();
    }

    public function load(mixed $resource, ?string $type = null): RouteCollection
    {
        return $this->autoload();
    }

    public function supports(mixed $resource, ?string $type = null): bool
    {
        return 'attribute' === $type;
    }

    public function autoload(): RouteCollection
    {
        $collection = new RouteCollection();
        $collection->addCollection($this->controllerLoader->load(CreatePaymentSessionController::class));
        $collection->addCollection($this->controllerLoader->load(HandleWebhookController::class));
        $collection->addCollection($this->controllerLoader->load(CapturePaymentController::class));
        $collection->addCollection($this->controllerLoader->load(CreateDirectPaymentController::class));
        $collection->addCollection($this->controllerLoader->load(GetPaymentActionsController::class));
        $collection->addCollection($this->controllerLoader->load(RefundPaymentController::class));
        $collection->addCollection($this->controllerLoader->load(VoidPaymentController::class));
        $collection->addCollection($this->controllerLoader->load(GetPaymentRefundsController::class));
        $collection->addCollection($this->controllerLoader->load(GetPaymentSessionController::class));
        $collection->addCollection($this->controllerLoader->load(PaymentCancelController::class));
        $collection->addCollection($this->controllerLoader->load(PaymentFailureController::class));
        $collection->addCollection($this->controllerLoader->load(PaymentSuccessController::class));
        $collection->addCollection($this->controllerLoader->load(SearchPaymentsController::class));
        $collection->addCollection($this->controllerLoader->load(SyncPaymentSessionController::class));
        $collection->addCollection($this->controllerLoader->load(ValidateWebhookController::class));

        return $collection;
    }
}
