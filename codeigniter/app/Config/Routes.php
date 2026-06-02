<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// We use 'test' because the baseURL already ends in /api/
$routes->get('test', 'Api\TestController::getStatus');
$routes->get('public/books/(:segment)/minishop/sales/(:segment)/receipt.pdf', 'Api\MinishopPublicReceiptsController::pdf/$1/$2');
$routes->get('public/books/(:segment)/minishop/sales/(:segment)/receipt', 'Api\MinishopPublicReceiptsController::show/$1/$2');

$routes->group('auth', static function ($routes) {
    $routes->post('login', 'Api\AuthController::login');
    $routes->post('register', 'Api\AuthController::register');
    $routes->post('logout', 'Api\AuthController::logout', ['filter' => 'auth']);
    $routes->get('me', 'Api\AuthController::me', ['filter' => 'auth']);
    $routes->put('profile', 'Api\ProfileController::updateProfile', ['filter' => 'auth']);
    $routes->put('password', 'Api\ProfileController::updatePassword', ['filter' => 'auth']);
});

$routes->group('books', ['filter' => 'auth'], static function ($routes) {
    $routes->get('', 'Api\BooksController::index');
    $routes->post('', 'Api\BooksController::create');
    // Register fixed subpaths before `(:segment)` so they are not mistaken for book IDs.
    $routes->get('archived', 'Api\BooksController::archived');
    $routes->get('types', 'Api\BooksController::types');
    $routes->post('(:segment)/archive', 'Api\BooksController::archive/$1');
    $routes->post('(:segment)/restore', 'Api\BooksController::restore/$1');
    $routes->put('(:segment)', 'Api\BooksController::update/$1');
    $routes->delete('(:segment)', 'Api\BooksController::delete/$1');
    $routes->get('(:segment)', 'Api\BooksController::show/$1');
    $routes->get('(:segment)/notes', 'Api\NotesController::index/$1');
    $routes->post('(:segment)/notes', 'Api\NotesController::create/$1');
    $routes->put('(:segment)/notes/(:segment)', 'Api\NotesController::update/$1/$2');
    $routes->post('(:segment)/notes/(:segment)/pin', 'Api\NotesController::pin/$1/$2');
    $routes->post('(:segment)/notes/(:segment)/unpin', 'Api\NotesController::unpin/$1/$2');
    $routes->post('(:segment)/notes/(:segment)/archive', 'Api\NotesController::archive/$1/$2');
    $routes->delete('(:segment)/notes/(:segment)', 'Api\NotesController::delete/$1/$2');
    $routes->get('(:segment)/finance', 'Api\FinanceController::index/$1');
    $routes->get('(:segment)/minishop/categories', 'Api\MinishopCategoriesController::index/$1');
    $routes->post('(:segment)/minishop/categories/manage', 'Api\MinishopCategoriesController::manage/$1');
    $routes->get('(:segment)/minishop/products', 'Api\MinishopProductsController::index/$1');
    $routes->post('(:segment)/minishop/products', 'Api\MinishopProductsController::create/$1');
    $routes->put('(:segment)/minishop/products/(:segment)', 'Api\MinishopProductsController::update/$1/$2');
    $routes->post('(:segment)/minishop/products/(:segment)/deactivate', 'Api\MinishopProductsController::deactivate/$1/$2');
    $routes->get('(:segment)/minishop/customers/list', 'Api\MinishopCustomersController::listOptions/$1');
    $routes->get('(:segment)/minishop/customers', 'Api\MinishopCustomersController::index/$1');
    $routes->get('(:segment)/minishop/customers/(:segment)', 'Api\MinishopCustomersController::show/$1/$2');
    $routes->post('(:segment)/minishop/customers', 'Api\MinishopCustomersController::create/$1');
    $routes->put('(:segment)/minishop/customers/(:segment)', 'Api\MinishopCustomersController::update/$1/$2');
    $routes->get('(:segment)/minishop/sales', 'Api\MinishopSalesController::index/$1');
    $routes->get('(:segment)/minishop/sales/analytics', 'Api\MinishopSalesController::analytics/$1');
    $routes->get('(:segment)/minishop/sales/(:segment)', 'Api\MinishopSalesController::show/$1/$2');
    $routes->post('(:segment)/minishop/sales', 'Api\MinishopSalesController::create/$1');
    $routes->post('(:segment)/minishop/sales/(:segment)/payments', 'Api\MinishopSalesController::addPayment/$1/$2');
    $routes->delete('(:segment)/minishop/sales/(:segment)/payments/(:segment)', 'Api\MinishopSalesController::deletePayment/$1/$2/$3');
    $routes->delete('(:segment)/minishop/sales/(:segment)', 'Api\MinishopSalesController::delete/$1/$2');
});
