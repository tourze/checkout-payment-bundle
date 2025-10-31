<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;

#[ORM\Entity]
#[ORM\Table(name: 'checkout_payment_refunds', options: ['comment' => 'Checkout.com支付退款记录'])]
class PaymentRefund implements \Stringable
{
    use SnowflakeKeyAware;
    use TimestampableAware;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true, options: ['comment' => '退款ID'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $refundId = null;

    #[ORM\ManyToOne(targetEntity: Payment::class, inversedBy: 'refunds')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Payment $payment = null;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '退款金额'])]
    #[Assert\NotBlank]
    #[Assert\Positive]
    private ?int $amount = null;

    #[ORM\Column(type: Types::STRING, length: 3, options: ['comment' => '货币代码'])]
    #[Assert\NotBlank]
    #[Assert\Currency]
    #[Assert\Length(max: 3)]
    private ?string $currency = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '退款参考号'])]
    #[Assert\Length(max: 255)]
    private ?string $reference = null;

    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '退款状态'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    private ?string $status = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '响应摘要'])]
    #[Assert\Length(max: 255)]
    private ?string $responseSummary = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true, options: ['comment' => '响应代码'])]
    #[Assert\Length(max: 50)]
    private ?string $responseCode = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '退款原因'])]
    #[Assert\Length(max: 2000)]
    private ?string $reason = null;

    /** @var array<string, mixed>|null */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '元数据'])]
    #[Assert\Valid]
    private ?array $metadata = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '处理时间'])]
    #[Assert\DateTime]
    private ?\DateTimeImmutable $processedTime = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false, 'comment' => '是否已批准'])]
    #[Assert\NotNull]
    private bool $approved = false;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '关联支付ID'])]
    #[Assert\Length(max: 255)]
    private ?string $paymentId = null;

    public function __construct()
    {
        $this->status = 'pending';
    }

    public function getRefundId(): ?string
    {
        return $this->refundId;
    }

    public function setRefundId(string $refundId): void
    {
        $this->refundId = $refundId;
    }

    public function getPayment(): ?Payment
    {
        return $this->payment;
    }

    public function setPayment(?Payment $payment): void
    {
        $this->payment = $payment;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): void
    {
        $this->reference = $reference;
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

    public function getResponseSummary(): ?string
    {
        return $this->responseSummary;
    }

    public function setResponseSummary(?string $responseSummary): void
    {
        $this->responseSummary = $responseSummary;
    }

    public function getResponseCode(): ?string
    {
        return $this->responseCode;
    }

    public function setResponseCode(?string $responseCode): void
    {
        $this->responseCode = $responseCode;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): void
    {
        $this->reason = $reason;
    }

    /** @return array<string, mixed>|null */
    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    /** @param array<string, mixed>|null $metadata */
    public function setMetadata(?array $metadata): void
    {
        $this->metadata = $metadata;
    }

    public function getProcessedAt(): ?\DateTimeImmutable
    {
        return $this->processedTime;
    }

    public function getPaymentId(): ?string
    {
        return $this->paymentId;
    }

    public function setPaymentId(?string $paymentId): void
    {
        $this->paymentId = $paymentId;
    }

    public function getApproved(): bool
    {
        return $this->approved;
    }

    public function setApproved(bool $approved): void
    {
        $this->approved = $approved;
    }

    public function isApproved(): bool
    {
        return 'Approved' === $this->status || $this->approved;
    }

    public function isPending(): bool
    {
        return 'pending' === $this->status;
    }

    public function isFailed(): bool
    {
        return 'Failed' === $this->status;
    }

    public function __toString(): string
    {
        return $this->refundId ?? (string) $this->id;
    }
}
