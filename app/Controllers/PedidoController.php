<?php

namespace App\Controllers;

use App\Core\Session;
use App\Helpers\Csrf;
use App\Helpers\Response;
use App\Helpers\View;
use App\Middleware\RoleMiddleware;
use App\Services\PedidoService;
use Throwable;

class PedidoController
{
    private PedidoService $service;

    public function __construct()
    {
        $this->service = new PedidoService();
    }

    public function index(): void
    {
        $this->authorize();
        $filters = [
            'search' => $_GET['search'] ?? '',
            'state' => $_GET['state'] ?? '',
            'site' => $_GET['site'] ?? '',
            'modality' => $_GET['modality'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
            'urgency' => $_GET['urgency'] ?? '',
        ];
        View::render('pedidos.index', [
            'user' => Session::get('auth_user'),
            'orders' => $this->service->list($filters),
            'states' => $this->service->states(),
            'sites' => $this->service->sites(),
            'filters' => $filters,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error'),
        ]);
    }

    public function create(): void
    {
        $this->authorize();
        View::render('pedidos.create', [
            'user' => Session::get('auth_user'),
            'catalogs' => $this->service->catalogs(),
            'error' => Session::getFlash('error'),
            'old' => Session::getFlash('old', []),
            'minimumDate' => date('Y-m-d'),
            'maximumDate' => date('Y-m-d', strtotime('+3 days')),
        ]);
    }

    public function store(): void
    {
        $this->authorize();
        $this->validateCsrf('/pedidos/nuevo');

        try {
            $user = Session::get('auth_user');
            $id = $this->service->create($_POST, (int) $user['id_usuario']);
            Session::flash('success', 'Pedido registrado correctamente.');
            Response::redirect('/pedidos/' . $id);
        } catch (\DomainException $e) {
            Session::flash('error', $e->getMessage());
            Session::flash('old', $_POST);
            Response::redirect('/pedidos/nuevo');
        } catch (Throwable $e) {
            error_log($e->getMessage());
            Session::flash('error', 'No se pudo registrar el pedido. Intenta nuevamente.');
            Session::flash('old', $_POST);
            Response::redirect('/pedidos/nuevo');
        }
    }

    public function show(string $id): void
    {
        $this->authorize();
        $order = $this->service->find((int) $id);
        if (!$order) {
            Response::html('<h1>404</h1><p>Pedido no encontrado.</p>', 404);
        }

        View::render('pedidos.show', [
            'user' => Session::get('auth_user'),
            'order' => $order,
            'transitions' => PedidoService::allowedTransitions($order['estado']),
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error'),
        ]);
    }

    public function updateState(string $id): void
    {
        $this->authorize();
        $this->validateCsrf('/pedidos/' . (int) $id);

        try {
            $user = Session::get('auth_user');
            $this->service->changeState((int) $id, $_POST, (int) $user['id_usuario']);
            Session::flash('success', 'Estado actualizado correctamente.');
        } catch (\DomainException $e) {
            Session::flash('error', $e->getMessage());
        } catch (Throwable $e) {
            error_log($e->getMessage());
            Session::flash('error', 'No se pudo actualizar el estado.');
        }

        Response::redirect('/pedidos/' . (int) $id);
    }

    private function authorize(): void
    {
        RoleMiddleware::handle(['Administrador', 'Encargado']);
    }

    private function validateCsrf(string $redirect): void
    {
        if (!Csrf::validate($_POST['_token'] ?? null)) {
            Session::flash('error', 'La sesión del formulario expiró. Intenta nuevamente.');
            Response::redirect($redirect);
        }
    }
}
