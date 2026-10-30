<?php
use App\Helpers\Csrf;
$pageTitle = $pageTitle ?? 'Gestión de pedidos';
$active = $active ?? '';
$escape = static fn (mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><meta name="theme-color" content="#68263e"><title><?= $escape($pageTitle) ?> - María Almenara</title><link rel="stylesheet" href="/assets/css/app.css"></head>
<body><div class="app-shell">
<aside class="sidebar">
    <a class="brand" href="/dashboard"><span class="brand-mark">MA</span><span>María Almenara<small>Operaciones</small></span></a>
    <nav aria-label="Navegación principal">
        <a class="menu-item <?= $active === 'dashboard' ? 'active' : '' ?>" href="/dashboard"><span class="menu-icon">⌂</span>Resumen</a>
        <a class="menu-item <?= $active === 'pedidos' ? 'active' : '' ?>" href="/pedidos"><span class="menu-icon">□</span>Pedidos</a>
        <a class="menu-item <?= $active === 'nuevo' ? 'active' : '' ?>" href="/pedidos/nuevo"><span class="menu-icon">＋</span>Nuevo pedido</a>
        <?php if (($user['rol'] ?? '') === 'Administrador'): ?><a class="menu-item <?= $active === 'usuarios' ? 'active' : '' ?>" href="/usuarios"><span class="menu-icon">◎</span>Usuarios</a><a class="menu-item <?= $active === 'apis' ? 'active' : '' ?>" href="/apis"><span class="menu-icon">↔</span>APIs</a><?php endif; ?>
    </nav>
    <div class="sidebar-user"><span class="avatar avatar-light"><?= $escape(mb_strtoupper(mb_substr($user['nombres'] ?? 'U', 0, 1) . mb_substr($user['apellidos'] ?? '', 0, 1))) ?></span><div><strong><?= $escape(($user['nombres'] ?? '') . ' ' . ($user['apellidos'] ?? '')) ?></strong><span><?= $escape($user['rol'] ?? '') ?></span></div><form action="/logout" method="POST"><?= Csrf::field() ?><button class="logout-button" type="submit" title="Cerrar sesión">↗</button></form></div>
</aside><main class="main-content">
