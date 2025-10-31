<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\Controller\Admin;

use CheckoutPaymentBundle\Entity\WebhookLog;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;

/**
 * @extends AbstractCrudController<WebhookLog>
 */
#[AdminCrud(routePath: '/checkout/webhook-log', routeName: 'checkout_webhook_log')]
final class WebhookLogCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return WebhookLog::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Webhook日志')
            ->setEntityLabelInPlural('Webhook日志')
            ->setPageTitle('index', 'Webhook日志列表')
            ->setPageTitle('detail', 'Webhook日志详情')
            ->setPageTitle('edit', '编辑Webhook日志')
            ->setHelp('index', '管理 Checkout.com Webhook 日志记录，跟踪事件处理和签名验证状态')
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['webhookId', 'eventId', 'paymentId', 'eventType', 'status'])
            ->showEntityActionsInlined()
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IntegerField::new('id', 'ID')
            ->hideOnForm()
        ;

        yield TextField::new('webhookId', 'Webhook ID')
            ->setColumns(6)
        ;

        yield TextField::new('eventId', '事件ID')
            ->setColumns(6)
        ;

        yield TextField::new('paymentId', '支付ID')
            ->setColumns(6)
            ->hideOnIndex()
        ;

        yield TextField::new('eventType', '事件类型')
            ->setColumns(6)
            ->formatValue(function (mixed $value): string {
                return $this->formatEventType(is_string($value) ? $value : '');
            })
        ;

        yield TextField::new('status', '处理状态')
            ->setColumns(6)
            ->formatValue(function (mixed $value): string {
                return $this->formatProcessingStatus(is_string($value) ? $value : '');
            })
        ;

        yield BooleanField::new('processed', '已处理')
            ->hideOnForm()
        ;

        yield BooleanField::new('signatureValid', '签名有效')
            ->hideOnForm()
        ;

        yield TextField::new('signature', '签名')
            ->setColumns(12)
            ->hideOnIndex()
            ->hideOnForm()
        ;

        yield IntegerField::new('responseStatus', '响应状态码')
            ->setColumns(6)
            ->hideOnIndex()
            ->hideOnForm()
        ;

        yield CodeEditorField::new('requestHeaders', '请求头')
            ->setLanguage('javascript')
            ->hideOnIndex()
            ->formatValue(function (mixed $value): string {
                return $this->formatJsonValue($value);
            })
        ;

        yield TextareaField::new('requestBody', '请求体')
            ->setColumns(12)
            ->hideOnIndex()
            ->setMaxLength(1000)
        ;

        yield CodeEditorField::new('payload', '负载数据')
            ->setLanguage('javascript')
            ->hideOnIndex()
        ;

        yield TextareaField::new('errorMessage', '错误信息')
            ->setColumns(12)
            ->hideOnIndex()
        ;

        yield CodeEditorField::new('processedData', '处理后的数据')
            ->setLanguage('javascript')
            ->hideOnIndex()
            ->formatValue(function (mixed $value): string {
                return $this->formatJsonValue($value);
            })
        ;

        yield DateTimeField::new('receiveTime', '接收时间')
            ->setColumns(6)
            ->hideOnForm()
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
            ->add(TextFilter::new('webhookId', 'Webhook ID'))
            ->add(TextFilter::new('eventType', '事件类型'))
            ->add(TextFilter::new('status', '处理状态'))
            ->add(TextFilter::new('paymentId', '支付ID'))
            ->add(BooleanFilter::new('processed', '已处理'))
            ->add(BooleanFilter::new('signatureValid', '签名有效'))
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

    private function formatEventType(string $eventType): string
    {
        $typeMap = [
            'payment_approved' => '<span class="badge bg-success">支付批准</span>',
            'payment_captured' => '<span class="badge bg-primary">支付捕获</span>',
            'payment_declined' => '<span class="badge bg-danger">支付拒绝</span>',
            'payment_refunded' => '<span class="badge bg-info">支付退款</span>',
            'payment_voided' => '<span class="badge bg-secondary">支付作废</span>',
            'payment_expired' => '<span class="badge bg-warning text-dark">支付过期</span>',
            'payment_partially_refunded' => '<span class="badge bg-info">部分退款</span>',
            'card_verified' => '<span class="badge bg-success">卡验证成功</span>',
            'card_verification_declined' => '<span class="badge bg-danger">卡验证失败</span>',
        ];

        return $typeMap[$eventType] ?? sprintf('<span class="badge bg-light text-dark">%s</span>', $eventType);
    }

    private function formatJsonValue(mixed $value): string
    {
        if (null === $value || '' === $value || [] === $value) {
            return '';
        }

        $encoded = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return false !== $encoded ? $encoded : '';
    }

    private function formatProcessingStatus(string $status): string
    {
        $statusMap = [
            'pending' => '<span class="badge bg-warning text-dark">待处理</span>',
            'processing' => '<span class="badge bg-primary">处理中</span>',
            'success' => '<span class="badge bg-success">处理成功</span>',
            'failed' => '<span class="badge bg-danger">处理失败</span>',
            'skipped' => '<span class="badge bg-secondary">已跳过</span>',
        ];

        return $statusMap[$status] ?? sprintf('<span class="badge bg-light text-dark">%s</span>', $status);
    }
}
