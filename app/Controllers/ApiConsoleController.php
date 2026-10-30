<?php

namespace App\Controllers;

use App\Core\Session;
use App\Helpers\View;
use App\Middleware\RoleMiddleware;

class ApiConsoleController
{
    public function index(): void
    {
        RoleMiddleware::handle('Administrador');
        View::render('apis.index', [
            'user' => Session::get('auth_user'),
            'externalKeyConfigured' => (string) (getenv('API_ACCESS_KEY') ?: '') !== '',
        ]);
    }
}
