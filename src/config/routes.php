<?php
// Le router est disponible via la variable $router
/**
// Routes principales
$router->get('/', 'HomeController@index')->name('home');

// Routes utilisateurs
$router->get('/users', 'UserController@index')->name('users.index');
$router->get('/users/{id}', 'UserController@show')
    ->where(['id' => '\d+'])
    ->name('users.show');
$router->post('/users', 'UserController@store')->name('users.store');

// Routes avec contraintes
$router->get('/posts/{slug}', 'PostController@show')
    ->where(['slug' => '[a-z0-9-]+'])
    ->name('posts.show');

// Groupes logiques
$router->get('/admin/dashboard', 'Admin\DashboardController@index')->name('admin.dashboard');
$router->get('/admin/users', 'Admin\UserController@index')->name('admin.users');
**/

// HOME
$router->get('/', 'AboutController@index')->name('home');


// User Pages
$router->get('/about', 'AboutController@index')->name('about');
$router->get('/services', 'ServicesController@index')->name('services');
$router->get('/products', 'ProductsController@index')->name('products');
$router->get('/contact', 'ContactController@index')->name('contact');
$router->get('/login', 'LoginController@index')->name('login');
$router->post('/login', 'UserController@attemptLogin')->name('login');
$router->get('/logout', 'UserController@attemptLogout')->name('logout');

// Dashboard section
$router->get('/dashboard/home', 'DashboardController@index')->name('dashboard');
$router->get('/dashboard/administration', 'DashboardAdministrationController@index')->name('dashboard.administration');

// Dashboard Product
$router->get('/dashboard/products', 'DashboardProductsController@index')->name('dashboard.products');

$router->get('/dashboard/products/mobilebike/add', 'DashboardProductsController@displayAddMobileBikePage')->name('dashboard.products.mobilebike.add');
$router->post('/dashboard/products/mobilebike/add', 'DashboardProductsController@saveMobileBike')->name('dashboard.products.mobilebike.add');

$router->get('/dashboard/products/sparepart/add', 'DashboardProductsController@displayAddSparePartPage')->name('dashboard.products.sparepart.add');
$router->post('/dashboard/products/sparepart/add', 'DashboardProductsController@saveSparePart')->name('dashboard.products.sparepart.add');

$router->post('/dashboard/products/delete/{id}', 'DashboardProductsController@deleteProduct')
    ->where(['id' => '\d+'])
    ->name('dashboard.products.delete');

$router->get('/dashboard/products/edit/{id}', 'DashboardProductsController@displayEditProductPage')
    ->where(['id' => '\d+'])
    ->name('dashboard.products.edit');
$router->post('/dashboard/products/edit/{id}', 'DashboardProductsController@editProduct')
    ->where(['id' => '\d+'])
    ->name('dashboard.products.edit');

// Dashboard Profile
$router->get('/dashboard/profile/edit', 'DashboardProfile@index')->name('dashboard.profile.edit');
$router->post('/dashboard/profile/edit', 'DashboardProfile@update')->name('dashboard.profile.edit');

// Dashboard Cart
$router->get('/dashboard/cart', 'DashboardCartController@index')->name('dashboard.cart');
$router->post('/dashboard/cart/add', 'DashboardCartController@addToCart')->name('dashboard.cart.addItem');
$router->post('/dashboard/cart/clear', 'DashboardCartController@clearCart')->name('dashboard.cart.clear');
$router->post('/dashboard/cart/remove', 'DashboardCartController@removeFromCart')->name('dashboard.cart.removeItem');
$router->post('/dashboard/cart/update-quantity', 'DashboardCartController@updateQuantity')->name('dashboard.cart.updateItemQuantity');