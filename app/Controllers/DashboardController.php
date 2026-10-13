<?php

namespace App\Controllers;

use App\Core\Session;
use App\Middleware\AuthMiddleware;
use App\Helpers\View;
use App\Services\PedidoService;

class DashboardController
{
    public function index(): void
    {
        AuthMiddleware::handle();

        $service = new PedidoService();

        View::render('dashboard.index', [
            'user' => Session::get('auth_user'),
            'dashboard' => $service->dashboard()
        ]);
    }
}
