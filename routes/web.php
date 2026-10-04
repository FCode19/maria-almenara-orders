<?php

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Core\Session;
use App\Helpers\Response;
use App\Middleware\RoleMiddleware;

$router->get('/', function (): void {
    if (Session::has('auth_user')) {
        Response::redirect('/dashboard');
    }

    Response::redirect('/login');
});

$router->get(
    '/login',
    [AuthController::class, 'showLogin']
);

$router->post(
    '/login',
    [AuthController::class, 'login']
);

$router->post(
    '/logout',
    [AuthController::class, 'logout']
);

$router->get(
    '/dashboard',
    [DashboardController::class, 'index']
);



$router->get('/usuarios', function (): void {
    RoleMiddleware::handle('Administrador');

    Response::html(
        '<h1>Usuarios</h1>
         <p>Acceso administrativo autorizado.</p>
         <p>Modulo pendiente de implementacion funcional.</p>'
    );
});