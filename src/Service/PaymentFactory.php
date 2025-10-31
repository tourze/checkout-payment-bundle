<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\Service;

use CheckoutPaymentBundle\Entity\Payment;
use CheckoutPaymentBundle\Entity\PaymentSession;

readonly class PaymentFactory
{
    /**
     * @param array<string, mixed> $apiResponse
     */
    public function createFromApiResponse(array $apiResponse, PaymentSession $session, string $reference): Payment
    {
        $payment = new Payment();
        $payment->setPaymentId(is_string($apiResponse['id'] ?? null) ? $apiResponse['id'] : '');
        $payment->setSession($session);
        $payment->setReference($reference);

        $this->setBasicFields($payment, $apiResponse);
        $this->setOptionalData($payment, $apiResponse);
        $this->setTimestamps($payment, $apiResponse);

        return $payment;
    }

    /**
     * @param array<string, mixed> $webhookData
     */
    public function updateFromWebhookData(Payment $payment, array $webhookData): void
    {
        $amount = 0;
        if (isset($webhookData['amount']) && is_numeric($webhookData['amount'])) {
            $amount = (int) $webhookData['amount'];
        }
        $payment->setAmount($amount);

        $payment->setCurrency(is_string($webhookData['currency'] ?? null) ? $webhookData['currency'] : 'USD');
        $payment->setStatus(is_string($webhookData['status'] ?? null) ? $webhookData['status'] : 'Unknown');
        $payment->setResponseSummary(is_string($webhookData['response_summary'] ?? null) ? $webhookData['response_summary'] : null);
        $payment->setResponseCode(is_string($webhookData['response_code'] ?? null) ? $webhookData['response_code'] : null);

        $this->setOptionalData($payment, $webhookData);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function setBasicFields(Payment $payment, array $data): void
    {
        $amount = 0;
        if (isset($data['amount']) && is_numeric($data['amount'])) {
            $amount = (int) $data['amount'];
        }
        $payment->setAmount($amount);

        $payment->setCurrency(is_string($data['currency'] ?? null) ? $data['currency'] : 'USD');
        $payment->setStatus(is_string($data['status'] ?? null) ? $data['status'] : 'Unknown');
        $payment->setResponseSummary(is_string($data['response_summary'] ?? null) ? $data['response_summary'] : null);
        $payment->setResponseCode(is_string($data['response_code'] ?? null) ? $data['response_code'] : null);
        $payment->setPaymentType(is_string($data['payment_type'] ?? null) ? $data['payment_type'] : null);
        $payment->setProcessingChannelId(is_string($data['processing_channel_id'] ?? null) ? $data['processing_channel_id'] : null);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function setOptionalData(Payment $payment, array $data): void
    {
        $payment->setSource(is_array($data['source'] ?? null) ? $this->ensureStringArray($data['source']) : null);
        $payment->setCustomer(is_array($data['customer'] ?? null) ? $this->ensureStringArray($data['customer']) : null);
        $payment->setBillingAddress(is_array($data['billing_address'] ?? null) ? $this->ensureStringArray($data['billing_address']) : null);
        $payment->setShippingAddress(is_array($data['shipping_address'] ?? null) ? $this->ensureStringArray($data['shipping_address']) : null);
        $payment->setRisk(is_array($data['risk'] ?? null) ? $this->ensureStringArray($data['risk']) : null);
        $payment->setMetadata(is_array($data['metadata'] ?? null) ? $this->ensureStringArray($data['metadata']) : null);
        $payment->setLinks(is_array($data['_links'] ?? null) ? $this->ensureStringArray($data['_links']) : null);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function setTimestamps(Payment $payment, array $data): void
    {
        if (isset($data['approved_on']) && is_string($data['approved_on'])) {
            $payment->setApprovedAt(new \DateTimeImmutable($data['approved_on']));
            $payment->setApproved(true);
        }

        if (isset($data['expires_on']) && is_string($data['expires_on'])) {
            $payment->setExpiresAt(new \DateTimeImmutable($data['expires_on']));
        }
    }

    /**
     * @param array<mixed, mixed> $array
     * @return array<string, mixed>
     */
    private function ensureStringArray(array $array): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            if (is_string($key)) {
                $result[$key] = $value;
            } elseif (is_int($key)) {
                $result[(string) $key] = $value;
            }
        }

        /** @var array<string, mixed> */
        return $result;
    }
}
