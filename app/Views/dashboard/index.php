<?php
$pageTitle = 'Resumen operativo';
$active = 'dashboard';
require BASE_PATH . '/app/Views/layouts/header.php';
$kpis = $dashboard['kpis'];
$maxState = max(1, ...array_map(static fn ($row) => (int) $row['total'], $dashboard['por_estado']));
$maxTrend = max(1, ...array_map(static fn ($row) => (int) $row['total'], $dashboard['tendencia']));
$urgencyLabel = ['overdue' => 'Vencido', 'today' => 'Vence hoy', 'soon' => 'Próximo'];
?>
<div class="page-header dashboard-heading">
    <div><span class="eyebrow"><?= date('d/m/Y') ?></span><h1>Hola, <?= $escape($user['nombres'] ?? 'equipo') ?></h1><p>Estas son las prioridades de la operación de hoy.</p></div>
    <div class="quick-actions"><a class="btn btn-secondary" href="/pedidos?urgency=overdue">Ver alertas</a><a class="btn btn-primary" href="/pedidos/nuevo">+ Nuevo pedido</a></div>
</div>
<?php if ((int) $kpis['vencidos'] > 0): ?><a class="priority-banner" href="/pedidos?urgency=overdue"><span class="priority-icon">!</span><span><strong><?= (int) $kpis['vencidos'] ?> pedido<?= (int) $kpis['vencidos'] === 1 ? '' : 's' ?> fuera de fecha</strong><small>Revisa su producción y actualiza el estado cuanto antes.</small></span><span class="banner-link">Atender ahora →</span></a><?php endif; ?>
<section class="kpi-grid">
    <article class="card kpi-card"><span class="kpi-icon icon-active">↗</span><span class="kpi-title">Pedidos activos</span><strong class="kpi-value"><?= (int) $kpis['activos'] ?></strong><span class="kpi-note">En flujo de atención</span></article>
    <article class="card kpi-card"><span class="kpi-icon icon-production">◒</span><span class="kpi-title">En producción</span><strong class="kpi-value"><?= (int) $kpis['en_produccion'] ?></strong><span class="kpi-note">Preparándose ahora</span></article>
    <article class="card kpi-card"><span class="kpi-icon icon-ready">✓</span><span class="kpi-title">Listos para entregar</span><strong class="kpi-value"><?= (int) $kpis['terminados'] ?></strong><span class="kpi-note">Requieren coordinación</span></article>
    <article class="card kpi-card"><span class="kpi-icon icon-compliance">%</span><span class="kpi-title">Cumplimiento</span><strong class="kpi-value"><?= (int) $dashboard['cumplimiento']['percentage'] ?>%</strong><span class="kpi-note"><?= (int) $dashboard['cumplimiento']['on_time'] ?> de <?= (int) $dashboard['cumplimiento']['delivered'] ?> entregados a tiempo</span></article>
</section>
<section class="dashboard-grid section-gap">
    <article class="card alerts-panel"><div class="card-heading"><div><span class="eyebrow">Atención inmediata</span><h2>Alertas de entrega</h2></div><span class="notification-count"><?= count($dashboard['alertas']) ?></span></div>
        <?php if (!$dashboard['alertas']): ?><div class="empty-state"><strong>Todo está bajo control</strong><span>No hay pedidos con vencimiento en los próximos 3 días.</span></div><?php endif; ?>
        <div class="alert-list"><?php foreach ($dashboard['alertas'] as $order): ?><a class="alert-order urgency-<?= $escape($order['urgency']) ?>" href="/pedidos/<?= (int) $order['id_pedido'] ?>"><span class="urgency-dot"></span><span class="alert-order-main"><strong><?= $escape($order['codigo_pedido']) ?> · <?= $escape($order['cliente']) ?></strong><small><?= $escape($order['sede']) ?> · <?= $escape($order['estado']) ?></small></span><span class="alert-order-date"><strong><?= $escape($urgencyLabel[$order['urgency']] ?? 'Próximo') ?></strong><small><?= date('d/m/Y', strtotime($order['fecha_requerida'])) ?></small></span></a><?php endforeach; ?></div>
    </article>
    <article class="card status-panel"><div class="card-heading"><div><span class="eyebrow">Carga actual</span><h2>Pedidos por estado</h2></div><a href="/pedidos">Ver todos</a></div><div class="bar-list"><?php foreach ($dashboard['por_estado'] as $row): ?><a href="/pedidos?state=<?= urlencode($row['estado']) ?>"><span class="bar-label"><span><?= $escape($row['estado']) ?></span><strong><?= (int) $row['total'] ?></strong></span><span class="bar-track"><span class="bar-fill status-fill-<?= $escape(strtolower(str_replace(' ', '-', $row['estado']))) ?>" style="width: <?= max(3, ((int) $row['total'] / $maxState) * 100) ?>%"></span></span></a><?php endforeach; ?></div></article>
</section>
<section class="two-column-grid section-gap">
    <article class="card"><div class="card-heading"><div><span class="eyebrow">Mes <?= $escape($dashboard['periodo_tendencia']) ?></span><h2>Pedidos por semana</h2></div></div><div class="mini-chart" aria-label="Pedidos registrados por semana del mes"><?php foreach ($dashboard['tendencia'] as $point): ?><div class="chart-column"><span class="chart-value"><?= (int) $point['total'] ?></span><span class="chart-bar" style="height: <?= max(8, ((int) $point['total'] / $maxTrend) * 100) ?>%"></span><small><?= $escape($point['label']) ?></small></div><?php endforeach; ?></div></article>
    <article class="card"><div class="card-heading"><div><span class="eyebrow">Distribución</span><h2>Pedidos por sede</h2></div></div><div class="site-list"><?php foreach ($dashboard['por_sede'] as $index => $row): ?><div><span><i style="--site-index:<?= (int) $index ?>"></i><?= $escape($row['sede']) ?></span><strong><?= (int) $row['total'] ?></strong></div><?php endforeach; ?></div></article>
</section>
<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
