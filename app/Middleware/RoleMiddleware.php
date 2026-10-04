<?php

namespace App\Middleware;

use App\Core\Session;
use App\Helpers\Response;

class RoleMiddleware
{
    public static function handle(
        string|array $roles
    ): void {
        AuthMiddleware::handle();

        $roles = (array) $roles;

        $user = Session::get('auth_user');

        if (
            !$user ||
            !in_array($user['rol'], $roles, true)
        ) {
            Response::html(
                '<h1>403</h1><p>No tienes permisos para acceder a este recurso.</p>',
                403
            );
        }
    }
}