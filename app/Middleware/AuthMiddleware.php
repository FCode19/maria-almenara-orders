<?php

namespace App\Middleware;

use App\Core\Session;
use App\Helpers\Response;

class AuthMiddleware
{
    public static function handle(): void
    {
        if (!Session::has('auth_user')) {
            Session::flash(
                'error',
                'Debes iniciar sesion para continuar.'
            );

            Response::redirect('/login');
        }
    }
}