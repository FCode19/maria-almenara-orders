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

    <title>
        Inicio de sesion - Maria Almenara
    </title>

    <link
        rel="stylesheet"
        href="/assets/css/app.css"
    >
</head>

<body class="login-page">

<div class="login-container">

    <section class="login-info">

        <h1>Gestion de Pedidos</h1>

        <p>
            Plataforma para el registro,
            seguimiento y control de pedidos.
        </p>

    </section>

    <section class="login-area">

        <form
            method="POST"
            action="/login"
            class="login-card"
            autocomplete="off"
        >

            <?= Csrf::field() ?>

            <h2>Bienvenido</h2>

            <p>
                Ingrese sus credenciales para continuar.
            </p>

            <?php if (!empty($error)): ?>

                <div class="alert-error">
                    <?= htmlspecialchars(
                        $error,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </div>

            <?php endif; ?>

            <label for="login">
                Usuario o correo
            </label>

            <input
                id="login"
                name="login"
                type="text"
                maxlength="120"
                required
                autocomplete="username"
            >

            <label for="password">
                Contrasena
            </label>

            <input
                id="password"
                name="password"
                type="password"
                required
                autocomplete="current-password"
            >

            <button type="submit">
                Iniciar sesion
            </button>

        </form>

    </section>

</div>

</body>
</html>