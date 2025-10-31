<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\Controller\Admin;

use CheckoutPaymentBundle\Entity\Payment;
use CheckoutPaymentBundle\Entity\PaymentRefund;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;

/**
 * @extends AbstractCrudController<PaymentRefund>
 */
#[AdminCrud(routePath: '/checkout/payment-refund', routeName: 'checkout_payment_refund')]
final class PaymentRefundCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PaymentRefund::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('退款记录')
            ->setEntityLabelInPlural('退款记录')
            ->setPageTitle('index', '退款记录列表')
            ->setPageTitle('detail', '退款记录详情')
            ->setPageTitle('edit', '编辑退款记录')
            ->setHelp('index', '管理 Checkout.com 支付退款记录，跟踪退款状态和处理结果')
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['refundId', 'reference', 'status', 'reason'])
            ->showEntityActionsInlined()
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IntegerField::new('id', 'ID')
            ->hideOnForm()
        ;

        yield TextField::new('refundId', '退款ID')
            ->setColumns(6)
            ->hideOnForm()
        ;

        yield AssociationField::new('payment', '关联支付')
            ->setColumns(6)
            ->formatValue(function (mixed $value): string {
                if (!$value instanceof Payment) {
                    return '';
                }

                return sprintf('%s (%s)', $value->getPaymentId(), $this->formatCurrency($value->getAmount() ?? 0, $value->getCurrency() ?? ''));
            })
        ;

        yield IntegerField::new('amount', '退款金额')
            ->setColumns(6)
            ->formatValue(function (mixed $value, PaymentRefund $entity): string {
                $amount = is_numeric($value) ? (int) $value : 0;
                $currency = $entity->getCurrency() ?? '';

                return $this->formatCurrency($amount, $currency);
            })
        ;

        yield TextField::new('currency', '货币')
            ->setColumns(6)
            ->hideOnIndex()
        ;

        yield TextField::new('reference', '退款参考号')
            ->setColumns(12)
            ->hideOnIndex()
        ;

        yield TextField::new('status', '退款状态')
            ->setColumns(6)
            ->formatValue(function (mixed $value): string {
                return $this->formatRefundStatus(is_string($value) ? $value : '');
            })
        ;

        yield TextField::new('paymentId', '原支付ID')
            ->setColumns(6)
            ->hideOnIndex()
            ->hideOnForm()
        ;

        yield BooleanField::new('approved', '已批准')
            ->hideOnForm()
        ;

        yield TextField::new('responseSummary', '响应摘要')
            ->setColumns(6)
            ->hideOnIndex()
        ;

        yield TextField::new('responseCode', '响应代码')
            ->setColumns(6)
            ->hideOnIndex()
        ;

        yield TextareaField::new('reason', '退款原因')
            ->setColumns(12)
            ->hideOnIndex()
        ;

        yield CodeEditorField::new('metadata', '元数据')
            ->setLanguage('javascript')
            ->hideOnIndex()
            ->formatValue(function (mixed $value): string {
                return $this->formatJsonValue($value);
            })
        ;

        yield DateTimeField::new('processedTime', '处理时间')
            ->setColumns(6)
            ->hideOnForm()
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
            ->add(TextFilter::new('refundId', '退款ID'))
            ->add(TextFilter::new('status', '退款状态'))
            ->add(EntityFilter::new('payment', '关联支付'))
            ->add(BooleanFilter::new('approved', '已批准'))
            ->add(TextFilter::new('currency', '货币'))
            ->add(TextFilter::new('reference', '退款参考号'))
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $detailAction = Action::new(Action::DETAIL, '查看详情', 'fas fa-eye')
            ->linkToCrudAction(Action::DETAIL)
        ;

        return $actions
            ->add(Crud::PAGE_INDEX, $detailAction)
            ->disable(Action::NEW)
            ->disable(Action::EDIT)
            ->disable(Action::DELETE)
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

    private function formatRefundStatus(string $status): string
    {
        $statusMap = [
            'pending' => '<span class="badge bg-warning text-dark">待处理</span>',
            'Approved' => '<span class="badge bg-success">已批准</span>',
            'Declined' => '<span class="badge bg-danger">已拒绝</span>',
            'Failed' => '<span class="badge bg-danger">失败</span>',
            'Processing' => '<span class="badge bg-primary">处理中</span>',
        ];

        return $statusMap[$status] ?? sprintf('<span class="badge bg-light text-dark">%s</span>', $status);
    }
}
