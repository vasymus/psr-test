<?php

namespace App\Service\Commission;

use App\Dto\CommissionResponseDto;
use App\Dto\TransactionDto;

interface CommissionFacadeInterface
{
    public function calculateCommission(TransactionDto $transactionDto): CommissionResponseDto;
}
