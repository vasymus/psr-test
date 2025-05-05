<?php

namespace App\Service\Commission\ExchangeRate;

use App\Dto\ExchangeRateRequestDto;
use App\Dto\ExchangeRateResponseDto;
use App\Service\Commission\ExchangeRate\Strategy\ExchangeRateGetterInterface;

class ExchangeRateContext implements ExchangeRateContextInterface
{
    /**
     * @param iterable<ExchangeRateGetterInterface> $strategies
     */
    public function __construct(protected iterable $strategies)
    {
    }

    public function getRates(ExchangeRateRequestDto $exchangeRateRequestDto): ExchangeRateResponseDto
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->supports($exchangeRateRequestDto)) {
                return $strategy->getRateItem($exchangeRateRequestDto);
            }
        }

        throw new \LogicException('Failed to get exchange rates.');
    }
}
