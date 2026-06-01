<div class="container-fluid">
    <div class="card shadow-none position-relative overflow-hidden mb-4">
        <div class="card-body px-4 py-3">
            <div class="row align-items-center">
                <div class="col-9">
                    <h4 class="fw-semibold mb-8">Escritorio Principal</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a class="text-muted text-decoration-none" href="<?= base_url() ?>">Panel de Control</a></li>
                            <li class="breadcrumb-item" aria-current="page">Empresas Monitorizadas</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <?php foreach ($empresas as $empresa): ?>
            <div class="col-12 col-md-6 col-lg-3 d-flex align-items-stretch company-card-container" data-id="<?= $empresa->id ?>">
                <a href="<?= base_url('companies/view/' . $empresa->id) ?>" 
                   id="card-<?= $empresa->id ?>"
                   class="card hover-img w-100 text-decoration-none border <?= $empresa->border_class ?> shadow-sm transition-all overflow-hidden h-100">
                    <?php $pulseColor = $empresa->pulse_color; ?>
                    <div class="card-body p-3 d-flex flex-column justify-content-between">
                        <!-- Logo + Nombre -->
                        <div class="text-center pt-1">
                            <?php $logoPath = $empresa->logo ? base_url('uploads/logos/' . $empresa->logo) : base_url('assets/images/logos/default-company.png'); ?>
                            <img src="<?= $logoPath ?>" alt="<?= esc($empresa->nombre) ?>" 
                                 class="rounded-circle shadow-sm mb-2 border border-2 border-white dashboard-company-logo" 
                                 width="65" height="65">
                            <h6 class="fw-bold text-dark mb-0 text-truncate px-2 fs-3"><?= esc($empresa->nombre) ?></h6>
                        </div>

                        <!-- Footer: LED + Estado -->
                        <div class="d-flex align-items-center justify-content-center mt-auto pt-2 border-top gap-2">
                            <div id="pulse-bg-<?= $empresa->id ?>" class="d-flex align-items-center justify-content-center bg-light-<?= $pulseColor ?> rounded-circle led-wrapper">
                                <span id="pulse-blob-<?= $empresa->id ?>" class="blob bg-<?= $pulseColor ?> rounded-circle telemetry-led"></span>
                            </div>
                            <div id="status-text-<?= $empresa->id ?>" class="fw-semibold fs-2">
                                <?php if ($empresa->alert_count > 0): ?>
                                    <span class="text-<?= $pulseColor ?>">Requiere Atención</span>
                                <?php else: ?>
                                    <span class="text-success">Todo Correcto</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>

        <?php if (empty($empresas)): ?>
            <div class="col-12">
                <div class="card p-5 text-center shadow-none border">
                    <div class="mb-3">
                        <i class="ti ti-building-community fs-10 text-muted"></i>
                    </div>
                    <h4 class="fw-semibold">No hay empresas registradas</h4>
                    <p class="text-muted">Comienza añadiendo tu primera empresa para monitorizar sus alertas de Proxmox.</p>
                    <div class="mt-3">
                        <a href="<?= base_url('companies/create') ?>" class="btn btn-primary">Añadir Empresa</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Función para actualizar los estados
    function refreshDashboard() {
        fetch('<?= base_url('dashboard/status') ?>')
            .then(response => response.json())
            .then(data => {
                data.forEach(empresa => {
                    const card = document.getElementById(`card-${empresa.id}`);
                    const pulseBg = document.getElementById(`pulse-bg-${empresa.id}`);
                    const pulseBlob = document.getElementById(`pulse-blob-${empresa.id}`);
                    const statusText = document.getElementById(`status-text-${empresa.id}`);

                    if (card) {
                        const textColor = empresa.pulse_color;

                        // Actualizar borde de la tarjeta
                        card.className = `card hover-img w-100 text-decoration-none border ${empresa.border_class} shadow-sm transition-all overflow-hidden h-100`;
                        
                        // Actualizar indicador LED
                        pulseBg.className = `d-flex align-items-center justify-content-center bg-light-${textColor} rounded-circle led-wrapper`;
                        pulseBlob.className = `blob bg-${textColor} rounded-circle telemetry-led`;

                        // Actualizar texto de estado
                        if (empresa.alert_count > 0) {
                            statusText.innerHTML = `<span class="text-${textColor}">Requiere Atención</span>`;
                        } else {
                            statusText.innerHTML = `<span class="text-success">Todo Correcto</span>`;
                        }
                    }
                });
            })
            .catch(error => console.error('Error actualizando el dashboard:', error));
    }

    // Ejecutar cada 5 segundos (5s)
    setInterval(refreshDashboard, 5000);
});
</script>


