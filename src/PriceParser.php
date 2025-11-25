<?php

namespace App;

class PriceParser
{
    private array $errors = [];
    private int $savedCount = 0;

    public function processAndStore(string $data): string
    {
        $this->errors = [];
        $this->savedCount = 0;

        $lines = explode("\n", $data);

        foreach ($lines as $lineNum => $line) {
            $line = trim($line);

            if (empty($line)) {
                continue;
            }

            $parsed = $this->parseLine($line);

            if ($parsed === null) {
                $this->errors[] = "Line " . ($lineNum + 1) . ": Could not parse '$line'";
                continue;
            }

            $this->saveEntry($parsed, $line, $lineNum);
        }

        return $this->buildResultMessage();
    }

    private function parseLine(string $line): ?array
    {
        $line = preg_replace('/\s+/', ' ', $line);

        // Try parsing with currency symbol first
        $result = $this->parseWithCurrency($line);
        if ($result !== null) {
            return $result;
        }

        // Try parsing without currency symbol
        return $this->parseWithoutCurrency($line);
    }

    private function parseWithCurrency(string $line): ?array
    {
        if (preg_match('/^(.+?)\s*([€$£¥₹])\s*(\d+[.,]\d{1,2}|\d+)\s*(.*)$/u', $line, $matches)) {
            return [
                'product' => trim($matches[1]),
                'price' => (float)str_replace(',', '.', $matches[3]),
                'unit' => !empty(trim($matches[4])) ? trim($matches[4]) : null
            ];
        }

        return null;
    }

    private function parseWithoutCurrency(string $line): ?array
    {
        if (preg_match('/^(.+?)\s+(\d+[.,]\d{1,2}|\d+)\s*(.*)$/u', $line, $matches)) {
            $product = trim($matches[1]);

            if (strlen($product) < 2) {
                return null;
            }

            return [
                'product' => $product,
                'price' => (float) str_replace(',', '.', $matches[2]),
                'unit' => !empty(trim($matches[3])) ? trim($matches[3]) : null
            ];
        }

        return null;
    }

    private function saveEntry(array $parsed, string $originalText, int $lineNum): void
    {
        try {
            PriceEntry::create([
                'product_name' => $parsed['product'],
                'price' => $parsed['price'],
                'unit' => $parsed['unit'],
                'original_text' => $originalText
            ]);
            $this->savedCount++;
        } catch (\Exception $e) {
            $this->errors[] = "Line " . ($lineNum + 1) . ": Error saving - " . $e->getMessage();
        }
    }

    private function buildResultMessage(): string
    {
        $message = "Successfully saved {$this->savedCount} price " .
                   ($this->savedCount === 1 ? 'entry' : 'entries') . "!";

        if (!empty($this->errors)) {
            $message .= " " . count($this->errors) . " error(s): " . implode('; ', $this->errors);
        }

        return $message;
    }
}