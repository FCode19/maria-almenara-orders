<?php

namespace App\Controllers;

use App\Helpers\Response;
use App\Middleware\ApiAuthMiddleware;
use App\Repositories\ApiRepository;
use Throwable;

class ApiController
{
    private ApiRepository $repository;

    public function __construct()
    {
        $this->repository = new ApiRepository();
    }

    public function summary(): void
    {
        $this->respond(fn () => $this->repository->summary());
    }

    public function alerts(): void
    {
        $this->respond(fn () => ['pedidos' => $this->repository->alerts()]);
    }

    public function search(): void
    {
        ApiAuthMiddleware::handle();
        $query = mb_substr(trim((string) ($_GET['consulta'] ?? '')), 0, 100);
        if (mb_strlen($query) < 2) {
            Response::json(['status' => 'error', 'message' => 'La consulta debe tener al menos 2 caracteres.'], 422);
        }
        $this->respond(fn () => ['consulta' => $query, 'pedidos' => $this->repository->search($query)], false);
    }

    public function tracking(string $code): void
    {
        ApiAuthMiddleware::handle();
        $code = mb_substr(trim(urldecode($code)), 0, 32);
        if ($code === '') {
            Response::json(['status' => 'error', 'message' => 'Código inválido.'], 422);
        }
        try {
            $order = $this->repository->tracking($code);
            if (!$order) {
                Response::json(['status' => 'error', 'message' => 'Pedido no encontrado.'], 404);
            }
            Response::json(['status' => 'ok', 'data' => $order]);
        } catch (Throwable $e) {
            $this->serverError($e);
        }
    }

    public function states(): void
    {
        $this->respond(fn () => ['estados' => $this->repository->states()]);
    }

    private function respond(callable $callback, bool $authorize = true): void
    {
        if ($authorize) ApiAuthMiddleware::handle();
        try {
            Response::json(['status' => 'ok', 'data' => $callback()]);
        } catch (Throwable $e) {
            $this->serverError($e);
        }
    }

    private function serverError(Throwable $e): never
    {
        error_log($e->getMessage());
        $response = ['status' => 'error', 'message' => 'No se pudo completar la consulta.'];
        if (filter_var(getenv('APP_DEBUG'), FILTER_VALIDATE_BOOLEAN)) {
            $response['detail'] = $e->getMessage();
        }
        Response::json($response, 500);
    }
}
