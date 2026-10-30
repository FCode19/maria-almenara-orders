<?php
use App\Helpers\Csrf;
$pageTitle = 'Crear usuario';
$active = 'usuarios';
require BASE_PATH . '/app/Views/layouts/header.php';
$value = static fn (string $key, mixed $default = ''): mixed => $old[$key] ?? $default;
?>
<div class="page-header"><div><a class="back-link" href="/usuarios">← Volver a usuarios</a><h1>Crear usuario</h1><p>Asigna acceso según la responsabilidad de la persona.</p></div></div>
<?php if ($error): ?><div class="alert alert-error"><?= $escape($error) ?></div><?php endif; ?>
<form class="card narrow-card" method="POST" action="/usuarios" autocomplete="off">
    <?= Csrf::field() ?>
    <div class="card-heading"><div><h2>Datos de la cuenta</h2><p>Todos los campos son obligatorios.</p></div></div>
    <div class="form-grid">
        <div class="form-group"><label for="nombres">Nombres</label><input id="nombres" name="nombres" maxlength="100" value="<?= $escape($value('nombres')) ?>" required></div>
        <div class="form-group"><label for="apellidos">Apellidos</label><input id="apellidos" name="apellidos" maxlength="100" value="<?= $escape($value('apellidos')) ?>" required></div>
        <div class="form-group"><label for="usuario">Usuario</label><input id="usuario" name="usuario" maxlength="50" pattern="[A-Za-z0-9._-]{4,50}" value="<?= $escape($value('usuario')) ?>" required><small>4 a 50 caracteres, sin espacios.</small></div>
        <div class="form-group"><label for="correo">Correo</label><input id="correo" name="correo" type="email" maxlength="120" value="<?= $escape($value('correo')) ?>" required></div>
        <div class="form-group full"><label for="id_rol">Rol</label><select id="id_rol" name="id_rol" required><option value="">Seleccionar rol</option><?php foreach ($roles as $role): ?><option value="<?= (int) $role['id_rol'] ?>" <?= (string) $value('id_rol') === (string) $role['id_rol'] ? 'selected' : '' ?>><?= $escape($role['nombre']) ?></option><?php endforeach; ?></select><small>El Encargado opera pedidos; el Administrador también gestiona usuarios.</small></div>
        <div class="form-group"><label for="password">Contraseña</label><input id="password" name="password" type="password" minlength="8" maxlength="72" required autocomplete="new-password"><small>Mínimo 8 caracteres, con letras y números.</small></div>
        <div class="form-group"><label for="password_confirmation">Confirmar contraseña</label><input id="password_confirmation" name="password_confirmation" type="password" minlength="8" maxlength="72" required autocomplete="new-password"></div>
    </div>
    <div class="form-actions"><a class="btn btn-secondary" href="/usuarios">Cancelar</a><button class="btn btn-primary" type="submit">Crear usuario</button></div>
</form>
<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
