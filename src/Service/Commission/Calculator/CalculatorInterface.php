<?php

namespace App\Service\Commission\Calculator;

use App\Dto\CommissionResponseDto;
use App\Dto\TransactionDto;

interface CalculatorInterface
{
    public function calculateCommission(TransactionDto $transactionDto): CommissionResponseDto;
}
