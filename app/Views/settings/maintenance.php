<div class="container-fluid">
    <!-- =====================================================================
     CABECERA Y BREADCRUMB (NAVEGACIÓN)
     ===================================================================== -->
    <!-- Card del Breadcrumb -->
    <div class="card shadow-none border position-relative overflow-hidden mb-4">
        <div class="card-body px-4 py-3">
            <div class="row align-items-center">
                <div class="col-9">
                    <h4 class="fw-semibold mb-8">Mantenimiento del Sistema</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a class="text-muted text-decoration-none" href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                            <li class="breadcrumb-item text-muted" aria-current="page">Mantenimiento</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- =====================================================================
         OPCIONES DE MANTENIMIENTO
         ===================================================================== -->
    <div class="row">
        <!-- Tarjeta de Mantenimiento General -->
        <div class="col-md-6 mb-4">
            <div class="card h-100 border bg-light-primary">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div>
                        <h5 class="card-title fw-bold text-primary mb-1">Mantenimiento Completo</h5>
                        <p class="card-text text-muted mb-4">Ejecuta todas las acciones de limpieza simultáneamente (sesiones inactivas, debugs y logs).</p>
                    </div>
                    <div>
                        <form action="<?= base_url('settings/maintenance/clear-all') ?>" method="POST" class="d-inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-primary px-4 py-2 w-100">
                                Ejecutar Mantenimiento Completo
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Optimizar Base de Datos -->
        <div class="col-md-6 mb-4">
            <div class="card h-100 shadow-none border">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="p-2 rounded bg-light-primary text-primary d-flex align-items-center justify-content-center">
                                <i class="ti ti-database-export fs-7"></i>
                            </div>
                            <h5 class="fw-bold mb-0">Optimizar Base de Datos</h5>
                        </div>
                        <p class="text-muted mb-4">Desfragmenta y comprime la base de datos SQLite (VACUUM) para liberar espacio en disco y mejorar el rendimiento y tiempos de respuesta del panel.</p>
                    </div>
                    <div>
                        <form action="<?= base_url('settings/maintenance/optimize-db') ?>" method="POST">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-primary w-100">
                             Ejecutar Optimización
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= SECCIÓN: DATOS Y RESPALDOS ================= -->

        <!-- Respaldos (Backup) -->
        <div class="col-12 mb-4">
            <div class="card shadow-none border">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="p-2 rounded bg-light-primary text-primary d-flex align-items-center justify-content-center">
                            <i class="ti ti-database fs-7"></i>
                        </div>
                        <h5 class="fw-bold mb-0">Respaldo y Restauración de Base de Datos</h5>
                    </div>
                    <p class="text-muted mb-4">Descarga una copia de seguridad completa del sistema actual en formato SQLite o sube un archivo de respaldo para restaurarlo. <strong>Atención:</strong> Al restaurar, se destruirán todos los datos configurados en este momento.</p>
                    
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="h-100 p-4 border rounded d-flex flex-column justify-content-between">
                                <div>
                                    <h6 class="fw-bold mb-2">Crear Copia de Seguridad</h6>
                                    <p class="text-muted fs-3 mb-4">Descarga una copia completa de la base de datos actual para guardarla en un lugar seguro.</p>
                                </div>
                                <div>
                                    <a href="<?= base_url('settings/maintenance/backup/download') ?>" class="btn btn-outline-primary px-4 py-2">
                                        <i class="ti ti-download me-1"></i> Descargar Base de Datos
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="h-100 p-4 border rounded d-flex flex-column justify-content-between">
                                <div>
                                    <h6 class="fw-bold mb-2">Restaurar Copia de Seguridad</h6>
                                    <p class="text-muted fs-3 mb-4">Sube un archivo de respaldo SQLite anterior (`.db` o `.sqlite`) para restaurar toda la configuración del sistema.</p>
                                </div>
                                <div>
                                    <form action="<?= base_url('settings/maintenance/backup/restore') ?>" method="POST" enctype="multipart/form-data" data-confirm="Esta acción es irreversible y reemplazará toda la base de datos actual con la del respaldo. ¿Estás seguro de que quieres continuar?">
                                        <?= csrf_field() ?>
                                        <div class="d-flex flex-wrap align-items-center gap-2">
                                            <input type="file" name="backup_file" id="backup_file" class="d-none" accept=".db,.sqlite">
                                            <label for="backup_file" class="btn btn-primary px-4 mb-0 py-2 cursor-pointer">
                                                <i class="ti ti-upload me-1"></i> Seleccionar Archivo
                                            </label>
                                            <span id="file-name" class="text-muted fs-3">Ningún archivo seleccionado</span>
                                            <button type="submit" class="btn btn-danger px-4 py-2 d-none" id="btn-submit-restore">
                                                Restaurar
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const fileInput = document.getElementById('backup_file');
    const fileNameSpan = document.getElementById('file-name');
    const submitBtn = document.getElementById('btn-submit-restore');
    
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            if (this.files && this.files.length > 0) {
                fileNameSpan.textContent = this.files[0].name;
                submitBtn.classList.remove('d-none');
            } else {
                fileNameSpan.textContent = 'Ningún archivo seleccionado';
                submitBtn.classList.add('d-none');
            }
        });
    }
});
</script>
