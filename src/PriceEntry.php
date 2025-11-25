<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PriceEntry extends Model
{
    protected $fillable = [
        'product_name',
        'price',
        'unit',
        'original_text'
    ];

    protected $casts = [
        'price' => 'decimal:2'
    ];
}