<?php

use App\Core\Database;
use App\Controllers\ApiController;
use App\Helpers\Response;

$router->get('/api/health', function (): void {
    try {
        $db = Database::connection();

        $statement = $db->query('SELECT 1 AS health');

        $result = $statement->fetch();

        Response::json([
            'status' => 'ok',
            'application' => getenv('APP_NAME'),
            'environment' => getenv('APP_ENV'),
            'database' => $result['health'] == 1
                ? 'connected'
                : 'unknown'
        ]);
    } catch (\Throwable $e) {
        $response = [
            'status' => 'error',
            'database' => 'disconnected'
        ];

        if (filter_var(
            getenv('APP_DEBUG'),
            FILTER_VALIDATE_BOOLEAN
        )) {
            $response['detail'] = $e->getMessage();
        }

        Response::json($response, 500);
    }
});

$router->get('/api/v1/pedidos/resumen', [ApiController::class, 'summary']);
$router->get('/api/v1/pedidos/alertas', [ApiController::class, 'alerts']);
$router->get('/api/v1/pedidos/buscar', [ApiController::class, 'search']);
$router->get('/api/v1/pedidos/seguimiento/{code}', [ApiController::class, 'tracking']);
$router->get('/api/v1/catalogos/estados', [ApiController::class, 'states']);
