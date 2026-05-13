<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// We use 'test' because the baseURL already ends in /api/
$routes->get('test', 'Api\TestController::getStatus');

$routes->group('auth', static function ($routes) {
    $routes->post('login', 'Api\AuthController::login');
    $routes->post('logout', 'Api\AuthController::logout', ['filter' => 'auth']);
    $routes->get('me', 'Api\AuthController::me', ['filter' => 'auth']);
});

$routes->group('books', ['filter' => 'auth'], static function ($routes) {
    $routes->get('', 'Api\BooksController::index');
    $routes->get('(:segment)', 'Api\BooksController::show/$1');
    $routes->get('(:segment)/notes', 'Api\NotesController::index/$1');
    $routes->get('(:segment)/todos', 'Api\TodosController::index/$1');
    $routes->get('(:segment)/finance', 'Api\FinanceController::index/$1');
});
