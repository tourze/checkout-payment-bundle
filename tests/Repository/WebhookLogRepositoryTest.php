<?php

namespace CheckoutPaymentBundle\Tests\Repository;

use CheckoutPaymentBundle\Entity\WebhookLog;
use CheckoutPaymentBundle\Repository\WebhookLogRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @template TEntity of WebhookLog
 * @extends AbstractRepositoryTestCase<TEntity>
 * @internal
 */
#[CoversClass(WebhookLogRepository::class)]
#[RunTestsInSeparateProcesses]
class WebhookLogRepositoryTest extends AbstractRepositoryTestCase
{
    /** @return ServiceEntityRepository<WebhookLog> */
    protected function getRepository(): ServiceEntityRepository
    {
        return self::getService(WebhookLogRepository::class);
    }

    protected function onSetUp(): void
    {
        // Clear all existing data first
        /** @var WebhookLogRepository $repository */
        $repository = $this->getRepository();

        foreach ($repository->findAll() as $entity) {
            /** @var WebhookLog $entity */
            $repository->remove($entity, true);
        }

        // Always create one fixture for count test requirement
        // The findAll test in AbstractRepositoryTestCase will clear data again in its own implementation
        /** @var WebhookLog $fixture */
        $fixture = $this->createNewEntity();
        $fixture->setWebhookId('fixture-webhook-for-count-test');
        $repository->save($fixture, true);
    }

    protected function createNewEntity(): WebhookLog
    {
        $webhook = new WebhookLog();
        $webhook->setWebhookId('wh_' . uniqid());
        $webhook->setEventId('evt_test_' . uniqid());
        $webhook->setPaymentId('pay_test_' . uniqid());
        $webhook->setEventType('payment_approved');
        $payload = false !== json_encode(['type' => 'payment_approved', 'data' => ['id' => 'pay_test_001']]) ? json_encode(['type' => 'payment_approved', 'data' => ['id' => 'pay_test_001']]) : '{}';
        $webhook->setPayload($payload);
        $webhook->setProcessed(false);
        $webhook->setRequestHeaders(['Content-Type' => 'application/json']);
        $webhook->setRequestBody('{}');
        $webhook->setResponseStatus(200);

        return $webhook;
    }

    public function testFindByEventId(): void
    {
        /** @var WebhookLog $webhook */
        $webhook = $this->createNewEntity();
        $webhook->setEventId('evt_test_001');
        self::getEntityManager()->persist($webhook);
        self::getEntityManager()->flush();

        /** @var WebhookLogRepository $repository */
        $repository = $this->getRepository();
        $found = $repository->findByEventId('evt_test_001');

        $this->assertInstanceOf(WebhookLog::class, $found);
        $this->assertEquals('evt_test_001', $found->getEventId());
    }

    public function testFindByPaymentId(): void
    {
        /** @var WebhookLog $webhook */
        $webhook = $this->createNewEntity();
        $webhook->setPaymentId('pay_test_002');
        self::getEntityManager()->persist($webhook);
        self::getEntityManager()->flush();

        /** @var WebhookLogRepository $repository */
        $repository = $this->getRepository();
        $webhooks = $repository->findByPaymentId('pay_test_002');

        $this->assertGreaterThanOrEqual(1, count($webhooks));
        $this->assertEquals('pay_test_002', $webhooks[0]->getPaymentId());
    }

    public function testFindByEventType(): void
    {
        /** @var WebhookLog $webhook */
        $webhook = $this->createNewEntity();
        $webhook->setEventType('payment_approved');
        self::getEntityManager()->persist($webhook);
        self::getEntityManager()->flush();

        /** @var WebhookLogRepository $repository */
        $repository = $this->getRepository();
        $webhooks = $repository->findByEventType('payment_approved');

        foreach ($webhooks as $webhookItem) {
            $this->assertEquals('payment_approved', $webhookItem->getEventType());
        }
    }

    public function testFindProcessedWebhooks(): void
    {
        /** @var WebhookLogRepository $repository */
        $repository = $this->getRepository();
        $webhooks = $repository->findProcessedWebhooks();

        $this->assertNotNull($webhooks);
        foreach ($webhooks as $webhook) {
            $this->assertTrue($webhook->isProcessed());
        }
    }

    public function testFindUnprocessedWebhooks(): void
    {
        /** @var WebhookLogRepository $repository */
        $repository = $this->getRepository();
        $webhooks = $repository->findUnprocessedWebhooks();

        foreach ($webhooks as $webhook) {
            $this->assertFalse($webhook->isProcessed());
        }
    }

    public function testFindFailedWebhooks(): void
    {
        /** @var WebhookLogRepository $repository */
        $repository = $this->getRepository();
        $webhooks = $repository->findFailedWebhooks();

        $this->assertNotNull($webhooks);
    }

    public function testFindByDateRange(): void
    {
        $startDate = new \DateTime('-1 day');
        $endDate = new \DateTime('+1 day');

        /** @var WebhookLogRepository $repository */
        $repository = $this->getRepository();
        $webhooks = $repository->findByDateRange($startDate, $endDate);

        $this->assertNotNull($webhooks);
    }

    public function testCountByEventType(): void
    {
        /** @var WebhookLogRepository $repository */
        $repository = $this->getRepository();
        $count = $repository->countByEventType('payment_approved');

        $this->assertGreaterThanOrEqual(0, $count);

        $count = $repository->countByEventType('non_existent');
        $this->assertEquals(0, $count);
    }

    public function testCleanupOldWebhooks(): void
    {
        $beforeDate = new \DateTime('-30 days');

        /** @var WebhookLogRepository $repository */
        $repository = $this->getRepository();
        $deletedCount = $repository->cleanupOldWebhooks($beforeDate);

        $this->assertGreaterThanOrEqual(0, $deletedCount);
    }

    public function testSaveWebhook(): void
    {
        /** @var WebhookLog $webhook */
        $webhook = $this->createNewEntity();
        $webhook->setEventId('evt_test_new');
        $webhook->setEventType('payment_voided');

        /** @var WebhookLogRepository $repository */
        $repository = $this->getRepository();
        $repository->save($webhook, true);

        $savedWebhook = $repository->findByEventId('evt_test_new');
        $this->assertInstanceOf(WebhookLog::class, $savedWebhook);
        $this->assertEquals('payment_voided', $savedWebhook->getEventType());
    }

    public function testRemoveWebhook(): void
    {
        /** @var WebhookLog $webhook */
        $webhook = $this->createNewEntity();
        $webhook->setEventId('evt_test_to_remove');
        self::getEntityManager()->persist($webhook);
        self::getEntityManager()->flush();

        /** @var WebhookLogRepository $repository */
        $repository = $this->getRepository();
        $repository->remove($webhook, true);

        $removedWebhook = $repository->findByEventId('evt_test_to_remove');
        $this->assertNull($removedWebhook);
    }

    public function testMarkAsProcessed(): void
    {
        /** @var WebhookLog $webhook */
        $webhook = $this->createNewEntity();
        $webhook->setEventId('evt_test_mark_processed');
        $webhook->setProcessed(false);
        self::getEntityManager()->persist($webhook);
        self::getEntityManager()->flush();

        /** @var WebhookLogRepository $repository */
        $repository = $this->getRepository();
        $repository->markAsProcessed('evt_test_mark_processed');

        $processedWebhook = $repository->findByEventId('evt_test_mark_processed');
        $this->assertNotNull($processedWebhook);
        $this->assertTrue($processedWebhook->isProcessed());
    }

    public function testFindRecentWebhooks(): void
    {
        /** @var WebhookLogRepository $repository */
        $repository = $this->getRepository();
        $webhooks = $repository->findRecentWebhooks(10);

        $this->assertLessThanOrEqual(10, count($webhooks));
    }

    public function testFindPendingWebhooks(): void
    {
        /** @var WebhookLogRepository $repository */
        $repository = $this->getRepository();
        $webhooks = $repository->findPendingWebhooks();

        $this->assertNotNull($webhooks);
    }

    public function testFindWebhooksByPaymentId(): void
    {
        /** @var WebhookLogRepository $repository */
        $repository = $this->getRepository();
        $webhooks = $repository->findWebhooksByPaymentId('pay_test_001');

        $this->assertNotNull($webhooks);
    }

    public function testFindWebhooksByType(): void
    {
        /** @var WebhookLogRepository $repository */
        $repository = $this->getRepository();
        $webhooks = $repository->findWebhooksByType('payment_approved');

        $this->assertNotNull($webhooks);
    }
}
