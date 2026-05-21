<div class="container-fluid">
    <?php $cronPingToken = (string) env('cron.pingToken'); ?>
    <div class="card shadow-none position-relative overflow-hidden">
        <div class="card-body px-4 py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h4 class="fw-semibold mb-8">Gestión de Empresas</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a class="text-muted text-decoration-none" href="<?= base_url() ?>">Inicio</a></li>
                            <li class="breadcrumb-item" aria-current="page">Empresas</li>
                        </ol>
                    </nav>
                </div>
                <div class="col-auto">
                    <?php if (auth()->user()->can('empresas.create')): ?>
                    <a href="<?= base_url('companies/create') ?>" class="btn btn-primary">
                        <i class="ti ti-building me-1"></i> <span class="d-none d-sm-inline">Nueva Empresa</span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col" class="ps-0">Empresa</th>
                            <th scope="col" class="d-none d-lg-table-cell text-center">CIF</th>
                            <th scope="col" class="d-none d-md-table-cell text-center">Email</th>
                            <th scope="col" class="d-none d-lg-table-cell text-center">Teléfono</th>
                            <th scope="col" class="d-none d-sm-table-cell text-center">Estado</th>
                            <?php if (auth()->user()->can('empresas.edit') || auth()->user()->can('empresas.delete')): ?>
                            <th scope="col" class="text-end pe-4">Acciones</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody class="border-top">
                        <?php foreach ($empresas as $empresa): ?>
                            <tr>
                                <td class="ps-0">
                                    <div class="d-flex align-items-center">
                                        <?php 
                                            $logoPath = $empresa->logo ? base_url('uploads/logos/' . $empresa->logo) : base_url('assets/images/logos/default-company.png');
                                        ?>
                                        <img src="<?= $logoPath ?>" class="rounded shadow-sm d-none d-sm-block company-logo-thumbnail" width="40" height="40" alt="" />
                                        <div class="ms-0 ms-sm-3">
                                            <h6 class="fw-semibold mb-0"><?= esc($empresa->nombre) ?></h6>
                                            <?php if ($empresa->email): ?>
                                                <a href="mailto:<?= esc($empresa->email) ?>" class="fs-2 text-muted d-md-none"><?= esc($empresa->email) ?></a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="d-none d-lg-table-cell text-center">
                                    <p class="mb-0 fs-3"><?= esc($empresa->cif) ?: '---' ?></p>
                                </td>
                                <td class="d-none d-md-table-cell text-center">
                                    <?php if ($empresa->email): ?>
                                        <a href="mailto:<?= esc($empresa->email) ?>" class="fs-3 text-dark"><?= esc($empresa->email) ?></a>
                                    <?php else: ?>
                                        <span class="fs-3 text-muted">---</span>
                                    <?php endif; ?>
                                </td>
                                <td class="d-none d-lg-table-cell text-center">
                                    <?php if ($empresa->telefono): ?>
                                        <a href="tel:<?= esc($empresa->telefono) ?>" class="fs-3 text-dark"><?= esc($empresa->telefono) ?></a>
                                    <?php else: ?>
                                        <span class="fs-3 text-muted">---</span>
                                    <?php endif; ?>
                                </td>
                                    <td class="d-none d-sm-table-cell text-center">
                                        <?php if ($empresa->active): ?>
                                            <span class="badge bg-light-success text-success fw-semibold fs-2 d-inline-block text-center badge-width-fixed">Activa</span>
                                        <?php else: ?>
                                            <span class="badge bg-light-danger text-danger fw-semibold fs-2 d-inline-block text-center badge-width-fixed">Inactiva</span>
                                        <?php endif; ?>
                                    </td>
                                <?php if (auth()->user()->can('empresas.edit') || auth()->user()->can('empresas.delete')): ?>
                                <td class="text-end pe-4">
                                    <div class="dropdown">
                                        <a class="text-muted" href="javascript:void(0)" role="button" id="dropdownMenuLink-<?= $empresa->id ?>" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="ti ti-dots-vertical fs-6"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuLink-<?= $empresa->id ?>">
                                            <a class="dropdown-item d-flex align-items-center gap-3" href="<?= base_url('companies/view/' . $empresa->id) ?>">
                                                <i class="fs-4 ti ti-eye"></i>Ver Alertas
                                            </a>
                                            <hr class="dropdown-divider">
                                            
                                            <!-- Copiar Webhook (Elemento oculto para JS) -->
                                            <span id="webhook-<?= $empresa->id ?>" class="d-none"><?= base_url('webhook/proxmox/' . $empresa->webhook_token) ?></span>
                                            <a class="dropdown-item d-flex align-items-center gap-3" href="javascript:void(0)" onclick="copyToClipboard('webhook-<?= $empresa->id ?>')">
                                                <i class="fs-4 ti ti-copy"></i>Copiar Webhook
                                            </a>

                                            <?php if ($cronPingToken !== ''): ?>
                                            <span id="cron-link-<?= $empresa->id ?>" class="d-none"><?= base_url('monitoring/ping-check/' . $cronPingToken) ?></span>
                                            <a class="dropdown-item d-flex align-items-center gap-3" href="javascript:void(0)" onclick="copyToClipboard('cron-link-<?= $empresa->id ?>')">
                                                <i class="fs-4 ti ti-clock-play"></i>Copiar Link Cron Ping
                                            </a>
                                            <?php endif; ?>
                                            
                                            <a class="dropdown-item d-flex align-items-center gap-3" href="javascript:void(0)" onclick="copyScriptToClipboard(<?= $empresa->id ?>)">
                                                <i class="fs-4 ti ti-terminal-2"></i>Copiar Script Proxmox
                                            </a>
                                            
                                            <hr class="dropdown-divider">
                                            
                                            <?php if (auth()->user()->can('empresas.edit')): ?>
                                            <a class="dropdown-item d-flex align-items-center gap-3" href="<?= base_url('companies/edit/' . $empresa->id) ?>">
                                                <i class="fs-4 ti ti-edit"></i>Editar
                                            </a>
                                            <?php endif; ?>
                                            
                                            <?php if (auth()->user()->can('empresas.delete')): ?>
                                            <a class="dropdown-item d-flex align-items-center gap-3 text-danger" href="javascript:void(0)" onclick="confirmDelete('<?= base_url('companies/delete/' . $empresa->id) ?>')">
                                                <i class="fs-4 ti ti-trash"></i>Eliminar
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($empresas)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No hay empresas registradas</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(elementId) {
    var text = document.getElementById(elementId).innerText;
    navigator.clipboard.writeText(text).then(function() {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true
        });
        Toast.fire({
            icon: 'success',
            title: 'Enlace copiado al portapapeles'
        });
    }, function(err) {
        console.error('Error al copiar: ', err);
    });
}

function copyScriptToClipboard(id) {
    const url = '<?= base_url('companies/get-script') ?>/' + id;
    
    fetch(url)
        .then(response => response.text())
        .then(text => {
            navigator.clipboard.writeText(text).then(function() {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true
                });
                Toast.fire({
                    icon: 'success',
                    title: 'Script de Proxmox copiado'
                });
            });
        })
        .catch(err => {
            console.error('Error:', err);
            Swal.fire('Error', 'No se pudo obtener el script', 'error');
        });
}


</script>
