<?php
$pageTitle = 'Pedidos';
$active = 'pedidos';
require BASE_PATH . '/app/Views/layouts/header.php';
?>
<div class="page-header"><div><h1>Pedidos</h1><p>Consulta y seguimiento de pedidos registrados.</p></div><a class="btn btn-primary" href="/pedidos/nuevo">+ Nuevo pedido</a></div>
<?php if ($success): ?><div class="alert alert-success"><?= $escape($success) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error"><?= $escape($error) ?></div><?php endif; ?>
<form class="card filters" method="GET" action="/pedidos"><div class="form-group"><label for="search">Buscar pedido o cliente</label><input id="search" name="search" value="<?= $escape($filters['search']) ?>" maxlength="100" placeholder="Código, seguimiento o cliente"></div><div class="form-group"><label for="state">Estado</label><select id="state" name="state"><option value="">Todos los estados</option><?php foreach ($states as $state): ?><option value="<?= $escape($state['nombre']) ?>" <?= $filters['state'] === $state['nombre'] ? 'selected' : '' ?>><?= $escape($state['nombre']) ?></option><?php endforeach; ?></select></div><div class="filter-actions"><button class="btn btn-primary" type="submit">Filtrar</button><a class="btn btn-secondary" href="/pedidos">Limpiar</a></div></form>
<div class="table-wrapper section-gap"><table><thead><tr><th>Código</th><th>Cliente</th><th>Registro</th><th>Fecha requerida</th><th>Modalidad</th><th>Estado</th><th></th></tr></thead><tbody>
<?php foreach ($orders as $order): ?><tr><td><strong><?= $escape($order['codigo_pedido']) ?></strong><small class="table-subtext"><?= $escape($order['codigo_seguimiento']) ?></small></td><td><?= $escape($order['cliente']) ?></td><td><?= date('d/m/Y H:i', strtotime($order['fecha_pedido'])) ?></td><td><?= date('d/m/Y', strtotime($order['fecha_requerida'])) ?></td><td><?= $escape(ucfirst(strtolower($order['modalidad']))) ?></td><td><span class="status-badge status-<?= $escape(strtolower(str_replace(' ', '-', $order['estado']))) ?>"><?= $escape($order['estado']) ?></span></td><td><a class="text-link" href="/pedidos/<?= (int) $order['id_pedido'] ?>">Ver detalle</a></td></tr><?php endforeach; ?>
<?php if (!$orders): ?><tr><td class="empty-state" colspan="7">No se encontraron pedidos.</td></tr><?php endif; ?>
</tbody></table></div>
<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
