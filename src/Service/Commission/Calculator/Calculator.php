<?php

namespace App\Service\Commission\Calculator;

use App\Dto\BinRequestDto;
use App\Dto\CommissionResponseDto;
use App\Dto\ExchangeRateRequestDto;
use App\Dto\TransactionDto;
use App\Service\Commission\Bin\BinContextInterface;
use App\Service\Commission\CommissionRate\CommissionRateInterface;
use App\Service\Commission\ExchangeRate\ExchangeRateContextInterface;

readonly class Calculator implements CalculatorInterface
{
    public function __construct(
        private BinContextInterface $binContext,
        private ExchangeRateContextInterface $exchangeRateContext,
        private CommissionRateInterface $commissionRate,
    ) {
    }

    public function calculateCommission(TransactionDto $transactionDto): CommissionResponseDto
    {
        try {
            if (!$this->validateTransaction($transactionDto)) {
                return $this->createNotValidCommissionResponse(['Missing required transaction data']);
            }

            $exchangeRateRequestDto = $this->mapTransactionDtoToExchangeRequestDto(
                $transactionDto,
                new ExchangeRateRequestDto()
            );
            $exchangeRateResponseDto = $this->exchangeRateContext->getRates($exchangeRateRequestDto);

            if (!$exchangeRateResponseDto->getSuccess() || null === $exchangeRateResponseDto->getRateItem()) {
                return $this->createNotValidCommissionResponse(
                    array_merge(
                        null === $exchangeRateResponseDto->getRateItem() ? ['Exchange rate data not available'] : [],
                        $exchangeRateResponseDto->getErrors()
                    )
                );
            }

            $binRequestDto = $this->mapTransactionDtoToBinRequestDto($transactionDto, new BinRequestDto());
            $binResponseDto = $this->binContext->getItem($binRequestDto);
            if (
                !$binResponseDto->getSuccess()
                || null === $binResponseDto->getBin()?->getCountry()?->getAlpha2()
            ) {
                return $this->createNotValidCommissionResponse(
                    array_merge(
                        null === $binResponseDto->getBin()?->getCountry()?->getAlpha2()
                            ? ['Failed to retrieve country BIN information']
                            : [],
                        $binResponseDto->getErrors()
                    )
                );
            }

            $commissionAmount = $this->calculateCommissionAmount(
                $transactionDto->getAmount(),
                $exchangeRateResponseDto->getRateItem()->getRate(),
                $this->commissionRate->getCommissionRate($binResponseDto->getBin()->getCountry()->getAlpha2())
            );

            return $this->createSuccessCommissionResponse($commissionAmount);
        } catch (\Throwable $exception) {
            return new CommissionResponseDto()
                ->setSuccess(false)
                ->addError($exception->getMessage());
        }
    }

    private function calculateCommissionAmount(
        float $amount,
        float $rate,
        float $commissionRate
    ): float {
        $commission = $amount / $rate * $commissionRate;
        return ceil($commission * 100) / 100;
    }

    private function validateTransaction(TransactionDto $transactionDto): bool
    {
        return null !== $transactionDto->getBin()
            && null !== $transactionDto->getAmount()
            && null !== $transactionDto->getBaseCurrency()
            && null !== $transactionDto->getTargetCurrency();
    }

    private function mapTransactionDtoToBinRequestDto(
        TransactionDto $transactionDto,
        BinRequestDto $binRequestDto
    ): BinRequestDto {
        return $binRequestDto->setBin($transactionDto->getBin());
    }

    private function mapTransactionDtoToExchangeRequestDto(
        TransactionDto $transactionDto,
        ExchangeRateRequestDto $exchangeRequestDto
    ): ExchangeRateRequestDto {
        return $exchangeRequestDto
            ->setBaseCurrency($transactionDto->getBaseCurrency())
            ->setTargetCurrency($transactionDto->getTargetCurrency());
    }

    private function createNotValidCommissionResponse(array $errors): CommissionResponseDto
    {
        return new CommissionResponseDto()
            ->setSuccess(false)
            ->setErrors($errors);
    }

    private function createSuccessCommissionResponse(float $amount): CommissionResponseDto
    {
        return new CommissionResponseDto()
            ->setSuccess(true)
            ->setAmount($amount);
    }
}
