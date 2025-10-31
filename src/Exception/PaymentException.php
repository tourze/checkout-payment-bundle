<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\Exception;

class PaymentException extends \RuntimeException
{
    public static function paymentNotFound(string $paymentId): self
    {
        return new self('Payment not found: ' . $paymentId);
    }

    public static function cannotRefund(string $status): self
    {
        return new self('Payment cannot be refunded. Current status: ' . $status);
    }

    public static function refundAmountExceedsAvailable(): self
    {
        return new self('Refund amount exceeds available refund amount');
    }

    public static function paymentSessionNotFound(string $sessionId): self
    {
        return new self('Payment session not found: ' . $sessionId);
    }

    public static function invalidPaymentStatus(string $expected, string $actual): self
    {
        return new self(sprintf('Expected payment status "%s", got "%s"', $expected, $actual));
    }

    public static function webhookProcessingFailed(string $message): self
    {
        return new self('Webhook processing failed: ' . $message);
    }

    public static function apiError(string $message): self
    {
        return new self('API error: ' . $message);
    }

    public static function invalidWebhookSignature(): self
    {
        return new self('Invalid webhook signature');
    }

    public static function methodNotImplemented(string $method): self
    {
        return new self('Method not implemented: ' . $method);
    }
}
