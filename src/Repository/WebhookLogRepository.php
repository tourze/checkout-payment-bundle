<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\Repository;

use CheckoutPaymentBundle\Entity\WebhookLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;

/**
 * @extends ServiceEntityRepository<WebhookLog>
 */
#[AsRepository(entityClass: WebhookLog::class)]
class WebhookLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WebhookLog::class);
    }

    public function save(WebhookLog $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(WebhookLog $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /** @return array<WebhookLog> */
    public function findRecentWebhooks(int $limit = 50): array
    {
        /** @var array<WebhookLog> */
        return $this->createQueryBuilder('wl')
            ->orderBy('wl.receiveTime', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    /** @return array<WebhookLog> */
    public function findWebhooksByType(string $eventType): array
    {
        /** @var array<WebhookLog> */
        return $this->createQueryBuilder('wl')
            ->where('wl.eventType = :eventType')
            ->setParameter('eventType', $eventType)
            ->orderBy('wl.receiveTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /** @return array<WebhookLog> */
    public function findFailedWebhooks(): array
    {
        /** @var array<WebhookLog> */
        return $this->createQueryBuilder('wl')
            ->where('wl.status = :status')
            ->setParameter('status', 'failed')
            ->orderBy('wl.receiveTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /** @return array<WebhookLog> */
    public function findWebhooksByPaymentId(string $paymentId): array
    {
        /** @var array<WebhookLog> */
        return $this->createQueryBuilder('wl')
            ->where('wl.processedData LIKE :paymentId')
            ->setParameter('paymentId', '%' . $paymentId . '%')
            ->orderBy('wl.receiveTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /** @return array<WebhookLog> */
    public function findPendingWebhooks(): array
    {
        /** @var array<WebhookLog> */
        return $this->createQueryBuilder('wl')
            ->where('wl.status = :status')
            ->setParameter('status', 'pending')
            ->orderBy('wl.receiveTime', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByEventId(string $eventId): ?WebhookLog
    {
        /** @var WebhookLog|null */
        return $this->createQueryBuilder('wl')
            ->where('wl.eventId = :eventId')
            ->setParameter('eventId', $eventId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /** @return array<WebhookLog> */
    public function findByPaymentId(string $paymentId): array
    {
        /** @var array<WebhookLog> */
        return $this->createQueryBuilder('wl')
            ->where('wl.paymentId = :paymentId')
            ->setParameter('paymentId', $paymentId)
            ->orderBy('wl.receiveTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /** @return array<WebhookLog> */
    public function findByEventType(string $eventType): array
    {
        /** @var array<WebhookLog> */
        return $this->createQueryBuilder('wl')
            ->where('wl.eventType = :eventType')
            ->setParameter('eventType', $eventType)
            ->orderBy('wl.receiveTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /** @return array<WebhookLog> */
    public function findProcessedWebhooks(): array
    {
        /** @var array<WebhookLog> */
        return $this->createQueryBuilder('wl')
            ->where('wl.processed = :processed')
            ->setParameter('processed', true)
            ->orderBy('wl.receiveTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /** @return array<WebhookLog> */
    public function findUnprocessedWebhooks(): array
    {
        /** @var array<WebhookLog> */
        return $this->createQueryBuilder('wl')
            ->where('wl.processed = :processed')
            ->setParameter('processed', false)
            ->orderBy('wl.receiveTime', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /** @return array<WebhookLog> */
    public function findByDateRange(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        /** @var array<WebhookLog> */
        return $this->createQueryBuilder('wl')
            ->where('wl.receiveTime BETWEEN :start AND :end')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->orderBy('wl.receiveTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function countByEventType(string $eventType): int
    {
        return (int) $this->createQueryBuilder('wl')
            ->select('COUNT(wl.id)')
            ->where('wl.eventType = :eventType')
            ->setParameter('eventType', $eventType)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function cleanupOldWebhooks(\DateTimeInterface $beforeDate): int
    {
        /** @var int */
        return $this->createQueryBuilder('wl')
            ->delete()
            ->where('wl.receiveTime < :beforeDate')
            ->setParameter('beforeDate', $beforeDate)
            ->getQuery()
            ->execute()
        ;
    }

    public function markAsProcessed(string $eventId): void
    {
        $webhook = $this->findByEventId($eventId);
        if (null !== $webhook) {
            $webhook->setProcessed(true);
            $webhook->setStatus('processed');
            $this->save($webhook, true);
        }
    }
}
