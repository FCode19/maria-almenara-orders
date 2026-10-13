# Sistema de Gestión de Pedidos - María Almenara

Proyecto académico desarrollado para el curso Integrador II: Sistemas.

## Objetivo

Implementar un sistema web que permita automatizar y centralizar la gestión de pedidos y el seguimiento de producción de tortas de María Almenara.

## Tecnologías

- PHP 8.2
- MariaDB
- HTML5
- CSS3
- JavaScript
- Bootstrap
- n8n
- API REST
- Telegram Bot
- Git / GitHub

## Estado del proyecto

V1 funcional del flujo web de pedidos.

## Funcionalidad V1

- Dashboard con indicadores obtenidos de MariaDB y próximas entregas.
- Listado y búsqueda de pedidos por código, seguimiento, cliente y estado.
- Registro transaccional de pedidos con cliente existente o nuevo y uno o más detalles.
- Modalidades RECOJO y DELIVERY, con dirección obligatoria para delivery.
- Fecha requerida con anticipación mínima de 3 días.
- Trazabilidad y transición controlada de estados:
  `Registrado -> Confirmado -> En produccion -> Terminado -> Entregado`.
- Cancelación permitida únicamente desde `Registrado` o `Confirmado`.
- Acceso al flujo de pedidos para los roles Administrador y Encargado.
- Funciones administrativas sensibles reservadas al Administrador.

## Rutas principales

- `GET /dashboard`
- `GET /pedidos`
- `GET /pedidos/nuevo`
- `POST /pedidos`
- `GET /pedidos/{id}`
- `POST /pedidos/{id}/estado`
- `GET /api/health`

## Despliegue

El repositorio se despliega en `/var/www/webroot/ROOT` y el `DocumentRoot`
debe apuntar a `/var/www/webroot/ROOT/public`. El archivo `.env` no se versiona.
No se requieren cambios de rutas ni mover `public/index.php` para SaveInCloud.

## Equipo

- Joseph Gutierrez
- Russel Bermudez
- Lenin Feria
- Jose Almenara
