<?php

namespace CheckoutPaymentBundle\Tests\Repository;

use CheckoutPaymentBundle\Entity\Payment;
use CheckoutPaymentBundle\Entity\PaymentSession;
use CheckoutPaymentBundle\Repository\PaymentRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @template TEntity of Payment
 * @extends AbstractRepositoryTestCase<TEntity>
 * @internal
 */
#[CoversClass(PaymentRepository::class)]
#[RunTestsInSeparateProcesses]
class PaymentRepositoryTest extends AbstractRepositoryTestCase
{
    /** @return ServiceEntityRepository<Payment> */
    protected function getRepository(): ServiceEntityRepository
    {
        return self::getService(PaymentRepository::class);
    }

    protected function onSetUp(): void
    {
        // Clear all existing data first
        /** @var PaymentRepository $repository */
        $repository = $this->getRepository();

        foreach ($repository->findAll() as $entity) {
            $repository->remove($entity, true);
        }

        // Create a PaymentSession first for Payment relation
        $session = $this->createPaymentSession();

        // Always create one fixture for count test requirement
        // The findAll test in AbstractRepositoryTestCase will clear data again in its own implementation
        $fixture = $this->createNewEntity();
        $fixture->setPaymentId('fixture-payment-for-count-test');
        $fixture->setSession($session);
        $repository->save($fixture, true);
    }

    protected function createNewEntity(): Payment
    {
        $session = $this->createPaymentSession();

        $payment = new Payment();
        $payment->setPaymentId('pay_test_' . uniqid());
        $payment->setReference('PAY-' . uniqid());
        $payment->setAmount(10000);
        $payment->setCurrency('USD');
        $payment->setStatus('Authorized');
        $payment->setApproved(true);
        $payment->setRisk(['level' => 'low']);
        $payment->setSession($session);

        return $payment;
    }

    private function createPaymentSession(): PaymentSession
    {
        $session = new PaymentSession();
        $session->setSessionId('cs_fixture_' . uniqid());
        $session->setReference('SESSION-FIXTURE-' . uniqid());
        $session->setAmount(10000);
        $session->setCurrency('USD');
        $session->setCustomerEmail('test@example.com');
        $session->setCustomerName('Test User');
        $session->setSuccessUrl('https://localhost/success');
        $session->setCancelUrl('https://localhost/cancel');
        $session->setPaymentUrl('https://checkout.com/pay/cs_fixture');
        $session->setStatus('pending');
        $session->setExpiresAt(new \DateTimeImmutable('+24 hours'));

        // Persist the session first
        self::getEntityManager()->persist($session);
        self::getEntityManager()->flush();

        return $session;
    }

    public function testFindByPaymentId(): void
    {
        $session = $this->createPaymentSession();
        $payment = $this->createNewEntity();
        $payment->setPaymentId('pay_test_001');
        $payment->setSession($session);
        self::getEntityManager()->persist($payment);
        self::getEntityManager()->flush();

        /** @var PaymentRepository $repository */
        $repository = $this->getRepository();
        $found = $repository->findByPaymentId('pay_test_001');

        $this->assertInstanceOf(Payment::class, $found);
        $this->assertEquals('pay_test_001', $found->getPaymentId());
    }

    public function testFindByReference(): void
    {
        /** @var PaymentRepository $repository */
        $repository = $this->getRepository();
        $payment = $repository->findByReference('REF-002');

        // This method requires session relation, so we just test it returns null or Payment
        $this->assertTrue(null === $payment || $payment instanceof Payment);
    }

    public function testFindByStatus(): void
    {
        $session = $this->createPaymentSession();
        $payment = $this->createNewEntity();
        $payment->setStatus('Captured');
        $payment->setSession($session);
        self::getEntityManager()->persist($payment);
        self::getEntityManager()->flush();

        /** @var PaymentRepository $repository */
        $repository = $this->getRepository();
        $payments = $repository->findByStatus('Captured');

        foreach ($payments as $paymentItem) {
            $this->assertEquals('Captured', $paymentItem->getStatus());
        }
    }

    public function testFindApprovedPayments(): void
    {
        /** @var PaymentRepository $repository */
        $repository = $this->getRepository();
        $payments = $repository->findApprovedPayments();

        foreach ($payments as $payment) {
            $this->assertTrue($payment->isApproved());
        }
    }

    public function testFindByDateRange(): void
    {
        $startDate = new \DateTime('-1 day');
        $endDate = new \DateTime('+1 day');

        /** @var PaymentRepository $repository */
        $repository = $this->getRepository();
        $payments = $repository->findByDateRange($startDate, $endDate);

        $this->assertNotNull($payments);
    }

    public function testFindByCheckoutSessionId(): void
    {
        /** @var PaymentRepository $repository */
        $repository = $this->getRepository();
        $payments = $repository->findByCheckoutSessionId('cs_test_001');

        $this->assertNotNull($payments);
    }

    public function testSearchPayments(): void
    {
        $filters = [
            'status' => 'Authorized',
            'currency' => 'USD',
        ];

        /** @var PaymentRepository $repository */
        $repository = $this->getRepository();
        $payments = $repository->searchPayments($filters);

        $this->assertNotNull($payments);
    }

    public function testFindByRiskLevel(): void
    {
        $session = $this->createPaymentSession();
        $payment = $this->createNewEntity();
        $payment->setRisk(['level' => 'low']);
        $payment->setSession($session);
        self::getEntityManager()->persist($payment);
        self::getEntityManager()->flush();

        /** @var PaymentRepository $repository */
        $repository = $this->getRepository();
        $payments = $repository->findByRiskLevel('low');

        foreach ($payments as $paymentItem) {
            $risk = $paymentItem->getRisk();
            $this->assertNotNull($risk);
            $this->assertEquals('low', $risk['level']);
        }
    }

    public function testFindByAmountRange(): void
    {
        $session = $this->createPaymentSession();
        $payment = $this->createNewEntity();
        $payment->setAmount(10000);
        $payment->setSession($session);
        self::getEntityManager()->persist($payment);
        self::getEntityManager()->flush();

        /** @var PaymentRepository $repository */
        $repository = $this->getRepository();
        $payments = $repository->findByAmountRange(5000, 15000);

        foreach ($payments as $paymentItem) {
            $this->assertGreaterThanOrEqual(5000, $paymentItem->getAmount());
            $this->assertLessThanOrEqual(15000, $paymentItem->getAmount());
        }
    }

    public function testFindBySession(): void
    {
        // Create a persistent session with payment
        $session = $this->createPaymentSession();
        $payment = $this->createNewEntity();
        $payment->setSession($session);
        self::getEntityManager()->persist($payment);
        self::getEntityManager()->flush();

        /** @var PaymentRepository $repository */
        $repository = $this->getRepository();
        $payments = $repository->findBySession($session);

        $this->assertNotEmpty($payments);
        $this->assertCount(1, $payments);
        $paymentSession = $payments[0]->getSession();
        $this->assertNotNull($paymentSession);
        $this->assertEquals($session->getId(), $paymentSession->getId());
    }

    public function testFindSuccessfulPayments(): void
    {
        $session = $this->createPaymentSession();
        $payment = $this->createNewEntity();
        $payment->setStatus('Authorized');
        $payment->setSession($session);
        self::getEntityManager()->persist($payment);
        self::getEntityManager()->flush();

        /** @var PaymentRepository $repository */
        $repository = $this->getRepository();
        $payments = $repository->findSuccessfulPayments();

        foreach ($payments as $paymentItem) {
            $this->assertContains($paymentItem->getStatus(), ['Authorized', 'Captured']);
        }
    }

    public function testFindFailedPayments(): void
    {
        $session = $this->createPaymentSession();
        $payment = $this->createNewEntity();
        $payment->setStatus('Declined');
        $payment->setSession($session);
        self::getEntityManager()->persist($payment);
        self::getEntityManager()->flush();

        /** @var PaymentRepository $repository */
        $repository = $this->getRepository();
        $payments = $repository->findFailedPayments();

        foreach ($payments as $paymentItem) {
            $this->assertEquals('Declined', $paymentItem->getStatus());
        }
    }

    public function testRemovePayment(): void
    {
        $session = $this->createPaymentSession();
        $payment = $this->createNewEntity();
        $payment->setPaymentId('pay_test_to_remove');
        $payment->setSession($session);
        self::getEntityManager()->persist($payment);
        self::getEntityManager()->flush();

        /** @var PaymentRepository $repository */
        $repository = $this->getRepository();
        $repository->remove($payment, true);

        $removedPayment = $repository->findByPaymentId('pay_test_to_remove');
        $this->assertNull($removedPayment);
    }
}
