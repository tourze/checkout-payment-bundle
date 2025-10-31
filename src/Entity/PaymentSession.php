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
#[ORM\Table(name: 'checkout_payment_sessions', options: ['comment' => 'Checkout.com支付会话'])]
class PaymentSession implements \Stringable
{
    use SnowflakeKeyAware;
    use TimestampableAware;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true, options: ['comment' => '会话ID'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $sessionId = null;

    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '订单参考号'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $reference = null;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '支付金额'])]
    #[Assert\NotBlank]
    #[Assert\Positive]
    private ?int $amount = null;

    #[ORM\Column(type: Types::STRING, length: 3, options: ['comment' => '货币代码'])]
    #[Assert\NotBlank]
    #[Assert\Currency]
    #[Assert\Length(max: 3)]
    private ?string $currency = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '描述'])]
    #[Assert\Length(max: 1000)]
    private ?string $description = null;

    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '客户邮箱'])]
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 255)]
    private ?string $customerEmail = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '客户姓名'])]
    #[Assert\Length(max: 255)]
    private ?string $customerName = null;

    /** @var array<string, mixed>|null */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '账单地址'])]
    #[Assert\Valid]
    private ?array $billingAddress = null;

    #[ORM\Column(type: Types::STRING, length: 512, options: ['comment' => '成功回调URL'])]
    #[Assert\NotBlank]
    #[Assert\Url]
    #[Assert\Length(max: 512)]
    private ?string $successUrl = null;

    #[ORM\Column(type: Types::STRING, length: 512, options: ['comment' => '取消回调URL'])]
    #[Assert\NotBlank]
    #[Assert\Url]
    #[Assert\Length(max: 512)]
    private ?string $cancelUrl = null;

    #[ORM\Column(type: Types::STRING, length: 512, nullable: true, options: ['comment' => '失败回调URL'])]
    #[Assert\Url]
    #[Assert\Length(max: 512)]
    private ?string $failureUrl = null;

    #[ORM\Column(type: Types::STRING, length: 512, options: ['comment' => '支付页面URL'])]
    #[Assert\NotBlank]
    #[Assert\Url]
    #[Assert\Length(max: 512)]
    private ?string $paymentUrl = null;

    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '会话状态'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    private ?string $status = null;

    /** @var array<string, mixed>|null */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '元数据'])]
    #[Assert\Valid]
    private ?array $metadata = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '过期时间'])]
    #[Assert\DateTime]
    private ?\DateTimeImmutable $expireTime = null;

    /** @var Collection<int, Payment> */
    #[ORM\OneToMany(targetEntity: Payment::class, mappedBy: 'session')]
    private Collection $payments;

    public function __construct()
    {
        $this->status = 'pending';
        $this->payments = new ArrayCollection();
    }

    public function getSessionId(): ?string
    {
        return $this->sessionId;
    }

    public function setSessionId(string $sessionId): void
    {
        $this->sessionId = $sessionId;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): void
    {
        $this->reference = $reference;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getCustomerEmail(): ?string
    {
        return $this->customerEmail;
    }

    public function setCustomerEmail(string $customerEmail): void
    {
        $this->customerEmail = $customerEmail;
    }

    public function getCustomerName(): ?string
    {
        return $this->customerName;
    }

    public function setCustomerName(?string $customerName): void
    {
        $this->customerName = $customerName;
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

    public function getSuccessUrl(): ?string
    {
        return $this->successUrl;
    }

    public function setSuccessUrl(string $successUrl): void
    {
        $this->successUrl = $successUrl;
    }

    public function getCancelUrl(): ?string
    {
        return $this->cancelUrl;
    }

    public function setCancelUrl(string $cancelUrl): void
    {
        $this->cancelUrl = $cancelUrl;
    }

    public function getFailureUrl(): ?string
    {
        return $this->failureUrl;
    }

    public function setFailureUrl(?string $failureUrl): void
    {
        $this->failureUrl = $failureUrl;
    }

    public function getPaymentUrl(): ?string
    {
        return $this->paymentUrl;
    }

    public function setPaymentUrl(string $paymentUrl): void
    {
        $this->paymentUrl = $paymentUrl;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
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

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expireTime;
    }

    public function setExpiresAt(?\DateTimeImmutable $expiresAt): void
    {
        $this->expireTime = $expiresAt;
    }

    /**
     * @return Collection<int, Payment>
     */
    public function getPayments(): Collection
    {
        return $this->payments;
    }

    public function addPayment(Payment $payment): void
    {
        if (!$this->payments->contains($payment)) {
            $this->payments->add($payment);
            $payment->setSession($this);
        }
    }

    public function removePayment(Payment $payment): void
    {
        if ($this->payments->removeElement($payment)) {
            if ($payment->getSession() === $this) {
                $payment->setSession(null);
            }
        }
    }

    public function getSuccessfulPayment(): ?Payment
    {
        foreach ($this->payments as $payment) {
            if ($payment->isApproved() || $payment->isCaptured()) {
                return $payment;
            }
        }

        return null;
    }

    public function getLatestPayment(): ?Payment
    {
        if ($this->payments->isEmpty()) {
            return null;
        }

        $lastPayment = $this->payments->last();

        return false !== $lastPayment ? $lastPayment : null;
    }

    public function isPaid(): bool
    {
        return null !== $this->getSuccessfulPayment();
    }

    public function getTotalRefundedAmount(): int
    {
        $total = 0;
        foreach ($this->payments as $payment) {
            if ($payment->isRefunded()) {
                $total += $payment->getRefundedAmount() ?? ($payment->getAmount() ?? 0);
            }
        }

        return $total;
    }

    public function getNetAmount(): int
    {
        return ($this->amount ?? 0) - $this->getTotalRefundedAmount();
    }

    public function __toString(): string
    {
        return $this->sessionId ?? (string) $this->id;
    }
}
