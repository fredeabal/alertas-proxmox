<div class="container-fluid">
    <div class="card shadow-none position-relative overflow-hidden">
        <div class="card-body px-4 py-3">
            <div class="row align-items-center">
                <div class="col-12">
                    <h4 class="fw-semibold mb-8">Editar Empresa: <?= esc($empresa->nombre) ?></h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a class="text-muted text-decoration-none" href="<?= base_url() ?>">Inicio</a></li>
                            <li class="breadcrumb-item"><a class="text-muted text-decoration-none" href="<?= base_url('companies') ?>">Empresas</a></li>
                            <li class="breadcrumb-item" aria-current="page">Editar</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <form action="<?= base_url('companies/update/' . $empresa->id) ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="row">
            <div class="col-lg-4 d-flex align-items-stretch">
                <div class="card w-100 position-relative overflow-hidden">
                    <div class="card-body p-4 text-center d-flex flex-column justify-content-center">
                        <?php 
                            $logoPath = $empresa->logo ? base_url('uploads/logos/' . $empresa->logo) : base_url('assets/images/logos/default-company.png');
                        ?>
                        <div class="mb-3">
                            <img id="logoPreview" src="<?= $logoPath ?>" alt="Preview" class="rounded shadow-sm" style="width: 120px; height: 120px; object-fit: cover;">
                        </div>
                        
                        <h4 class="fw-semibold mb-3"><?= esc($empresa->nombre) ?></h4>

                        <label for="logo" class="btn btn-primary btn-sm mb-2 cursor-pointer">
                            <i class="ti ti-upload me-1"></i> Cambiar Logo
                        </label>
                        <input class="d-none" type="file" id="logo" name="logo" accept="image/*" onchange="previewImage(this)">
                        <div class="form-text fs-2">JPG, PNG o GIF. Máx 2MB</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8 d-flex align-items-stretch">
                <div class="card w-100 position-relative overflow-hidden">
                    <div class="card-body p-4">
                        <h5 class="mb-3">Datos de la Empresa</h5>
                        
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre de la empresa" value="<?= old('nombre', $empresa->nombre) ?>" required>
                                    <label for="nombre">Nombre de la Empresa</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="cif" name="cif" placeholder="CIF/NIF" value="<?= old('cif', $empresa->cif) ?>">
                                    <label for="cif">CIF / NIF</label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input type="email" class="form-control" id="email" name="email" placeholder="Email de contacto" value="<?= old('email', $empresa->email) ?>">
                                    <label for="email">Correo Electrónico</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="telefono" name="telefono" placeholder="Teléfono" value="<?= old('telefono', $empresa->telefono) ?>">
                                    <label for="telefono">Teléfono</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="direccion" name="direccion" placeholder="Dirección física" value="<?= old('direccion', $empresa->direccion) ?>">
                                <label for="direccion">Dirección</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="proxmox_host" class="form-label">IP/Hostname Proxmox</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="proxmox_host" name="proxmox_host" placeholder="IP o hostname de Proxmox" aria-label="IP o hostname de Proxmox" aria-describedby="ping-btn" value="<?= old('proxmox_host', $empresa->proxmox_host ?? '') ?>">
                                <button class="btn bg-info-subtle text-info" type="button" id="ping-btn">Ping</button>
                            </div>
                            <small id="ping-result" class="d-block mt-2 text-muted"></small>
                        </div>

                        <div class="row mb-4">
                            <div class="col-sm-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="send_email" name="send_email" value="1" <?= $empresa->send_email ? 'checked' : '' ?>>
                                    <label class="form-check-label fw-bold text-dark" for="send_email">Enviar alertas por email</label>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="ai_enabled" name="ai_enabled" value="1" <?= $empresa->ai_enabled ? 'checked' : '' ?>>
                                    <label class="form-check-label fw-bold text-dark" for="ai_enabled">
                                        <i class="ti ti-robot text-primary me-1"></i> Resumen IA
                                    </label>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="active" name="active" value="1" <?= $empresa->active ? 'checked' : '' ?>>
                                    <label class="form-check-label fw-bold text-dark" for="active">Empresa Activa</label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 border-top pt-3">
                            <div class="d-flex flex-column flex-sm-row gap-2">
                                <button type="submit" class="btn btn-primary font-medium px-4">
                                    <div class="d-flex align-items-center justify-content-center">
                                        <i class="ti ti-device-floppy me-2 fs-4"></i> Guardar Cambios
                                    </div>
                                </button>
                                <a href="<?= base_url('companies') ?>" class="btn btn-outline-primary px-4">Cancelar</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('logoPreview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const pingBtn = document.getElementById('ping-btn');
    const hostInput = document.getElementById('proxmox_host');
    const resultEl = document.getElementById('ping-result');

    if (!pingBtn || !hostInput || !resultEl) {
        return;
    }

    pingBtn.addEventListener('click', async function () {
        const host = hostInput.value.trim();
        if (!host) {
            resultEl.className = 'd-block mt-2 text-danger';
            resultEl.textContent = 'Ingresa una IP o hostname para hacer ping.';
            return;
        }

        pingBtn.disabled = true;
        resultEl.className = 'd-block mt-2 text-muted';
        resultEl.textContent = 'Probando conectividad...';

        try {
            const response = await fetch('<?= base_url('companies/ping') ?>?host=' + encodeURIComponent(host), {
                method: 'GET',
                headers: { 'Accept': 'application/json' }
            });

            const data = await response.json();
            if (response.ok && data.ok) {
                resultEl.className = 'd-block mt-2 text-success';
                resultEl.textContent = data.message || 'Ping OK';
            } else {
                resultEl.className = 'd-block mt-2 text-danger';
                resultEl.textContent = data.message || 'No se pudo hacer ping.';
            }
        } catch (error) {
            resultEl.className = 'd-block mt-2 text-danger';
            resultEl.textContent = 'Error de red al ejecutar el ping.';
        } finally {
            pingBtn.disabled = false;
        }
    });
});
</script>
