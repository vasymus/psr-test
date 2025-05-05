<?php

namespace App\Service\Commission\ExchangeRate\Strategy;

use App\Dto\ExchangeRateRequestDto;
use App\Dto\ExchangeRateResponseDto;

interface ExchangeRateGetterInterface
{
    public function supports(ExchangeRateRequestDto $exchangeRateRequestDto): bool;
    public function getRateItem(ExchangeRateRequestDto $exchangeRateRequestDto): ExchangeRateResponseDto;
}
