<?php

declare(strict_types=1);

namespace CheckoutPaymentBundle\Service;

use CheckoutPaymentBundle\Entity\PaymentSession;

readonly class PaymentSessionFactory
{
    /** @param array<string, mixed> $data */
    public function createFromData(array $data): PaymentSession
    {
        $session = new PaymentSession();
        $this->setBasicSessionFields($session, $data);
        $this->setCustomerSessionFields($session, $data);
        $this->setUrlSessionFields($session, $data);
        $this->setOptionalSessionData($session, $data);

        return $session;
    }

    /** @param array<string, mixed> $data */
    private function setBasicSessionFields(PaymentSession $session, array $data): void
    {
        $session->setReference(is_string($data['reference'] ?? null) ? $data['reference'] : '');
        $session->setAmount(is_numeric($data['amount'] ?? null) ? (int) $data['amount'] : 0);
        $session->setCurrency(is_string($data['currency'] ?? null) ? $data['currency'] : 'USD');
        $session->setDescription(isset($data['description']) && is_string($data['description']) ? $data['description'] : null);
    }

    /** @param array<string, mixed> $data */
    private function setCustomerSessionFields(PaymentSession $session, array $data): void
    {
        $session->setCustomerEmail(is_string($data['customer_email'] ?? null) ? $data['customer_email'] : '');
        $session->setCustomerName(isset($data['customer_name']) && is_string($data['customer_name']) ? $data['customer_name'] : null);
    }

    /** @param array<string, mixed> $data */
    private function setUrlSessionFields(PaymentSession $session, array $data): void
    {
        $session->setSuccessUrl(is_string($data['success_url'] ?? null) ? $data['success_url'] : '');
        $session->setCancelUrl(is_string($data['cancel_url'] ?? null) ? $data['cancel_url'] : '');
        $session->setFailureUrl(isset($data['failure_url']) && is_string($data['failure_url']) ? $data['failure_url'] : null);
    }

    /** @param array<string, mixed> $data */
    private function setOptionalSessionData(PaymentSession $session, array $data): void
    {
        if (isset($data['billing_address']) && is_array($data['billing_address'])) {
            $session->setBillingAddress($this->ensureStringArray($data['billing_address']));
        }

        if (isset($data['metadata']) && is_array($data['metadata'])) {
            $session->setMetadata($this->ensureStringArray($data['metadata']));
        }
    }

    /**
     * @param array<mixed, mixed> $array
     * @return array<string, mixed>
     */
    private function ensureStringArray(array $array): array
    {
        /** @var array<string, mixed> $result */
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
