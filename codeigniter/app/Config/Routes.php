<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// We use 'test' because the baseURL already ends in /api/
$routes->get('test', 'Api\TestController::getStatus');
