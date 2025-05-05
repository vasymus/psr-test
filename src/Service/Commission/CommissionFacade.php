<?php

namespace App\Service\Commission;

use App\Dto\CommissionResponseDto;
use App\Dto\TransactionDto;

readonly class CommissionFacade implements CommissionFacadeInterface
{
    public function __construct(private CommissionFactory $factory)
    {
    }

    public function calculateCommission(TransactionDto $transactionDto): CommissionResponseDto
    {
        return $this->factory->createCalculator()->calculateCommission($transactionDto);
    }
}
