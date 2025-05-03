<?php

namespace App\Service\Commission\CommissionRate;

use App\Service\Commission\CommissionConfig;

readonly class CommissionRate implements CommissionRateInterface
{
    public function __construct(private CommissionConfig $config,)
    {
    }

    public function getCommissionRate(string $countryA2): float
    {
        $isEu = in_array($countryA2, $this->config->getEuCountriesA2());

        return $isEu ? $this->config->getEuCommissionRate() : $this->config->getNonEuCommissionRate();
    }
}
