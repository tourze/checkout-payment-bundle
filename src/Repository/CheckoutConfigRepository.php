<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\Repository;

use CheckoutPaymentBundle\Entity\CheckoutConfig;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;

/**
 * @extends ServiceEntityRepository<CheckoutConfig>
 */
#[AsRepository(entityClass: CheckoutConfig::class)]
class CheckoutConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CheckoutConfig::class);
    }

    public function save(CheckoutConfig $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CheckoutConfig $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return array<CheckoutConfig>
     */
    public function findEnabledConfigs(): array
    {
        /** @var array<CheckoutConfig> */
        return $this->findBy(['enabled' => true], ['name' => 'ASC']);
    }

    public function findDefaultConfig(): ?CheckoutConfig
    {
        /** @var CheckoutConfig|null */
        return $this->findOneBy(['enabled' => true, 'isDefault' => true]);
    }

    public function findByName(string $name): ?CheckoutConfig
    {
        /** @var CheckoutConfig|null */
        return $this->findOneBy(['name' => $name]);
    }

    /**
     * @return array<CheckoutConfig>
     */
    public function findByEnvironment(bool $isSandbox): array
    {
        /** @var array<CheckoutConfig> */
        return $this->findBy(['enabled' => true, 'isSandbox' => $isSandbox], ['name' => 'ASC']);
    }
}
