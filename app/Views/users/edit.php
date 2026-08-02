<div class="container-fluid">
    <div class="card shadow-none position-relative overflow-hidden">
        <div class="card-body px-4 py-3">
            <div class="row align-items-center">
                <div class="col-12">
                    <h4 class="fw-semibold mb-8">Editar Usuario: <?= esc($user->username) ?></h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a class="text-muted text-decoration-none" href="<?= base_url() ?>">Inicio</a></li>
                            <li class="breadcrumb-item"><a class="text-muted text-decoration-none" href="<?= base_url('users') ?>">Usuarios</a></li>
                            <li class="breadcrumb-item" aria-current="page">Editar</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="mb-4 fw-semibold border-bottom pb-3">Información del Usuario</h5>
                    
                    <form action="<?= base_url('users/update/' . $user->id) ?>" method="post" onsubmit="this.querySelector('button[type=submit]').disabled=true; return true;">
                        <?= csrf_field() ?>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="tb-username" name="username" placeholder=" " value="<?= old('username', $user->username) ?>" required>
                                    <label for="tb-username">Nombre de usuario</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="email" class="form-control" id="tb-email" name="email" placeholder=" " value="<?= old('email', $user->email) ?>" required>
                                    <label for="tb-email">Correo Electrónico</label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3 position-relative">
                                    <input type="password" class="form-control" id="tb-pwd" name="password" placeholder=" ">
                                    <label for="tb-pwd">Contraseña (dejar en blanco para no cambiar)</label>
                                    <button class="btn position-absolute top-50 end-0 translate-middle-y me-2 border-0" type="button" onclick="togglePassword()">
                                        <i class="ti ti-eye fs-5"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="tb-group" name="group" required>
                                        <?php $currentGroup = $user->getGroups()[0] ?? ''; ?>
                                        <?php foreach ($groups as $id => $group): ?>
                                            <option value="<?= $id ?>" <?= old('group', $currentGroup) == $id ? 'selected' : '' ?>><?= $group['title'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <label for="tb-group">Rol del Usuario</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 mb-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="active" name="active" value="1" <?= $user->active ? 'checked' : '' ?>>
                                    <label class="form-check-label fw-bold text-dark" for="active">Cuenta Activa</label>
                                </div>
                            </div>
                        </div>

                        <h5 class="mb-4 fw-semibold mt-2">Permisos del Sistema</h5>
                        <?php $directPermissions = $user->getPermissions(); ?>
                        
                        <div class="card shadow-none border mb-4">
                            <div class="card-body p-0">
                                
                                <!-- Categoría: Usuarios -->
                                <?php
                                $catPerms = ['users.view', 'users.create', 'users.edit', 'users.delete'];
                                $isGroupPerm = false;
                                $isChecked = false;
                                foreach ($catPerms as $perm) {
                                    if ($user->can($perm)) {
                                        $isChecked = true;
                                        if (!in_array($perm, $directPermissions)) {
                                            $isGroupPerm = true;
                                        }
                                    }
                                }
                                if (!empty(old('permissions'))) {
                                    $isChecked = false;
                                    foreach ($catPerms as $perm) {
                                        if (in_array($perm, old('permissions', []))) {
                                            $isChecked = true;
                                        }
                                    }
                                }
                                ?>
                                <div class="p-3 border-bottom d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                                    <div class="d-flex align-items-center gap-3 mb-3 mb-md-0">
                                        <div class="p-2 bg-light-primary text-primary rounded d-flex align-items-center justify-content-center">
                                            <i class="ti ti-users fs-5"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-semibold">Gestión de Usuarios</h6>
                                            <small class="text-muted">Control total sobre los accesos y cuentas del sistema.</small>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-wrap gap-4">
                                        <div class="form-check form-switch d-flex align-items-center ps-0 mb-0 <?= $isGroupPerm ? 'opacity-75' : '' ?>">
                                            <input class="form-check-input ms-0 me-2 category-trigger" type="checkbox" role="switch" id="cat_users" data-target="users" <?= $isChecked ? 'checked' : '' ?> <?= $isGroupPerm ? 'disabled title="Este permiso proviene del rol asignado"' : '' ?>>
                                            <label class="form-check-label fw-semibold <?= $isGroupPerm ? 'text-muted' : 'text-dark' ?>" for="cat_users">
                                                Habilitar Acceso
                                                <?php if ($isGroupPerm): ?>
                                                    <span class="badge bg-light-primary text-primary ms-1 px-2 py-1" style="font-size:10px;">Rol</span>
                                                <?php endif; ?>
                                            </label>
                                        </div>
                                        <?php foreach ($catPerms as $perm): ?>
                                            <?php 
                                            $permChecked = !empty(old('permissions')) ? in_array($perm, old('permissions', [])) : in_array($perm, $directPermissions);
                                            if ($isGroupPerm) {
                                                $permChecked = true;
                                            }
                                            ?>
                                            <input type="checkbox" name="permissions[]" value="<?= $perm ?>" class="d-none" data-parent="users" <?= $permChecked ? 'checked' : '' ?>>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <!-- Categoría: Empresas -->
                                <?php
                                $catPerms = ['empresas.view', 'empresas.create', 'empresas.edit', 'empresas.delete'];
                                $isGroupPerm = false;
                                $isChecked = false;
                                foreach ($catPerms as $perm) {
                                    if ($user->can($perm)) {
                                        $isChecked = true;
                                        if (!in_array($perm, $directPermissions)) {
                                            $isGroupPerm = true;
                                        }
                                    }
                                }
                                if (!empty(old('permissions'))) {
                                    $isChecked = false;
                                    foreach ($catPerms as $perm) {
                                        if (in_array($perm, old('permissions', []))) {
                                            $isChecked = true;
                                        }
                                    }
                                }
                                ?>
                                <div class="p-3 border-bottom d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                                    <div class="d-flex align-items-center gap-3 mb-3 mb-md-0">
                                        <div class="p-2 bg-light-primary text-primary rounded d-flex align-items-center justify-content-center">
                                            <i class="ti ti-building-skyscraper fs-5"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-semibold">Gestión de Empresas</h6>
                                            <small class="text-muted">Administración completa de clientes y dominios.</small>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-wrap gap-4">
                                        <div class="form-check form-switch d-flex align-items-center ps-0 mb-0 <?= $isGroupPerm ? 'opacity-75' : '' ?>">
                                            <input class="form-check-input ms-0 me-2 category-trigger" type="checkbox" role="switch" id="cat_empresas" data-target="empresas" <?= $isChecked ? 'checked' : '' ?> <?= $isGroupPerm ? 'disabled title="Este permiso proviene del rol asignado"' : '' ?>>
                                            <label class="form-check-label fw-semibold <?= $isGroupPerm ? 'text-muted' : 'text-dark' ?>" for="cat_empresas">
                                                Habilitar Acceso
                                                <?php if ($isGroupPerm): ?>
                                                    <span class="badge bg-light-primary text-primary ms-1 px-2 py-1" style="font-size:10px;">Rol</span>
                                                <?php endif; ?>
                                            </label>
                                        </div>
                                        <?php foreach ($catPerms as $perm): ?>
                                            <?php 
                                            $permChecked = !empty(old('permissions')) ? in_array($perm, old('permissions', [])) : in_array($perm, $directPermissions);
                                            if ($isGroupPerm) {
                                                $permChecked = true;
                                            }
                                            ?>
                                            <input type="checkbox" name="permissions[]" value="<?= $perm ?>" class="d-none" data-parent="empresas" <?= $permChecked ? 'checked' : '' ?>>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <!-- Categoría: Canales de Alerta -->
                                <?php
                                $catPerms = ['alertas.view', 'alertas.manage'];
                                $isGroupPerm = false;
                                $isChecked = false;
                                foreach ($catPerms as $perm) {
                                    if ($user->can($perm)) {
                                        $isChecked = true;
                                        if (!in_array($perm, $directPermissions)) {
                                            $isGroupPerm = true;
                                        }
                                    }
                                }
                                if (!empty(old('permissions'))) {
                                    $isChecked = false;
                                    foreach ($catPerms as $perm) {
                                        if (in_array($perm, old('permissions', []))) {
                                            $isChecked = true;
                                        }
                                    }
                                }
                                ?>
                                <div class="p-3 border-bottom d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                                    <div class="d-flex align-items-center gap-3 mb-3 mb-md-0">
                                        <div class="p-2 bg-light-primary text-primary rounded d-flex align-items-center justify-content-center">
                                            <i class="ti ti-bell fs-5"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-semibold">Canales de Alerta</h6>
                                            <small class="text-muted">Configuración de notificaciones (Mail, Telegram, Discord, etc).</small>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-wrap gap-4">
                                        <div class="form-check form-switch d-flex align-items-center ps-0 mb-0 <?= $isGroupPerm ? 'opacity-75' : '' ?>">
                                            <input class="form-check-input ms-0 me-2 category-trigger" type="checkbox" role="switch" id="cat_alertas" data-target="alertas" <?= $isChecked ? 'checked' : '' ?> <?= $isGroupPerm ? 'disabled title="Este permiso proviene del rol asignado"' : '' ?>>
                                            <label class="form-check-label fw-semibold <?= $isGroupPerm ? 'text-muted' : 'text-dark' ?>" for="cat_alertas">
                                                Habilitar Acceso
                                                <?php if ($isGroupPerm): ?>
                                                    <span class="badge bg-light-primary text-primary ms-1 px-2 py-1" style="font-size:10px;">Rol</span>
                                                <?php endif; ?>
                                            </label>
                                        </div>
                                        <?php foreach ($catPerms as $perm): ?>
                                            <?php 
                                            $permChecked = !empty(old('permissions')) ? in_array($perm, old('permissions', [])) : in_array($perm, $directPermissions);
                                            if ($isGroupPerm) {
                                                $permChecked = true;
                                            }
                                            ?>
                                            <input type="checkbox" name="permissions[]" value="<?= $perm ?>" class="d-none" data-parent="alertas" <?= $permChecked ? 'checked' : '' ?>>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <!-- Categoría: Inteligencia Artificial -->
                                <?php
                                $catPerms = ['ai.view', 'ai.manage'];
                                $isGroupPerm = false;
                                $isChecked = false;
                                foreach ($catPerms as $perm) {
                                    if ($user->can($perm)) {
                                        $isChecked = true;
                                        if (!in_array($perm, $directPermissions)) {
                                            $isGroupPerm = true;
                                        }
                                    }
                                }
                                if (!empty(old('permissions'))) {
                                    $isChecked = false;
                                    foreach ($catPerms as $perm) {
                                        if (in_array($perm, old('permissions', []))) {
                                            $isChecked = true;
                                        }
                                    }
                                }
                                ?>
                                <div class="p-3 border-bottom d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                                    <div class="d-flex align-items-center gap-3 mb-3 mb-md-0">
                                        <div class="p-2 bg-light-primary text-primary rounded d-flex align-items-center justify-content-center">
                                            <i class="ti ti-cpu fs-5"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-semibold">Inteligencia Artificial</h6>
                                            <small class="text-muted">Ajustes y prompts del motor de IA.</small>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-wrap gap-4">
                                        <div class="form-check form-switch d-flex align-items-center ps-0 mb-0 <?= $isGroupPerm ? 'opacity-75' : '' ?>">
                                            <input class="form-check-input ms-0 me-2 category-trigger" type="checkbox" role="switch" id="cat_ai" data-target="ai" <?= $isChecked ? 'checked' : '' ?> <?= $isGroupPerm ? 'disabled title="Este permiso proviene del rol asignado"' : '' ?>>
                                            <label class="form-check-label fw-semibold <?= $isGroupPerm ? 'text-muted' : 'text-dark' ?>" for="cat_ai">
                                                Habilitar Acceso
                                                <?php if ($isGroupPerm): ?>
                                                    <span class="badge bg-light-primary text-primary ms-1 px-2 py-1" style="font-size:10px;">Rol</span>
                                                <?php endif; ?>
                                            </label>
                                        </div>
                                        <?php foreach ($catPerms as $perm): ?>
                                            <?php 
                                            $permChecked = !empty(old('permissions')) ? in_array($perm, old('permissions', [])) : in_array($perm, $directPermissions);
                                            if ($isGroupPerm) {
                                                $permChecked = true;
                                            }
                                            ?>
                                            <input type="checkbox" name="permissions[]" value="<?= $perm ?>" class="d-none" data-parent="ai" <?= $permChecked ? 'checked' : '' ?>>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <!-- Categoría: Mantenimiento -->
                                <?php
                                $catPerms = ['admin.access'];
                                $isGroupPerm = false;
                                $isChecked = false;
                                foreach ($catPerms as $perm) {
                                    if ($user->can($perm)) {
                                        $isChecked = true;
                                        if (!in_array($perm, $directPermissions)) {
                                            $isGroupPerm = true;
                                        }
                                    }
                                }
                                if (!empty(old('permissions'))) {
                                    $isChecked = false;
                                    foreach ($catPerms as $perm) {
                                        if (in_array($perm, old('permissions', []))) {
                                            $isChecked = true;
                                        }
                                    }
                                }
                                ?>
                                <div class="p-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                                    <div class="d-flex align-items-center gap-3 mb-3 mb-md-0">
                                        <div class="p-2 bg-light-primary text-primary rounded d-flex align-items-center justify-content-center">
                                            <i class="ti ti-tool fs-5"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-semibold">Mantenimiento del Sistema</h6>
                                            <small class="text-muted">Limpieza profunda y respaldos de datos.</small>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-wrap gap-4">
                                        <div class="form-check form-switch d-flex align-items-center ps-0 mb-0 <?= $isGroupPerm ? 'opacity-75' : '' ?>">
                                            <input class="form-check-input ms-0 me-2 category-trigger" type="checkbox" role="switch" id="cat_maint" data-target="maint" <?= $isChecked ? 'checked' : '' ?> <?= $isGroupPerm ? 'disabled title="Este permiso proviene del rol asignado"' : '' ?>>
                                            <label class="form-check-label fw-semibold <?= $isGroupPerm ? 'text-muted' : 'text-dark' ?>" for="cat_maint">
                                                Habilitar Acceso
                                                <?php if ($isGroupPerm): ?>
                                                    <span class="badge bg-light-primary text-primary ms-1 px-2 py-1" style="font-size:10px;">Rol</span>
                                                <?php endif; ?>
                                            </label>
                                        </div>
                                        <?php foreach ($catPerms as $perm): ?>
                                            <?php 
                                            $permChecked = !empty(old('permissions')) ? in_array($perm, old('permissions', [])) : in_array($perm, $directPermissions);
                                            if ($isGroupPerm) {
                                                $permChecked = true;
                                            }
                                            ?>
                                            <input type="checkbox" name="permissions[]" value="<?= $perm ?>" class="d-none" data-parent="maint" <?= $permChecked ? 'checked' : '' ?>>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="col-12 mt-4 pt-4 border-top">
                            <div class="d-flex justify-content-center gap-2">
                                <button type="submit" class="btn btn-primary font-medium px-4 d-inline-flex align-items-center justify-content-center">
                                    <i class="ti ti-device-floppy me-2 fs-4"></i> Actualizar
                                </button>
                                <a href="<?= base_url('users') ?>" class="btn btn-outline-primary px-4 d-inline-flex align-items-center justify-content-center">
                                    <i class="ti ti-x me-2 fs-4"></i> Cancelar
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const input = document.getElementById('tb-pwd');
    input.type = input.type === 'password' ? 'text' : 'password';
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.category-trigger').forEach(trigger => {
        trigger.addEventListener('change', function() {
            const target = this.getAttribute('data-target');
            const isChecked = this.checked;
            document.querySelectorAll(`[data-parent="${target}"]`).forEach(cb => {
                cb.checked = isChecked;
            });
        });
    });
});
</script>
