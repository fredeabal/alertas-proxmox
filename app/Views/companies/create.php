<div class="container-fluid">
    <div class="card shadow-none position-relative overflow-hidden">
        <div class="card-body px-4 py-3">
            <div class="row align-items-center">
                <div class="col-12">
                    <h4 class="fw-semibold mb-8">Crear Nueva Empresa</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a class="text-muted text-decoration-none" href="<?= base_url() ?>">Inicio</a></li>
                            <li class="breadcrumb-item"><a class="text-muted text-decoration-none" href="<?= base_url('companies') ?>">Empresas</a></li>
                            <li class="breadcrumb-item" aria-current="page">Crear</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <form action="<?= base_url('companies/store') ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="row">
            <div class="col-lg-4 d-flex align-items-stretch">
                <div class="card w-100 position-relative overflow-hidden">
                    <div class="card-body p-4 text-center d-flex flex-column justify-content-center">
                        <div class="mb-3">
                            <img id="logoPreview" src="<?= base_url('assets/images/logos/default-company.png') ?>" alt="Preview" class="rounded shadow-sm company-logo-preview">
                        </div>
                        
                        <h4 class="fw-semibold mb-3">Nueva Empresa</h4>

                        <label for="logo" class="btn btn-primary btn-sm mb-2 cursor-pointer">
                            <i class="ti ti-upload me-1"></i> Seleccionar Logo
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
                                    <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre de la empresa" value="<?= old('nombre') ?>" required>
                                    <label for="nombre">Nombre de la Empresa</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="cif" name="cif" placeholder="CIF/NIF" value="<?= old('cif') ?>">
                                    <label for="cif">CIF / NIF</label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input type="email" class="form-control" id="email" name="email" placeholder="Email de contacto" value="<?= old('email') ?>">
                                    <label for="email">Correo Electrónico</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="telefono" name="telefono" placeholder="Teléfono" value="<?= old('telefono') ?>">
                                    <label for="telefono">Teléfono</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="direccion" name="direccion" placeholder="Dirección física" value="<?= old('direccion') ?>">
                                <label for="direccion">Dirección</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="proxmox_host" name="proxmox_host" placeholder="IP o hostname de Proxmox" value="<?= old('proxmox_host') ?>">
                                <label for="proxmox_host">IP/Hostname Proxmox</label>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-sm-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="send_email" name="send_email" value="1" checked>
                                    <label class="form-check-label fw-bold text-dark" for="send_email">Alertas por email</label>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="ai_enabled" name="ai_enabled" value="1" checked>
                                    <label class="form-check-label fw-bold text-dark" for="ai_enabled">
                                        Resumen IA
                                    </label>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="active" name="active" value="1" checked>
                                    <label class="form-check-label fw-bold text-dark" for="active">Empresa Activa</label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 border-top pt-3">
                            <div class="d-flex flex-column flex-sm-row gap-2">
                                <button type="submit" class="btn btn-primary font-medium px-4">
                                    <div class="d-flex align-items-center justify-content-center">
                                        <i class="ti ti-device-floppy me-2 fs-4"></i> Crear Empresa
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
</script>
