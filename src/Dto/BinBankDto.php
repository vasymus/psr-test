<?php

namespace App\Dto;

class BinBankDto
{
    private ?string $name = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $v): self
    {
        $this->name = $v;
        return $this;
    }
}
