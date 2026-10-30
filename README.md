# Sistema de Gestión de Pedidos - María Almenara

Proyecto académico desarrollado para el curso Integrador II: Sistemas.

## Objetivo

Implementar un sistema web que permita automatizar y centralizar la gestión de pedidos y el seguimiento de producción de tortas de María Almenara.

## Tecnologías

- PHP 8.3
- MySQL 8 / MariaDB
- HTML5
- CSS3
- JavaScript
- Bootstrap
- n8n
- API REST
- Telegram Bot
- Git / GitHub

## Estado del proyecto

V1 mejorada para el APF3, orientada a la operación diaria del Encargado.

## Funcionalidad V1

- Dashboard con indicadores obtenidos de MariaDB y próximas entregas.
- Listado y búsqueda de pedidos por código, seguimiento, cliente y estado.
- Registro transaccional de pedidos con cliente existente o nuevo y uno o más detalles.
- Modalidades RECOJO y DELIVERY, con dirección obligatoria para delivery.
- Fecha requerida limitada a un máximo de 3 días desde el registro.
- Trazabilidad y transición controlada de estados:
  `Registrado -> Confirmado -> En produccion -> Terminado -> Entregado`.
- Cancelación permitida únicamente desde `Registrado` o `Confirmado`.
- Acceso al flujo de pedidos para los roles Administrador y Encargado.
- Funciones administrativas sensibles reservadas al Administrador.
- Alertas internas derivadas de la fecha requerida: próximo a vencer, vence hoy y vencido.
- Indicadores de carga activa, cumplimiento de entrega, estados, sedes y tendencia reciente.
- Filtros operativos por sede, modalidad, rango de fechas y prioridad.
- Gestión administrativa de usuarios: listado, creación y activación/desactivación.

## Rutas principales

- `GET /dashboard`
- `GET /pedidos`
- `GET /pedidos/nuevo`
- `POST /pedidos`
- `GET /pedidos/{id}`
- `POST /pedidos/{id}/estado`
- `GET /usuarios` (Administrador)
- `GET /usuarios/nuevo` (Administrador)
- `POST /usuarios` (Administrador)
- `POST /usuarios/{id}/estado` (Administrador)
- `GET /apis` (Administrador)
- `GET /api/health`
- `GET /api/v1/pedidos/resumen`
- `GET /api/v1/pedidos/alertas`
- `GET /api/v1/pedidos/buscar?consulta={valor}`
- `GET /api/v1/pedidos/seguimiento/{codigo}`
- `GET /api/v1/catalogos/estados`

## APIs e integraciones

`/api/health` es público. Los endpoints bajo `/api/v1` requieren una sesión de
Administrador o la clave definida en `API_ACCESS_KEY`. Las integraciones externas
pueden enviarla como `X-API-Key` o `Authorization: Bearer`. La clave no se muestra
en el Centro de APIs ni debe versionarse.

## Despliegue

En DreamHost el repositorio se clona en `/home/ma_root/maria-almenara-orders` y el
Web Directory debe apuntar a `/home/ma_root/maria-almenara-orders/public`. El archivo
`.env` no se versiona ni se reemplaza durante un despliegue.

Para cargar el escenario operativo de noviembre de 2026 puede importarse
`database/datos_demo_apf3.sql`. El script reemplaza únicamente pedidos, detalles,
historial y notificaciones por 60 registros nuevos; conserva clientes, usuarios y catálogos.
No se ejecuta automáticamente.

## Equipo

- Joseph Gutierrez
- Russel Bermudez
- Lenin Feria
- Jose Almenara
