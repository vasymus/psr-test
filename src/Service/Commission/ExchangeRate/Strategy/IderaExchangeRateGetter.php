<?php

namespace App\Service\Commission\ExchangeRate\Strategy;

use App\Dto\ExchangeRateDto;
use App\Dto\ExchangeRateRequestDto;
use App\Dto\ExchangeRateResponseDto;
use App\Service\Commission\CommissionConfig;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

class IderaExchangeRateGetter implements ExchangeRateGetterInterface
{
    /**
     * @var string[]|null
     */
    protected static ?array $supportedSymbols = null;

    /**
     * @var array<string, array<string, ExchangeRateDto>>
     */
    protected static ?array $rates = [];

    public function __construct(
        readonly private CommissionConfig $config,
        readonly private HttpClientInterface $client,
    ) {
    }

    public function supports(ExchangeRateRequestDto $exchangeRateRequestDto): bool
    {
        $supportedSymbols = $this->getSupportedSymbols();

        if (empty($supportedSymbols)) {
            return false;
        }

        $baseCurrency = $exchangeRateRequestDto->getBaseCurrency();
        $targetCurrency = $exchangeRateRequestDto->getTargetCurrency();

        return in_array($baseCurrency, $supportedSymbols, true) && in_array($targetCurrency, $supportedSymbols, true);
    }

    public function getRateItem(ExchangeRateRequestDto $exchangeRateRequestDto): ExchangeRateResponseDto
    {
        try {
            $rates = $this->getExchangeRates($exchangeRateRequestDto->getBaseCurrency());
            $rateItem = $rates[$exchangeRateRequestDto->getTargetCurrency()] ?? null;
            if (null === $rateItem) {
                throw new \RuntimeException('Exchange rate not found');
            }

            return $this->createSuccessExchangeRateResponse($rateItem);
        } catch (\Throwable $exception) {
            return $this->createFailedExchangeRateResponse([$exception->getMessage()]);
        }
    }

    /**
     * @param string $baseCurrency
     *
     * @return array<string, ExchangeRateDto>
     */
    private function getExchangeRates(string $baseCurrency): array
    {
        if (array_key_exists($baseCurrency, self::$rates)) {
            return self::$rates[$baseCurrency];
        }

        $url = $this->buildRateRequestUrl($baseCurrency);
        $httpResponse = $this->client->request('GET', $url, [
            'headers' => [
                'Accept' => 'application/json',
            ],
            'max_redirects' => 5,
        ]);

        if ($httpResponse->getStatusCode() !== 200) {
            throw new \RuntimeException('API returned status code: %d', $httpResponse->getStatusCode());
        }

        $data = $httpResponse->toArray(false);

        self::$rates[$baseCurrency] = $this->mapExchangeRatesResponse($baseCurrency, $data);

        return self::$rates[$baseCurrency];
    }

    private function mapExchangeRatesResponse(string $baseCurrency, array $data): array
    {
        if (
            !isset($data['success'])
            || $data['success'] !== true
            || empty($data['rates'])
        ) {
            return [];
        }

        $result = [];

        $date = $this->parseDate($data);

        foreach ($data['rates'] as $currency => $rate) {
            $result[$currency] = new ExchangeRateDto()
                ->setBaseCurrency($baseCurrency)
                ->setTargetCurrency($currency)
                ->setDate($date)
                ->setRate($rate);
        }

        return $result;
    }

    private function createSuccessExchangeRateResponse(ExchangeRateDto $exchangeRateDto): ExchangeRateResponseDto
    {
        return new ExchangeRateResponseDto()
            ->setSuccess(true)
            ->setRateItem($exchangeRateDto);
    }

    private function createFailedExchangeRateResponse(array $errors): ExchangeRateResponseDto
    {
        return new ExchangeRateResponseDto()
            ->setSuccess(false)
            ->setErrors($errors);
    }

    private function buildRateRequestUrl(string $baseCurrency): string
    {
        $url = rtrim($this->config->getExchangeRatesUrl(), '/') . '/latest';
        $queryParams = [
            'access_key' => $this->config->getExchangeRatesApiKey(),
            'base' => $baseCurrency,
        ];

        return $url . '?' . http_build_query($queryParams);
    }

    private function parseDate(array $data): ?\DateTimeInterface
    {
        try {
            if (!empty($data['date'])) {
                return new \DateTime($data['date']);
            }

            if (isset($data['timestamp']) && is_numeric($data['timestamp'])) {
                $dateTime = new \DateTime();
                $dateTime->setTimestamp((int)$data['timestamp']);
                return $dateTime;
            }
        } catch (\Throwable) {
        }

        return null;
    }

    private function getSupportedSymbols(): array
    {
        if (null !== self::$supportedSymbols) {
            return self::$supportedSymbols;
        }

        $url = $this->buildSymbolsRequestUrl();

        try {
            $response = $this->client->request('GET', $url, [
                'headers' => [
                    'Accept' => 'application/json',
                ],
                'max_redirects' => 5,
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode !== 200) {
                return [];
            }

            $data = $response->toArray(false);

            if (!isset($data['success']) || $data['success'] !== true || !isset($data['symbols'])) {
                return [];
            }

            self::$supportedSymbols = array_keys($data['symbols']);

            return self::$supportedSymbols;
        } catch (ExceptionInterface) {
            return [];
        }
    }

    private function buildSymbolsRequestUrl(): string
    {
        $url = rtrim($this->config->getExchangeRatesUrl(), '/') . '/v1/symbols';
        $queryParams = [
            'access_key' => $this->config->getExchangeRatesApiKey()
        ];

        return $url . '?' . http_build_query($queryParams);
    }
}
