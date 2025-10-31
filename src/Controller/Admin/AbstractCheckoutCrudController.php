<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\Controller\Admin;

use CheckoutPaymentBundle\Entity\CheckoutConfig;
use CheckoutPaymentBundle\Repository\CheckoutConfigRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

/**
 * @template TEntity of object
 * @extends AbstractCrudController<TEntity>
 */
abstract class AbstractCheckoutCrudController extends AbstractCrudController
{
    /**
     * @required 必须的方法依赖注入
     */
    public function autowire(
        CheckoutConfigRepository $checkoutConfigRepository,
    ): void {
        $this->checkoutConfigRepository = $checkoutConfigRepository;
    }

    private CheckoutConfigRepository $checkoutConfigRepository;

    public function createEntity(string $entityFqcn): object
    {
        $entity = parent::createEntity($entityFqcn);

        // 如果实体有CheckoutConfig关联，设置默认配置
        if (method_exists($entity, 'setCheckoutConfig')) {
            $config = $this->checkoutConfigRepository->findOneBy(['enabled' => true]);

            if (null !== $config) {
                $entity->setCheckoutConfig($config);
            }
        }

        return $entity;
    }
}
