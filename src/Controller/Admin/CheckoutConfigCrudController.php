<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\Controller\Admin;

use CheckoutPaymentBundle\Entity\CheckoutConfig;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends AbstractCrudController<CheckoutConfig>
 */
#[AdminCrud(routePath: '/checkout/config', routeName: 'checkout_config')]
final class CheckoutConfigCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CheckoutConfig::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')
            ->setLabel('ID')
            ->hideOnForm()
        ;

        yield TextField::new('name')
            ->setLabel('配置名称')
            ->setRequired(true)
            ->setHelp('给这个配置起一个易于识别的名称')
        ;

        yield TextareaField::new('description')
            ->setLabel('配置描述')
            ->setHelp('描述这个配置的用途和适用场景')
        ;

        yield TextField::new('apiKey')
            ->setLabel('API密钥')
            ->setRequired(true)
            ->setHelp('Checkout分配的API密钥')
        ;

        yield BooleanField::new('isSandbox')
            ->setLabel('沙箱环境')
            ->setHelp('是否使用沙箱环境')
        ;

        yield BooleanField::new('enabled')
            ->setLabel('是否启用')
            ->setHelp('是否启用此配置进行支付处理')
        ;

        yield BooleanField::new('isDefault')
            ->setLabel('默认配置')
            ->setHelp('是否为默认配置')
        ;

        yield IntegerField::new('timeout')
            ->setLabel('超时时间')
            ->setHelp('请求超时时间（秒）')
        ;

        yield IntegerField::new('retryAttempts')
            ->setLabel('重试次数')
            ->setHelp('请求失败时的重试次数')
        ;

        yield DateTimeField::new('createTime')
            ->setLabel('创建时间')
            ->hideOnForm()
        ;

        yield DateTimeField::new('updateTime')
            ->setLabel('更新时间')
            ->hideOnForm()
        ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Checkout配置')
            ->setEntityLabelInPlural('Checkout配置列表')
            ->setPageTitle('index', 'Checkout配置管理')
            ->setPageTitle('detail', 'Checkout配置详情')
            ->setPageTitle('edit', '编辑Checkout配置')
            ->setPageTitle('new', '新增Checkout配置')
            ->setSearchFields(['name', 'apiKey', 'description'])
            ->setDefaultSort(['createTime' => 'DESC'])
            ->setEntityPermission('ROLE_ADMIN')
            ->showEntityActionsInlined()
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->setPermission(Action::DELETE, 'ROLE_ADMIN')
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('name')
            ->add('isSandbox')
            ->add('enabled')
            ->add('isDefault')
        ;
    }
}
