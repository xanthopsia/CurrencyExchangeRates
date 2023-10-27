<?php
declare(strict_types=1);

namespace App;

use App\Collections\ExchangeCollection;
use App\Collections\ResultCollection;

class Application
{
    private ExchangeCollection $exchanges;

    public function __construct()
    {
        $this->exchanges = new ExchangeCollection();
    }

    public function run()
    {
        while (true) {
            $input = explode(' ', readline("Enter amount and base currency (<amount> <currency>): "));
            $amount = $input[0];

            if (!is_numeric($amount) || $amount <= 0) {
                echo 'Invalid base amount. Please enter a valid positive number' . PHP_EOL;
                continue;
            }

            $isoCodes = (new IsoCodes())->get();

            $currencyInput = strtoupper(readline('Enter target currency: '));
            if (empty($currencyInput) || !Currency::isValidIsoCode($currencyInput, $isoCodes)) {
                echo 'Invalid target currency. Please enter a valid ISO code' . PHP_EOL;
                continue;
            }

            $currency = new Currency($currencyInput);

            $baseCurrencyInput = strtoupper($input[1] ?? '');

            if (empty($baseCurrencyInput) || !Currency::isValidIsoCode($baseCurrencyInput, $isoCodes)) {
                echo 'Invalid base currency. Please enter a valid ISO code' . PHP_EOL;
                continue;
            }

            $baseCurrency = new Currency($baseCurrencyInput);

            $results = $this->getResults($baseCurrency, $currency);
            $this->displayResults($results, (int)$amount * 100);
        }
    }


    private function getResults(Currency $baseCurrency, Currency $currency): ResultCollection
    {
        $results = new ResultCollection($baseCurrency);

        foreach ($this->exchanges->get() as $exchange) {
            $result = $exchange->fetchExchangeData($baseCurrency, $currency);
            if ($result) {
                $results->add($result);
            }
        }

        return $results;
    }

    private function displayResults(ResultCollection $results, int $amount): void
    {
        echo "Base Currency: {$results->getBaseCurrency()->getIsoCode()}" . PHP_EOL;

        $recommendedExchange = true;

        foreach ($results->sortDescending() as $result) {
            if ($recommendedExchange) {
                echo "\033[1;32m";
                echo 'RECOMMENDED' . PHP_EOL;
            } else {
                echo "\033[0m";
            }

            $convertedAmount = $amount / 100 * $result->getRate();

            echo "Currency: {$result->getCurrency()->getIsoCode()}" . PHP_EOL;
            echo "Rate: {$result->getRate()}" . PHP_EOL;
            echo "Conversion Amount: $convertedAmount" . PHP_EOL;
            echo "Source: {$result->getSource()}" . PHP_EOL;
            echo '----------------' . PHP_EOL;

            $recommendedExchange = false;
        }

        echo "\033[0m";
    }
}
