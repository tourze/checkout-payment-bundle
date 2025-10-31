<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\Repository;

use CheckoutPaymentBundle\Entity\PaymentSession;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;

/**
 * @extends ServiceEntityRepository<PaymentSession>
 */
#[AsRepository(entityClass: PaymentSession::class)]
class PaymentSessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaymentSession::class);
    }

    public function save(PaymentSession $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PaymentSession $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByReference(string $reference): ?PaymentSession
    {
        /** @var PaymentSession|null */
        return $this->findOneBy(['reference' => $reference]);
    }

    public function findBySessionId(string $sessionId): ?PaymentSession
    {
        /** @var PaymentSession|null */
        return $this->findOneBy(['sessionId' => $sessionId]);
    }

    /** @return array<PaymentSession> */
    public function findPendingSessions(): array
    {
        /** @var array<PaymentSession> */
        return $this->createQueryBuilder('ps')
            ->where('ps.status = :status')
            ->setParameter('status', 'pending')
            ->orderBy('ps.createTime', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /** @return array<PaymentSession> */
    public function findExpiredSessions(\DateTimeImmutable $expiredAt): array
    {
        /** @var array<PaymentSession> */
        return $this->createQueryBuilder('ps')
            ->where('ps.createTime < :expiredAt')
            ->andWhere('ps.status = :status')
            ->setParameter('expiredAt', $expiredAt)
            ->setParameter('status', 'pending')
            ->getQuery()
            ->getResult()
        ;
    }

    /** @return array<PaymentSession> */
    public function findByStatus(string $status): array
    {
        /** @var array<PaymentSession> */
        return $this->createQueryBuilder('ps')
            ->where('ps.status = :status')
            ->setParameter('status', $status)
            ->orderBy('ps.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /** @return array<PaymentSession> */
    public function findByCustomerEmail(string $email): array
    {
        /** @var array<PaymentSession> */
        return $this->createQueryBuilder('ps')
            ->where('ps.customerEmail = :email')
            ->setParameter('email', $email)
            ->orderBy('ps.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /** @return array<PaymentSession> */
    public function findActiveSessions(): array
    {
        /** @var array<PaymentSession> */
        return $this->createQueryBuilder('ps')
            ->where('ps.status IN (:statuses)')
            ->setParameter('statuses', ['pending', 'processing'])
            ->orderBy('ps.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /** @return array<PaymentSession> */
    public function findByAmountRange(int $minAmount, int $maxAmount): array
    {
        /** @var array<PaymentSession> */
        return $this->createQueryBuilder('ps')
            ->where('ps.amount BETWEEN :min AND :max')
            ->setParameter('min', $minAmount)
            ->setParameter('max', $maxAmount)
            ->orderBy('ps.amount', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /** @return array<PaymentSession> */
    public function findByDateRange(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        /** @var array<PaymentSession> */
        return $this->createQueryBuilder('ps')
            ->where('ps.createTime BETWEEN :start AND :end')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->orderBy('ps.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function cleanupExpiredSessions(\DateTimeInterface $expiryDate): int
    {
        /** @var int */
        return $this->createQueryBuilder('ps')
            ->delete()
            ->where('ps.expireTime < :expiryDate')
            ->andWhere('ps.status = :status')
            ->setParameter('expiryDate', $expiryDate)
            ->setParameter('status', 'pending')
            ->getQuery()
            ->execute()
        ;
    }
}
