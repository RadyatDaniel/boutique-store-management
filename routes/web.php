<?php
/**
 * Application Routes
 * Define all routes for the application
 */

namespace App\Routes;

use App\Core\Router;

/**
 * Initialize router and register all routes
 */
return function (Router $router) {
    
    // ============================================
    // AUTH ROUTES (Public)
    // ============================================
    $router->get('/', 'AuthController@showLogin', 'home');
    $router->get('/login', 'AuthController@showLogin', 'login');
    $router->post('/login', 'AuthController@login', 'login.store');
    $router->post('/logout', 'AuthController@logout', 'logout');
    $router->get('/register', 'AuthController@showRegister', 'register');
    $router->post('/register', 'AuthController@register', 'register.store');

    // ============================================
    // PROTECTED ROUTES (Require Authentication)
    // ============================================
    
    // DASHBOARD
    $router->get('/dashboard', 'DashboardController@index', 'dashboard');

    // ============================================
    // MANAGER ROUTES
    // ============================================
    $router->group(['prefix' => '/manager', 'middleware' => 'auth.manager'], function ($router) {
        
        // Branch Management
        $router->resource('branches', 'BranchController');
        $router->get('/branches/{id}/edit', 'BranchController@edit', 'branches.edit');
        $router->put('/branches/{id}', 'BranchController@update', 'branches.update');
        $router->delete('/branches/{id}', 'BranchController@destroy', 'branches.destroy');

        // User Management
        $router->resource('users', 'UserController');
        $router->get('/users/{id}/edit', 'UserController@edit', 'users.edit');
        $router->put('/users/{id}', 'UserController@update', 'users.update');
        $router->delete('/users/{id}', 'UserController@destroy', 'users.destroy');

        // Inventory Management
        $router->get('/inventory', 'InventoryController@index', 'inventory.index');
        $router->put('/inventory/{id}', 'InventoryController@updatePrice', 'inventory.updatePrice');

        // Reports
        $router->get('/reports/sales', 'ReportController@salesReport', 'reports.sales');
        $router->get('/reports/inventory', 'ReportController@inventoryReport', 'reports.inventory');
        $router->get('/reports/performance', 'ReportController@performanceReport', 'reports.performance');

        // Stock Transfers
        $router->get('/transfers', 'TransferController@index', 'transfers.index');
        $router->post('/transfers', 'TransferController@store', 'transfers.store');
    });

    // ============================================
    // STORE KEEPER ROUTES
    // ============================================
    $router->group(['prefix' => '/store-keeper', 'middleware' => 'auth.store_keeper'], function ($router) {
        
        // Inventory Operations
        $router->get('/inventory', 'InventoryController@index', 'inventory.index');
        $router->get('/inventory/create', 'InventoryController@create', 'inventory.create');
        $router->post('/inventory', 'InventoryController@store', 'inventory.store');
        $router->get('/inventory/{id}/edit', 'InventoryController@edit', 'inventory.edit');
        $router->put('/inventory/{id}', 'InventoryController@update', 'inventory.update');

        // Stock Management
        $router->get('/stock', 'StockController@index', 'stock.index');
        $router->put('/stock/{id}', 'StockController@update', 'stock.update');
        $router->post('/stock/receive', 'StockController@receive', 'stock.receive');
        $router->post('/stock/damage', 'StockController@recordDamage', 'stock.damage');

        // Reports
        $router->get('/reports/inventory', 'ReportController@inventoryReport', 'reports.inventory');
        $router->get('/reports/alerts', 'ReportController@stockAlerts', 'reports.alerts');
        
        // Sales Tracking
        $router->get('/sales', 'SalesController@myDailySales', 'sales.daily');
    });

    // ============================================
    // SELLER ROUTES
    // ============================================
    $router->group(['prefix' => '/seller', 'middleware' => 'auth.seller'], function ($router) {
        
        // Sales Operations
        $router->get('/sales/create', 'SalesController@create', 'sales.create');
        $router->post('/sales', 'SalesController@store', 'sales.store');
        $router->get('/sales/{id}/receipt', 'SalesController@receipt', 'sales.receipt');

        // Inventory Access
        $router->get('/inventory', 'InventoryController@view', 'inventory.view');
        $router->get('/inventory/search', 'InventoryController@search', 'inventory.search');

        // Sales Tracking
        $router->get('/sales/daily', 'SalesController@myDailySales', 'sales.daily');
        $router->get('/sales/weekly', 'SalesController@myWeeklySales', 'sales.weekly');
        $router->get('/sales/monthly', 'SalesController@myMonthlySales', 'sales.monthly');
    });

    // ============================================
    // API ROUTES (Optional - JSON responses)
    // ============================================
    $router->group(['prefix' => '/api/v1'], function ($router) {
        
        // Inventory API
        $router->get('/inventory', 'Api\InventoryController@index', 'api.inventory.index');
        $router->get('/inventory/{id}', 'Api\InventoryController@show', 'api.inventory.show');
        $router->post('/inventory', 'Api\InventoryController@store', 'api.inventory.store');
        $router->put('/inventory/{id}', 'Api\InventoryController@update', 'api.inventory.update');

        // Sales API
        $router->get('/sales', 'Api\SalesController@index', 'api.sales.index');
        $router->post('/sales', 'Api\SalesController@store', 'api.sales.store');

        // Branch API
        $router->get('/branches', 'Api\BranchController@index', 'api.branches.index');
    });

    // ============================================
    // ERROR ROUTES
    // ============================================
    $router->get('/404', 'ErrorController@notFound', 'error.404');
    $router->get('/500', 'ErrorController@serverError', 'error.500');

};
?>
