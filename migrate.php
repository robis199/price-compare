<?php

require_once __DIR__ . '/bootstrap.php';

use Illuminate\Database\Capsule\Manager as Capsule;

// Create price_entries table
Capsule::schema()->dropIfExists('price_entries');

Capsule::schema()->create('price_entries', function ($table) {
    $table->id();
    $table->string('product_name');
    $table->decimal('price', 10, 2);
    $table->string('unit')->nullable();
    $table->text('original_text');
    $table->timestamps();
});

echo "Migration completed successfully!\n";
