<?php

namespace App\Services\Payment;

class PaymentResult
{
    private $successful;

    private $gateway;

    private $transactionId;

    private $message;

    private function __construct(bool $successful, string $gateway, ?string $transactionId, ?string $message)
    {
        $this->successful = $successful;
        $this->gateway = $gateway;
        $this->transactionId = $transactionId;
        $this->message = $message;
    }

    public static function approved(string $gateway, string $transactionId): self
    {
        return new self(true, $gateway, $transactionId, null);
    }

    public static function declined(string $gateway, ?string $transactionId, string $message): self
    {
        return new self(false, $gateway, $transactionId, $message);
    }

    public function successful(): bool
    {
        return $this->successful;
    }

    public function gateway(): string
    {
        return $this->gateway;
    }

    public function transactionId(): ?string
    {
        return $this->transactionId;
    }

    public function message(): ?string
    {
        return $this->message;
    }
}
