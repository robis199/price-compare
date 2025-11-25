<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Validate required environment variables
$requiredEnvVars = ['DB_CONNECTION', 'DB_HOST', 'DB_DATABASE', 'DB_USERNAME'];

foreach ($requiredEnvVars as $var) {
    if (!isset($_ENV[$var])) {
        die("Missing required environment variable: $var. Please check your .env file.");
    }
}

// Validate database driver
$allowedDrivers = ['mysql', 'pgsql', 'sqlite', 'sqlsrv'];
if (!in_array($_ENV['DB_CONNECTION'], $allowedDrivers)) {
    die("Invalid database driver: {$_ENV['DB_CONNECTION']}. Allowed: " . implode(', ', $allowedDrivers));
}

// Setup Eloquent ORM
$capsule = new Capsule;

$capsule->addConnection([
    'driver'    => $_ENV['DB_CONNECTION'],
    'host'      => $_ENV['DB_HOST'],
    'database'  => $_ENV['DB_DATABASE'],
    'username'  => $_ENV['DB_USERNAME'],
    'password'  => $_ENV['DB_PASSWORD'] ?? '',
    'charset'   => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix'    => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

return $capsule;