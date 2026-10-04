<?php

namespace App\Repositories;

use App\Core\Database;

class UsuarioRepository
{
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