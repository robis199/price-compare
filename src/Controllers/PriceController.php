<?php

namespace App\Controllers;

use App\Models\PriceEntry;
use App\Services\PriceParser;
use Twig\Environment;

class PriceController
{
    private const MAX_INPUT_LENGTH = 10000;

    public function __construct(
        private readonly Environment $twig,
        private readonly string $basePath = ''
    ) {}

    public function index(): void
    {
        $this->startSession();
        $this->generateCsrfToken();

        $message = '';
        $entries = PriceEntry::orderBy('created_at', 'desc')->get();

        echo $this->twig->render('prices/index.twig', [
            'message' => $message,
            'entries' => $entries,
            'csrf_token' => $_SESSION['csrf_token'],
            'base_path' => $this->basePath
        ]);
    }

    public function store(): void
    {
        $this->startSession();

        // Validate CSRF token
        if (!$this->validateCsrfToken()) {
            http_response_code(403);
            die('CSRF validation failed');
        }

        $message = '';

        if (isset($_POST['price_data'])) {
            $priceData = trim($_POST['price_data']);

            // Validate input length
            if (strlen($priceData) > self::MAX_INPUT_LENGTH) {
                $message = 'Error: Input exceeds maximum length of ' . number_format(self::MAX_INPUT_LENGTH) . ' characters.';
            } elseif (!empty($priceData)) {
                $parser = new PriceParser();
                $message = $parser->processAndStore($priceData);
            }
        }

        $entries = PriceEntry::orderBy('created_at', 'desc')->get();

        echo $this->twig->render('prices/index.twig', [
            'message' => $message,
            'entries' => $entries,
            'csrf_token' => $_SESSION['csrf_token'],
            'base_path' => $this->basePath
        ]);
    }

    private function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    private function generateCsrfToken(): void
    {
        if (!isset($_SESSION['csrf_token'])) {
            try {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            } catch (\Random\RandomException $e) {
                die('Failed to generate CSRF token');
            }
        }
    }

    private function validateCsrfToken(): bool
    {
        if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token'])) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
    }
}
