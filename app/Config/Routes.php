<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// app/Config/Routes.php
$routes->get('/api/test', 'Home::test');
