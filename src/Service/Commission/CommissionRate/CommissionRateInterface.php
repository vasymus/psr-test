<?php

namespace App\Service\Commission\CommissionRate;

interface CommissionRateInterface
{
    public function getCommissionRate(string $countryA2): float;
}
