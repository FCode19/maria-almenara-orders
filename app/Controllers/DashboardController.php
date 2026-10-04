<?php

namespace App\Controllers;

use App\Core\Session;
use App\Middleware\AuthMiddleware;
use App\Helpers\View;

class DashboardController
{
    public function index(): void
    {
        AuthMiddleware::handle();

        View::render('dashboard.index', [
            'user' => Session::get('auth_user')
        ]);
    }
}