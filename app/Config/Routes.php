<?php

use CodeIgniter\Router\RouteCollection;

$routes->options('api/(:any)', function () {
    return response()->setStatusCode(200);
});

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->get('/api/test', 'Home::test');
$routes->post('/api/login', 'AccessController::login');
$routes->post('/api/register', 'AccessController::register');
$routes->post('/api/google-login', 'AccessController::googleLogin');

$routes->group('api', ['filter' => 'jwt'], static function ($routes) {
    $routes->get('me', 'AccessController::me');
});