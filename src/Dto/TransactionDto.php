<?php

namespace App\Dto;

class TransactionDto
{
    private ?string $bin = null;
    private ?int $amount = null;
    private ?string $baseCurrency = null;
    private ?string $targetCurrency = null;

    public function getBin(): ?string
    {
        return $this->bin;
    }

    public function setBin(?string $bin): self
    {
        $this->bin = $bin;
        return $this;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(?int $amount): self
    {
        $this->amount = $amount;
        return $this;
    }

    public function getBaseCurrency(): ?string
    {
        return $this->baseCurrency;
    }

    public function setBaseCurrency(?string $baseCurrency): self
    {
        $this->baseCurrency = $baseCurrency;
        return $this;
    }

    public function getTargetCurrency(): ?string
    {
        return $this->targetCurrency;
    }

    public function setTargetCurrency(?string $targetCurrency): self
    {
        $this->targetCurrency = $targetCurrency;
        return $this;
    }

    public static function fromArray(array $data): self
    {
        return new self()
            ->setBin($data['bin'] ?? null)
            ->setAmount(isset($data['amount']) ? (int)$data['amount'] : null)
            ->setBaseCurrency($data['base'] ?? null)
            ->setTargetCurrency($data['currency'] ?? null);
    }
}
