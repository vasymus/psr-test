<?php

namespace Tests\Service\Commission\Bin\Strategy;

use App\Dto\BinRequestDto;
use App\Service\Commission\Bin\Strategy\BinListNetBinGetter;
use App\Service\Commission\CommissionConfig;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

class BinListNetBinGetterTest extends TestCase
{
    private CommissionConfig $config;
    private HttpClientInterface $httpClient;
    private BinListNetBinGetter $binGetter;

    protected function setUp(): void
    {
        $this->config = $this->createMock(CommissionConfig::class);
        $this->httpClient = $this->createMock(HttpClientInterface::class);

        $this->config->method('getBinListNetUrl')
            ->willReturn('https://lookup.binlist.net');

        $this->binGetter = new BinListNetBinGetter(
            $this->config,
            $this->httpClient
        );
    }

    public function testSupportsWithValidBin(): void
    {
        $requestDto = new BinRequestDto();
        $requestDto->setBin('45717360');

        $this->assertTrue($this->binGetter->supports($requestDto));
    }

    public function testSupportsWithNullBin(): void
    {
        $requestDto = new BinRequestDto();

        $this->assertFalse($this->binGetter->supports($requestDto));
    }

    public function testGetItemSuccessful(): void
    {
        $requestDto = new BinRequestDto();
        $requestDto->setBin('45717360');

        $mockResponseData = [
            'number' => ['length' => 16, 'luhn' => true],
            'scheme' => 'mastercard',
            'type' => 'debit',
            'brand' => 'Debit Mastercard',
            'country' => [
                'numeric' => '440',
                'alpha2' => 'LT',
                'name' => 'Lithuania',
                'emoji' => '🇱🇹',
                'currency' => 'EUR',
                'latitude' => 56,
                'longitude' => 24
            ],
            'bank' => [
                'name' => 'Swedbank Ab'
            ]
        ];

        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method('getStatusCode')->willReturn(200);
        $mockResponse->method('toArray')->willReturn($mockResponseData);

        $this->httpClient->method('request')
            ->with(
                'GET',
                'https://lookup.binlist.net/45717360',
                $this->anything()
            )
            ->willReturn($mockResponse);

        $result = $this->binGetter->getItem($requestDto);

        $this->assertTrue($result->getSuccess());
        $this->assertEmpty($result->getErrors());

        $binDto = $result->getBin();
        $this->assertNotNull($binDto);
        $this->assertEquals('mastercard', $binDto->getScheme());
        $this->assertEquals('debit', $binDto->getType());
        $this->assertEquals('Debit Mastercard', $binDto->getBrand());

        $countryDto = $binDto->getCountry();
        $this->assertNotNull($countryDto);
        $this->assertEquals('LT', $countryDto->getAlpha2());
        $this->assertEquals('Lithuania', $countryDto->getName());
        $this->assertEquals('EUR', $countryDto->getCurrency());
        $this->assertEquals(56, $countryDto->getLatitude());
        $this->assertEquals(24, $countryDto->getLongitude());

        $bankDto = $binDto->getBank();
        $this->assertNotNull($bankDto);
        $this->assertEquals('Swedbank Ab', $bankDto->getName());
    }

    public function testGetItemWithNon200Response(): void
    {
        $requestDto = new BinRequestDto();
        $requestDto->setBin('45717360');

        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method('getStatusCode')->willReturn(404);

        $this->httpClient->method('request')
            ->willReturn($mockResponse);

        $result = $this->binGetter->getItem($requestDto);

        $this->assertFalse($result->getSuccess());
        $this->assertCount(1, $result->getErrors());
        $this->assertEquals('API returned status code: 404', $result->getErrors()[0]);
    }

    public function testGetItemWithPartialResponse(): void
    {
        $requestDto = new BinRequestDto();
        $requestDto->setBin('45717360');

        $mockResponseData = [
            'scheme' => 'mastercard',
            'type' => 'debit',
            'brand' => 'Debit Mastercard'
        ];

        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method('getStatusCode')->willReturn(200);
        $mockResponse->method('toArray')->willReturn($mockResponseData);

        $this->httpClient->method('request')
            ->willReturn($mockResponse);

        $result = $this->binGetter->getItem($requestDto);

        $this->assertTrue($result->getSuccess());
        $this->assertEmpty($result->getErrors());

        $binDto = $result->getBin();
        $this->assertNotNull($binDto);
        $this->assertEquals('mastercard', $binDto->getScheme());
        $this->assertEquals('debit', $binDto->getType());
        $this->assertEquals('Debit Mastercard', $binDto->getBrand());

        $this->assertNull($binDto->getCountry());
        $this->assertNull($binDto->getBank());
    }

    public function testGetItemWithHttpException(): void
    {
        $requestDto = new BinRequestDto();
        $requestDto->setBin('45717360');

        $this->httpClient->method('request')
            ->willThrowException($this->createMock(ExceptionInterface::class));

        $result = $this->binGetter->getItem($requestDto);

        $this->assertFalse($result->getSuccess());
        $this->assertCount(1, $result->getErrors());
    }

    public function testGetItemWithGenericException(): void
    {
        $requestDto = new BinRequestDto();
        $requestDto->setBin('45717360');

        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method('getStatusCode')->willReturn(200);
        $mockResponse->method('toArray')
            ->willThrowException(new \RuntimeException('Invalid JSON'));

        $this->httpClient->method('request')
            ->willReturn($mockResponse);

        $result = $this->binGetter->getItem($requestDto);

        $this->assertFalse($result->getSuccess());
        $this->assertCount(1, $result->getErrors());
        $this->assertEquals('Unexpected error: Invalid JSON', $result->getErrors()[0]);
    }
}
