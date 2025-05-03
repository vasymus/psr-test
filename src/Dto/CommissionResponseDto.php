<?php

namespace App\Dto;

class CommissionResponseDto
{
    private ?bool $success = null;
    private ?array $errors = null;
    private ?float $amount = null;

    public function getSuccess(): ?bool
    {
        return $this->success;
    }

    public function setSuccess(?bool $success): self
    {
        $this->success = $success;
        return $this;
    }

    public function getErrors(): ?array
    {
        return $this->errors;
    }

    public function addError(string $error): self
    {
        $this->errors[] = $error;
        return $this;
    }

    public function setErrors(?array $errors): self
    {
        $this->errors = $errors;
        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount): self
    {
        $this->amount = $amount;
        return $this;
    }
}
