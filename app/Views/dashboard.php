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
                    <div class="card-body p-3 d-flex flex-column justify-content-between">
                        <div class="position-relative text-center mb-3 pt-1">
                            <!-- Logo Centrado -->
                            <?php $logoPath = $empresa->logo ? base_url('uploads/logos/' . $empresa->logo) : base_url('assets/images/logos/default-company.png'); ?>
                            <img src="<?= $logoPath ?>" alt="<?= esc($empresa->nombre) ?>" 
                                 class="rounded-circle shadow-sm mb-2 border border-2 border-white" 
                                 width="65" height="65" style="object-fit: cover;">
                            
                            <!-- Nombre Centrado -->
                            <h6 class="fw-bold text-dark mb-0 text-truncate px-2 fs-3"><?= esc($empresa->nombre) ?></h6>
                        </div>

                        <div class="d-flex align-items-center justify-content-center mt-auto pt-2 border-top">
                            <?php 
                                $pulseColor = $empresa->pulse_color;
                            ?>
                            <div id="pulse-bg-<?= $empresa->id ?>" class="d-flex align-items-center justify-content-center bg-light-<?= $pulseColor ?> rounded-circle me-2" style="width: 24px; height: 24px;">
                                <span id="pulse-blob-<?= $empresa->id ?>" class="blob bg-<?= $pulseColor ?> rounded-circle" style="width: 8px; height: 8px;"></span>
                            </div>
                            
                            <div id="status-text-<?= $empresa->id ?>" class="fw-semibold fs-2">
                                <?php if ($empresa->alert_count > 0): ?>
                                    <?php 
                                        $textWord = 'Aviso';
                                        if ($empresa->pulse_color === 'danger') $textWord = 'Fallo';
                                        if ($empresa->pulse_color === 'info') $textWord = 'Info';
                                    ?>
                                    <span class="text-<?= $empresa->pulse_color ?>">
                                        <strong class="me-1 text-<?= $empresa->pulse_color ?>"><?= $empresa->alert_count ?></strong>
                                        <?= $textWord ?><?= $empresa->alert_count > 1 ? 's' : '' ?>
                                    </span>
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
                        // Actualizar borde de la tarjeta
                        card.className = `card hover-img w-100 text-decoration-none border ${empresa.border_class} shadow-sm transition-all overflow-hidden h-100`;
                        
                        // Actualizar indicador LED
                        pulseBg.className = `d-flex align-items-center justify-content-center bg-light-${empresa.pulse_color} rounded-circle me-2`;
                        pulseBlob.className = `blob bg-${empresa.pulse_color} rounded-circle`;

                        // Actualizar texto de estado
                        let html = '';
                        if (empresa.alert_count > 0) {
                            const textColor = empresa.pulse_color;
                            let textWord = 'Aviso';
                            if (textColor === 'danger') textWord = 'Fallo';
                            if (textColor === 'info') textWord = 'Info';
                            
                            html = `<span class="text-${textColor}">
                                        <strong class="me-1 text-${textColor}">${empresa.alert_count}</strong>
                                        ${textWord}${empresa.alert_count > 1 ? 's' : ''}
                                    </span>`;
                        } else {
                            html = `<span class="text-success">Todo Correcto</span>`;
                        }
                        statusText.innerHTML = html;
                    }
                });
            })
            .catch(error => console.error('Error actualizando el dashboard:', error));
    }

    // Ejecutar cada 10 segundos
    setInterval(refreshDashboard, 10000);
});
</script>

<style>
.hover-img {
    transition: all 0.3s ease;
}
.hover-img:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.08) !important;
}
.hover-img:hover .group-hover-arrow i {
    transform: translateX(4px);
}
.transition-all {
    transition: all 0.3s ease;
}
/* Pulse Animations for Status LED */
.blob.bg-success { animation: pulse-success 2s infinite; }
.blob.bg-danger { animation: pulse-danger 2s infinite; }
.blob.bg-warning { animation: pulse-warning 2s infinite; }
.blob.bg-info { animation: pulse-info 2s infinite; }

@keyframes pulse-success {
    0% { box-shadow: 0 0 0 0 rgba(19, 222, 185, 0.7); }
    70% { box-shadow: 0 0 0 6px rgba(19, 222, 185, 0); }
    100% { box-shadow: 0 0 0 0 rgba(19, 222, 185, 0); }
}
@keyframes pulse-danger {
    0% { box-shadow: 0 0 0 0 rgba(249, 0, 69, 0.7); }
    70% { box-shadow: 0 0 0 6px rgba(249, 0, 69, 0); }
    100% { box-shadow: 0 0 0 0 rgba(249, 0, 69, 0); }
}
@keyframes pulse-warning {
    0% { box-shadow: 0 0 0 0 rgba(255, 174, 31, 0.7); }
    70% { box-shadow: 0 0 0 6px rgba(255, 174, 31, 0); }
    100% { box-shadow: 0 0 0 0 rgba(255, 174, 31, 0); }
}
@keyframes pulse-info {
    0% { box-shadow: 0 0 0 0 rgba(83, 155, 255, 0.7); }
    70% { box-shadow: 0 0 0 6px rgba(83, 155, 255, 0); }
    100% { box-shadow: 0 0 0 0 rgba(83, 155, 255, 0); }
}
</style>
