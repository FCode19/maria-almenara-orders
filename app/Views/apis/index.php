<?php
$pageTitle = 'Centro de APIs';
$active = 'apis';
require BASE_PATH . '/app/Views/layouts/header.php';
?>
<div class="page-header"><div><span class="eyebrow">Integraciones</span><h1>Centro de APIs</h1><p>Comprueba en un solo lugar las consultas disponibles para Telegram y servicios internos.</p></div><span class="api-config-state <?= $externalKeyConfigured ? 'is-ready' : 'is-pending' ?>"><?= $externalKeyConfigured ? 'Clave externa configurada' : 'Falta API_ACCESS_KEY' ?></span></div>

<?php if (!$externalKeyConfigured): ?><div class="alert api-warning"><strong>Las pruebas administrativas funcionan con tu sesión.</strong> Para conectar Telegram o n8n, agrega una clave larga y aleatoria como <code>API_ACCESS_KEY</code> en el archivo <code>.env</code>.</div><?php endif; ?>

<section class="api-layout">
    <div class="api-list">
        <article class="card api-card" data-method="GET" data-endpoint="/api/health"><div><span class="api-method is-public">GET</span><span class="api-access">Pública</span><h2>Estado del sistema</h2><p>Comprueba aplicación y conexión con la base de datos.</p><code>/api/health</code></div><button class="btn btn-secondary api-run" type="button">Probar</button></article>
        <article class="card api-card" data-method="GET" data-endpoint="/api/v1/pedidos/resumen"><div><span class="api-method">GET</span><span class="api-access">Protegida</span><h2>Resumen de pedidos</h2><p>Totales operativos y distribución por estado.</p><code>/api/v1/pedidos/resumen</code></div><button class="btn btn-secondary api-run" type="button">Probar</button></article>
        <article class="card api-card" data-method="GET" data-endpoint="/api/v1/pedidos/alertas"><div><span class="api-method">GET</span><span class="api-access">Protegida</span><h2>Alertas de entrega</h2><p>Pedidos vencidos, para hoy o próximos a vencer.</p><code>/api/v1/pedidos/alertas</code></div><button class="btn btn-secondary api-run" type="button">Probar</button></article>
        <article class="card api-card api-card-input" data-method="GET" data-endpoint="/api/v1/pedidos/buscar"><div><span class="api-method">GET</span><span class="api-access">Protegida</span><h2>Buscar pedidos</h2><p>Consulta por código, seguimiento, teléfono o cliente.</p><div class="api-input-row"><input class="api-parameter" name="consulta" placeholder="Ej. PED-202611 o Andrea" maxlength="100"><button class="btn btn-secondary api-run" type="button">Probar</button></div><code>/api/v1/pedidos/buscar?consulta={valor}</code></div></article>
        <article class="card api-card api-card-input" data-method="GET" data-endpoint="/api/v1/pedidos/seguimiento/{code}"><div><span class="api-method">GET</span><span class="api-access">Protegida</span><h2>Seguimiento completo</h2><p>Devuelve estado, productos y trazabilidad de un pedido.</p><div class="api-input-row"><input class="api-parameter" name="code" placeholder="Código de pedido o seguimiento" maxlength="32"><button class="btn btn-secondary api-run" type="button">Probar</button></div><code>/api/v1/pedidos/seguimiento/{code}</code></div></article>
        <article class="card api-card" data-method="GET" data-endpoint="/api/v1/catalogos/estados"><div><span class="api-method">GET</span><span class="api-access">Protegida</span><h2>Estados disponibles</h2><p>Catálogo vigente para respuestas y validaciones.</p><code>/api/v1/catalogos/estados</code></div><button class="btn btn-secondary api-run" type="button">Probar</button></article>
    </div>
    <aside class="card api-response-panel"><div class="card-heading"><div><span class="eyebrow">Resultado</span><h2>Respuesta de la API</h2></div><span id="api-result-status" class="api-result-status">Sin ejecutar</span></div><div class="api-response-meta"><span id="api-result-endpoint">Selecciona una consulta</span><span id="api-result-time">—</span></div><pre id="api-result-body">{
  "message": "Aquí aparecerá la respuesta"
}</pre></aside>
</section>
<script src="/assets/js/api-console.js" defer></script>
<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
