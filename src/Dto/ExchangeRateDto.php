<?php

namespace App\Dto;

class ExchangeRateDto
{
    private ?string $baseCurrency = null;
    private ?string $targetCurrency = null;
    private ?\DateTimeInterface $date = null;
    private ?float $rate = null;

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

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): self
    {
        $this->date = $date;
        return $this;
    }

    public function getRate(): ?float
    {
        return $this->rate;
    }

    public function setRate(?float $rate): self
    {
        $this->rate = $rate;
        return $this;
    }
}
