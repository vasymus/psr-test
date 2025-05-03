<?php

namespace App\Dto;

class BinDto
{
    private ?array $number = null;
    private ?string $scheme = null;
    private ?string $type = null;
    private ?string $brand = null;
    private ?BinCountryDto $country = null;
    private ?BinBankDto $bank = null;

    public function getNumber(): ?array
    {
        return $this->number;
    }

    public function setNumber(?array $v): self
    {
        $this->number = $v;
        return $this;
    }

    public function getScheme(): ?string
    {
        return $this->scheme;
    }

    public function setScheme(?string $v): self
    {
        $this->scheme = $v;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $v): self
    {
        $this->type = $v;
        return $this;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(?string $v): self
    {
        $this->brand = $v;
        return $this;
    }

    public function getCountry(): ?BinCountryDto
    {
        return $this->country;
    }

    public function setCountry(?BinCountryDto $v): self
    {
        $this->country = $v;
        return $this;
    }

    public function getBank(): ?BinBankDto
    {
        return $this->bank;
    }

    public function setBank(?BinBankDto $v): self
    {
        $this->bank = $v;
        return $this;
    }
}
