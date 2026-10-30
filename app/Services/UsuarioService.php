<?php

namespace App\Services;

use App\Repositories\UsuarioRepository;

class UsuarioService
{
    private UsuarioRepository $repository;

    public function __construct()
    {
        $this->repository = new UsuarioRepository();
    }

    public function all(): array
    {
        return $this->repository->all();
    }

    public function roles(): array
    {
        return $this->repository->roles();
    }

    public function create(array $input): int
    {
        $data = [
            'nombres' => trim((string) ($input['nombres'] ?? '')),
            'apellidos' => trim((string) ($input['apellidos'] ?? '')),
            'usuario' => strtolower(trim((string) ($input['usuario'] ?? ''))),
            'correo' => strtolower(trim((string) ($input['correo'] ?? ''))),
            'id_rol' => (int) ($input['id_rol'] ?? 0),
        ];
        $password = (string) ($input['password'] ?? '');
        $confirmation = (string) ($input['password_confirmation'] ?? '');
        $errors = [];

        if ($data['nombres'] === '' || mb_strlen($data['nombres']) > 100) $errors[] = 'Ingrese nombres válidos.';
        if ($data['apellidos'] === '' || mb_strlen($data['apellidos']) > 100) $errors[] = 'Ingrese apellidos válidos.';
        if (!preg_match('/^[a-z0-9._-]{4,50}$/', $data['usuario'])) $errors[] = 'El usuario debe tener entre 4 y 50 caracteres y usar solo letras, números, punto, guion o guion bajo.';
        if (!filter_var($data['correo'], FILTER_VALIDATE_EMAIL) || mb_strlen($data['correo']) > 120) $errors[] = 'Ingrese un correo válido.';
        if (!$this->repository->isActiveRole($data['id_rol'])) $errors[] = 'Seleccione un rol válido.';
        if (strlen($password) < 8 || strlen($password) > 72 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) $errors[] = 'La contraseña debe tener entre 8 y 72 caracteres, con letras y números.';
        if ($password !== $confirmation) $errors[] = 'Las contraseñas no coinciden.';
        if ($this->repository->exists($data['usuario'], $data['correo'])) $errors[] = 'El usuario o correo ya está registrado.';

        if ($errors) throw new \DomainException(implode(' ', array_unique($errors)));
        $data['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        return $this->repository->create($data);
    }

    public function toggleState(int $userId, int $currentUserId): void
    {
        if ($userId <= 0) throw new \DomainException('Usuario inválido.');
        if ($userId === $currentUserId) throw new \DomainException('No puedes desactivar tu propia cuenta.');
        if (!$this->repository->toggleState($userId)) throw new \DomainException('El usuario no existe.');
    }
}
