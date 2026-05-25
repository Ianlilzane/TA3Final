<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Diretso login box agad pagkabukas ng site
$routes->get('/', 'AuthController::login');
$routes->setAutoRoute(false);

// Auth routes
$routes->get('register', 'AuthController::register');
$routes->post('register', 'AuthController::registerStore');
$routes->get('login', 'AuthController::login');
$routes->post('login', 'AuthController::loginStore');
$routes->get('logout', 'AuthController::logout');

// Shop AJAX routes
$routes->group('shop', ['filter' => 'authfilter'], function($routes) {
    $routes->get('/', 'ShopController::index');
    $routes->post('placeOrder', 'ShopController::placeOrder');
    $routes->post('confirmDelivery', 'ShopController::confirmDelivery');
    $routes->post('cancelOrder', 'ShopController::cancelOrder');
});

// Admin routes (protected and role-checked)
$routes->group('admin', ['filter' => 'adminfilter'], function($routes) {
    $routes->get('dashboard', 'Admin\DashboardController::index');
    $routes->post('dashboard/updateStatus', 'Admin\DashboardController::updateStatus');
    $routes->post('dashboard/updateImage', 'Admin\DashboardController::updateProductImage');
    $routes->post('dashboard/createProduct', 'Admin\DashboardController::createProduct');
    $routes->post('dashboard/updateProduct', 'Admin\DashboardController::updateProduct');
    $routes->post('dashboard/deleteProduct', 'Admin\DashboardController::deleteProduct');
});

// Finance routes (protected and role-checked)
$routes->group('finance', ['filter' => 'financefilter'], function($routes) {
    $routes->get('dashboard', 'FinanceController::index');
    $routes->get('transactions', 'FinanceController::transactions');
});

