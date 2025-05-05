<?php

namespace App\Dto;

class BinRequestDto
{
    private ?string $bin = null;

    public function getBin(): ?string
    {
        return $this->bin;
    }

    public function setBin(?string $bin): self
    {
        $this->bin = $bin;
        return $this;
    }
}
