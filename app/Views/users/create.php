<div class="container-fluid">
    <div class="card shadow-none position-relative overflow-hidden">
        <div class="card-body px-4 py-3">
            <div class="row align-items-center">
                <div class="col-12">
                    <h4 class="fw-semibold mb-8">Crear Nuevo Usuario</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a class="text-muted text-decoration-none" href="<?= base_url() ?>">Inicio</a></li>
                            <li class="breadcrumb-item"><a class="text-muted text-decoration-none" href="<?= base_url('users') ?>">Usuarios</a></li>
                            <li class="breadcrumb-item" aria-current="page">Crear</li>
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
                    
                    <form action="<?= base_url('users/store') ?>" method="post" onsubmit="this.querySelector('button[type=submit]').disabled=true; return true;">
                        <?= csrf_field() ?>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="tb-username" name="username" placeholder="Nombre de usuario" value="<?= old('username') ?>" required>
                                    <label for="tb-username">Nombre de usuario</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="email" class="form-control" id="tb-email" name="email" placeholder="correo@ejemplo.com" value="<?= old('email') ?>" required>
                                    <label for="tb-email">Correo Electrónico</label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3 position-relative">
                                    <input type="password" class="form-control" id="tb-pwd" name="password" placeholder="Contraseña" required>
                                    <label for="tb-pwd">Contraseña</label>
                                    <button class="btn position-absolute top-50 end-0 translate-middle-y me-2 border-0" type="button" onclick="togglePassword()">
                                        <i class="ti ti-eye fs-5"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="tb-group" name="group" required>
                                        <option value="">Selecciona un rol</option>
                                        <?php foreach ($groups as $id => $group): ?>
                                            <option value="<?= $id ?>" <?= old('group') == $id ? 'selected' : '' ?>><?= $group['title'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <label for="tb-group">Rol del Usuario</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 mb-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="active" name="active" value="1" checked>
                                    <label class="form-check-label fw-bold text-dark" for="active">Cuenta Activa</label>
                                </div>
                            </div>
                        </div>

                        <h5 class="mb-4 fw-semibold mt-2">Permisos del Sistema</h5>
                        
                        <div class="row">
                            <!-- Categoría: Usuarios -->
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card shadow-none border h-100">
                                    <div class="card-header bg-light-primary py-2 px-3">
                                        <h6 class="card-title fw-semibold text-primary mb-0">
                                            <i class="ti ti-users fs-5 me-2"></i> Gestión de Usuarios
                                        </h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <?php 
                                        $userPerms = [
                                            'users.view'   => 'Ver',
                                            'users.create' => 'Crear',
                                            'users.edit'   => 'Editar',
                                            'users.delete' => 'Borrar'
                                        ];
                                        foreach ($userPerms as $perm => $label): 
                                        ?>
                                            <div class="form-check form-switch mb-2 d-flex align-items-center ps-0">
                                                <input class="form-check-input ms-0 me-2" type="checkbox" role="switch" id="perm_<?= str_replace('.', '_', $perm) ?>" name="permissions[]" value="<?= $perm ?>">
                                                <label class="form-check-label fw-semibold text-dark" for="perm_<?= str_replace('.', '_', $perm) ?>"><?= $label ?></label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Categoría: Empresas -->
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card shadow-none border h-100">
                                    <div class="card-header bg-light-primary py-2 px-3">
                                        <h6 class="card-title fw-semibold text-primary mb-0">
                                            <i class="ti ti-building-skyscraper fs-5 me-2"></i> Gestión de Empresas
                                        </h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <?php 
                                        $empPerms = [
                                            'empresas.view'   => 'Ver',
                                            'empresas.create' => 'Crear',
                                            'empresas.edit'   => 'Editar',
                                            'empresas.delete' => 'Borrar'
                                        ];
                                        foreach ($empPerms as $perm => $label): 
                                        ?>
                                            <div class="form-check form-switch mb-2 d-flex align-items-center ps-0">
                                                <input class="form-check-input ms-0 me-2" type="checkbox" role="switch" id="perm_<?= str_replace('.', '_', $perm) ?>" name="permissions[]" value="<?= $perm ?>">
                                                <label class="form-check-label fw-semibold text-dark" for="perm_<?= str_replace('.', '_', $perm) ?>"><?= $label ?></label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Categoría: SMTP -->
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card shadow-none border h-100">
                                    <div class="card-header bg-light-primary py-2 px-3">
                                        <h6 class="card-title fw-semibold text-primary mb-0">
                                            <i class="ti ti-settings fs-5 me-2"></i> Configuración SMTP
                                        </h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <?php 
                                        $sysPerms = [
                                            'email.manage'  => 'Gestionar Email'
                                        ];
                                        foreach ($sysPerms as $perm => $label): 
                                        ?>
                                            <div class="form-check form-switch mb-2 d-flex align-items-center ps-0">
                                                <input class="form-check-input ms-0 me-2" type="checkbox" role="switch" id="perm_<?= str_replace('.', '_', $perm) ?>" name="permissions[]" value="<?= $perm ?>">
                                                <label class="form-check-label fw-semibold text-dark" for="perm_<?= str_replace('.', '_', $perm) ?>"><?= $label ?></label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mt-4 pt-4 border-top">
                            <div class="d-flex flex-column flex-sm-row justify-content-end gap-2">
                                <a href="<?= base_url('users') ?>" class="btn btn-outline-primary px-4">Cancelar</a>
                                <button type="submit" class="btn btn-primary font-medium px-4">
                                    <div class="d-flex align-items-center justify-content-center">
                                        <i class="ti ti-send me-2 fs-4"></i>
                                        Guardar Usuario
                                    </div>
                                </button>
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
</script>
