<?php

namespace App\Service\Commission;

use App\Service\Config\ConfigRepository;

class CommissionConfig
{
    protected const string CONFIG_NAME = 'calculator';

    public function __construct(
        private readonly ConfigRepository $config
    ) {
    }

    public function getBinListNetUrl(): string
    {
        return $this->config->get(static::CONFIG_NAME . '.bin_list_net.url', '');
    }

    public function getExchangeRatesUrl(): string
    {
        return $this->config->get(static::CONFIG_NAME . '.exchange_rates.url', '');
    }

    public function getExchangeRatesApiKey(): string
    {
        return $this->config->get(static::CONFIG_NAME . '.exchange_rates.api_key', '');
    }

    public function getEuCountriesA2(): array
    {
        return $this->config->get(static::CONFIG_NAME . '.eu_countries_a2', []);
    }

    public function getEuCommissionRate(): float
    {
        return $this->config->get(static::CONFIG_NAME . '.eu_commission_rate', 0.01);
    }

    public function getNonEuCommissionRate(): float
    {
        return $this->config->get(static::CONFIG_NAME . '.non_eu_commission_rate', 0.02);
    }
}
