<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\Repository;

use CheckoutPaymentBundle\Entity\PaymentRefund;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;

/**
 * @extends ServiceEntityRepository<PaymentRefund>
 */
#[AsRepository(entityClass: PaymentRefund::class)]
class PaymentRefundRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaymentRefund::class);
    }

    public function save(PaymentRefund $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PaymentRefund $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByRefundId(string $refundId): ?PaymentRefund
    {
        /** @var PaymentRefund|null */
        return $this->findOneBy(['refundId' => $refundId]);
    }

    /** @return array<PaymentRefund> */
    public function findByPaymentId(string $paymentId): array
    {
        /** @var array<PaymentRefund> */
        return $this->createQueryBuilder('pr')
            ->join('pr.payment', 'p')
            ->where('p.paymentId = :paymentId')
            ->setParameter('paymentId', $paymentId)
            ->orderBy('pr.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /** @return array<PaymentRefund> */
    public function findApprovedRefunds(): array
    {
        /** @var array<PaymentRefund> */
        return $this->createQueryBuilder('pr')
            ->where('pr.status = :status')
            ->setParameter('status', 'Approved')
            ->orderBy('pr.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /** @return array<PaymentRefund> */
    public function findPendingRefunds(): array
    {
        /** @var array<PaymentRefund> */
        return $this->createQueryBuilder('pr')
            ->where('pr.status = :status')
            ->setParameter('status', 'pending')
            ->orderBy('pr.createTime', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /** @return array<PaymentRefund> */
    public function findFailedRefunds(): array
    {
        /** @var array<PaymentRefund> */
        return $this->createQueryBuilder('pr')
            ->where('pr.status = :status')
            ->setParameter('status', 'Failed')
            ->orderBy('pr.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /** @return array<PaymentRefund> */
    public function findRefundsByDateRange(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate): array
    {
        /** @var array<PaymentRefund> */
        return $this->createQueryBuilder('pr')
            ->where('pr.createTime >= :startDate')
            ->andWhere('pr.createTime <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('pr.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /** @return array<PaymentRefund> */
    public function findByReference(string $reference): array
    {
        /** @var array<PaymentRefund> */
        return $this->createQueryBuilder('pr')
            ->where('pr.reference = :reference')
            ->setParameter('reference', $reference)
            ->orderBy('pr.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /** @return array<PaymentRefund> */
    public function findByStatus(string $status): array
    {
        /** @var array<PaymentRefund> */
        return $this->createQueryBuilder('pr')
            ->where('pr.status = :status')
            ->setParameter('status', $status)
            ->orderBy('pr.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /** @return array<PaymentRefund> */
    public function findByDateRange(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        /** @var array<PaymentRefund> */
        return $this->createQueryBuilder('pr')
            ->where('pr.createTime BETWEEN :start AND :end')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->orderBy('pr.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function calculateTotalRefundsByPaymentId(string $paymentId): int
    {
        $result = $this->createQueryBuilder('pr')
            ->select('SUM(pr.amount) as total')
            ->where('pr.paymentId = :paymentId')
            ->andWhere('pr.status = :status')
            ->setParameter('paymentId', $paymentId)
            ->setParameter('status', 'Approved')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return (int) ($result ?? 0);
    }
}
