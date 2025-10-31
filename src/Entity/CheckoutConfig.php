<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;

#[ORM\Entity]
#[ORM\Table(name: 'checkout_config', options: ['comment' => 'Checkout支付配置'])]
class CheckoutConfig implements \Stringable
{
    use SnowflakeKeyAware;
    use TimestampableAware;

    #[ORM\Column(length: 255, unique: true, options: ['comment' => '配置名称'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $name;

    #[ORM\Column(type: Types::TEXT, options: ['comment' => '配置描述'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 10000)]
    private string $description;

    #[ORM\Column(length: 255, options: ['comment' => 'API密钥'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $apiKey;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true, 'comment' => '是否启用'])]
    #[Assert\NotNull]
    private bool $enabled = true;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true, 'comment' => '是否沙箱环境'])]
    #[Assert\NotNull]
    private bool $isSandbox = true;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 30, 'comment' => '超时时间'])]
    #[Assert\NotNull]
    #[Assert\Positive]
    private int $timeout = 30;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 3, 'comment' => '重试次数'])]
    #[Assert\NotNull]
    #[Assert\Positive]
    private int $retryAttempts = 3;

    /** @var array<string, mixed>|null */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '额外配置'])]
    #[Assert\Valid]
    private ?array $extraConfig = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false, 'comment' => '是否默认配置'])]
    #[Assert\NotNull]
    private bool $isDefault = false;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function setApiKey(string $apiKey): void
    {
        $this->apiKey = $apiKey;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function isSandbox(): bool
    {
        return $this->isSandbox;
    }

    public function setSandbox(bool $isSandbox): void
    {
        $this->isSandbox = $isSandbox;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    public function setTimeout(int $timeout): void
    {
        $this->timeout = $timeout;
    }

    public function getRetryAttempts(): int
    {
        return $this->retryAttempts;
    }

    public function setRetryAttempts(int $retryAttempts): void
    {
        $this->retryAttempts = $retryAttempts;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getExtraConfig(): ?array
    {
        return $this->extraConfig;
    }

    /**
     * @param array<string, mixed>|null $extraConfig
     */
    public function setExtraConfig(?array $extraConfig): void
    {
        $this->extraConfig = $extraConfig;
    }

    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    public function setDefault(bool $isDefault): void
    {
        $this->isDefault = $isDefault;
    }

    public function getApiUrl(): string
    {
        return $this->isSandbox
            ? 'https://api.sandbox.checkout.com'
            : 'https://api.checkout.com';
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
