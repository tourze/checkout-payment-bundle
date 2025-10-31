<?php

namespace CheckoutPaymentBundle\Tests\Repository;

use CheckoutPaymentBundle\Entity\PaymentRefund;
use CheckoutPaymentBundle\Repository\PaymentRefundRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @template TEntity of PaymentRefund
 * @extends AbstractRepositoryTestCase<TEntity>
 * @internal
 */
#[CoversClass(PaymentRefundRepository::class)]
#[RunTestsInSeparateProcesses]
class PaymentRefundRepositoryTest extends AbstractRepositoryTestCase
{
    /** @return ServiceEntityRepository<PaymentRefund> */
    protected function getRepository(): ServiceEntityRepository
    {
        return self::getService(PaymentRefundRepository::class);
    }

    protected function onSetUp(): void
    {
        // Clear all existing data first
        /** @var PaymentRefundRepository $repository */
        $repository = $this->getRepository();

        foreach ($repository->findAll() as $entity) {
            $repository->remove($entity, true);
        }

        // Always create one fixture for count test requirement
        // The findAll test in AbstractRepositoryTestCase will clear data again in its own implementation
        $fixture = $this->createNewEntity();
        $fixture->setRefundId('fixture-refund-for-count-test');
        $repository->save($fixture, true);
    }

    protected function createNewEntity(): PaymentRefund
    {
        $refund = new PaymentRefund();
        $refund->setRefundId('ref_test_' . uniqid());
        $refund->setAmount(5000);
        $refund->setCurrency('USD');
        $refund->setStatus('Pending');
        $refund->setReference('REFUND-' . uniqid());
        $refund->setApproved(false);

        return $refund;
    }

    public function testFindByRefundId(): void
    {
        $refund = $this->createNewEntity();
        $uniqueRefundId = 'ref_test_' . uniqid() . '_001';
        $refund->setRefundId($uniqueRefundId);
        self::getEntityManager()->persist($refund);
        self::getEntityManager()->flush();

        /** @var PaymentRefundRepository $repository */
        $repository = $this->getRepository();
        $found = $repository->findByRefundId($uniqueRefundId);

        $this->assertInstanceOf(PaymentRefund::class, $found);
        $this->assertEquals($uniqueRefundId, $found->getRefundId());
    }

    public function testFindByPaymentId(): void
    {
        /** @var PaymentRefundRepository $repository */
        $repository = $this->getRepository();
        $refunds = $repository->findByPaymentId('pay_test_002');

        $this->assertNotNull($refunds);
    }

    public function testFindByReference(): void
    {
        $refund = $this->createNewEntity();
        $refund->setReference('REFUND-001');
        self::getEntityManager()->persist($refund);
        self::getEntityManager()->flush();

        /** @var PaymentRefundRepository $repository */
        $repository = $this->getRepository();
        $refunds = $repository->findByReference('REFUND-001');

        $this->assertGreaterThanOrEqual(1, count($refunds));
        $this->assertEquals('REFUND-001', $refunds[0]->getReference());
    }

    public function testFindByStatus(): void
    {
        $refund = $this->createNewEntity();
        $refund->setStatus('Approved');
        self::getEntityManager()->persist($refund);
        self::getEntityManager()->flush();

        /** @var PaymentRefundRepository $repository */
        $repository = $this->getRepository();
        $refunds = $repository->findByStatus('Approved');

        foreach ($refunds as $refundItem) {
            $this->assertEquals('Approved', $refundItem->getStatus());
        }
    }

    public function testFindApprovedRefunds(): void
    {
        /** @var PaymentRefundRepository $repository */
        $repository = $this->getRepository();
        $refunds = $repository->findApprovedRefunds();

        $this->assertNotNull($refunds);
        foreach ($refunds as $refund) {
            $this->assertEquals('Approved', $refund->getStatus());
        }
    }

    public function testFindPendingRefunds(): void
    {
        /** @var PaymentRefundRepository $repository */
        $repository = $this->getRepository();
        $refunds = $repository->findPendingRefunds();

        $this->assertNotNull($refunds);
        foreach ($refunds as $refund) {
            $this->assertEquals('pending', $refund->getStatus());
        }
    }

    public function testFindByDateRange(): void
    {
        $startDate = new \DateTime('-1 day');
        $endDate = new \DateTime('+1 day');

        /** @var PaymentRefundRepository $repository */
        $repository = $this->getRepository();
        $refunds = $repository->findByDateRange($startDate, $endDate);

        $this->assertNotNull($refunds);
    }

    public function testCalculateTotalRefundsByPaymentId(): void
    {
        /** @var PaymentRefundRepository $repository */
        $repository = $this->getRepository();
        $total = $repository->calculateTotalRefundsByPaymentId('pay_test_002');

        $this->assertGreaterThanOrEqual(0, $total);
    }

    public function testSaveRefund(): void
    {
        $refund = $this->createNewEntity();
        $refund->setRefundId('ref_test_new');
        $refund->setReference('REFUND-NEW');

        /** @var PaymentRefundRepository $repository */
        $repository = $this->getRepository();
        $repository->save($refund, true);

        $savedRefund = $repository->findByRefundId('ref_test_new');
        $this->assertInstanceOf(PaymentRefund::class, $savedRefund);
        $this->assertEquals('REFUND-NEW', $savedRefund->getReference());
    }

    public function testRemoveRefund(): void
    {
        $refund = $this->createNewEntity();
        $refund->setRefundId('ref_test_to_remove');
        self::getEntityManager()->persist($refund);
        self::getEntityManager()->flush();

        /** @var PaymentRefundRepository $repository */
        $repository = $this->getRepository();
        $repository->remove($refund, true);

        $removedRefund = $repository->findByRefundId('ref_test_to_remove');
        $this->assertNull($removedRefund);
    }

    public function testFindFailedRefunds(): void
    {
        /** @var PaymentRefundRepository $repository */
        $repository = $this->getRepository();
        $refunds = $repository->findFailedRefunds();

        $this->assertNotNull($refunds);
        foreach ($refunds as $refund) {
            $this->assertEquals('Failed', $refund->getStatus());
        }
    }

    public function testFindRefundsByDateRange(): void
    {
        $startDate = new \DateTimeImmutable('-1 day');
        $endDate = new \DateTimeImmutable('+1 day');

        /** @var PaymentRefundRepository $repository */
        $repository = $this->getRepository();
        $refunds = $repository->findRefundsByDateRange($startDate, $endDate);

        $this->assertNotNull($refunds);
    }
}
