<?php
declare(strict_types=1);

namespace App\Api;

use App\Currency;
use App\Result;
use GuzzleHttp\Client;

class FastForexAPI
{
    private const BASE_URL = 'https://api.fastforex.io/fetch-one';

    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'verify' => false,
        ]);
    }

    public function fetchExchangeData(Currency $baseCurrency, Currency $currency): ?Result
    {
        $baseIsoCode = $baseCurrency->getIsoCode();
        $currencyIsoCode = $currency->getIsoCode();

        $url = $this->buildUrl($baseIsoCode, $currencyIsoCode);

        $response = $this->client->get($url);

        if ($response->getStatusCode() !== 200) {
            exit ("Request failed!\n");
        }

        $data = json_decode($response->getBody()->getContents());

        if (empty($data) || !property_exists($data, 'result') || !property_exists($data->result, $currencyIsoCode)) {
            exit ("Error retrieving data \n");
        }

        $rate = $data->result->$currencyIsoCode;
        return new Result($currency, $rate, self::BASE_URL);
    }

    private function buildUrl(string $baseIsoCode, string $currencyIsoCode): string
    {
        return self::BASE_URL . '?' . http_build_query([
                'api_key' => $_ENV['FASTFOREX_API_KEY'],
                'from' => $baseIsoCode,
                'to' => $currencyIsoCode,
            ]);
    }
}
