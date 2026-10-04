<?php

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $config = require dirname(__DIR__, 2) . '/config/database.php';

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['database'],
            $config['charset']
        );

        try {
            self::$connection = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );

            return self::$connection;
        } catch (PDOException $e) {
            $message = 'No se pudo establecer conexion con la base de datos.';

            if (filter_var(
                getenv('APP_DEBUG'),
                FILTER_VALIDATE_BOOLEAN
            )) {
                $message .= ' Detalle PDO: ' . $e->getMessage();
            }

            throw new RuntimeException(
                $message,
                0,
                $e
            );
        }
    }

    private function __construct()
    {
    }
}