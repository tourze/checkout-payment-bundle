<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;

#[ORM\Entity]
#[ORM\Table(name: 'checkout_payments', options: ['comment' => 'Checkout.com支付记录'])]
class Payment implements \Stringable
{
    use SnowflakeKeyAware;
    use TimestampableAware;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true, options: ['comment' => '支付ID'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $paymentId = null;

    #[ORM\ManyToOne(targetEntity: PaymentSession::class, inversedBy: 'payments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?PaymentSession $session = null;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '支付金额（最小单位）'])]
    #[Assert\NotBlank]
    #[Assert\Positive]
    private ?int $amount = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['comment' => '已退款金额'])]
    #[Assert\PositiveOrZero]
    private ?int $refundedAmount = null;

    #[ORM\Column(type: Types::STRING, length: 3, options: ['comment' => '货币代码'])]
    #[Assert\NotBlank]
    #[Assert\Currency]
    #[Assert\Length(max: 3)]
    private ?string $currency = null;

    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '订单参考号'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $reference = null;

    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '支付状态'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    private ?string $status = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '响应摘要'])]
    #[Assert\Length(max: 255)]
    private ?string $responseSummary = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true, options: ['comment' => '响应代码'])]
    #[Assert\Length(max: 50)]
    private ?string $responseCode = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true, options: ['comment' => '支付类型'])]
    #[Assert\Length(max: 50)]
    private ?string $paymentType = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '处理渠道ID'])]
    #[Assert\Length(max: 255)]
    private ?string $processingChannelId = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '商户发起标识'])]
    #[Assert\Length(max: 255)]
    private ?string $merchantInitiated = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '目标ID'])]
    #[Assert\Length(max: 255)]
    private ?string $destinationId = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '方案ID'])]
    #[Assert\Length(max: 255)]
    private ?string $schemeId = null;

    /** @var array<string, mixed>|null */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '支付来源信息'])]
    #[Assert\Valid]
    private ?array $source = null;

    /** @var array<string, mixed>|null */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '客户信息'])]
    #[Assert\Valid]
    private ?array $customer = null;

    /** @var array<string, mixed>|null */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '账单地址'])]
    #[Assert\Valid]
    private ?array $billingAddress = null;

    /** @var array<string, mixed>|null */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '配送地址'])]
    #[Assert\Valid]
    private ?array $shippingAddress = null;

    /** @var array<string, mixed>|null */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '风险评估'])]
    #[Assert\Valid]
    private ?array $risk = null;

    /** @var array<string, mixed>|null */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '元数据'])]
    #[Assert\Valid]
    private ?array $metadata = null;

    /** @var array<string, mixed>|null */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '相关链接'])]
    #[Assert\Valid]
    private ?array $links = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '处理时间'])]
    #[Assert\DateTime]
    private ?\DateTimeImmutable $processedOn = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['comment' => '是否启用3DS'])]
    #[Assert\Type(type: 'bool')]
    private bool $is3ds = false;

    #[ORM\Column(type: Types::BOOLEAN, options: ['comment' => '是否已批准'])]
    #[Assert\Type(type: 'bool')]
    private bool $approved = false;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '授权码'])]
    #[Assert\Length(max: 255)]
    private ?string $authCode = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '处理时间'])]
    #[Assert\DateTime]
    private ?\DateTimeImmutable $processedTime = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '批准时间'])]
    #[Assert\DateTime]
    private ?\DateTimeImmutable $approvedTime = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '捕获时间'])]
    #[Assert\DateTime]
    private ?\DateTimeImmutable $capturedTime = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '退款时间'])]
    #[Assert\DateTime]
    private ?\DateTimeImmutable $refundedTime = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '作废时间'])]
    #[Assert\DateTime]
    private ?\DateTimeImmutable $voidedTime = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '过期时间'])]
    #[Assert\DateTime]
    private ?\DateTimeImmutable $expireTime = null;

    /** @var Collection<int, PaymentRefund> */
    #[ORM\OneToMany(targetEntity: PaymentRefund::class, mappedBy: 'payment')]
    private Collection $refunds;

    public function __construct()
    {
        $this->refunds = new ArrayCollection();
    }

    public function getPaymentId(): ?string
    {
        return $this->paymentId;
    }

    public function setPaymentId(string $paymentId): void
    {
        $this->paymentId = $paymentId;
    }

    public function getSession(): ?PaymentSession
    {
        return $this->session;
    }

    public function setSession(?PaymentSession $session): void
    {
        $this->session = $session;
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

    public function setReference(string $reference): void
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

    /** @return array<string, mixed>|null */
    public function getSource(): ?array
    {
        return $this->source;
    }

    /** @param array<string, mixed>|null $source */
    public function setSource(?array $source): void
    {
        $this->source = $source;
    }

    /** @return array<string, mixed>|null */
    public function getCustomer(): ?array
    {
        return $this->customer;
    }

    /** @param array<string, mixed>|null $customer */
    public function setCustomer(?array $customer): void
    {
        $this->customer = $customer;
    }

    /** @return array<string, mixed>|null */
    public function getBillingAddress(): ?array
    {
        return $this->billingAddress;
    }

    /** @param array<string, mixed>|null $billingAddress */
    public function setBillingAddress(?array $billingAddress): void
    {
        $this->billingAddress = $billingAddress;
    }

    /** @return array<string, mixed>|null */
    public function getRisk(): ?array
    {
        return $this->risk;
    }

    /** @param array<string, mixed>|null $risk */
    public function setRisk(?array $risk): void
    {
        $this->risk = $risk;
    }

    public function getProcessedOn(): ?\DateTimeImmutable
    {
        return $this->processedOn;
    }

    public function setProcessedOn(?\DateTimeImmutable $processedOn): void
    {
        $this->processedOn = $processedOn;
    }

    public function is3ds(): bool
    {
        return $this->is3ds;
    }

    public function set3ds(bool $is3ds): void
    {
        $this->is3ds = $is3ds;
    }

    public function isApproved(): bool
    {
        return $this->approved;
    }

    public function setApproved(bool $approved): void
    {
        $this->approved = $approved;
    }

    public function getAuthCode(): ?string
    {
        return $this->authCode;
    }

    public function setAuthCode(?string $authCode): void
    {
        $this->authCode = $authCode;
    }

    public function getProcessedAt(): ?\DateTimeImmutable
    {
        return $this->processedTime;
    }

    public function getRefundedAmount(): ?int
    {
        return $this->refundedAmount;
    }

    public function setRefundedAmount(?int $refundedAmount): void
    {
        $this->refundedAmount = $refundedAmount;
    }

    public function getPaymentType(): ?string
    {
        return $this->paymentType;
    }

    public function setPaymentType(?string $paymentType): void
    {
        $this->paymentType = $paymentType;
    }

    public function getProcessingChannelId(): ?string
    {
        return $this->processingChannelId;
    }

    public function setProcessingChannelId(?string $processingChannelId): void
    {
        $this->processingChannelId = $processingChannelId;
    }

    public function getMerchantInitiated(): ?string
    {
        return $this->merchantInitiated;
    }

    public function setMerchantInitiated(?string $merchantInitiated): void
    {
        $this->merchantInitiated = $merchantInitiated;
    }

    public function getDestinationId(): ?string
    {
        return $this->destinationId;
    }

    public function setDestinationId(?string $destinationId): void
    {
        $this->destinationId = $destinationId;
    }

    public function getSchemeId(): ?string
    {
        return $this->schemeId;
    }

    public function setSchemeId(?string $schemeId): void
    {
        $this->schemeId = $schemeId;
    }

    /** @return array<string, mixed>|null */
    public function getShippingAddress(): ?array
    {
        return $this->shippingAddress;
    }

    /** @param array<string, mixed>|null $shippingAddress */
    public function setShippingAddress(?array $shippingAddress): void
    {
        $this->shippingAddress = $shippingAddress;
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

    /** @return array<string, mixed>|null */
    public function getLinks(): ?array
    {
        return $this->links;
    }

    /** @param array<string, mixed>|null $links */
    public function setLinks(?array $links): void
    {
        $this->links = $links;
    }

    public function getApprovedAt(): ?\DateTimeImmutable
    {
        return $this->approvedTime;
    }

    public function setApprovedAt(?\DateTimeImmutable $approvedAt): void
    {
        $this->approvedTime = $approvedAt;
    }

    public function getCapturedAt(): ?\DateTimeImmutable
    {
        return $this->capturedTime;
    }

    public function setCapturedAt(?\DateTimeImmutable $capturedAt): void
    {
        $this->capturedTime = $capturedAt;
    }

    public function getRefundedAt(): ?\DateTimeImmutable
    {
        return $this->refundedTime;
    }

    public function setRefundedAt(?\DateTimeImmutable $refundedAt): void
    {
        $this->refundedTime = $refundedAt;
    }

    public function getVoidedAt(): ?\DateTimeImmutable
    {
        return $this->voidedTime;
    }

    public function setVoidedAt(?\DateTimeImmutable $voidedAt): void
    {
        $this->voidedTime = $voidedAt;
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expireTime;
    }

    public function setExpiresAt(?\DateTimeImmutable $expiresAt): void
    {
        $this->expireTime = $expiresAt;
    }

    public function isCaptured(): bool
    {
        return 'Captured' === $this->status && null !== $this->capturedTime;
    }

    public function isRefunded(): bool
    {
        return 'Refunded' === $this->status && null !== $this->refundedTime;
    }

    public function isFullyRefunded(): bool
    {
        $refundedAmount = $this->getRefundedAmount() ?? $this->getTotalRefundedAmount();

        return $refundedAmount >= $this->amount;
    }

    public function isVoided(): bool
    {
        return 'Voided' === $this->status && null !== $this->voidedTime;
    }

    public function canCapture(): bool
    {
        return $this->isApproved() && !$this->isCaptured() && !$this->isVoided();
    }

    public function canRefund(): bool
    {
        return ($this->isCaptured() || 'Partially Refunded' === $this->getStatus()) && !$this->isFullyRefunded();
    }

    public function canVoid(): bool
    {
        return $this->isApproved() && !$this->isCaptured() && !$this->isVoided();
    }

    public function getAvailableRefundAmount(): int
    {
        if (!$this->canRefund()) {
            return 0;
        }

        // 在退款过程中，使用 refundedAmount 属性
        $refundedAmount = $this->getRefundedAmount() ?? 0;

        return max(0, ($this->amount ?? 0) - $refundedAmount);
    }

    /** @return Collection<int, PaymentRefund> */
    public function getRefunds(): Collection
    {
        return $this->refunds;
    }

    public function addRefund(PaymentRefund $refund): void
    {
        if (!$this->refunds->contains($refund)) {
            $this->refunds->add($refund);
            $refund->setPayment($this);
        }
    }

    public function removeRefund(PaymentRefund $refund): void
    {
        if ($this->refunds->removeElement($refund)) {
            if ($refund->getPayment() === $this) {
                $refund->setPayment(null);
            }
        }
    }

    /** @return Collection<int, PaymentRefund> */
    public function getApprovedRefunds(): Collection
    {
        return $this->refunds->filter(function ($refund) {
            return $refund->isApproved();
        });
    }

    public function getTotalRefundedAmount(): int
    {
        $total = 0;
        foreach ($this->getApprovedRefunds() as $refund) {
            $total += $refund->getAmount() ?? 0;
        }

        return $total;
    }

    /** @return Collection<int, PaymentRefund> */
    public function getPendingRefunds(): Collection
    {
        return $this->refunds->filter(function ($refund) {
            return $refund->isPending();
        });
    }

    public function __toString(): string
    {
        return $this->paymentId ?? (string) $this->id;
    }

    /** @return Collection<int, PaymentRefund> */
    public function getFailedRefunds(): Collection
    {
        return $this->refunds->filter(function ($refund) {
            return $refund->isFailed();
        });
    }
}
