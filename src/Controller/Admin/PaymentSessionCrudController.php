<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\Controller\Admin;

use CheckoutPaymentBundle\Entity\Payment;
use CheckoutPaymentBundle\Entity\PaymentSession;
use Doctrine\Common\Collections\Collection;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;

/**
 * @extends AbstractCrudController<PaymentSession>
 */
#[AdminCrud(routePath: '/checkout/payment-session', routeName: 'checkout_payment_session')]
final class PaymentSessionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PaymentSession::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('支付会话')
            ->setEntityLabelInPlural('支付会话')
            ->setPageTitle('index', '支付会话列表')
            ->setPageTitle('detail', '支付会话详情')
            ->setPageTitle('new', '创建支付会话')
            ->setPageTitle('edit', '编辑支付会话')
            ->setHelp('index', '管理 Checkout.com 支付会话，包括会话状态、支付信息和关联的支付记录')
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['sessionId', 'reference', 'customerEmail', 'customerName', 'status'])
            ->showEntityActionsInlined()
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IntegerField::new('id', 'ID')
            ->hideOnForm()
        ;

        yield TextField::new('sessionId', '会话ID')
            ->setColumns(6)
        ;

        yield TextField::new('reference', '订单参考号')
            ->setColumns(6)
        ;

        yield IntegerField::new('amount', '支付金额')
            ->setColumns(6)
            ->formatValue(function (mixed $value, PaymentSession $entity): string {
                $amount = is_numeric($value) ? (int) $value : 0;
                $currency = $entity->getCurrency() ?? '';

                return $this->formatCurrency($amount, $currency);
            })
        ;

        yield TextField::new('currency', '货币')
            ->setColumns(6)
        ;

        yield TextareaField::new('description', '描述')
            ->setColumns(12)
            ->hideOnIndex()
        ;

        yield EmailField::new('customerEmail', '客户邮箱')
            ->setColumns(6)
        ;

        yield TextField::new('customerName', '客户姓名')
            ->setColumns(6)
        ;

        yield CodeEditorField::new('billingAddress', '账单地址')
            ->setLanguage('javascript')
            ->hideOnIndex()
            ->formatValue(function (mixed $value): string {
                return $this->formatJsonValue($value);
            })
        ;

        yield UrlField::new('successUrl', '成功回调URL')
            ->setColumns(6)
            ->hideOnIndex()
        ;

        yield UrlField::new('cancelUrl', '取消回调URL')
            ->setColumns(6)
            ->hideOnIndex()
        ;

        yield UrlField::new('failureUrl', '失败回调URL')
            ->setColumns(6)
            ->hideOnIndex()
        ;

        yield UrlField::new('paymentUrl', '支付页面URL')
            ->setColumns(12)
            ->hideOnIndex()
        ;

        yield TextField::new('status', '会话状态')
            ->setColumns(6)
            ->formatValue(function (mixed $value): string {
                return $this->formatSessionStatus(is_string($value) ? $value : '');
            })
        ;

        yield CodeEditorField::new('metadata', '元数据')
            ->setLanguage('javascript')
            ->hideOnIndex()
            ->formatValue(function (mixed $value): string {
                return $this->formatJsonValue($value);
            })
        ;

        yield AssociationField::new('payments', '关联支付')
            ->setColumns(12)
            ->formatValue(function (mixed $value): string {
                if (!$value instanceof Collection) {
                    return '无支付';
                }

                return $this->formatPaymentsInfo($value);
            })
            ->hideOnForm()
        ;

        yield DateTimeField::new('expiresAt', '过期时间')
            ->setColumns(6)
        ;

        yield DateTimeField::new('createTime', '创建时间')
            ->setColumns(6)
            ->hideOnForm()
        ;

        yield DateTimeField::new('updateTime', '更新时间')
            ->setColumns(6)
            ->hideOnForm()
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('sessionId', '会话ID'))
            ->add(TextFilter::new('reference', '订单参考号'))
            ->add(TextFilter::new('status', '会话状态'))
            ->add(TextFilter::new('customerEmail', '客户邮箱'))
            ->add(TextFilter::new('customerName', '客户姓名'))
            ->add(TextFilter::new('currency', '货币'))
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $detailAction = Action::new(Action::DETAIL, '查看详情', 'fas fa-eye')
            ->linkToCrudAction(Action::DETAIL)
        ;

        return $actions
            ->add(Crud::PAGE_INDEX, $detailAction)
        ;
    }

    private function formatCurrency(int $amount, string $currency): string
    {
        return sprintf('%s %s', number_format($amount / 100, 2), strtoupper($currency));
    }

    private function formatJsonValue(mixed $value): string
    {
        if (null === $value || '' === $value || [] === $value) {
            return '';
        }

        $encoded = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return false !== $encoded ? $encoded : '';
    }

    /**
     * @param Collection<int, Payment> $payments
     */
    private function formatPaymentsInfo(Collection $payments): string
    {
        $count = $payments->count();
        if (0 === $count) {
            return '无支付';
        }

        $successful = $this->countSuccessfulPayments($payments);

        return sprintf('%d 笔支付 (成功: %d)', $count, $successful);
    }

    /**
     * @param Collection<int, Payment> $payments
     */
    private function countSuccessfulPayments(Collection $payments): int
    {
        $successful = 0;
        foreach ($payments as $payment) {
            if ($payment->isApproved() || $payment->isCaptured()) {
                ++$successful;
            }
        }

        return $successful;
    }

    private function formatSessionStatus(string $status): string
    {
        $statusMap = [
            'pending' => '<span class="badge bg-warning text-dark">待处理</span>',
            'active' => '<span class="badge bg-primary">活跃</span>',
            'paid' => '<span class="badge bg-success">已支付</span>',
            'cancelled' => '<span class="badge bg-secondary">已取消</span>',
            'expired' => '<span class="badge bg-danger">已过期</span>',
        ];

        return $statusMap[$status] ?? sprintf('<span class="badge bg-light text-dark">%s</span>', $status);
    }
}
