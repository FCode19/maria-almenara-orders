<?php

namespace App\Repositories;

use App\Core\Database;

class UsuarioRepository
{
    public function all(): array
    {
        return Database::connection()->query(
            "SELECT u.id_usuario, u.nombres, u.apellidos, u.usuario, u.correo, u.activo,
                    u.fecha_creacion, r.nombre AS rol
             FROM tbl_usuario u INNER JOIN tbl_rol r ON r.id_rol = u.id_rol
             ORDER BY u.activo DESC, u.fecha_creacion DESC, u.id_usuario DESC"
        )->fetchAll();
    }

    public function roles(): array
    {
        return Database::connection()->query(
            'SELECT id_rol, nombre FROM tbl_rol WHERE activo = 1 ORDER BY nombre'
        )->fetchAll();
    }

    public function isActiveRole(int $roleId): bool
    {
        $statement = Database::connection()->prepare('SELECT COUNT(*) FROM tbl_rol WHERE id_rol = :id AND activo = 1');
        $statement->execute(['id' => $roleId]);
        return (int) $statement->fetchColumn() === 1;
    }

    public function exists(string $username, string $email): bool
    {
        $statement = Database::connection()->prepare('SELECT COUNT(*) FROM tbl_usuario WHERE usuario = :usuario OR correo = :correo');
        $statement->execute(['usuario' => $username, 'correo' => $email]);
        return (int) $statement->fetchColumn() > 0;
    }

    public function create(array $data): int
    {
        $statement = Database::connection()->prepare(
            'INSERT INTO tbl_usuario (id_rol, nombres, apellidos, usuario, correo, password_hash, activo)
             VALUES (:id_rol, :nombres, :apellidos, :usuario, :correo, :password_hash, 1)'
        );
        $statement->execute($data);
        return (int) Database::connection()->lastInsertId();
    }

    public function toggleState(int $userId): bool
    {
        $statement = Database::connection()->prepare(
            'UPDATE tbl_usuario SET activo = IF(activo = 1, 0, 1) WHERE id_usuario = :id'
        );
        $statement->execute(['id' => $userId]);
        return $statement->rowCount() === 1;
    }

    public function findByLogin(string $login): ?array
    {
        $db = Database::connection();

        $sql = "
            SELECT
                u.id_usuario,
                u.id_rol,
                u.nombres,
                u.apellidos,
                u.usuario,
                u.correo,
                u.password_hash,
                u.activo,
                r.nombre AS rol,
                r.activo AS rol_activo
            FROM tbl_usuario u
            INNER JOIN tbl_rol r
                ON r.id_rol = u.id_rol
            WHERE
                u.usuario = :usuario
                OR u.correo = :correo
            LIMIT 1
        ";

        $statement = $db->prepare($sql);

        $statement->execute([
            'usuario' => $login,
            'correo' => $login
        ]);

        $user = $statement->fetch();

        return $user ?: null;
    }
}
