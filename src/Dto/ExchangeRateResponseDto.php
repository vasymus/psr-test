<?php

namespace App\Dto;

class ExchangeRateResponseDto
{
    private ?bool $success = null;

    /**
     * @var string[]
     */
    private array $errors = [];

    /**
     * @var ExchangeRateDto[]
     */
    private array $rates = [];

    private ?ExchangeRateDto $rateItem = null;

    public function getRateItem(): ?ExchangeRateDto
    {
        return $this->rateItem;
    }

    public function setRateItem(?ExchangeRateDto $rateItem): self
    {
        $this->rateItem = $rateItem;
        return $this;
    }

    public function getSuccess(): ?bool
    {
        return $this->success;
    }

    public function setSuccess(?bool $success): self
    {
        $this->success = $success;
        return $this;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function setErrors(array $errors): self
    {
        $this->errors = $errors;
        return $this;
    }

    public function addError(string $error): self
    {
        $this->errors[] = $error;
        return $this;
    }

    public function getRates(): array
    {
        return $this->rates;
    }

    public function addRate(ExchangeRateDto $rate): self
    {
        $this->rates[] = $rate;
        return $this;
    }

    public function setRates(array $rates): self
    {
        $this->rates = $rates;
        return $this;
    }
}
