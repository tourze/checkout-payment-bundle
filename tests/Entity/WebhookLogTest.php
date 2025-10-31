<?php

namespace CheckoutPaymentBundle\Tests\Entity;

use CheckoutPaymentBundle\Entity\WebhookLog;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(WebhookLog::class)]
final class WebhookLogTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new WebhookLog();
    }

    public function testWebhookLogEntity(): void
    {
        $log = new WebhookLog();

        $this->assertNull($log->getId());
        $this->assertNull($log->getWebhookId());
        $this->assertNull($log->getEventType());
        $this->assertNull($log->getPayload());
        $this->assertNull($log->getSignature());
        $this->assertFalse($log->isSignatureValid());
        $this->assertEquals('pending', $log->getStatus());
        $this->assertNull($log->getErrorMessage());
        $this->assertNull($log->getProcessedData());
        $this->assertInstanceOf(\DateTimeImmutable::class, $log->getReceivedAt());
        $this->assertNull($log->getProcessedAt());

        $payload = false !== json_encode(['test' => 'data']) ? json_encode(['test' => 'data']) : '{}';
        self::assertIsString($payload);
        $signature = 'test_signature';

        $log->setWebhookId('whk_test123');
        $log->setEventType('payment_approved');
        $log->setPayload($payload);
        $log->setSignature($signature);
        $log->setSignatureValid(true);
        $log->setStatus('processed');
        $log->setErrorMessage(null);
        $log->setProcessedData(['payment_id' => 'pay_test123']);

        $this->assertEquals('whk_test123', $log->getWebhookId());
        $this->assertEquals('payment_approved', $log->getEventType());
        $this->assertEquals($payload, $log->getPayload());
        $this->assertEquals($signature, $log->getSignature());
        $this->assertTrue($log->isSignatureValid());
        $this->assertEquals('processed', $log->getStatus());
        $this->assertNull($log->getErrorMessage());
        $this->assertEquals(['payment_id' => 'pay_test123'], $log->getProcessedData());
    }

    public function testStatusUpdateTimestamps(): void
    {
        $log = new WebhookLog();
        $originalReceivedAt = $log->getReceivedAt();
        $this->assertNull($log->getProcessedAt());

        sleep(1);
        $log->setStatus('processed');

        $this->assertEquals($originalReceivedAt, $log->getReceivedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $log->getProcessedAt());
        $this->assertGreaterThan($originalReceivedAt, $log->getProcessedAt());
    }

    /** @return iterable<string, array{string, mixed}> */
    public static function propertiesProvider(): iterable
    {
        $payload = false !== json_encode(['test' => 'data']) ? json_encode(['test' => 'data']) : '{}';
        self::assertIsString($payload);
        $signature = 'test_signature';

        return [
            'webhookId' => ['webhookId', 'whk_test123'],
            'eventType' => ['eventType', 'payment_approved'],
            'payload' => ['payload', $payload],
            'signature' => ['signature', $signature],
            'signatureValid' => ['signatureValid', true],
            'status' => ['status', 'processed'],
            'errorMessage' => ['errorMessage', null],
            'processedData' => ['processedData', ['payment_id' => 'pay_test123']],
        ];
    }
}
