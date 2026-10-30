<?php

namespace App\Controllers;

use App\Core\Session;
use App\Helpers\Csrf;
use App\Helpers\Response;
use App\Helpers\View;
use App\Middleware\RoleMiddleware;
use App\Services\UsuarioService;
use Throwable;

class UsuarioController
{
    private UsuarioService $service;

    public function __construct()
    {
        $this->service = new UsuarioService();
    }

    public function index(): void
    {
        $this->authorize();
        View::render('usuarios.index', [
            'user' => Session::get('auth_user'),
            'users' => $this->service->all(),
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error'),
        ]);
    }

    public function create(): void
    {
        $this->authorize();
        View::render('usuarios.create', [
            'user' => Session::get('auth_user'),
            'roles' => $this->service->roles(),
            'old' => Session::getFlash('old', []),
            'error' => Session::getFlash('error'),
        ]);
    }

    public function store(): void
    {
        $this->authorize();
        $this->validateCsrf('/usuarios/nuevo');
        try {
            $this->service->create($_POST);
            Session::flash('success', 'Usuario creado correctamente.');
            Response::redirect('/usuarios');
        } catch (\DomainException $e) {
            Session::flash('error', $e->getMessage());
            Session::flash('old', $this->safeOld($_POST));
            Response::redirect('/usuarios/nuevo');
        } catch (Throwable $e) {
            error_log($e->getMessage());
            Session::flash('error', 'No se pudo crear el usuario.');
            Session::flash('old', $this->safeOld($_POST));
            Response::redirect('/usuarios/nuevo');
        }
    }

    public function toggleState(string $id): void
    {
        $this->authorize();
        $this->validateCsrf('/usuarios');
        try {
            $current = Session::get('auth_user');
            $this->service->toggleState((int) $id, (int) $current['id_usuario']);
            Session::flash('success', 'Estado del usuario actualizado.');
        } catch (\DomainException $e) {
            Session::flash('error', $e->getMessage());
        } catch (Throwable $e) {
            error_log($e->getMessage());
            Session::flash('error', 'No se pudo actualizar el usuario.');
        }
        Response::redirect('/usuarios');
    }

    private function authorize(): void
    {
        RoleMiddleware::handle('Administrador');
    }

    private function validateCsrf(string $redirect): void
    {
        if (!Csrf::validate($_POST['_token'] ?? null)) {
            Session::flash('error', 'La sesión del formulario expiró. Intenta nuevamente.');
            Response::redirect($redirect);
        }
    }

    private function safeOld(array $input): array
    {
        unset($input['password'], $input['password_confirmation'], $input['_token']);
        return $input;
    }
}
