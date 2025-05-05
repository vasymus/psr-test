<?php

namespace App\Dto;

class BinCountryDto
{
    private ?string $numeric = null;
    private ?string $alpha2 = null;
    private ?string $name = null;
    private ?string $emoji = null;
    private ?string $currency = null;
    private ?float $latitude = null;
    private ?float $longitude = null;

    public function getNumeric(): ?string
    {
        return $this->numeric;
    }

    public function setNumeric(?string $v): self
    {
        $this->numeric = $v;
        return $this;
    }

    public function getAlpha2(): ?string
    {
        return $this->alpha2;
    }

    public function setAlpha2(?string $v): self
    {
        $this->alpha2 = $v;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $v): self
    {
        $this->name = $v;
        return $this;
    }

    public function getEmoji(): ?string
    {
        return $this->emoji;
    }

    public function setEmoji(?string $v): self
    {
        $this->emoji = $v;
        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(?string $v): self
    {
        $this->currency = $v;
        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $v): self
    {
        $this->latitude = $v;
        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $v): self
    {
        $this->longitude = $v;
        return $this;
    }
}
