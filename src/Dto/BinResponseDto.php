<?php

namespace App\Dto;

class BinResponseDto
{
    private ?bool $success = null;

    /**
     * @var string[]
     */
    private array $errors = [];

    private ?BinDto $bin = null;

    public function getBin(): ?BinDto
    {
        return $this->bin;
    }

    public function setBin(?BinDto $bin): self
    {
        $this->bin = $bin;
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
}
