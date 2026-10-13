<?php

namespace App\Repositories;

use App\Core\Database;
use PDO;
use Throwable;

class PedidoRepository
{
    public function catalogs(): array
    {
        $db = Database::connection();

        return [
            'clientes' => $db->query("SELECT id_cliente, nombres, apellidos, telefono FROM tbl_cliente WHERE activo = 1 ORDER BY nombres, apellidos")->fetchAll(),
            'sedes' => $db->query("SELECT id_sede, nombre, direccion FROM tbl_sede WHERE activo = 1 ORDER BY nombre")->fetchAll(),
            'tortas' => $db->query("SELECT id_torta, nombre FROM tbl_torta WHERE activo = 1 ORDER BY nombre")->fetchAll(),
            'tamanos' => $db->query("SELECT id_tamano, nombre FROM tbl_tamano WHERE activo = 1 ORDER BY nombre")->fetchAll(),
            'sabores' => $db->query("SELECT id_sabor, nombre FROM tbl_sabor WHERE activo = 1 ORDER BY nombre")->fetchAll(),
        ];
    }

    public function states(): array
    {
        return Database::connection()
            ->query("SELECT id_estado, nombre, orden FROM tbl_estado_pedido WHERE activo = 1 ORDER BY orden")
            ->fetchAll();
    }

    public function list(array $filters = []): array
    {
        $where = [];
        $params = [];

        if (($filters['search'] ?? '') !== '') {
            $where[] = "(p.codigo_pedido LIKE :search_order OR p.codigo_seguimiento LIKE :search_tracking OR CONCAT(c.nombres, ' ', COALESCE(c.apellidos, '')) LIKE :search_client)";
            $search = '%' . $filters['search'] . '%';
            $params['search_order'] = $search;
            $params['search_tracking'] = $search;
            $params['search_client'] = $search;
        }

        if (($filters['state'] ?? '') !== '') {
            $where[] = 'e.nombre = :state';
            $params['state'] = $filters['state'];
        }

        $sql = "SELECT p.id_pedido, p.codigo_pedido, p.codigo_seguimiento, p.modalidad,
                       p.fecha_pedido, p.fecha_requerida, e.nombre AS estado,
                       CONCAT(c.nombres, ' ', COALESCE(c.apellidos, '')) AS cliente
                FROM tbl_pedido p
                INNER JOIN tbl_cliente c ON c.id_cliente = p.id_cliente
                INNER JOIN tbl_estado_pedido e ON e.id_estado = p.id_estado_actual";

        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY p.fecha_creacion DESC, p.id_pedido DESC LIMIT 200';
        $statement = Database::connection()->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function find(int $id): ?array
    {
        $db = Database::connection();
        $statement = $db->prepare(
            "SELECT p.*, e.nombre AS estado, s.nombre AS sede, s.direccion AS direccion_sede,
                    CONCAT(c.nombres, ' ', COALESCE(c.apellidos, '')) AS cliente,
                    c.telefono, c.correo,
                    CONCAT(u.nombres, ' ', u.apellidos) AS registrado_por
             FROM tbl_pedido p
             INNER JOIN tbl_estado_pedido e ON e.id_estado = p.id_estado_actual
             INNER JOIN tbl_sede s ON s.id_sede = p.id_sede
             INNER JOIN tbl_cliente c ON c.id_cliente = p.id_cliente
             INNER JOIN tbl_usuario u ON u.id_usuario = p.id_usuario_registro
             WHERE p.id_pedido = :id LIMIT 1"
        );
        $statement->execute(['id' => $id]);
        $order = $statement->fetch();

        if (!$order) {
            return null;
        }

        $detail = $db->prepare(
            "SELECT d.cantidad, d.mensaje_personalizado, d.observaciones,
                    t.nombre AS torta, ta.nombre AS tamano, sa.nombre AS sabor
             FROM tbl_pedido_detalle d
             INNER JOIN tbl_torta t ON t.id_torta = d.id_torta
             INNER JOIN tbl_tamano ta ON ta.id_tamano = d.id_tamano
             INNER JOIN tbl_sabor sa ON sa.id_sabor = d.id_sabor
             WHERE d.id_pedido = :id ORDER BY d.id_detalle"
        );
        $detail->execute(['id' => $id]);

        $history = $db->prepare(
            "SELECT h.fecha_hora, h.observacion, e.nombre AS estado,
                    CONCAT(u.nombres, ' ', u.apellidos) AS usuario
             FROM tbl_historial_pedido h
             INNER JOIN tbl_estado_pedido e ON e.id_estado = h.id_estado
             INNER JOIN tbl_usuario u ON u.id_usuario = h.id_usuario
             WHERE h.id_pedido = :id ORDER BY h.fecha_hora, h.id_historial"
        );
        $history->execute(['id' => $id]);

        $order['detalles'] = $detail->fetchAll();
        $order['historial'] = $history->fetchAll();

        return $order;
    }

    public function create(array $data, int $userId): int
    {
        $db = Database::connection();
        $db->beginTransaction();

        try {
            $clientId = (int) ($data['id_cliente'] ?? 0);
            if ($clientId === 0) {
                $client = $db->prepare(
                    'INSERT INTO tbl_cliente (nombres, apellidos, telefono, correo) VALUES (:nombres, :apellidos, :telefono, :correo)'
                );
                $client->execute($data['nuevo_cliente']);
                $clientId = (int) $db->lastInsertId();
            }

            $state = $db->prepare("SELECT id_estado FROM tbl_estado_pedido WHERE nombre = 'Registrado' AND activo = 1 LIMIT 1");
            $state->execute();
            $stateId = (int) $state->fetchColumn();

            $order = $db->prepare(
                'INSERT INTO tbl_pedido (codigo_pedido, codigo_seguimiento, id_cliente, id_sede, id_estado_actual, id_usuario_registro, modalidad, direccion_entrega, fecha_requerida, observaciones)
                 VALUES (:codigo_pedido, :codigo_seguimiento, :id_cliente, :id_sede, :id_estado_actual, :id_usuario_registro, :modalidad, :direccion_entrega, :fecha_requerida, :observaciones)'
            );
            $order->execute([
                'codigo_pedido' => $data['codigo_pedido'],
                'codigo_seguimiento' => $data['codigo_seguimiento'],
                'id_cliente' => $clientId,
                'id_sede' => $data['id_sede'],
                'id_estado_actual' => $stateId,
                'id_usuario_registro' => $userId,
                'modalidad' => $data['modalidad'],
                'direccion_entrega' => $data['direccion_entrega'],
                'fecha_requerida' => $data['fecha_requerida'],
                'observaciones' => $data['observaciones'],
            ]);
            $orderId = (int) $db->lastInsertId();

            $detail = $db->prepare(
                'INSERT INTO tbl_pedido_detalle (id_pedido, id_torta, id_tamano, id_sabor, cantidad, mensaje_personalizado, observaciones)
                 VALUES (:id_pedido, :id_torta, :id_tamano, :id_sabor, :cantidad, :mensaje_personalizado, :observaciones)'
            );
            foreach ($data['detalles'] as $item) {
                $detail->execute(['id_pedido' => $orderId] + $item);
            }

            $history = $db->prepare(
                'INSERT INTO tbl_historial_pedido (id_pedido, id_estado, id_usuario, observacion) VALUES (:id_pedido, :id_estado, :id_usuario, :observacion)'
            );
            $history->execute([
                'id_pedido' => $orderId,
                'id_estado' => $stateId,
                'id_usuario' => $userId,
                'observacion' => 'Pedido registrado',
            ]);

            $db->commit();
            return $orderId;
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    public function changeState(int $orderId, string $newState, int $userId, ?string $note): void
    {
        $db = Database::connection();
        $db->beginTransaction();

        try {
            $current = $db->prepare(
                'SELECT e.nombre FROM tbl_pedido p INNER JOIN tbl_estado_pedido e ON e.id_estado = p.id_estado_actual WHERE p.id_pedido = :id FOR UPDATE'
            );
            $current->execute(['id' => $orderId]);
            $currentState = $current->fetchColumn();
            if (!$currentState) {
                throw new \DomainException('El pedido no existe.');
            }

            $allowed = [
                'Registrado' => ['Confirmado', 'Cancelado'],
                'Confirmado' => ['En produccion', 'Cancelado'],
                'En produccion' => ['Terminado'],
                'Terminado' => ['Entregado'],
                'Entregado' => [],
                'Cancelado' => [],
            ];
            if (!in_array($newState, $allowed[$currentState] ?? [], true)) {
                throw new \DomainException("La transición de {$currentState} a {$newState} no está permitida.");
            }

            $state = $db->prepare('SELECT id_estado FROM tbl_estado_pedido WHERE nombre = :nombre AND activo = 1 LIMIT 1');
            $state->execute(['nombre' => $newState]);
            $stateId = (int) $state->fetchColumn();
            if ($stateId === 0) {
                throw new \DomainException('El estado solicitado no está disponible.');
            }

            $update = $db->prepare('UPDATE tbl_pedido SET id_estado_actual = :estado WHERE id_pedido = :id');
            $update->execute(['estado' => $stateId, 'id' => $orderId]);

            $history = $db->prepare(
                'INSERT INTO tbl_historial_pedido (id_pedido, id_estado, id_usuario, observacion) VALUES (:pedido, :estado, :usuario, :observacion)'
            );
            $history->execute([
                'pedido' => $orderId,
                'estado' => $stateId,
                'usuario' => $userId,
                'observacion' => $note ?: 'Estado actualizado a ' . $newState,
            ]);

            $db->commit();
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    public function dashboard(): array
    {
        $db = Database::connection();
        $kpis = $db->query(
            "SELECT COUNT(*) AS total,
                    SUM(e.nombre = 'Registrado') AS registrados,
                    SUM(e.nombre = 'En produccion') AS en_produccion,
                    SUM(e.nombre = 'Terminado') AS terminados,
                    SUM(e.nombre = 'Entregado') AS entregados,
                    SUM(e.nombre = 'Cancelado') AS cancelados,
                    SUM(p.fecha_requerida BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY) AND e.nombre NOT IN ('Entregado','Cancelado')) AS proximos
             FROM tbl_pedido p INNER JOIN tbl_estado_pedido e ON e.id_estado = p.id_estado_actual"
        )->fetch();

        $byState = $db->query(
            'SELECT e.nombre AS estado, COUNT(p.id_pedido) AS total FROM tbl_estado_pedido e LEFT JOIN tbl_pedido p ON p.id_estado_actual = e.id_estado WHERE e.activo = 1 GROUP BY e.id_estado, e.nombre, e.orden ORDER BY e.orden'
        )->fetchAll();

        $upcoming = $db->query(
            "SELECT p.id_pedido, p.codigo_pedido, p.fecha_requerida, e.nombre AS estado,
                    CONCAT(c.nombres, ' ', COALESCE(c.apellidos, '')) AS cliente
             FROM tbl_pedido p INNER JOIN tbl_estado_pedido e ON e.id_estado = p.id_estado_actual
             INNER JOIN tbl_cliente c ON c.id_cliente = p.id_cliente
             WHERE p.fecha_requerida >= CURDATE() AND e.nombre NOT IN ('Entregado','Cancelado')
             ORDER BY p.fecha_requerida, p.id_pedido LIMIT 6"
        )->fetchAll();

        return ['kpis' => $kpis, 'por_estado' => $byState, 'proximos' => $upcoming];
    }

    public function activeIds(string $table, string $column, array $ids): array
    {
        $allowed = [
            'tbl_cliente' => 'id_cliente', 'tbl_sede' => 'id_sede', 'tbl_torta' => 'id_torta',
            'tbl_tamano' => 'id_tamano', 'tbl_sabor' => 'id_sabor',
        ];
        if (!isset($allowed[$table]) || $allowed[$table] !== $column || !$ids) {
            return [];
        }
        $ids = array_values(array_unique(array_map('intval', $ids)));
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $statement = Database::connection()->prepare("SELECT {$column} FROM {$table} WHERE activo = 1 AND {$column} IN ({$placeholders})");
        $statement->execute($ids);
        return array_map('intval', $statement->fetchAll(PDO::FETCH_COLUMN));
    }
}
