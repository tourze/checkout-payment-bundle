<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\Service;

use CheckoutPaymentBundle\Entity\CheckoutConfig;
use CheckoutPaymentBundle\Entity\Payment;
use CheckoutPaymentBundle\Entity\PaymentRefund;
use CheckoutPaymentBundle\Entity\PaymentSession;
use CheckoutPaymentBundle\Entity\WebhookLog;
use Knp\Menu\ItemInterface;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;

/**
 * Checkout支付管理菜单服务
 */
readonly class AdminMenu implements MenuProviderInterface
{
    public function __construct(
        private LinkGeneratorInterface $linkGenerator,
    ) {
    }

    public function __invoke(ItemInterface $item): void
    {
        if (null === $item->getChild('支付管理')) {
            $item->addChild('支付管理');
        }

        $paymentMenu = $item->getChild('支付管理');
        if (null === $paymentMenu) {
            return;
        }

        // 配置管理菜单
        $paymentMenu->addChild('支付配置')
            ->setUri($this->linkGenerator->getCurdListPage(CheckoutConfig::class))
            ->setAttribute('icon', 'fas fa-cog')
        ;

        // 分隔符 - 支付数据
        $paymentMenu->addChild('---支付数据---')->setAttribute('divider', true);

        // 支付会话管理菜单
        $paymentMenu->addChild('支付会话')
            ->setUri($this->linkGenerator->getCurdListPage(PaymentSession::class))
            ->setAttribute('icon', 'fas fa-clock')
        ;

        // 支付记录菜单
        $paymentMenu->addChild('支付记录')
            ->setUri($this->linkGenerator->getCurdListPage(Payment::class))
            ->setAttribute('icon', 'fas fa-credit-card')
        ;

        // 退款记录菜单
        $paymentMenu->addChild('退款记录')
            ->setUri($this->linkGenerator->getCurdListPage(PaymentRefund::class))
            ->setAttribute('icon', 'fas fa-undo')
        ;

        // 分隔符 - 系统监控
        $paymentMenu->addChild('---系统监控---')->setAttribute('divider', true);

        // Webhook日志菜单
        $paymentMenu->addChild('Webhook日志')
            ->setUri($this->linkGenerator->getCurdListPage(WebhookLog::class))
            ->setAttribute('icon', 'fas fa-history')
        ;
    }
}
