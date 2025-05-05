<?php

namespace Tests\Service\Commission\ExchangeRate\Strategy;

use App\Dto\ExchangeRateDto;
use App\Dto\ExchangeRateRequestDto;
use App\Service\Commission\CommissionConfig;
use App\Service\Commission\ExchangeRate\Strategy\IderaExchangeRateGetter;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use ReflectionProperty;
use DateTime;

class IderaExchangeRateGetterTest extends TestCase
{
    private CommissionConfig $config;
    private HttpClientInterface $client;
    private IderaExchangeRateGetter $exchangeRateGetter;

    protected function setUp(): void
    {
        $this->config = $this->createMock(CommissionConfig::class);
        $this->client = $this->createMock(HttpClientInterface::class);

        $this->config->method('getExchangeRatesUrl')
            ->willReturn('https://api.exchangeratesapi.io');

        $this->config->method('getExchangeRatesApiKey')
            ->willReturn('test-api-key');

        $this->exchangeRateGetter = new IderaExchangeRateGetter(
            $this->config,
            $this->client
        );

        $this->resetStaticProperties();
    }

    protected function tearDown(): void
    {
        $this->resetStaticProperties();
    }

    private function resetStaticProperties(): void
    {
        $refSupported = new ReflectionProperty(IderaExchangeRateGetter::class, 'supportedSymbols');
        $refSupported->setValue(null, null);

        $refRates = new ReflectionProperty(IderaExchangeRateGetter::class, 'rates');
        $refRates->setValue(null, []);
    }

    private function setSupportedSymbols(?array $symbols): void
    {
        $refSupported = new ReflectionProperty(IderaExchangeRateGetter::class, 'supportedSymbols');
        $refSupported->setValue(null, $symbols);
    }

    private function setRates(array $rates): void
    {
        $refRates = new ReflectionProperty(IderaExchangeRateGetter::class, 'rates');
        $refRates->setValue(null, $rates);
    }

    public function testSupportsWithExistingSymbols(): void
    {
        $this->setSupportedSymbols(['EUR', 'USD', 'GBP']);

        $request = new ExchangeRateRequestDto();
        $request->setBaseCurrency('EUR');
        $request->setTargetCurrency('USD');

        $this->assertTrue($this->exchangeRateGetter->supports($request));
    }

    public function testSupportsWithNonExistingSymbols(): void
    {
        $this->setSupportedSymbols(['EUR', 'USD']);

        $request = new ExchangeRateRequestDto();
        $request->setBaseCurrency('EUR');
        $request->setTargetCurrency('XYZ');

        $this->assertFalse($this->exchangeRateGetter->supports($request));
    }

    public function testSupportsWithFailedApiResponse(): void
    {
        $this->setSupportedSymbols(null);

        $symbolsResponse = $this->createMock(ResponseInterface::class);
        $symbolsResponse->method('getStatusCode')->willReturn(401);
        $symbolsResponse->method('toArray')->willReturn([]);

        $this->client->method('request')
            ->willReturn($symbolsResponse);

        $request = new ExchangeRateRequestDto();
        $request->setBaseCurrency('EUR');
        $request->setTargetCurrency('USD');

        $this->assertFalse($this->exchangeRateGetter->supports($request));
    }

    public function testSupportsWithHttpException(): void
    {
        $this->setSupportedSymbols(null);

        $this->client->method('request')
            ->willThrowException($this->createMock(ExceptionInterface::class));

        $request = new ExchangeRateRequestDto();
        $request->setBaseCurrency('EUR');
        $request->setTargetCurrency('USD');

        $this->assertFalse($this->exchangeRateGetter->supports($request));
    }

    public function testGetRateItemSuccess(): void
    {
        $this->setSupportedSymbols(['EUR', 'USD', 'GBP', 'JPY']);

        $date = new DateTime('2025-05-03');
        $rateItem = new ExchangeRateDto();
        $rateItem->setBaseCurrency('EUR')
            ->setTargetCurrency('USD')
            ->setRate(1.130135)
            ->setDate($date);

        $rates = ['EUR' => ['USD' => $rateItem]];
        $this->setRates($rates);

        $request = new ExchangeRateRequestDto();
        $request->setBaseCurrency('EUR');
        $request->setTargetCurrency('USD');

        $response = $this->exchangeRateGetter->getRateItem($request);

        $this->assertTrue($response->getSuccess());
        $this->assertEmpty($response->getErrors());

        $resultRateItem = $response->getRateItem();
        $this->assertNotNull($resultRateItem);
        $this->assertEquals('EUR', $resultRateItem->getBaseCurrency());
        $this->assertEquals('USD', $resultRateItem->getTargetCurrency());
        $this->assertEquals(1.130135, $resultRateItem->getRate());

        $resultDate = $resultRateItem->getDate();
        $this->assertNotNull($resultDate);
        $this->assertEquals('2025-05-03', $resultDate->format('Y-m-d'));
    }

    public function testGetRateItemWithMissingCurrency(): void
    {
        $this->setSupportedSymbols(['EUR', 'USD', 'GBP', 'JPY']);

        $date = new DateTime('2025-05-03');
        $rateItem = new ExchangeRateDto();
        $rateItem->setBaseCurrency('EUR')
            ->setTargetCurrency('GBP')
            ->setRate(0.85139)
            ->setDate($date);

        $rates = ['EUR' => ['GBP' => $rateItem]];
        $this->setRates($rates);

        $ratesResponse = $this->createMock(ResponseInterface::class);
        $ratesResponse->method('getStatusCode')->willReturn(200);
        $ratesData = [
            'success' => true,
            'timestamp' => 1746301183,
            'base' => 'EUR',
            'date' => '2025-05-03',
            'rates' => [
                'GBP' => 0.85139,
                'JPY' => 163.796616
            ]
        ];
        $ratesResponse->method('toArray')->willReturn($ratesData);

        $this->client->method('request')
            ->willReturn($ratesResponse);

        $request = new ExchangeRateRequestDto();
        $request->setBaseCurrency('EUR');
        $request->setTargetCurrency('USD');

        $response = $this->exchangeRateGetter->getRateItem($request);

        $this->assertFalse($response->getSuccess());
        $this->assertCount(1, $response->getErrors());
        $this->assertEquals('Exchange rate not found', $response->getErrors()[0]);
    }

    public function testGetRateItemWithFailedRatesResponse(): void
    {
        $this->setSupportedSymbols(['EUR', 'USD', 'GBP', 'JPY']);

        $ratesResponse = $this->createMock(ResponseInterface::class);
        $ratesResponse->method('getStatusCode')->willReturn(200);
        $ratesData = [
            'success' => false,
            'error' => [
                'code' => 101,
                'info' => 'API key not valid'
            ]
        ];
        $ratesResponse->method('toArray')->willReturn($ratesData);

        $this->client->method('request')
            ->willReturn($ratesResponse);

        $request = new ExchangeRateRequestDto();
        $request->setBaseCurrency('EUR');
        $request->setTargetCurrency('USD');

        $response = $this->exchangeRateGetter->getRateItem($request);

        $this->assertFalse($response->getSuccess());
    }

    public function testGetRateItemWithHttpErrorStatusCode(): void
    {
        $this->setSupportedSymbols(['EUR', 'USD', 'GBP', 'JPY']);

        $ratesResponse = $this->createMock(ResponseInterface::class);
        $ratesResponse->method('getStatusCode')->willReturn(401);

        $this->client->method('request')
            ->willReturn($ratesResponse);

        $request = new ExchangeRateRequestDto();
        $request->setBaseCurrency('EUR');
        $request->setTargetCurrency('USD');

        $response = $this->exchangeRateGetter->getRateItem($request);

        $this->assertFalse($response->getSuccess());
    }

    public function testGetRateItemWithHttpException(): void
    {
        $this->setSupportedSymbols(['EUR', 'USD', 'GBP', 'JPY']);

        $this->client->method('request')
            ->willThrowException(new \RuntimeException('Connection timeout'));

        $request = new ExchangeRateRequestDto();
        $request->setBaseCurrency('EUR');
        $request->setTargetCurrency('USD');

        $response = $this->exchangeRateGetter->getRateItem($request);

        $this->assertFalse($response->getSuccess());
        $this->assertCount(1, $response->getErrors());
        $this->assertEquals('Connection timeout', $response->getErrors()[0]);
    }
}
