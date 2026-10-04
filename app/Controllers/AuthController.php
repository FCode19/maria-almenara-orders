<?php

namespace App\Controllers;

use App\Core\Session;
use App\Helpers\Csrf;
use App\Helpers\Response;
use App\Helpers\View;
use App\Services\AuthService;

class AuthController
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function showLogin(): void
    {
        if (Session::has('auth_user')) {
            Response::redirect('/dashboard');
        }

        View::render('auth.login', [
            'error' => Session::getFlash('error')
        ]);
    }

    public function login(): void
    {
        if (!Csrf::validate($_POST['_token'] ?? null)) {
            Session::flash(
                'error',
                'La sesion del formulario expiro. Intenta nuevamente.'
            );

            Response::redirect('/login');
        }

        $login = trim($_POST['login'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($login === '' || $password === '') {
            Session::flash(
                'error',
                'Ingrese usuario y contrasena.'
            );

            Response::redirect('/login');
        }

        $user = $this->authService->authenticate(
            $login,
            $password
        );

        if (!$user) {
            Session::flash(
                'error',
                'Credenciales invalidas.'
            );

            Response::redirect('/login');
        }

        Session::regenerate();

        Session::set('auth_user', $user);

        Csrf::regenerate();

        Response::redirect('/dashboard');
    }

    public function logout(): void
    {
        if (!Csrf::validate($_POST['_token'] ?? null)) {
            Response::html(
                '<h1>419</h1><p>Solicitud expirada.</p>',
                419
            );
        }

        Session::destroy();

        Response::redirect('/login');
    }
}