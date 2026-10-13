<?php
$pageTitle = 'Dashboard';
$active = 'dashboard';
require BASE_PATH . '/app/Views/layouts/header.php';
$kpis = $dashboard['kpis'];
?>
<div class="page-header"><div><h1>Dashboard</h1><p>Resumen operativo del flujo de pedidos.</p></div><a class="btn btn-primary" href="/pedidos/nuevo">+ Nuevo pedido</a></div>
<section class="kpi-grid">
    <article class="card"><span class="kpi-title">Pedidos totales</span><strong class="kpi-value"><?= (int) $kpis['total'] ?></strong><span class="kpi-note">Registrados en el sistema</span></article>
    <article class="card"><span class="kpi-title">En producción</span><strong class="kpi-value"><?= (int) $kpis['en_produccion'] ?></strong><span class="kpi-note">Requieren seguimiento</span></article>
    <article class="card"><span class="kpi-title">Terminados</span><strong class="kpi-value"><?= (int) $kpis['terminados'] ?></strong><span class="kpi-note">Listos para entrega</span></article>
    <article class="card"><span class="kpi-title">Próximas entregas</span><strong class="kpi-value"><?= (int) $kpis['proximos'] ?></strong><span class="kpi-note">En los próximos 3 días</span></article>
</section>
<section class="two-column-grid section-gap">
    <article class="card"><div class="card-heading"><h2>Pedidos por estado</h2><a href="/pedidos">Ver todos</a></div><div class="state-list"><?php foreach ($dashboard['por_estado'] as $row): ?><div><span class="status-badge status-<?= $escape(strtolower(str_replace(' ', '-', $row['estado']))) ?>"><?= $escape($row['estado']) ?></span><strong><?= (int) $row['total'] ?></strong></div><?php endforeach; ?></div></article>
    <article class="card"><div class="card-heading"><h2>Próximas entregas</h2></div><?php if (!$dashboard['proximos']): ?><p class="empty-state">No hay entregas pendientes.</p><?php endif; ?><div class="upcoming-list"><?php foreach ($dashboard['proximos'] as $order): ?><a href="/pedidos/<?= (int) $order['id_pedido'] ?>"><span><strong><?= $escape($order['codigo_pedido']) ?></strong><small><?= $escape($order['cliente']) ?></small></span><span><?= date('d/m/Y', strtotime($order['fecha_requerida'])) ?></span></a><?php endforeach; ?></div></article>
</section>
<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
