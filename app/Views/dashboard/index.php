<?php

use App\Helpers\Csrf;

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Dashboard - Maria Almenara</title>

    <link
        rel="stylesheet"
        href="/assets/css/app.css"
    >
</head>

<body>

<header class="topbar">

    <div>
        <strong>Maria Almenara</strong>
        <span>Gestion de Pedidos</span>
    </div>

    <div class="user-info">

        <span>
            <?= htmlspecialchars(
                $user['nombres']
                . ' '
                . $user['apellidos'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </span>

        <small>
            <?= htmlspecialchars(
                $user['rol'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </small>

        <form
            action="/logout"
            method="POST"
        >
            <?= Csrf::field() ?>

            <button type="submit">
                Cerrar sesion
            </button>
        </form>

    </div>

</header>

<main class="content">

    <h1>Dashboard</h1>

    <p>
        Bienvenido al sistema de gestion de pedidos.
    </p>

    <?php if ($user['rol'] === 'Administrador'): ?>

        <div class="admin-box">
            Acceso administrativo habilitado.
        </div>

    <?php endif; ?>

</main>

</body>
</html>