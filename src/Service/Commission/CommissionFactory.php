<?php

namespace App\Service\Commission;

use App\Service\Commission\Bin\BinContext;
use App\Service\Commission\Bin\BinContextInterface;
use App\Service\Commission\Bin\Strategy\BinGetterInterface;
use App\Service\Commission\Bin\Strategy\BinListNetBinGetter;
use App\Service\Commission\Calculator\Calculator;
use App\Service\Commission\Calculator\CalculatorInterface;
use App\Service\Commission\CommissionRate\CommissionRate;
use App\Service\Commission\CommissionRate\CommissionRateInterface;
use App\Service\Commission\ExchangeRate\ExchangeRateContext;
use App\Service\Commission\ExchangeRate\ExchangeRateContextInterface;
use App\Service\Commission\ExchangeRate\Strategy\ExchangeRateGetterInterface;
use App\Service\Commission\ExchangeRate\Strategy\IderaExchangeRateGetter;
use Symfony\Component\HttpClient\HttpClient;

readonly class CommissionFactory
{
    public function __construct(private CommissionConfig $config)
    {
    }

    public function createCalculator(): CalculatorInterface
    {
        return new Calculator(
            binContext: $this->createBinContext(),
            exchangeRateContext: $this->createExchangeRateContext(),
            commissionRate: $this->createCommissionRate(),
        );
    }

    protected function createBinContext(): BinContextInterface
    {
        return new BinContext([
            $this->createBinListNetBinGetter()
        ]);
    }

    protected function createExchangeRateContext(): ExchangeRateContextInterface
    {
        return new ExchangeRateContext([
            $this->createIderaExchangeRateGetter()
        ]);
    }

    protected function createCommissionRate(): CommissionRateInterface
    {
        return new CommissionRate($this->getConfig());
    }

    protected function createBinListNetBinGetter(): BinGetterInterface
    {
        return new BinListNetBinGetter(
            $this->getConfig(),
            HttpClient::create()
        );
    }

    protected function createIderaExchangeRateGetter(): ExchangeRateGetterInterface
    {
        return new IderaExchangeRateGetter(
            $this->getConfig(),
            HttpClient::create()
        );
    }

    protected function getConfig(): CommissionConfig
    {
        return $this->config;
    }
}
