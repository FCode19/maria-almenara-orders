<?php
use App\Helpers\Csrf;
$pageTitle = $pageTitle ?? 'Gestión de pedidos';
$active = $active ?? '';
$escape = static fn (mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title><?= $escape($pageTitle) ?> - María Almenara</title><link rel="stylesheet" href="/assets/css/app.css"></head>
<body><div class="app-shell">
<aside class="sidebar">
    <a class="brand" href="/dashboard">María Almenara<small>Gestión de Pedidos</small></a>
    <nav aria-label="Navegación principal">
        <a class="menu-item <?= $active === 'dashboard' ? 'active' : '' ?>" href="/dashboard">Dashboard</a>
        <a class="menu-item <?= $active === 'pedidos' ? 'active' : '' ?>" href="/pedidos">Pedidos</a>
        <a class="menu-item <?= $active === 'nuevo' ? 'active' : '' ?>" href="/pedidos/nuevo">Nuevo pedido</a>
        <?php if (($user['rol'] ?? '') === 'Administrador'): ?><a class="menu-item <?= $active === 'usuarios' ? 'active' : '' ?>" href="/usuarios">Usuarios</a><?php endif; ?>
    </nav>
    <div class="sidebar-user"><strong><?= $escape(($user['nombres'] ?? '') . ' ' . ($user['apellidos'] ?? '')) ?></strong><span><?= $escape($user['rol'] ?? '') ?></span><form action="/logout" method="POST"><?= Csrf::field() ?><button class="logout-button" type="submit">Cerrar sesión</button></form></div>
</aside><main class="main-content">
