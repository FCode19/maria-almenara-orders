<?php

namespace App\Services;

use App\Repositories\UsuarioRepository;

class AuthService
{
    private UsuarioRepository $repository;

    public function __construct()
    {
        $this->repository = new UsuarioRepository();
    }

    public function authenticate(
        string $login,
        string $password
    ): ?array {
        $login = trim($login);

        if ($login === '' || $password === '') {
            return null;
        }

        if (mb_strlen($login) > 120) {
            return null;
        }

        $user = $this->repository->findByLogin($login);

        if (!$user) {
            return null;
        }

        if ((int) $user['activo'] !== 1) {
            return null;
        }

        if ((int) $user['rol_activo'] !== 1) {
            return null;
        }

        if (!password_verify(
            $password,
            $user['password_hash']
        )) {
            return null;
        }

        unset(
            $user['password_hash'],
            $user['activo'],
            $user['rol_activo']
        );

        return $user;
    }
}