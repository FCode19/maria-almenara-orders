<?php
use App\Helpers\Csrf;
$pageTitle = 'Usuarios';
$active = 'usuarios';
require BASE_PATH . '/app/Views/layouts/header.php';
?>
<div class="page-header">
    <div><span class="eyebrow">Administración</span><h1>Usuarios</h1><p>Gestiona el acceso del equipo sin exponer credenciales.</p></div>
    <a class="btn btn-primary" href="/usuarios/nuevo">+ Crear usuario</a>
</div>
<?php if ($success): ?><div class="alert alert-success"><?= $escape($success) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error"><?= $escape($error) ?></div><?php endif; ?>
<section class="card table-card">
    <div class="card-heading"><div><h2>Equipo con acceso</h2><p><?= count($users) ?> cuentas registradas</p></div></div>
    <div class="table-wrapper embedded"><table><thead><tr><th>Persona</th><th>Usuario</th><th>Rol</th><th>Estado</th><th>Creación</th><th class="align-right">Acción</th></tr></thead><tbody>
    <?php foreach ($users as $account): ?>
        <tr><td><div class="person-cell"><span class="avatar"><?= $escape(mb_strtoupper(mb_substr($account['nombres'], 0, 1) . mb_substr($account['apellidos'], 0, 1))) ?></span><span><strong><?= $escape($account['nombres'] . ' ' . $account['apellidos']) ?></strong><small><?= $escape($account['correo']) ?></small></span></div></td><td>@<?= $escape($account['usuario']) ?></td><td><span class="role-badge"><?= $escape($account['rol']) ?></span></td><td><span class="account-state <?= (int) $account['activo'] === 1 ? 'is-active' : 'is-inactive' ?>"><?= (int) $account['activo'] === 1 ? 'Activo' : 'Inactivo' ?></span></td><td><?= date('d/m/Y', strtotime($account['fecha_creacion'])) ?></td><td class="align-right"><?php if ((int) $account['id_usuario'] === (int) $user['id_usuario']): ?><span class="muted-text">Tu cuenta</span><?php else: ?><form method="POST" action="/usuarios/<?= (int) $account['id_usuario'] ?>/estado"><?= Csrf::field() ?><button class="btn btn-compact <?= (int) $account['activo'] === 1 ? 'btn-danger-soft' : 'btn-secondary' ?>" type="submit"><?= (int) $account['activo'] === 1 ? 'Desactivar' : 'Activar' ?></button></form><?php endif; ?></td></tr>
    <?php endforeach; ?>
    </tbody></table></div>
</section>
<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
