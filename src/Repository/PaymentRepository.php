<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\Repository;

use CheckoutPaymentBundle\Entity\Payment;
use CheckoutPaymentBundle\Entity\PaymentSession;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;

/**
 * @extends ServiceEntityRepository<Payment>
 */
#[AsRepository(entityClass: Payment::class)]
class PaymentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Payment::class);
    }

    public function save(Payment $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Payment $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByPaymentId(string $paymentId): ?Payment
    {
        /** @var Payment|null */
        return $this->findOneBy(['paymentId' => $paymentId]);
    }

    public function findByReference(string $reference): ?Payment
    {
        /** @var Payment|null */
        return $this->createQueryBuilder('p')
            ->join('p.session', 'ps')
            ->where('ps.reference = :reference')
            ->setParameter('reference', $reference)
            ->orderBy('p.createTime', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /** @return array<Payment> */
    public function findBySession(PaymentSession $session): array
    {
        /** @var array<Payment> */
        return $this->createQueryBuilder('p')
            ->where('p.session = :session')
            ->setParameter('session', $session)
            ->orderBy('p.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /** @return array<Payment> */
    public function findSuccessfulPayments(): array
    {
        /** @var array<Payment> */
        return $this->createQueryBuilder('p')
            ->where('p.status IN (:statuses)')
            ->setParameter('statuses', ['Authorized', 'Captured'])
            ->orderBy('p.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /** @return array<Payment> */
    public function findFailedPayments(): array
    {
        /** @var array<Payment> */
        return $this->createQueryBuilder('p')
            ->where('p.status = :status')
            ->setParameter('status', 'Declined')
            ->orderBy('p.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /** @return array<Payment> */
    public function findByStatus(string $status): array
    {
        /** @var array<Payment> */
        return $this->createQueryBuilder('p')
            ->where('p.status = :status')
            ->setParameter('status', $status)
            ->orderBy('p.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /** @return array<Payment> */
    public function findApprovedPayments(): array
    {
        /** @var array<Payment> */
        return $this->createQueryBuilder('p')
            ->where('p.approved = :approved')
            ->setParameter('approved', true)
            ->orderBy('p.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /** @return array<Payment> */
    public function findByDateRange(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        /** @var array<Payment> */
        return $this->createQueryBuilder('p')
            ->where('p.createTime BETWEEN :start AND :end')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->orderBy('p.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /** @return array<Payment> */
    public function findByCheckoutSessionId(string $sessionId): array
    {
        /** @var array<Payment> */
        return $this->createQueryBuilder('p')
            ->join('p.session', 's')
            ->where('s.sessionId = :sessionId')
            ->setParameter('sessionId', $sessionId)
            ->orderBy('p.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param array<string, mixed> $criteria
     * @return array<Payment>
     */
    public function searchPayments(array $criteria): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.session', 's')
        ;

        if (isset($criteria['status'])) {
            $qb->andWhere('p.status = :status')
                ->setParameter('status', $criteria['status'])
            ;
        }

        if (isset($criteria['amount_min'])) {
            $qb->andWhere('p.amount >= :amount_min')
                ->setParameter('amount_min', $criteria['amount_min'])
            ;
        }

        if (isset($criteria['amount_max'])) {
            $qb->andWhere('p.amount <= :amount_max')
                ->setParameter('amount_max', $criteria['amount_max'])
            ;
        }

        if (isset($criteria['currency'])) {
            $qb->andWhere('p.currency = :currency')
                ->setParameter('currency', $criteria['currency'])
            ;
        }

        if (isset($criteria['reference']) && is_string($criteria['reference'])) {
            $qb->andWhere('p.reference LIKE :reference OR s.reference LIKE :reference')
                ->setParameter('reference', '%' . $criteria['reference'] . '%')
            ;
        }

        /** @var array<Payment> */
        return $qb->orderBy('p.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /** @return array<Payment> */
    public function findByRiskLevel(string $riskLevel): array
    {
        /** @var array<Payment> */
        return $this->createQueryBuilder('p')
            ->where('p.risk LIKE :riskPattern')
            ->setParameter('riskPattern', '%"level":"' . $riskLevel . '"%')
            ->orderBy('p.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /** @return array<Payment> */
    public function findByAmountRange(int $minAmount, int $maxAmount): array
    {
        /** @var array<Payment> */
        return $this->createQueryBuilder('p')
            ->where('p.amount BETWEEN :min AND :max')
            ->setParameter('min', $minAmount)
            ->setParameter('max', $maxAmount)
            ->orderBy('p.amount', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
}
