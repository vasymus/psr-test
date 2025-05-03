<?php

namespace App\Service\Commission\ExchangeRate;

use App\Dto\ExchangeRateRequestDto;
use App\Dto\ExchangeRateResponseDto;

interface ExchangeRateContextInterface
{
    public function getRates(ExchangeRateRequestDto $exchangeRateRequestDto): ExchangeRateResponseDto;
}
