<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;

#[ORM\Entity]
#[ORM\Table(name: 'checkout_webhook_logs', options: ['comment' => 'Checkout.com Webhook日志记录'])]
class WebhookLog implements \Stringable
{
    use SnowflakeKeyAware;
    use TimestampableAware;

    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => 'Webhook ID'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $webhookId = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '事件ID'])]
    #[Assert\Length(max: 255)]
    private ?string $eventId = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '支付ID'])]
    #[Assert\Length(max: 255)]
    private ?string $paymentId = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false, 'comment' => '是否已处理'])]
    #[Assert\NotNull]
    private bool $processed = false;

    /** @var array<string, string>|null */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '请求头'])]
    #[Assert\Valid]
    private ?array $requestHeaders = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '请求体'])]
    #[Assert\Length(max: 10000)]
    private ?string $requestBody = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['comment' => '响应状态码'])]
    #[Assert\Positive]
    private ?int $responseStatus = null;

    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '事件类型'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private ?string $eventType = null;

    #[ORM\Column(type: Types::TEXT, options: ['comment' => '负载数据'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 10000)]
    private ?string $payload = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '签名'])]
    #[Assert\Length(max: 255)]
    private ?string $signature = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['comment' => '签名是否有效'])]
    #[Assert\Type(type: 'bool')]
    private bool $signatureValid = false;

    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '处理状态'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    private ?string $status = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '错误信息'])]
    #[Assert\Length(max: 2000)]
    private ?string $errorMessage = null;

    /** @var array<string, mixed>|null */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '处理后的数据'])]
    #[Assert\Valid]
    private ?array $processedData = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => '接收时间'])]
    #[Assert\DateTime]
    private ?\DateTimeImmutable $receiveTime = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '处理时间'])]
    #[Assert\DateTime]
    private ?\DateTimeImmutable $processedTime = null;

    public function __construct()
    {
        $this->receiveTime = new \DateTimeImmutable();
        $this->status = 'pending';
    }

    public function getWebhookId(): ?string
    {
        return $this->webhookId;
    }

    public function setWebhookId(string $webhookId): void
    {
        $this->webhookId = $webhookId;
    }

    public function getEventType(): ?string
    {
        return $this->eventType;
    }

    public function setEventType(string $eventType): void
    {
        $this->eventType = $eventType;
    }

    public function getPayload(): ?string
    {
        return $this->payload;
    }

    public function setPayload(string $payload): void
    {
        $this->payload = $payload;
    }

    public function getSignature(): ?string
    {
        return $this->signature;
    }

    public function setSignature(?string $signature): void
    {
        $this->signature = $signature;
    }

    public function isSignatureValid(): bool
    {
        return $this->signatureValid;
    }

    public function setSignatureValid(bool $signatureValid): void
    {
        $this->signatureValid = $signatureValid;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
        $this->processedTime = new \DateTimeImmutable();
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage(?string $errorMessage): void
    {
        $this->errorMessage = $errorMessage;
    }

    /** @return array<string, mixed>|null */
    public function getProcessedData(): ?array
    {
        return $this->processedData;
    }

    /** @param array<string, mixed>|null $processedData */
    public function setProcessedData(?array $processedData): void
    {
        $this->processedData = $processedData;
    }

    public function getReceivedAt(): ?\DateTimeImmutable
    {
        return $this->receiveTime;
    }

    public function getProcessedAt(): ?\DateTimeImmutable
    {
        return $this->processedTime;
    }

    public function getEventId(): ?string
    {
        return $this->eventId;
    }

    public function setEventId(?string $eventId): void
    {
        $this->eventId = $eventId;
    }

    public function getPaymentId(): ?string
    {
        return $this->paymentId;
    }

    public function setPaymentId(?string $paymentId): void
    {
        $this->paymentId = $paymentId;
    }

    public function isProcessed(): bool
    {
        return $this->processed;
    }

    public function setProcessed(bool $processed): void
    {
        $this->processed = $processed;
    }

    /** @return array<string, string>|null */
    public function getRequestHeaders(): ?array
    {
        return $this->requestHeaders;
    }

    /** @param array<string, string>|null $requestHeaders */
    public function setRequestHeaders(?array $requestHeaders): void
    {
        $this->requestHeaders = $requestHeaders;
    }

    public function getRequestBody(): ?string
    {
        return $this->requestBody;
    }

    public function setRequestBody(?string $requestBody): void
    {
        $this->requestBody = $requestBody;
    }

    public function getResponseStatus(): ?int
    {
        return $this->responseStatus;
    }

    public function setResponseStatus(?int $responseStatus): void
    {
        $this->responseStatus = $responseStatus;
    }

    public function __toString(): string
    {
        return $this->webhookId ?? (string) $this->id;
    }
}
