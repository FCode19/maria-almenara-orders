<?php

namespace App\Middleware;

use App\Core\Session;
use App\Helpers\Response;

class ApiAuthMiddleware
{
    public static function handle(): void
    {
        $user = Session::get('auth_user');
        if (($user['rol'] ?? '') === 'Administrador') {
            return;
        }

        $configuredKey = (string) (getenv('API_ACCESS_KEY') ?: '');
        if ($configuredKey === '') {
            Response::json([
                'status' => 'error',
                'message' => 'La clave de acceso de la API no está configurada.'
            ], 503);
        }

        $providedKey = trim((string) ($_SERVER['HTTP_X_API_KEY'] ?? ''));
        $authorization = trim((string) ($_SERVER['HTTP_AUTHORIZATION'] ?? ''));
        if ($providedKey === '' && preg_match('/^Bearer\s+(.+)$/i', $authorization, $matches)) {
            $providedKey = trim($matches[1]);
        }

        if ($providedKey === '' || !hash_equals($configuredKey, $providedKey)) {
            Response::json([
                'status' => 'error',
                'message' => 'No autorizado.'
            ], 401);
        }
    }
}
