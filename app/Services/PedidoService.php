<?php

namespace App\Services;

use App\Repositories\PedidoRepository;
use DateTimeImmutable;

class PedidoService
{
    private PedidoRepository $repository;

    public function __construct()
    {
        $this->repository = new PedidoRepository();
    }

    public function catalogs(): array
    {
        return $this->repository->catalogs();
    }

    public function states(): array
    {
        return $this->repository->states();
    }

    public function list(array $filters): array
    {
        return $this->repository->list([
            'search' => mb_substr(trim((string) ($filters['search'] ?? '')), 0, 100),
            'state' => mb_substr(trim((string) ($filters['state'] ?? '')), 0, 30),
        ]);
    }

    public function find(int $id): ?array
    {
        return $id > 0 ? $this->repository->find($id) : null;
    }

    public function dashboard(): array
    {
        return $this->repository->dashboard();
    }

    public function create(array $input, int $userId): int
    {
        $data = $this->validateOrder($input);
        $data['codigo_pedido'] = $this->code('PED', 6);
        $data['codigo_seguimiento'] = $this->code('MA', 10);

        return $this->repository->create($data, $userId);
    }

    public function changeState(int $orderId, array $input, int $userId): void
    {
        $state = trim((string) ($input['estado'] ?? ''));
        $validStates = array_column($this->states(), 'nombre');
        if (!in_array($state, $validStates, true)) {
            throw new \DomainException('Seleccione un estado válido.');
        }

        $note = trim((string) ($input['observacion'] ?? ''));
        if (mb_strlen($note) > 250) {
            throw new \DomainException('La observación del cambio no puede superar 250 caracteres.');
        }

        $this->repository->changeState($orderId, $state, $userId, $note ?: null);
    }

    public static function allowedTransitions(string $state): array
    {
        return [
            'Registrado' => ['Confirmado', 'Cancelado'],
            'Confirmado' => ['En produccion', 'Cancelado'],
            'En produccion' => ['Terminado'],
            'Terminado' => ['Entregado'],
            'Entregado' => [],
            'Cancelado' => [],
        ][$state] ?? [];
    }

    private function validateOrder(array $input): array
    {
        $errors = [];
        $clientMode = ($input['cliente_modo'] ?? 'existente') === 'nuevo' ? 'nuevo' : 'existente';
        $clientId = (int) ($input['id_cliente'] ?? 0);
        $siteId = (int) ($input['id_sede'] ?? 0);
        $mode = strtoupper(trim((string) ($input['modalidad'] ?? '')));
        $address = trim((string) ($input['direccion_entrega'] ?? ''));
        $requiredDate = trim((string) ($input['fecha_requerida'] ?? ''));
        $notes = trim((string) ($input['observaciones'] ?? ''));

        if ($clientMode === 'existente') {
            if ($clientId <= 0 || !$this->isActive('tbl_cliente', 'id_cliente', $clientId)) {
                $errors[] = 'Seleccione un cliente activo.';
            }
        } else {
            $newClient = [
                'nombres' => trim((string) ($input['cliente_nombres'] ?? '')),
                'apellidos' => $this->nullable($input['cliente_apellidos'] ?? null),
                'telefono' => trim((string) ($input['cliente_telefono'] ?? '')),
                'correo' => $this->nullable($input['cliente_correo'] ?? null),
            ];
            if ($newClient['nombres'] === '' || mb_strlen($newClient['nombres']) > 100) {
                $errors[] = 'Ingrese los nombres del cliente (máximo 100 caracteres).';
            }
            if ($newClient['telefono'] === '' || mb_strlen($newClient['telefono']) > 20) {
                $errors[] = 'Ingrese un teléfono válido (máximo 20 caracteres).';
            }
            if ($newClient['apellidos'] !== null && mb_strlen($newClient['apellidos']) > 100) {
                $errors[] = 'Los apellidos no pueden superar 100 caracteres.';
            }
            if ($newClient['correo'] !== null && (!filter_var($newClient['correo'], FILTER_VALIDATE_EMAIL) || mb_strlen($newClient['correo']) > 120)) {
                $errors[] = 'Ingrese un correo válido.';
            }
        }

        if ($siteId <= 0 || !$this->isActive('tbl_sede', 'id_sede', $siteId)) {
            $errors[] = 'Seleccione una sede activa.';
        }
        if (!in_array($mode, ['RECOJO', 'DELIVERY'], true)) {
            $errors[] = 'Seleccione una modalidad válida.';
        }
        if ($mode === 'DELIVERY' && $address === '') {
            $errors[] = 'La dirección es obligatoria para delivery.';
        }
        if (mb_strlen($address) > 250) {
            $errors[] = 'La dirección no puede superar 250 caracteres.';
        }

        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $requiredDate);
        $minimum = (new DateTimeImmutable('today'))->modify('+3 days');
        if (!$date || $date->format('Y-m-d') !== $requiredDate || $date < $minimum) {
            $errors[] = 'La fecha requerida debe ser como mínimo dentro de 3 días.';
        }
        if (mb_strlen($notes) > 65535) {
            $errors[] = 'Las observaciones son demasiado extensas.';
        }

        $cakeIds = (array) ($input['id_torta'] ?? []);
        $sizeIds = (array) ($input['id_tamano'] ?? []);
        $flavorIds = (array) ($input['id_sabor'] ?? []);
        $quantities = (array) ($input['cantidad'] ?? []);
        $messages = (array) ($input['mensaje_personalizado'] ?? []);
        $detailNotes = (array) ($input['detalle_observaciones'] ?? []);
        $count = count($cakeIds);

        if ($count === 0 || $count > 20 || count($sizeIds) !== $count || count($flavorIds) !== $count || count($quantities) !== $count) {
            $errors[] = 'Agregue entre 1 y 20 detalles completos.';
        }

        $activeCakes = $this->activeSet('tbl_torta', 'id_torta', $cakeIds);
        $activeSizes = $this->activeSet('tbl_tamano', 'id_tamano', $sizeIds);
        $activeFlavors = $this->activeSet('tbl_sabor', 'id_sabor', $flavorIds);
        $details = [];
        for ($i = 0; $i < $count && $i < 20; $i++) {
            $cake = (int) ($cakeIds[$i] ?? 0);
            $size = (int) ($sizeIds[$i] ?? 0);
            $flavor = (int) ($flavorIds[$i] ?? 0);
            $quantity = filter_var($quantities[$i] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 100]]);
            $message = $this->nullable($messages[$i] ?? null);
            $detailNote = $this->nullable($detailNotes[$i] ?? null);

            if (!isset($activeCakes[$cake]) || !isset($activeSizes[$size]) || !isset($activeFlavors[$flavor]) || $quantity === false) {
                $errors[] = 'Cada detalle debe tener torta, tamaño, sabor y cantidad válidos.';
                break;
            }
            if (($message !== null && mb_strlen($message) > 200) || ($detailNote !== null && mb_strlen($detailNote) > 250)) {
                $errors[] = 'Revise la longitud del mensaje u observación de cada detalle.';
                break;
            }
            $details[] = [
                'id_torta' => $cake, 'id_tamano' => $size, 'id_sabor' => $flavor,
                'cantidad' => $quantity, 'mensaje_personalizado' => $message, 'observaciones' => $detailNote,
            ];
        }

        if ($errors) {
            throw new \DomainException(implode(' ', array_unique($errors)));
        }

        return [
            'id_cliente' => $clientMode === 'existente' ? $clientId : 0,
            'nuevo_cliente' => $newClient ?? null,
            'id_sede' => $siteId,
            'modalidad' => $mode,
            'direccion_entrega' => $mode === 'DELIVERY' ? $address : null,
            'fecha_requerida' => $requiredDate,
            'observaciones' => $notes === '' ? null : $notes,
            'detalles' => $details,
        ];
    }

    private function code(string $prefix, int $bytes): string
    {
        return $prefix . '-' . strtoupper(substr(bin2hex(random_bytes($bytes)), 0, $bytes * 2));
    }

    private function nullable(mixed $value): ?string
    {
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    private function isActive(string $table, string $column, int $id): bool
    {
        return in_array($id, $this->repository->activeIds($table, $column, [$id]), true);
    }

    private function activeSet(string $table, string $column, array $ids): array
    {
        return array_fill_keys($this->repository->activeIds($table, $column, $ids), true);
    }
}
