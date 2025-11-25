<?php

use FastRoute\RouteCollector;
use App\Controllers\PriceController;

return function(RouteCollector $r) {
    $r->addRoute('GET', '/', [PriceController::class, 'index']);
    $r->addRoute('POST', '/store', [PriceController::class, 'store']);
};
