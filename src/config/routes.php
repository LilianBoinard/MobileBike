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

$router->get('/', 'HomeController@index')->name('home');
$router->get('/about', 'AboutController@index')->name('about');
$router->get('/services', 'ServicesController@index')->name('services');
$router->get('/products', 'ProductsController@index')->name('products');
$router->get('/contact', 'ContactController@index')->name('contact');
$router->get('/test/{id}', 'TestController@index')
    ->where(['id' => '\d+'])
    ->name('test');