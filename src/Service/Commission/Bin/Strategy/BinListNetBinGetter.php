<?php

namespace App\Service\Commission\Bin\Strategy;

use App\Dto\BinBankDto;
use App\Dto\BinCountryDto;
use App\Dto\BinDto;
use App\Dto\BinRequestDto;
use App\Dto\BinResponseDto;
use App\Service\Commission\CommissionConfig;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

readonly class BinListNetBinGetter implements BinGetterInterface
{
    public function __construct(
        private CommissionConfig $config,
        private HttpClientInterface $client,
    ) {
    }

    public function supports(BinRequestDto $binRequestDto): bool
    {
        return null !== $binRequestDto->getBin();
    }

    public function getItem(BinRequestDto $binRequestDto): BinResponseDto
    {
        $response = new BinResponseDto();
        $url = rtrim($this->config->getBinListNetUrl(), '/') . '/' . $binRequestDto->getBin();

        try {
            $httpResponse = $this->client->request('GET', $url, [
                'headers' => [
                    'Accept' => 'application/json',
                ],
                'max_redirects' => 5,
            ]);

            $statusCode = $httpResponse->getStatusCode();

            if ($statusCode !== 200) {
                $response->setSuccess(false);
                $response->setErrors(["API returned status code: {$statusCode}"]);
                return $response;
            }

            $data = $httpResponse->toArray(false);
            $binDto = $this->mapResponseToDto($data);
            $response->setSuccess(true);
            $response->setBin($binDto);
        } catch (ExceptionInterface $e) {
            $response->setSuccess(false);
            $response->setErrors([$e->getMessage()]);
        } catch (\Throwable $e) {
            $response->setSuccess(false);
            $response->setErrors(["Unexpected error: {$e->getMessage()}"]);
        }

        return $response;
    }

    private function mapResponseToDto(array $data): BinDto
    {
        $binDto = new BinDto();
        $binDto->setNumber($data['number'] ?? null)
            ->setScheme($data['scheme'] ?? null)
            ->setType($data['type'] ?? null)
            ->setBrand($data['brand'] ?? null);

        if (isset($data['country'])) {
            $binDto->setCountry($this->createCountryDto($data['country']));
        }

        if (isset($data['bank'])) {
            $binDto->setBank($this->createBankDto($data['bank']));
        }

        return $binDto;
    }

    private function createCountryDto(array $countryData): BinCountryDto
    {
        return new BinCountryDto()
            ->setNumeric($countryData['numeric'] ?? null)
            ->setAlpha2($countryData['alpha2'] ?? null)
            ->setName($countryData['name'] ?? null)
            ->setEmoji($countryData['emoji'] ?? null)
            ->setCurrency($countryData['currency'] ?? null)
            ->setLatitude($countryData['latitude'] ?? null)
            ->setLongitude($countryData['longitude'] ?? null);
    }

    private function createBankDto(array $bankData): BinBankDto
    {
        return new BinBankDto()->setName($bankData['name'] ?? null);
    }
}
