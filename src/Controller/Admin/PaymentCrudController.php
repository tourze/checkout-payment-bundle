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
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;

/**
 * @extends AbstractCrudController<Payment>
 */
#[AdminCrud(routePath: '/checkout/payment', routeName: 'checkout_payment')]
final class PaymentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Payment::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('支付记录')
            ->setEntityLabelInPlural('支付记录')
            ->setPageTitle('index', '支付记录列表')
            ->setPageTitle('detail', '支付记录详情')
            ->setPageTitle('edit', '编辑支付记录')
            ->setHelp('index', '管理 Checkout.com 支付记录，包括支付状态、金额、退款等信息')
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['paymentId', 'reference', 'status', 'responseSummary'])
            ->showEntityActionsInlined()
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IntegerField::new('id', 'ID')
            ->hideOnForm()
        ;

        yield TextField::new('paymentId', '支付ID')
            ->setColumns(6)
            ->hideOnForm()
        ;

        yield AssociationField::new('session', '支付会话')
            ->setColumns(6)
            ->formatValue(function (mixed $value): string {
                if (!$value instanceof PaymentSession) {
                    return '';
                }

                return sprintf('%s (%s)', $value->getSessionId(), $this->formatCurrency($value->getAmount() ?? 0, $value->getCurrency() ?? ''));
            })
        ;

        yield IntegerField::new('amount', '支付金额')
            ->setColumns(6)
            ->formatValue(function (mixed $value, Payment $entity): string {
                $amount = is_numeric($value) ? (int) $value : 0;
                $currency = $entity->getCurrency() ?? '';

                return $this->formatCurrency($amount, $currency);
            })
        ;

        yield IntegerField::new('refundedAmount', '已退款金额')
            ->setColumns(6)
            ->formatValue(function (mixed $value, Payment $entity): string {
                if (!is_numeric($value) || 0 === (int) $value) {
                    return '0';
                }
                $currency = $entity->getCurrency() ?? '';

                return $this->formatCurrency((int) $value, $currency);
            })
            ->hideOnForm()
        ;

        yield TextField::new('currency', '货币')
            ->setColumns(6)
            ->hideOnIndex()
        ;

        yield TextField::new('reference', '订单参考号')
            ->setColumns(12)
        ;

        yield TextField::new('status', '支付状态')
            ->setColumns(6)
            ->formatValue(function (mixed $value): string {
                return $this->formatPaymentStatus(is_string($value) ? $value : '');
            })
        ;

        yield TextField::new('paymentType', '支付类型')
            ->setColumns(6)
            ->hideOnIndex()
        ;

        yield TextField::new('responseSummary', '响应摘要')
            ->setColumns(6)
            ->hideOnIndex()
        ;

        yield TextField::new('responseCode', '响应代码')
            ->setColumns(6)
            ->hideOnIndex()
        ;

        yield BooleanField::new('is3ds', '3DS验证')
            ->hideOnForm()
        ;

        yield BooleanField::new('approved', '已批准')
            ->hideOnForm()
        ;

        yield TextField::new('authCode', '授权码')
            ->setColumns(6)
            ->hideOnIndex()
            ->hideOnForm()
        ;

        yield CodeEditorField::new('source', '支付来源')
            ->setLanguage('javascript')
            ->hideOnIndex()
            ->formatValue(function (mixed $value): string {
                return $this->formatJsonValue($value);
            })
        ;

        yield CodeEditorField::new('customer', '客户信息')
            ->setLanguage('javascript')
            ->hideOnIndex()
            ->formatValue(function (mixed $value): string {
                return $this->formatJsonValue($value);
            })
        ;

        yield CodeEditorField::new('billingAddress', '账单地址')
            ->setLanguage('javascript')
            ->hideOnIndex()
            ->formatValue(function (mixed $value): string {
                return $this->formatJsonValue($value);
            })
        ;

        yield CodeEditorField::new('metadata', '元数据')
            ->setLanguage('javascript')
            ->hideOnIndex()
            ->formatValue(function (mixed $value): string {
                return $this->formatJsonValue($value);
            })
        ;

        yield AssociationField::new('refunds', '退款记录')
            ->setColumns(12)
            ->formatValue(function (mixed $value): string {
                if (!$value instanceof Collection) {
                    return '无退款';
                }
                $count = $value->count();

                return $count > 0 ? sprintf('%d 笔退款', $count) : '无退款';
            })
            ->hideOnForm()
        ;

        yield DateTimeField::new('processedTime', '处理时间')
            ->setColumns(6)
            ->hideOnForm()
        ;

        yield DateTimeField::new('approvedTime', '批准时间')
            ->setColumns(6)
            ->hideOnIndex()
            ->hideOnForm()
        ;

        yield DateTimeField::new('capturedTime', '捕获时间')
            ->setColumns(6)
            ->hideOnIndex()
            ->hideOnForm()
        ;

        yield DateTimeField::new('refundedTime', '退款时间')
            ->setColumns(6)
            ->hideOnIndex()
            ->hideOnForm()
        ;

        yield DateTimeField::new('voidedTime', '作废时间')
            ->setColumns(6)
            ->hideOnIndex()
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
            ->add(TextFilter::new('paymentId', '支付ID'))
            ->add(TextFilter::new('reference', '订单参考号'))
            ->add(TextFilter::new('status', '支付状态'))
            ->add(EntityFilter::new('session', '支付会话'))
            ->add(BooleanFilter::new('approved', '已批准'))
            ->add(BooleanFilter::new('is3ds', '3DS验证'))
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

    private function formatPaymentStatus(string $status): string
    {
        $statusMap = [
            'Pending' => '<span class="badge bg-warning text-dark">待处理</span>',
            'Authorized' => '<span class="badge bg-primary">已授权</span>',
            'Captured' => '<span class="badge bg-success">已捕获</span>',
            'Voided' => '<span class="badge bg-secondary">已作废</span>',
            'Partially Refunded' => '<span class="badge bg-info">部分退款</span>',
            'Refunded' => '<span class="badge bg-dark">已退款</span>',
            'Declined' => '<span class="badge bg-danger">已拒绝</span>',
            'Expired' => '<span class="badge bg-warning text-dark">已过期</span>',
            'Card Verified' => '<span class="badge bg-success">卡验证成功</span>',
            'Card Verification Declined' => '<span class="badge bg-danger">卡验证失败</span>',
        ];

        return $statusMap[$status] ?? sprintf('<span class="badge bg-light text-dark">%s</span>', $status);
    }
}
