<?php
    // ---------------------------------------------------------------------
    // Mapa centralizado de severidades de Proxmox VE
    // 'actionable' = true => requiere resolver antes de poder borrar
    // ---------------------------------------------------------------------
    $severityMap = [
        'info'     => ['class' => 'bg-info',    'label' => 'Info',    'actionable' => false],
        'notice'   => ['class' => 'bg-primary',  'label' => 'Aviso',   'actionable' => false],
        'warning'  => ['class' => 'bg-warning',  'label' => 'Alerta',  'actionable' => true],
        'error'    => ['class' => 'bg-danger',   'label' => 'Error',   'actionable' => true],
        'critical' => ['class' => 'bg-danger',   'label' => 'Crítico', 'actionable' => true],
        'unknown'  => ['class' => 'bg-dark',     'label' => 'Desc.',   'actionable' => true],
    ];
    $defaultSeverity = ['class' => 'bg-dark', 'label' => 'Desc.', 'actionable' => true];
    $canEdit = auth()->user()->can('empresas.edit');
?>
<div class="container-fluid">
    <div class="card shadow-none position-relative overflow-hidden mb-4">
        <div class="card-body px-4 py-3">
            <div class="row align-items-center">
                <div class="col-auto">
                    <img src="<?= $empresa->logo ? base_url('uploads/logos/' . $empresa->logo) : base_url('assets/images/logos/default-company.png') ?>" 
                         class="rounded shadow-sm company-logo-view" width="70" height="70">
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

    <!-- Monitoreo de Disponibilidad y Latencia (Ping Uptime) -->
    <?php if (!empty($empresa->proxmox_host)): ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php $lastLog = end($pingLogs); ?>
    


    <div class="card telemetry-card shadow-sm mb-4">
        <div class="card-body p-3">
            <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between mb-3 gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="p-1 bg-light-<?= ($lastLog && $lastLog->status === 'online') ? 'success' : (($lastLog) ? 'danger' : 'secondary') ?> rounded-circle d-flex align-items-center justify-content-center led-wrapper">
                        <span class="telemetry-led bg-<?= ($lastLog && $lastLog->status === 'online') ? 'success' : (($lastLog) ? 'danger' : 'secondary') ?> rounded-circle"></span>
                    </span>
                    <div>
                        <span class="text-muted text-uppercase fw-semibold d-block mb-0 telemetry-host-title">Monitoreo de Host</span>
                        <h6 class="fw-bold mb-0 font-monospace text-body telemetry-host-name"><?= esc($empresa->proxmox_host) ?></h6>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">

                    <!-- Pill Uptime -->
                    <div class="text-muted fw-semibold fs-2 d-flex align-items-center gap-1">
                        <i class="fa-solid fa-chart-line text-primary"></i>
                        <span>Uptime: <strong class="text-body fw-bold font-monospace"><?= ($uptimePercentage !== null) ? $uptimePercentage . '%' : 'Sin datos' ?></strong></span>
                    </div>
                    
                    <div class="vr opacity-25 d-none d-sm-block mx-1"></div>
                    
                    <!-- Pill Latency -->
                    <div class="text-muted fw-semibold fs-2 d-flex align-items-center gap-1">
                        <i class="fa-solid fa-gauge-high text-primary"></i>
                        <span>Latencia Avg: <strong class="text-body fw-bold font-monospace"><?= ($averageLatency !== null) ? $averageLatency . ' ms' : 'N/A' ?></strong></span>
                    </div>
                    
                    <div class="vr opacity-25 d-none d-sm-block mx-1"></div>
                    
                    <button class="btn btn-link p-0 text-muted fs-3 border-0 text-decoration-none telemetry-spin ms-1" onclick="window.location.reload();" title="Recargar">
                        <i class="fa-solid fa-rotate-right"></i>
                    </button>
                </div>
            </div>

            <div class="telemetry-chart-container">
                <canvas id="uptimeChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Script de configuración de Chart.js -->
    <?php if (!empty($pingLogs)): ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('uptimeChart');
        if (!ctx) return;

        const rawLogs = <?= json_encode($pingLogs) ?>;
        if (rawLogs.length === 0) return;

        // Mapear datos
        const labels = rawLogs.map(log => {
            const d = new Date(log.created_at);
            return d.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit' }) + ' ' + 
                   d.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
        });

        const latencyData = rawLogs.map(log => {
            return log.status === 'online' ? parseFloat(log.latency || 0) : null;
        });

        const downtimeData = rawLogs.map(log => {
            return log.status === 'offline' ? 1 : null;
        });

        const statusColors = rawLogs.map(log => {
            return log.status === 'online' ? '#13deb9' : '#ef4444'; // Rojo vibrante para caídas de servicio
        });

        const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
        let gridColor = isDark ? 'rgba(255, 255, 255, 0.06)' : 'rgba(0, 0, 0, 0.06)';
        let textColor = isDark ? '#adb0bb' : '#495057';
        let tooltipBg = isDark ? '#1e293b' : '#ffffff';
        let tooltipBorder = isDark ? '#334155' : '#e2e8f0';

        const chartCtx = ctx.getContext('2d');
        const gradient = chartCtx.createLinearGradient(0, 0, 0, 280);
        gradient.addColorStop(0, 'rgba(19, 222, 185, 0.25)');
        gradient.addColorStop(1, 'rgba(19, 222, 185, 0.01)');

        const config = {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        type: 'bar',
                        label: 'Caída de Servicio',
                        data: downtimeData,
                        backgroundColor: 'rgba(239, 68, 68, 0.16)', // Rojo suave translúcido para rellenar el hueco
                        borderColor: 'rgba(239, 68, 68, 0.25)',
                        borderWidth: { top: 1, right: 0, bottom: 0, left: 0 },
                        barPercentage: 1.0,
                        categoryPercentage: 1.0,
                        yAxisID: 'yDowntime',
                        order: 2
                    },
                    {
                        label: 'Latencia',
                        data: latencyData,
                        borderColor: '#13deb9',
                        borderWidth: 2.5,
                        pointBackgroundColor: statusColors,
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 0.75,
                        pointRadius: rawLogs.map(log => {
                            return log.status === 'online' ? (rawLogs.length > 100 ? 0 : 3) : 5;
                        }),
                        pointHoverRadius: rawLogs.map(log => {
                            return log.status === 'online' ? 5 : 7;
                        }),
                        backgroundColor: gradient,
                        fill: true,
                        tension: 0.35,
                        spanGaps: false,
                        yAxisID: 'y',
                        order: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: tooltipBg,
                        titleColor: isDark ? '#cbd5e1' : '#64748b',
                        bodyColor: isDark ? '#f8fafc' : '#0f172a',
                        borderColor: tooltipBorder,
                        borderWidth: 1,
                        padding: 10,
                        cornerRadius: 8,
                        displayColors: false,
                        filter: function(tooltipItem) {
                            return tooltipItem.dataset.label === 'Latencia';
                        },
                        callbacks: {
                            title: function(context) {
                                const index = context[0].dataIndex;
                                const log = rawLogs[index];
                                const d = new Date(log.created_at);
                                return d.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit' }) + ' ' + 
                                       d.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
                            },
                            label: function(context) {
                                const index = context.dataIndex;
                                const log = rawLogs[index];
                                if (log.status === 'online') {
                                    return `⚡ Latencia: ${parseFloat(log.latency || 0).toFixed(1)} ms`;
                                } else {
                                    return `❌ Caído (Sin respuesta)`;
                                }
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        max: rawLogs.length - 1,
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: textColor,
                            font: {
                                size: 8,
                                family: 'inherit'
                            },
                            maxRotation: 0,
                            autoSkip: true,
                            maxTicksLimit: 6
                        }
                    },
                    y: {
                        grid: {
                            color: gridColor
                        },
                        ticks: {
                            color: textColor,
                            font: {
                                size: 8,
                                family: 'inherit'
                            },
                            callback: function(value) {
                                return value + ' ms';
                            },
                            maxTicksLimit: 4
                        },
                        suggestedMin: 0
                    },
                    yDowntime: {
                        display: false,
                        min: 0,
                        max: 1,
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                }
            }
        };

        const chartInstance = new Chart(ctx, config);

        // MutationObserver para detectar cambio de tema claro/oscuro
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.attributeName === 'data-bs-theme') {
                    const isDarkTheme = document.documentElement.getAttribute('data-bs-theme') === 'dark';
                    
                    const newGridColor = isDarkTheme ? 'rgba(255, 255, 255, 0.06)' : 'rgba(0, 0, 0, 0.06)';
                    const newTextColor = isDarkTheme ? '#adb0bb' : '#495057';
                    const newTooltipBg = isDarkTheme ? '#1e293b' : '#ffffff';
                    const newTooltipBorder = isDarkTheme ? '#334155' : '#e2e8f0';

                    chartInstance.options.scales.x.ticks.color = newTextColor;
                    chartInstance.options.scales.y.ticks.color = newTextColor;
                    chartInstance.options.scales.y.grid.color = newGridColor;

                    chartInstance.options.plugins.tooltip.backgroundColor = newTooltipBg;
                    chartInstance.options.plugins.tooltip.borderColor = newTooltipBorder;
                    chartInstance.options.plugins.tooltip.titleColor = isDarkTheme ? '#cbd5e1' : '#64748b';
                    chartInstance.options.plugins.tooltip.bodyColor = isDarkTheme ? '#f8fafc' : '#0f172a';

                    chartInstance.update();
                }
            });
        });
        observer.observe(document.documentElement, { attributes: true });
    });
    </script>
    <?php endif; ?>
    <?php endif; ?>


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
                           class="btn <?= empty($current_severity) ? 'btn-primary' : 'btn-outline-primary' ?> btn-sm px-3"
                           title="Todos los eventos">
                            <i class="fa-solid fa-border-all"></i>
                            <span class="d-none d-sm-inline ms-1">Todos</span>
                        </a>
                        <a href="<?= base_url('companies/view/' . $empresa->id . '?severity=error') ?>" 
                           class="btn <?= $current_severity === 'error' ? 'btn-danger' : 'btn-outline-danger' ?> btn-sm px-3"
                           title="Eventos de Error">
                            <i class="fa-solid fa-circle-exclamation"></i>
                            <span class="d-none d-sm-inline ms-1">Error</span>
                        </a>
                        <a href="<?= base_url('companies/view/' . $empresa->id . '?severity=warning') ?>" 
                           class="btn <?= $current_severity === 'warning' ? 'btn-warning' : 'btn-outline-warning' ?> btn-sm px-3"
                           title="Avisos / Alertas">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            <span class="d-none d-sm-inline ms-1">Aviso</span>
                        </a>
                        <a href="<?= base_url('companies/view/' . $empresa->id . '?severity=info') ?>" 
                           class="btn <?= $current_severity === 'info' ? 'btn-info' : 'btn-outline-info' ?> btn-sm px-3"
                           title="Eventos de Información">
                            <i class="fa-solid fa-circle-info"></i>
                            <span class="d-none d-sm-inline ms-1">Info</span>
                        </a>
                        <a href="<?= base_url('companies/view/' . $empresa->id . '?severity=resolved') ?>" 
                           class="btn <?= $current_severity === 'resolved' ? 'btn-success' : 'btn-outline-success' ?> btn-sm px-3"
                           title="Eventos Resueltos">
                            <i class="fa-solid fa-circle-check"></i>
                            <span class="d-none d-sm-inline ms-1">Resueltos</span>
                        </a>
                    </div>

                    <form id="bulk-action-form" action="<?= base_url('alerts/bulk-action') ?>" method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" id="bulk-action-input" value="">
                        
                        <?php if ($canEdit): ?>
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
                                        <?php if ($canEdit): ?>
                                        <th scope="col" class="col-checkbox">
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
                                    <?php foreach ($alertas as $alerta): 
                                        $sev = strtolower(trim($alerta->severity));
                                        $severityUi = $severityMap[$sev] ?? $defaultSeverity;
                                        $isActionable = $severityUi['actionable'];
                                        $isResolved = ($alerta->status === 'resolved');
                                        $canDelete = ($isResolved || !$isActionable);
                                    ?>
                                        <tr class="transition-all">
                                            <?php if ($canEdit): ?>
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
                                                <span class="badge <?= $severityUi['class'] ?> fw-semibold fs-2 px-2 py-1 d-inline-block text-center severity-badge-ui">
                                                    <?= $severityUi['label'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <h6 class="fw-semibold mb-1 text-wrap alert-title-text"><?= esc($alerta->title) ?></h6>
                                            </td>
                                            <td class="text-center text-nowrap d-none d-md-table-cell">
                                                <span class="text-dark fs-3"><?= esc($alerta->hostname ?: 'N/A') ?></span>
                                            </td>

                                            <!-- Columna Estado -->
                                            <td class="text-center text-nowrap d-none d-sm-table-cell">
                                                <?php if (!$isActionable): ?>
                                                    <span class="badge bg-light-info text-info fw-semibold fs-2 px-2 py-1 d-inline-block text-center alert-status-badge">OK</span>
                                                <?php elseif ($isResolved): ?>
                                                    <span class="badge bg-light-success text-success fw-semibold fs-2 px-2 py-1 d-inline-block text-center alert-status-badge">Resuelta</span>
                                                <?php elseif ($canEdit): ?>
                                                    <span class="badge bg-light-danger text-danger fw-semibold fs-2 px-2 py-1 cursor-pointer resolve-alert-btn d-inline-block text-center alert-status-badge" 
                                                          data-url="<?= base_url('alerts/resolve/' . $alerta->id) ?>">
                                                        Pendiente
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-light-danger text-danger fw-semibold fs-2 px-2 py-1 d-inline-block text-center alert-status-badge">Pendiente</span>
                                                <?php endif; ?>
                                            </td>

                                            <!-- Menú de Acciones -->
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
                                                        <?php if (!$isResolved && $isActionable && $canEdit): ?>
                                                        <li>
                                                            <a class="dropdown-item d-flex align-items-center gap-2 text-dark resolve-alert-btn" href="javascript:void(0)" data-url="<?= base_url('alerts/resolve/' . $alerta->id) ?>">
                                                                <i class="ti ti-check fs-4"></i> Solucionar Alerta
                                                            </a>
                                                        </li>
                                                        <?php endif; ?>
                                                        <?php if ($canDelete && $canEdit): ?>
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
<?php foreach ($alertas as $alerta): 
    $sev = strtolower(trim($alerta->severity));
    $severityUi = $severityMap[$sev] ?? $defaultSeverity;
?>
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
                        <span class="badge <?= $severityUi['class'] ?> fw-semibold fs-2 px-2 py-1 d-inline-block text-center me-3 severity-badge-ui"><?= $severityUi['label'] ?></span>
                        <span class="text-muted fs-3"><i class="ti ti-calendar me-1"></i> <?= date('d/m/Y - H:i:s', strtotime($alerta->created_at)) ?></span>
                    </div>
                    
                    <?php if (!empty($alerta->ai_summary)): ?>
                        <h6 class="fw-semibold mb-2">
                            <i class="ti ti-sparkles text-primary me-1"></i> Análisis IA:
                        </h6>
                        <div class="p-3 rounded-3 text-dark mb-4 border ai-summary-container">
                            <?= esc($alerta->ai_summary) ?>
                        </div>
                    <?php endif; ?>
 
                    <h6 class="fw-semibold mb-2">Mensaje del Sistema:</h6>
                    <div class="p-3 rounded-3 text-dark font-monospace mb-4 border modal-pre-message"><?= trim(esc($alerta->message)) ?></div>
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
    // Botones de Eliminar
    document.querySelectorAll('.delete-alert-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            confirmDelete(this.getAttribute('data-url'));
        });
    });

    // Botones de Resolver Alerta
    document.querySelectorAll('.resolve-alert-btn').forEach(btn => {
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
    bulkActionsBar.classList.toggle('d-none', checkedCount === 0);
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
    const isDelete = (action === 'delete');

    Swal.fire({
        title: isDelete ? '¿Eliminar las alertas seleccionadas?' : '¿Marcar como solucionadas las alertas seleccionadas?',
        text: isDelete ? "Las alertas se eliminarán de forma definitiva." : "Esta acción se aplicará a todas las alertas marcadas.",
        icon: isDelete ? 'warning' : 'question',
        showCancelButton: true,
        confirmButtonColor: isDelete ? '#fa896b' : '#13deb9',
        cancelButtonColor: '#6c757d',
        confirmButtonText: isDelete ? 'Sí, eliminar' : 'Sí, solucionar',
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
