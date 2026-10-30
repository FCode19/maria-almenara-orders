<?php

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\PedidoController;
use App\Controllers\UsuarioController;
use App\Controllers\ApiConsoleController;
use App\Core\Session;
use App\Helpers\Response;

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

$router->get('/pedidos', [PedidoController::class, 'index']);
$router->get('/pedidos/nuevo', [PedidoController::class, 'create']);
$router->post('/pedidos', [PedidoController::class, 'store']);
$router->get('/pedidos/{id}', [PedidoController::class, 'show']);
$router->post('/pedidos/{id}/estado', [PedidoController::class, 'updateState']);



$router->get('/usuarios', [UsuarioController::class, 'index']);
$router->get('/usuarios/nuevo', [UsuarioController::class, 'create']);
$router->post('/usuarios', [UsuarioController::class, 'store']);
$router->post('/usuarios/{id}/estado', [UsuarioController::class, 'toggleState']);
$router->get('/apis', [ApiConsoleController::class, 'index']);
