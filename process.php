<?php

use App\PriceParser;

function processAndStorePriceData(string $data): string
{
    $parser = new PriceParser();
    return $parser->processAndStore($data);
}