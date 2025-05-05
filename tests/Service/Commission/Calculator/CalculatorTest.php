<?php

namespace Tests\Service\Commission\Calculator;

use App\Dto\BinCountryDto;
use App\Dto\BinDto;
use App\Dto\BinResponseDto;
use App\Dto\ExchangeRateDto;
use App\Dto\ExchangeRateResponseDto;
use App\Dto\TransactionDto;
use App\Service\Commission\Bin\BinContextInterface;
use App\Service\Commission\Calculator\Calculator;
use App\Service\Commission\CommissionRate\CommissionRateInterface;
use App\Service\Commission\ExchangeRate\ExchangeRateContextInterface;
use PHPUnit\Framework\TestCase;

class CalculatorTest extends TestCase
{
    private BinContextInterface $binContext;
    private ExchangeRateContextInterface $exchangeRateContext;
    private CommissionRateInterface $commissionRate;
    private Calculator $calculator;

    protected function setUp(): void
    {
        $this->binContext = $this->createMock(BinContextInterface::class);
        $this->exchangeRateContext = $this->createMock(ExchangeRateContextInterface::class);
        $this->commissionRate = $this->createMock(CommissionRateInterface::class);

        $this->calculator = new Calculator(
            $this->binContext,
            $this->exchangeRateContext,
            $this->commissionRate
        );
    }

    public function testCalculateCommissionSuccessForEuCountry(): void
    {
        $transaction = new TransactionDto();
        $transaction->setBin('45717360')
            ->setAmount(100.00)
            ->setBaseCurrency('EUR')
            ->setTargetCurrency('EUR');

        $exchangeRateDto = new ExchangeRateDto();
        $exchangeRateDto->setBaseCurrency('EUR')
            ->setTargetCurrency('EUR')
            ->setRate(1.0);

        $exchangeRateResponse = new ExchangeRateResponseDto();
        $exchangeRateResponse->setSuccess(true)
            ->setRateItem($exchangeRateDto);

        $this->exchangeRateContext->method('getRates')
            ->willReturn($exchangeRateResponse);

        $countryDto = new BinCountryDto();
        $countryDto->setAlpha2('DE');

        $binDto = new BinDto();
        $binDto->setCountry($countryDto);

        $binResponse = new BinResponseDto();
        $binResponse->setSuccess(true)
            ->setBin($binDto);

        $this->binContext->method('getItem')
            ->willReturn($binResponse);

        $this->commissionRate->method('getCommissionRate')
            ->with('DE')
            ->willReturn(0.01);

        $result = $this->calculator->calculateCommission($transaction);

        $this->assertTrue($result->getSuccess());
        $this->assertEquals(1.0, $result->getAmount());
    }

    public function testCalculateCommissionSuccessForNonEuCountry(): void
    {
        $transaction = new TransactionDto();
        $transaction->setBin('45717360')
            ->setAmount(100.00)
            ->setBaseCurrency('EUR')
            ->setTargetCurrency('USD');

        $exchangeRateDto = new ExchangeRateDto();
        $exchangeRateDto->setBaseCurrency('EUR')
            ->setTargetCurrency('USD')
            ->setRate(1.1);

        $exchangeRateResponse = new ExchangeRateResponseDto();
        $exchangeRateResponse->setSuccess(true)
            ->setRateItem($exchangeRateDto);

        $this->exchangeRateContext->method('getRates')
            ->willReturn($exchangeRateResponse);

        $countryDto = new BinCountryDto();
        $countryDto->setAlpha2('US');

        $binDto = new BinDto();
        $binDto->setCountry($countryDto);

        $binResponse = new BinResponseDto();
        $binResponse->setSuccess(true)
            ->setBin($binDto);

        $this->binContext->method('getItem')
            ->willReturn($binResponse);

        $this->commissionRate->method('getCommissionRate')
            ->with('US')
            ->willReturn(0.02);

        $result = $this->calculator->calculateCommission($transaction);

        $this->assertTrue($result->getSuccess());
        $this->assertEquals(1.82, $result->getAmount());
    }

    public function testCeilingWorks(): void
    {
        $transaction = new TransactionDto();
        $transaction->setBin('45717360')
            ->setAmount(46.0)
            ->setBaseCurrency('EUR')
            ->setTargetCurrency('EUR');

        $exchangeRateDto = new ExchangeRateDto();
        $exchangeRateDto->setBaseCurrency('EUR')
            ->setTargetCurrency('EUR')
            ->setRate(1.0);

        $exchangeRateResponse = new ExchangeRateResponseDto();
        $exchangeRateResponse->setSuccess(true)
            ->setRateItem($exchangeRateDto);

        $this->exchangeRateContext->method('getRates')
            ->willReturn($exchangeRateResponse);

        $countryDto = new BinCountryDto();
        $countryDto->setAlpha2('DE');

        $binDto = new BinDto();
        $binDto->setCountry($countryDto);

        $binResponse = new BinResponseDto();
        $binResponse->setSuccess(true)
            ->setBin($binDto);

        $this->binContext->method('getItem')
            ->willReturn($binResponse);

        $this->commissionRate->method('getCommissionRate')
            ->with('DE')
            ->willReturn(0.01);

        $result = $this->calculator->calculateCommission($transaction);

        $this->assertTrue($result->getSuccess());
        $this->assertEquals(0.46, $result->getAmount());
    }

    public function testInvalidTransaction(): void
    {
        $transaction = new TransactionDto();
        $transaction->setAmount(100.00)
            ->setBaseCurrency('EUR')
            ->setTargetCurrency('USD');

        $result = $this->calculator->calculateCommission($transaction);

        $this->assertFalse($result->getSuccess());
        $this->assertContains('Missing required transaction data', $result->getErrors());
    }

    public function testFailedExchangeRateRetrieval(): void
    {
        $transaction = new TransactionDto();
        $transaction->setBin('45717360')
            ->setAmount(100.00)
            ->setBaseCurrency('EUR')
            ->setTargetCurrency('XYZ');

        $exchangeRateResponse = new ExchangeRateResponseDto();
        $exchangeRateResponse->setSuccess(false)
            ->setErrors(['Currency not supported']);

        $this->exchangeRateContext->method('getRates')
            ->willReturn($exchangeRateResponse);

        $result = $this->calculator->calculateCommission($transaction);

        $this->assertFalse($result->getSuccess());
        $this->assertContains('Currency not supported', $result->getErrors());
    }

    public function testExchangeRateDataNotAvailable(): void
    {
        $transaction = new TransactionDto();
        $transaction->setBin('45717360')
            ->setAmount(100.00)
            ->setBaseCurrency('EUR')
            ->setTargetCurrency('USD');

        $exchangeRateResponse = new ExchangeRateResponseDto();
        $exchangeRateResponse->setSuccess(true)
            ->setRateItem(null);

        $this->exchangeRateContext->method('getRates')
            ->willReturn($exchangeRateResponse);

        $result = $this->calculator->calculateCommission($transaction);

        $this->assertFalse($result->getSuccess());
        $this->assertContains('Exchange rate data not available', $result->getErrors());
    }

    public function testFailedBinRetrieval(): void
    {
        $transaction = new TransactionDto();
        $transaction->setBin('99999999')
            ->setAmount(100.00)
            ->setBaseCurrency('EUR')
            ->setTargetCurrency('EUR');

        $exchangeRateDto = new ExchangeRateDto();
        $exchangeRateDto->setBaseCurrency('EUR')
            ->setTargetCurrency('EUR')
            ->setRate(1.0);

        $exchangeRateResponse = new ExchangeRateResponseDto();
        $exchangeRateResponse->setSuccess(true)
            ->setRateItem($exchangeRateDto);

        $this->exchangeRateContext->method('getRates')
            ->willReturn($exchangeRateResponse);

        $binResponse = new BinResponseDto();
        $binResponse->setSuccess(false)
            ->setErrors(['BIN not found']);

        $this->binContext->method('getItem')
            ->willReturn($binResponse);

        $result = $this->calculator->calculateCommission($transaction);

        $this->assertFalse($result->getSuccess());
        $this->assertContains('BIN not found', $result->getErrors());
    }

    public function testBinCountryNotAvailable(): void
    {
        $transaction = new TransactionDto();
        $transaction->setBin('45717360')
            ->setAmount(100.00)
            ->setBaseCurrency('EUR')
            ->setTargetCurrency('EUR');

        $exchangeRateDto = new ExchangeRateDto();
        $exchangeRateDto->setBaseCurrency('EUR')
            ->setTargetCurrency('EUR')
            ->setRate(1.0);

        $exchangeRateResponse = new ExchangeRateResponseDto();
        $exchangeRateResponse->setSuccess(true)
            ->setRateItem($exchangeRateDto);

        $this->exchangeRateContext->method('getRates')
            ->willReturn($exchangeRateResponse);

        $binDto = new BinDto();

        $binResponse = new BinResponseDto();
        $binResponse->setSuccess(true)
            ->setBin($binDto);

        $this->binContext->method('getItem')
            ->willReturn($binResponse);

        $result = $this->calculator->calculateCommission($transaction);

        $this->assertFalse($result->getSuccess());
        $this->assertContains('Failed to retrieve country BIN information', $result->getErrors());
    }

    public function testExceptionHandling(): void
    {
        $transaction = new TransactionDto();
        $transaction->setBin('45717360')
            ->setAmount(100.00)
            ->setBaseCurrency('EUR')
            ->setTargetCurrency('EUR');

        $this->exchangeRateContext->method('getRates')
            ->willThrowException(new \RuntimeException('Service unavailable'));

        $result = $this->calculator->calculateCommission($transaction);

        $this->assertFalse($result->getSuccess());
        $this->assertContains('Service unavailable', $result->getErrors());
    }
}
