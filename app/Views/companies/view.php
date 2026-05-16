<div class="container-fluid">
    <div class="card shadow-none position-relative overflow-hidden mb-4">
        <div class="card-body px-4 py-3">
            <div class="row align-items-center">
                <div class="col-auto">
                    <img src="<?= $empresa->logo ? base_url('uploads/logos/' . $empresa->logo) : base_url('assets/images/logos/default-company.png') ?>" 
                         class="rounded shadow-sm" width="70" height="70" style="object-fit: cover;">
                </div>
                <div class="col">
                    <h4 class="fw-semibold mb-0"><?= esc($empresa->nombre) ?></h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a class="text-muted text-decoration-none" href="<?= base_url() ?>">Escritorio</a></li>
                            <li class="breadcrumb-item text-primary fw-bold" aria-current="page">Alertas de Proxmox</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
                        <div>
                            <h5 class="card-title fw-semibold mb-1">Historial de Notificaciones</h5>
                            <p class="card-subtitle mb-0 d-none d-sm-block">Gestión de eventos detectados por Proxmox</p>
                        </div>
                        <div class="badge bg-white text-dark border fw-semibold fs-2 p-2 px-3 h-100">
                            Total: <?= $pager->getTotal() ?> eventos
                        </div>
                    </div>

                    <!-- Filtros de Severidad -->
                    <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-start gap-2 mb-4">
                        <a href="<?= base_url('companies/view/' . $empresa->id) ?>" 
                           class="btn <?= empty($current_severity) ? 'btn-primary' : 'btn-outline-primary' ?> btn-sm px-3">
                            Todos
                        </a>
                        <a href="<?= base_url('companies/view/' . $empresa->id . '?severity=error') ?>" 
                           class="btn <?= $current_severity === 'error' ? 'btn-danger' : 'btn-outline-danger' ?> btn-sm px-3">
                            Error
                        </a>
                        <a href="<?= base_url('companies/view/' . $empresa->id . '?severity=warning') ?>" 
                           class="btn <?= $current_severity === 'warning' ? 'btn-warning' : 'btn-outline-warning' ?> btn-sm px-3">
                            Aviso
                        </a>
                        <a href="<?= base_url('companies/view/' . $empresa->id . '?severity=info') ?>" 
                           class="btn <?= $current_severity === 'info' ? 'btn-info' : 'btn-outline-info' ?> btn-sm px-3">
                            Info
                        </a>
                        <a href="<?= base_url('companies/view/' . $empresa->id . '?severity=resolved') ?>" 
                           class="btn <?= $current_severity === 'resolved' ? 'btn-success' : 'btn-outline-success' ?> btn-sm px-3">
                            Resueltos
                        </a>
                    </div>

                    <form id="bulk-action-form" action="<?= base_url('alerts/bulk-action') ?>" method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" id="bulk-action-input" value="">
                        
                        <?php if (auth()->user()->can('empresas.edit')): ?>
                        <!-- Barra de Acciones Masivas (Oculta por defecto) -->
                        <div id="bulk-actions-bar" class="bg-light-primary p-3 rounded-3 mb-4 d-none animate__animated animate__fadeIn">
                            <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2">
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <span class="fw-semibold text-primary" id="selected-count">0 alertas seleccionadas</span>
                                    <div class="vr d-none d-sm-block"></div>
                                    <button type="button" class="btn btn-outline-danger btn-sm px-3" onclick="submitBulkAction('delete')">
                                        <i class="ti ti-trash me-1"></i> Borrar
                                    </button>
                                    <button type="button" class="btn btn-outline-success btn-sm px-3" onclick="submitBulkAction('resolve')">
                                        <i class="ti ti-check me-1"></i> Solucionar
                                    </button>
                                </div>
                                <button type="button" class="btn-close" onclick="deselectAll()"></button>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="alertas-table">
                                <thead>
                                    <tr class="text-muted fw-semibold">
                                        <?php if (auth()->user()->can('empresas.edit')): ?>
                                        <th scope="col" style="width: 40px;">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="select-all">
                                            </div>
                                        </th>
                                        <?php endif; ?>
                                        <th scope="col" class="text-nowrap">Fecha</th>
                                        <th scope="col" class="text-nowrap">Severidad</th>
                                        <th scope="col">Título</th>
                                        <th scope="col" class="text-center text-nowrap d-none d-md-table-cell">Hostname</th>
                                        <th scope="col" class="text-center text-nowrap d-none d-sm-table-cell">Estado</th>
                                        <th scope="col" class="text-center text-nowrap"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($alertas as $alerta): ?>
                                        <tr class="transition-all">
                                            <?php if (auth()->user()->can('empresas.edit')): ?>
                                            <td>
                                                <div class="form-check">
                                                    <input class="form-check-input alert-checkbox" type="checkbox" name="ids[]" value="<?= $alerta->id ?>">
                                                </div>
                                            </td>
                                            <?php endif; ?>
                                            <td class="text-nowrap">
                                                <p class="mb-0 fs-3 fw-semibold"><?= date('d/m/Y', strtotime($alerta->created_at)) ?></p>
                                                <p class="mb-0 fs-2 text-muted"><?= date('H:i:s', strtotime($alerta->created_at)) ?></p>
                                            </td>
                                            <td class="text-nowrap">
                                                <?php 
                                                    $severityClass = 'bg-info';
                                                    if (stripos($alerta->severity, 'error') !== false || stripos($alerta->severity, 'crit') !== false) {
                                                        $severityClass = 'bg-danger';
                                                        $severityLabel = 'Error';
                                                    } elseif (stripos($alerta->severity, 'warn') !== false) {
                                                        $severityClass = 'bg-warning';
                                                        $severityLabel = 'Aviso';
                                                    } elseif (stripos($alerta->severity, 'info') !== false) {
                                                        $severityLabel = 'Info';
                                                    }
                                                ?>
                                                <span class="badge <?= $severityClass ?> fw-semibold fs-2 px-2 py-1 d-inline-block text-center" style="width: 80px;">
                                                    <?= $severityLabel ?>
                                                </span>
                                            </td>
                                            <td>
                                                <h6 class="fw-semibold mb-1 text-wrap" style="max-width: 250px;"><?= esc($alerta->title) ?></h6>
                                                <?php if (!empty($alerta->ai_summary)): ?>
                                                    <p class="fs-2 text-muted mb-0 text-wrap" style="max-width: 250px;">
                                                        <i class="ti ti-sparkles text-primary me-1"></i> <?= esc($alerta->ai_summary) ?>
                                                    </p>
                                                <?php endif; ?>

                                            </td>
                                            <td class="text-center text-nowrap d-none d-md-table-cell">
                                                <span class="text-dark fs-3"><?= esc($alerta->hostname ?: 'N/A') ?></span>
                                            </td>
                                            <td class="text-center text-nowrap d-none d-sm-table-cell">
                                                <?php 
                                                    $isActionable = (stripos($alerta->severity, 'error') !== false || stripos($alerta->severity, 'crit') !== false || stripos($alerta->severity, 'warn') !== false);
                                                ?>
                                                
                                                <?php if ($isActionable): ?>
                                                    <?php if ($alerta->status === 'resolved'): ?>
                                                        <span class="badge bg-light-success text-success fw-semibold fs-2 px-2 py-1 d-inline-block text-center" style="width: 80px;">Resuelta</span>
                                                    <?php else: ?>
                                                        <?php if (auth()->user()->can('empresas.edit')): ?>
                                                            <span class="badge bg-light-danger text-danger fw-semibold fs-2 px-2 py-1 cursor-pointer resolve-alert-btn d-inline-block text-center" 
                                                                  style="width: 80px;"
                                                                  data-url="<?= base_url('alerts/resolve/' . $alerta->id) ?>">
                                                                Pendiente
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="badge bg-light-danger text-danger fw-semibold fs-2 px-2 py-1 d-inline-block text-center" style="width: 80px;">Pendiente</span>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="badge bg-light-info text-info fw-semibold fs-2 px-2 py-1 d-inline-block text-center" style="width: 80px;">OK</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center text-nowrap">
                                                <div class="dropdown">
                                                    <a href="javascript:void(0)" class="text-muted" data-bs-toggle="dropdown" aria-expanded="false" title="Opciones">
                                                        <i class="ti ti-dots-vertical fs-5"></i>
                                                    </a>
                                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2">
                                                        <li>
                                                            <a class="dropdown-item d-flex align-items-center gap-2 text-dark" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#alertaModal<?= $alerta->id ?>">
                                                                <i class="ti ti-eye fs-4"></i> Ver Detalles
                                                            </a>
                                                        </li>
                                                        <?php if ($alerta->status !== 'resolved' && (stripos($alerta->severity, 'error') !== false || stripos($alerta->severity, 'crit') !== false || stripos($alerta->severity, 'warn') !== false) && auth()->user()->can('empresas.edit')): ?>
                                                        <li>
                                                            <a class="dropdown-item d-flex align-items-center gap-2 text-dark resolve-alert-btn" href="javascript:void(0)" data-url="<?= base_url('alerts/resolve/' . $alerta->id) ?>">
                                                                <i class="ti ti-check fs-4"></i> Solucionar Alerta
                                                            </a>
                                                        </li>
                                                        <?php endif; ?>
                                                        <?php 
                                                            $canDelete = ($alerta->status === 'resolved' || in_array($alerta->severity, ['info', 'notice', 'debug']));
                                                        ?>
                                                        <?php if ($canDelete && auth()->user()->can('empresas.edit')): ?>
                                                        <li>
                                                            <a class="dropdown-item d-flex align-items-center gap-2 text-dark delete-alert-btn" href="javascript:void(0)" data-url="<?= base_url('alerts/delete/' . $alerta->id) ?>">
                                                                <i class="ti ti-trash fs-4"></i> Eliminar
                                                            </a>
                                                        </li>
                                                        <?php endif; ?>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>

                                    <?php if (empty($alertas)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-5">
                                                <div class="mb-3">
                                                    <i class="ti ti-bell-off fs-10 text-muted"></i>
                                                </div>
                                                <h5 class="fw-semibold">No hay alertas registradas</h5>
                                                <p class="text-muted">Aún no se ha recibido ningún disparo desde Proxmox para esta empresa.</p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </form>

                    <?php if ($pager): ?>
                        <div class="mt-4 d-flex justify-content-center">
                            <?= $pager->links('default', 'pagination') ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modales de Detalles (Fuera de la tabla para evitar problemas en móvil) -->
<?php foreach ($alertas as $alerta): ?>
    <div class="modal fade text-start" id="alertaModal<?= $alerta->id ?>" tabindex="-1" aria-labelledby="alertaModalLabel<?= $alerta->id ?>" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-semibold" id="alertaModalLabel<?= $alerta->id ?>">
                        <i class="ti ti-file-description me-1"></i> Detalles del Evento
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="d-flex flex-wrap align-items-center mb-4 gap-2">
                        <?php 
                            $severityClass = 'bg-info';
                            $severityLabel = esc($alerta->severity);
                            if (stripos($alerta->severity, 'error') !== false || stripos($alerta->severity, 'crit') !== false) {
                                $severityClass = 'bg-danger';
                                $severityLabel = 'Error';
                            } elseif (stripos($alerta->severity, 'warn') !== false) {
                                $severityClass = 'bg-warning';
                                $severityLabel = 'Aviso';
                            } elseif (stripos($alerta->severity, 'info') !== false) {
                                $severityLabel = 'Info';
                            }
                        ?>
                        <span class="badge <?= $severityClass ?> fw-semibold fs-2 px-2 py-1 d-inline-block text-center me-3" style="width: 80px;"><?= $severityLabel ?></span>
                        <span class="text-muted fs-3"><i class="ti ti-calendar me-1"></i> <?= date('d/m/Y - H:i:s', strtotime($alerta->created_at)) ?></span>
                    </div>
                    
                    <h6 class="fw-semibold mb-2">Mensaje:</h6>
                    <div class="p-3 rounded-3 text-dark font-monospace mb-4" style="white-space: pre-wrap; font-size: 0.85rem; border: 1px solid #e5eaef; overflow-x: auto;"><?= trim(esc($alerta->message)) ?></div>

                    <?php if (!empty($alerta->ai_summary)): ?>
                    <h6 class="fw-semibold mb-2 d-flex align-items-center">
                        <i class="ti ti-sparkles text-primary me-2"></i> Resumen IA:
                    </h6>
                    <div class="p-3 rounded-3 bg-light-primary text-dark mb-4 border border-primary-subtle" style="font-size: 0.9rem; line-height: 1.5;">
                        <?= esc($alerta->ai_summary) ?>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cerrar Ventana</button>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<!-- Script para SweetAlert en el Borrado -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const deleteBtns = document.querySelectorAll('.delete-alert-btn');
    deleteBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            confirmDelete(this.getAttribute('data-url'));
        });
    });

    // Botones de Resolver Alerta
    const resolveBtns = document.querySelectorAll('.resolve-alert-btn');
    resolveBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            confirmAction(
                this.getAttribute('data-url'), 
                '¿Marcar como solucionada?', 
                'La alerta pasará al estado OK y se considerará resuelta.', 
                'question', 
                '<i class="ti ti-check me-1"></i> Sí, solucionar', 
                '#13deb9'
            );
        });
    });
});

// Lógica para Selección Masiva
const selectAll = document.getElementById('select-all');
const alertCheckboxes = document.querySelectorAll('.alert-checkbox');
const bulkActionsBar = document.getElementById('bulk-actions-bar');
const selectedCountText = document.getElementById('selected-count');
const bulkActionInput = document.getElementById('bulk-action-input');
const bulkForm = document.getElementById('bulk-action-form');

function updateBulkBar() {
    if (!bulkActionsBar || !selectedCountText) return;
    
    const checkedCount = document.querySelectorAll('.alert-checkbox:checked').length;
    selectedCountText.innerText = `${checkedCount} alertas seleccionadas`;
    
    if (checkedCount > 0) {
        bulkActionsBar.classList.remove('d-none');
    } else {
        bulkActionsBar.classList.add('d-none');
    }
}

if (selectAll) {
    selectAll.addEventListener('change', function() {
        alertCheckboxes.forEach(cb => cb.checked = this.checked);
        updateBulkBar();
    });
}

alertCheckboxes.forEach(cb => {
    cb.addEventListener('change', updateBulkBar);
});

function deselectAll() {
    selectAll.checked = false;
    alertCheckboxes.forEach(cb => cb.checked = false);
    updateBulkBar();
}

function submitBulkAction(action) {
    const actionText = action === 'delete' ? '¿Eliminar las alertas seleccionadas?' : '¿Marcar como solucionadas las alertas seleccionadas?';
    const confirmBtnText = action === 'delete' ? 'Sí, eliminar' : 'Sí, solucionar';
    const confirmBtnColor = action === 'delete' ? '#fa896b' : '#13deb9';

    Swal.fire({
        title: actionText,
        text: action === 'delete' ? "Las alertas se eliminarán de forma definitiva." : "Esta acción se aplicará a todas las alertas marcadas.",
        icon: action === 'delete' ? 'warning' : 'question',
        showCancelButton: true,
        confirmButtonColor: confirmBtnColor,
        cancelButtonColor: '#6c757d',
        confirmButtonText: confirmBtnText,
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            bulkActionInput.value = action;
            bulkForm.submit();
        }
    });
}
</script>
