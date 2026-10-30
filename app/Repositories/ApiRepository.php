<?php

namespace App\Repositories;

use App\Core\Database;

class ApiRepository
{
    public function summary(): array
    {
        $db = Database::connection();
        $totals = $db->query(
            "SELECT COUNT(*) total,
                    SUM(e.nombre NOT IN ('Entregado','Cancelado')) activos,
                    SUM(e.nombre = 'En produccion') en_produccion,
                    SUM(e.nombre = 'Terminado') listos,
                    SUM(e.nombre = 'Entregado') entregados,
                    SUM(e.nombre = 'Cancelado') cancelados,
                    SUM(p.fecha_requerida < CURDATE() AND e.nombre NOT IN ('Entregado','Cancelado')) vencidos
             FROM tbl_pedido p
             INNER JOIN tbl_estado_pedido e ON e.id_estado = p.id_estado_actual"
        )->fetch();

        $states = $db->query(
            "SELECT e.nombre estado, COUNT(p.id_pedido) total
             FROM tbl_estado_pedido e
             LEFT JOIN tbl_pedido p ON p.id_estado_actual = e.id_estado
             WHERE e.activo = 1
             GROUP BY e.id_estado, e.nombre, e.orden ORDER BY e.orden"
        )->fetchAll();

        return ['totales' => $totals, 'por_estado' => $states];
    }

    public function alerts(): array
    {
        return Database::connection()->query(
            "SELECT p.codigo_pedido, p.codigo_seguimiento,
                    CONCAT(c.nombres, ' ', COALESCE(c.apellidos, '')) cliente,
                    s.nombre sede, e.nombre estado, p.modalidad, p.fecha_requerida,
                    DATEDIFF(p.fecha_requerida, CURDATE()) dias_restantes,
                    CASE WHEN p.fecha_requerida < CURDATE() THEN 'VENCIDO'
                         WHEN p.fecha_requerida = CURDATE() THEN 'VENCE_HOY'
                         ELSE 'PROXIMO' END prioridad
             FROM tbl_pedido p
             INNER JOIN tbl_cliente c ON c.id_cliente = p.id_cliente
             INNER JOIN tbl_sede s ON s.id_sede = p.id_sede
             INNER JOIN tbl_estado_pedido e ON e.id_estado = p.id_estado_actual
             WHERE p.fecha_requerida <= DATE_ADD(CURDATE(), INTERVAL 3 DAY)
               AND e.nombre NOT IN ('Entregado','Cancelado')
             ORDER BY p.fecha_requerida, p.id_pedido LIMIT 50"
        )->fetchAll();
    }

    public function search(string $query): array
    {
        $statement = Database::connection()->prepare(
            "SELECT p.codigo_pedido, p.codigo_seguimiento,
                    CONCAT(c.nombres, ' ', COALESCE(c.apellidos, '')) cliente,
                    c.telefono, s.nombre sede, e.nombre estado, p.modalidad,
                    p.fecha_pedido, p.fecha_requerida
             FROM tbl_pedido p
             INNER JOIN tbl_cliente c ON c.id_cliente = p.id_cliente
             INNER JOIN tbl_sede s ON s.id_sede = p.id_sede
             INNER JOIN tbl_estado_pedido e ON e.id_estado = p.id_estado_actual
             WHERE p.codigo_pedido LIKE :codigo
                OR p.codigo_seguimiento LIKE :seguimiento
                OR c.telefono LIKE :telefono
                OR CONCAT(c.nombres, ' ', COALESCE(c.apellidos, '')) LIKE :cliente
             ORDER BY p.fecha_pedido DESC LIMIT 20"
        );
        $value = '%' . $query . '%';
        $statement->execute(['codigo' => $value, 'seguimiento' => $value, 'telefono' => $value, 'cliente' => $value]);
        return $statement->fetchAll();
    }

    public function tracking(string $code): ?array
    {
        $db = Database::connection();
        $statement = $db->prepare(
            "SELECT p.id_pedido, p.codigo_pedido, p.codigo_seguimiento,
                    CONCAT(c.nombres, ' ', COALESCE(c.apellidos, '')) cliente,
                    c.telefono, s.nombre sede, e.nombre estado, p.modalidad,
                    p.direccion_entrega, p.fecha_pedido, p.fecha_requerida, p.observaciones
             FROM tbl_pedido p
             INNER JOIN tbl_cliente c ON c.id_cliente = p.id_cliente
             INNER JOIN tbl_sede s ON s.id_sede = p.id_sede
             INNER JOIN tbl_estado_pedido e ON e.id_estado = p.id_estado_actual
             WHERE p.codigo_pedido = :pedido OR p.codigo_seguimiento = :seguimiento
             LIMIT 1"
        );
        $statement->execute(['pedido' => $code, 'seguimiento' => $code]);
        $order = $statement->fetch();
        if (!$order) return null;

        $details = $db->prepare(
            "SELECT t.nombre torta, ta.nombre tamano, sa.nombre sabor, d.cantidad,
                    d.mensaje_personalizado, d.observaciones
             FROM tbl_pedido_detalle d
             INNER JOIN tbl_torta t ON t.id_torta = d.id_torta
             INNER JOIN tbl_tamano ta ON ta.id_tamano = d.id_tamano
             INNER JOIN tbl_sabor sa ON sa.id_sabor = d.id_sabor
             WHERE d.id_pedido = :id ORDER BY d.id_detalle"
        );
        $details->execute(['id' => $order['id_pedido']]);

        $history = $db->prepare(
            "SELECT e.nombre estado, h.fecha_hora, h.observacion
             FROM tbl_historial_pedido h
             INNER JOIN tbl_estado_pedido e ON e.id_estado = h.id_estado
             WHERE h.id_pedido = :id ORDER BY h.fecha_hora, h.id_historial"
        );
        $history->execute(['id' => $order['id_pedido']]);
        unset($order['id_pedido']);
        $order['productos'] = $details->fetchAll();
        $order['trazabilidad'] = $history->fetchAll();
        return $order;
    }

    public function states(): array
    {
        return Database::connection()->query(
            'SELECT nombre, orden FROM tbl_estado_pedido WHERE activo = 1 ORDER BY orden'
        )->fetchAll();
    }
}
