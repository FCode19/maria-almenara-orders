<?php
use App\Helpers\Csrf;
$pageTitle = 'Nuevo pedido';
$active = 'nuevo';
require BASE_PATH . '/app/Views/layouts/header.php';
$oldValue = static fn (string $key, mixed $default = ''): mixed => $old[$key] ?? $default;
$oldArray = static fn (string $key): array => is_array($old[$key] ?? null) ? $old[$key] : [];
$detailCount = max(1, min(20, count($oldArray('id_torta'))));
?>
<div class="page-header"><div><h1>Registrar pedido</h1><p>Completa los datos del cliente y las tortas solicitadas.</p></div></div>
<?php if ($error): ?><div class="alert alert-error"><?= $escape($error) ?></div><?php endif; ?>
<form method="POST" action="/pedidos" id="order-form">
<?= Csrf::field() ?>
<section class="card">
    <h2>Cliente y entrega</h2>
    <div class="choice-row"><label><input type="radio" name="cliente_modo" value="existente" <?= $oldValue('cliente_modo', 'existente') !== 'nuevo' ? 'checked' : '' ?>> Cliente existente</label><label><input type="radio" name="cliente_modo" value="nuevo" <?= $oldValue('cliente_modo') === 'nuevo' ? 'checked' : '' ?>> Nuevo cliente</label></div>
    <div id="existing-client" class="form-grid"><div class="form-group full"><label for="id_cliente">Cliente</label><select id="id_cliente" name="id_cliente"><option value="">Seleccionar cliente</option><?php foreach ($catalogs['clientes'] as $client): ?><option value="<?= (int) $client['id_cliente'] ?>" <?= (string) $oldValue('id_cliente') === (string) $client['id_cliente'] ? 'selected' : '' ?>><?= $escape(trim($client['nombres'] . ' ' . $client['apellidos']) . ' · ' . $client['telefono']) ?></option><?php endforeach; ?></select></div></div>
    <div id="new-client" class="form-grid hidden">
        <div class="form-group"><label for="cliente_nombres">Nombres</label><input id="cliente_nombres" name="cliente_nombres" maxlength="100" value="<?= $escape($oldValue('cliente_nombres')) ?>"></div>
        <div class="form-group"><label for="cliente_apellidos">Apellidos</label><input id="cliente_apellidos" name="cliente_apellidos" maxlength="100" value="<?= $escape($oldValue('cliente_apellidos')) ?>"></div>
        <div class="form-group"><label for="cliente_telefono">Teléfono</label><input id="cliente_telefono" name="cliente_telefono" maxlength="20" value="<?= $escape($oldValue('cliente_telefono')) ?>"></div>
        <div class="form-group"><label for="cliente_correo">Correo (opcional)</label><input id="cliente_correo" name="cliente_correo" type="email" maxlength="120" value="<?= $escape($oldValue('cliente_correo')) ?>"></div>
    </div>
    <div class="form-grid section-gap-sm">
        <div class="form-group"><label for="id_sede">Sede</label><select id="id_sede" name="id_sede" required><option value="">Seleccionar sede</option><?php foreach ($catalogs['sedes'] as $site): ?><option value="<?= (int) $site['id_sede'] ?>" <?= (string) $oldValue('id_sede') === (string) $site['id_sede'] ? 'selected' : '' ?>><?= $escape($site['nombre']) ?></option><?php endforeach; ?></select></div>
        <div class="form-group"><label for="modalidad">Modalidad</label><select id="modalidad" name="modalidad" required><option value="RECOJO" <?= $oldValue('modalidad', 'RECOJO') === 'RECOJO' ? 'selected' : '' ?>>Recojo</option><option value="DELIVERY" <?= $oldValue('modalidad') === 'DELIVERY' ? 'selected' : '' ?>>Delivery</option></select></div>
        <div id="delivery-address" class="form-group full hidden"><label for="direccion_entrega">Dirección de entrega</label><input id="direccion_entrega" name="direccion_entrega" maxlength="250" value="<?= $escape($oldValue('direccion_entrega')) ?>"></div>
        <div class="form-group"><label for="fecha_requerida">Fecha requerida</label><input id="fecha_requerida" name="fecha_requerida" type="date" min="<?= $escape($minimumDate) ?>" max="<?= $escape($maximumDate) ?>" value="<?= $escape($oldValue('fecha_requerida', $maximumDate)) ?>" required><small>Debe entregarse como máximo dentro de 3 días.</small></div>
        <div class="form-group full"><label for="observaciones">Observaciones generales</label><textarea id="observaciones" name="observaciones"><?= $escape($oldValue('observaciones')) ?></textarea></div>
    </div>
</section>
<section class="card section-gap">
    <div class="card-heading"><div><h2>Detalle del pedido</h2><p>Agrega una o más tortas.</p></div><button class="btn btn-secondary" id="add-detail" type="button">+ Agregar torta</button></div>
    <div id="details">
    <?php for ($i = 0; $i < $detailCount; $i++): ?>
        <div class="detail-row"><div class="detail-heading"><strong>Detalle <span class="detail-number"><?= $i + 1 ?></span></strong><button class="remove-detail" type="button">Quitar</button></div><div class="detail-grid">
            <div class="form-group"><label>Torta</label><select name="id_torta[]" required><option value="">Seleccionar</option><?php foreach ($catalogs['tortas'] as $item): ?><option value="<?= (int) $item['id_torta'] ?>" <?= (string) ($oldArray('id_torta')[$i] ?? '') === (string) $item['id_torta'] ? 'selected' : '' ?>><?= $escape($item['nombre']) ?></option><?php endforeach; ?></select></div>
            <div class="form-group"><label>Tamaño</label><select name="id_tamano[]" required><option value="">Seleccionar</option><?php foreach ($catalogs['tamanos'] as $item): ?><option value="<?= (int) $item['id_tamano'] ?>" <?= (string) ($oldArray('id_tamano')[$i] ?? '') === (string) $item['id_tamano'] ? 'selected' : '' ?>><?= $escape($item['nombre']) ?></option><?php endforeach; ?></select></div>
            <div class="form-group"><label>Sabor</label><select name="id_sabor[]" required><option value="">Seleccionar</option><?php foreach ($catalogs['sabores'] as $item): ?><option value="<?= (int) $item['id_sabor'] ?>" <?= (string) ($oldArray('id_sabor')[$i] ?? '') === (string) $item['id_sabor'] ? 'selected' : '' ?>><?= $escape($item['nombre']) ?></option><?php endforeach; ?></select></div>
            <div class="form-group"><label>Cantidad</label><input name="cantidad[]" type="number" min="1" max="100" value="<?= $escape($oldArray('cantidad')[$i] ?? 1) ?>" required></div>
            <div class="form-group"><label>Mensaje personalizado</label><input name="mensaje_personalizado[]" maxlength="200" value="<?= $escape($oldArray('mensaje_personalizado')[$i] ?? '') ?>"></div>
            <div class="form-group"><label>Observación del detalle</label><input name="detalle_observaciones[]" maxlength="250" value="<?= $escape($oldArray('detalle_observaciones')[$i] ?? '') ?>"></div>
        </div></div>
    <?php endfor; ?>
    </div>
    <div class="form-actions"><a class="btn btn-secondary" href="/pedidos">Cancelar</a><button class="btn btn-primary" type="submit">Registrar pedido</button></div>
</section>
</form>
<script src="/assets/js/orders.js" defer></script>
<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
