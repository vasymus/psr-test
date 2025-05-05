<?php

namespace App\Dto;

class ExchangeRateRequestDto
{
    private ?string $baseCurrency = null;
    private ?string $targetCurrency = null;

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
}
