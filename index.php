<?php

require_once __DIR__ . '/bootstrap.php';

use FastRoute\RouteCollector;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

// Setup Twig
$loader = new FilesystemLoader(__DIR__ . '/src/Views');
$twig = new Environment($loader, [
    'cache' => false, // Disable cache for development
    'autoescape' => 'html',
]);

// Setup FastRoute
$dispatcher = FastRoute\simpleDispatcher(require __DIR__ . '/routes.php');

// Get HTTP method and URI
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

// Auto-detect base path from script location
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
$basePath = $scriptName === '/' ? '' : $scriptName;

// Remove base path from URI
if ($basePath !== '' && strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
}

// Ensure URI starts with /
if (empty($uri) || $uri[0] !== '/') {
    $uri = '/' . $uri;
}

// Dispatch route
$routeInfo = $dispatcher->dispatch($httpMethod, $uri);


switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        http_response_code(404);
        echo '404 Not Found';
        break;

    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        http_response_code(405);
        echo '405 Method Not Allowed';
        break;

    case FastRoute\Dispatcher::FOUND:
        [$controller, $method] = $routeInfo[1];
        $vars = $routeInfo[2];

        // Instantiate controller and call method
        $controllerInstance = new $controller($twig, $basePath);
        $controllerInstance->$method(...array_values($vars));
        break;
}
