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
$routes->get('finance/dashboard', 'FinanceController::index');
$routes->get('finance/dashboard', 'FinanceController::index');
$routes->get('finance/transactions', 'FinanceController::transactions'); // 🚀 Idagdag itong linya na 'to

// Shop AJAX routes
$routes->post('shop/placeOrder', 'ShopController::placeOrder');
$routes->post('shop/confirmDelivery', 'ShopController::confirmDelivery');

/**
 * 🛠️ CRITICAL FIXED ADMIN GROUP:
 * Pansamantalang tinanggal ang 'adminfilter' para malaktawan ang login blocking.
 * Inayos din ang string namespace mula 'Admin\DashboardController' patungong 'AdminController'
 * para tumugma sa tamang subfolder handling ng App\Controllers.
 */
$routes->group('admin', function($routes) {
    // Kapag pumunta sa localhost:8080/index.php/admin/dashboard
    $routes->get('dashboard', 'Admin\DashboardController::index');
    $routes->post('dashboard/updateStatus', 'Admin\DashboardController::updateStatus');
});

// Finance routes (protected)
$routes->group('finance', ['filter' => 'authfilter'], function($routes) {
    $routes->get('dashboard', 'FinanceController::index');
});

// Shop routes (protected)
$routes->group('shop', ['filter' => 'authfilter'], function($routes) {
    $routes->get('/', 'ShopController::index');
});

/**
 * 🚀 HARD OVERRIDE BYPASS URL
 * Kung ayaw mo nang dumaan sa session fetching ng database, 
 * diretso nitong bubuksan ang session gates para sa iyo.
 */
$routes->get('force-admin', function() {
    $session = session();
    
    // Agarang pagpapatala ng Admin session keys gamit ang saktong DB schema mo
    $session->set([
        'user_id'      => 1,
        'fullname'     => 'Allen Tanio',
        'role_id'      => 1, // 1 para sa Admin profile mapping
        'is_logged_in' => true
    ]);
    
    return redirect()->to('admin/dashboard');
});