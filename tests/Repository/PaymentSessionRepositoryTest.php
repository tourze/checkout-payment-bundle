<?php

namespace CheckoutPaymentBundle\Tests\Repository;

use CheckoutPaymentBundle\Entity\PaymentSession;
use CheckoutPaymentBundle\Repository\PaymentSessionRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @template TEntity of PaymentSession
 * @extends AbstractRepositoryTestCase<TEntity>
 * @internal
 */
#[CoversClass(PaymentSessionRepository::class)]
#[RunTestsInSeparateProcesses]
class PaymentSessionRepositoryTest extends AbstractRepositoryTestCase
{
    /** @return ServiceEntityRepository<PaymentSession> */
    protected function getRepository(): ServiceEntityRepository
    {
        return self::getService(PaymentSessionRepository::class);
    }

    protected function onSetUp(): void
    {
        // Clear all existing data first
        /** @var PaymentSessionRepository $repository */
        $repository = $this->getRepository();

        foreach ($repository->findAll() as $entity) {
            $repository->remove($entity, true);
        }

        // Always create one fixture for count test requirement
        // The findAll test in AbstractRepositoryTestCase will clear data again in its own implementation
        $fixture = $this->createNewEntity();
        $fixture->setSessionId('fixture-session-for-count-test');
        $repository->save($fixture, true);
    }

    protected function createNewEntity(): PaymentSession
    {
        $session = new PaymentSession();
        $session->setSessionId('cs_test_' . uniqid());
        $session->setReference('SESSION-' . uniqid());
        $session->setAmount(10000);
        $session->setCurrency('USD');
        $session->setStatus('open');
        $session->setPaymentUrl('https://checkout.com/pay/' . uniqid());
        $session->setSuccessUrl('https://example.com/success');
        $session->setCancelUrl('https://example.com/cancel');
        $session->setCustomerEmail('test@example.com');
        $session->setExpiresAt(new \DateTimeImmutable('+24 hours'));

        return $session;
    }

    public function testFindBySessionId(): void
    {
        $session = $this->createNewEntity();
        $session->setSessionId('cs_test_001');
        self::getEntityManager()->persist($session);
        self::getEntityManager()->flush();

        /** @var PaymentSessionRepository $repository */
        $repository = $this->getRepository();
        $found = $repository->findBySessionId('cs_test_001');

        $this->assertInstanceOf(PaymentSession::class, $found);
        $this->assertEquals('cs_test_001', $found->getSessionId());
    }

    public function testFindByReference(): void
    {
        $session = $this->createNewEntity();
        $session->setReference('SESSION-002');
        self::getEntityManager()->persist($session);
        self::getEntityManager()->flush();

        /** @var PaymentSessionRepository $repository */
        $repository = $this->getRepository();
        $found = $repository->findByReference('SESSION-002');

        $this->assertInstanceOf(PaymentSession::class, $found);
        $this->assertEquals('SESSION-002', $found->getReference());
    }

    public function testFindByStatus(): void
    {
        $session1 = $this->createNewEntity();
        $session1->setStatus('complete');
        $session2 = $this->createNewEntity();
        $session2->setStatus('complete');
        self::getEntityManager()->persist($session1);
        self::getEntityManager()->persist($session2);
        self::getEntityManager()->flush();

        /** @var PaymentSessionRepository $repository */
        $repository = $this->getRepository();
        $sessions = $repository->findByStatus('complete');

        $this->assertGreaterThanOrEqual(2, count($sessions));
        foreach ($sessions as $session) {
            $this->assertEquals('complete', $session->getStatus());
        }
    }

    public function testFindByCustomerEmail(): void
    {
        $session = $this->createNewEntity();
        $session->setCustomerEmail('customer1@example.com');
        self::getEntityManager()->persist($session);
        self::getEntityManager()->flush();

        /** @var PaymentSessionRepository $repository */
        $repository = $this->getRepository();
        $sessions = $repository->findByCustomerEmail('customer1@example.com');

        $this->assertGreaterThanOrEqual(1, count($sessions));
        $this->assertEquals('customer1@example.com', $sessions[0]->getCustomerEmail());
    }

    public function testFindExpiredSessions(): void
    {
        $expiredDate = new \DateTimeImmutable('-2 days');

        /** @var PaymentSessionRepository $repository */
        $repository = $this->getRepository();
        $sessions = $repository->findExpiredSessions($expiredDate);

        $this->assertNotNull($sessions);
    }

    public function testFindActiveSessions(): void
    {
        $session = $this->createNewEntity();
        $session->setStatus('pending');
        self::getEntityManager()->persist($session);
        self::getEntityManager()->flush();

        /** @var PaymentSessionRepository $repository */
        $repository = $this->getRepository();
        $sessions = $repository->findActiveSessions();

        foreach ($sessions as $sessionItem) {
            $this->assertContains($sessionItem->getStatus(), ['pending', 'processing']);
        }
    }

    public function testFindByAmountRange(): void
    {
        $session = $this->createNewEntity();
        $session->setAmount(10000);
        self::getEntityManager()->persist($session);
        self::getEntityManager()->flush();

        /** @var PaymentSessionRepository $repository */
        $repository = $this->getRepository();
        $sessions = $repository->findByAmountRange(5000, 15000);

        foreach ($sessions as $sessionItem) {
            $this->assertGreaterThanOrEqual(5000, $sessionItem->getAmount());
            $this->assertLessThanOrEqual(15000, $sessionItem->getAmount());
        }
    }

    public function testFindByDateRange(): void
    {
        /** @var PaymentSessionRepository $repository */
        $repository = $this->getRepository();
        $sessions = $repository->findByDateRange(
            new \DateTime('-2 days'),
            new \DateTime('+2 days')
        );

        $this->assertNotNull($sessions);
    }

    public function testCleanupExpiredSessions(): void
    {
        $expiryDate = new \DateTime('-1 day');

        /** @var PaymentSessionRepository $repository */
        $repository = $this->getRepository();
        $deletedCount = $repository->cleanupExpiredSessions($expiryDate);

        $this->assertGreaterThanOrEqual(0, $deletedCount);
    }

    public function testSaveSession(): void
    {
        $session = $this->createNewEntity();
        $session->setSessionId('cs_test_new');
        $session->setReference('SESSION-NEW');

        /** @var PaymentSessionRepository $repository */
        $repository = $this->getRepository();
        $repository->save($session, true);

        $savedSession = $repository->findBySessionId('cs_test_new');
        $this->assertInstanceOf(PaymentSession::class, $savedSession);
        $this->assertEquals('SESSION-NEW', $savedSession->getReference());
    }

    public function testRemoveSession(): void
    {
        $session = $this->createNewEntity();
        $session->setSessionId('cs_test_to_remove');
        self::getEntityManager()->persist($session);
        self::getEntityManager()->flush();

        /** @var PaymentSessionRepository $repository */
        $repository = $this->getRepository();
        $repository->remove($session, true);

        $removedSession = $repository->findBySessionId('cs_test_to_remove');
        $this->assertNull($removedSession);
    }

    public function testFindPendingSessions(): void
    {
        /** @var PaymentSessionRepository $repository */
        $repository = $this->getRepository();
        $sessions = $repository->findPendingSessions();

        $this->assertNotNull($sessions);
        foreach ($sessions as $session) {
            $this->assertEquals('pending', $session->getStatus());
        }
    }
}
