<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\Exception;

class CheckoutApiException extends \RuntimeException
{
    public static function requestFailed(string $message, ?\Throwable $previous = null): self
    {
        return new self($message, 0, $previous);
    }

    public static function responseError(string $message, ?\Throwable $previous = null): self
    {
        return new self($message, 0, $previous);
    }
}
