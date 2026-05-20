<?php
// ---------------------------------------------------------------------
// Herramienta de Diagnóstico de Rutas y Permisos para SQLite
// ---------------------------------------------------------------------

$publicPath   = __DIR__;
$writablePath = realpath(__DIR__ . '/../writable');
$dbPath       = $writablePath ? ($writablePath . '/database.db') : (dirname(__DIR__) . '/writable/database.db');
$logsPath     = $writablePath ? ($writablePath . '/logs') : (dirname(__DIR__) . '/writable/logs');
$sessionPath  = $writablePath ? ($writablePath . '/session') : (dirname(__DIR__) . '/writable/session');

// Verificar permisos
$writableOk = is_writable($writablePath);
$dbExists   = file_exists($dbPath);
$dbOk       = $dbExists ? is_writable($dbPath) : is_writable(dirname($dbPath));
$logsOk     = is_writable($logsPath);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico de Servidor - Proxmox Alert</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #0f172a;
            color: #e2e8f0;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 0;
        }
        .diag-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            max-width: 700px;
            width: 100%;
        }
        .path-block {
            background-color: #020617;
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
            padding: 12px 16px;
            position: relative;
            word-break: break-all;
        }
        .copy-btn {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            color: #94a3b8;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        .copy-btn:hover {
            background: #13deb9;
            color: #0f172a;
            border-color: #13deb9;
        }
        .alert-security {
            background-color: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
        }
        .text-neon {
            color: #13deb9;
            text-shadow: 0 0 10px rgba(19, 222, 185, 0.3);
        }
        .badge-status {
            font-size: 0.8rem;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
        }
        .badge-success-neon {
            background-color: rgba(19, 222, 185, 0.15);
            color: #13deb9;
            border: 1px solid rgba(19, 222, 185, 0.3);
        }
        .badge-danger-neon {
            background-color: rgba(239, 68, 68, 0.15);
            color: #fca5a5;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
    </style>
</head>
<body>

<div class="diag-card p-4 p-md-5 m-3">
    <div class="text-center mb-4">
        <i class="fa-solid fa-server fa-3x text-neon mb-3"></i>
        <h3 class="fw-bold">Panel de Diagnóstico</h3>
        <p class="text-muted">Utiliza esta herramienta para comprobar rutas absolutas y permisos de escritura en tu servidor.</p>
    </div>

    <!-- Sección de Permisos -->
    <div class="mb-5">
        <h5 class="fw-bold mb-3 text-white"><i class="fa-solid fa-shield-halved text-neon me-2"></i> Permisos de Escritura (Evita Errores 500)</h5>
        <div class="table-responsive">
            <table class="table table-dark table-borderless align-middle mb-0" style="background: transparent;">
                <thead>
                    <tr class="text-muted border-bottom border-secondary">
                        <th>Directorio / Archivo</th>
                        <th>Estado</th>
                        <th>Acción Recomendada</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="fw-bold">Carpeta <code class="text-white">writable/</code></div>
                            <div class="text-muted fs-7">Ubicación de logs y caché</div>
                        </td>
                        <td>
                            <?php if ($writableOk): ?>
                                <span class="badge-status badge-success-neon"><i class="fa-solid fa-check me-1"></i> Escribible</span>
                            <?php else: ?>
                                <span class="badge-status badge-danger-neon"><i class="fa-solid fa-xmark me-1"></i> Sin Permiso</span>
                            <?php endif; ?>
                        </td>
                        <td class="fs-7 text-muted">
                            <?= !$writableOk ? 'Ejecuta: <code class="text-neon">chmod -R 775 writable</code> y <code class="text-neon">chown -R www-data:www-data writable</code>' : 'Correcto' ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="fw-bold">Base de Datos <code class="text-white">database.db</code></div>
                            <div class="text-muted fs-7"><?= $dbExists ? 'Archivo SQLite detectado' : 'Pendiente de migración' ?></div>
                        </td>
                        <td>
                            <?php if ($dbOk): ?>
                                <span class="badge-status badge-success-neon"><i class="fa-solid fa-check me-1"></i> Escribible</span>
                            <?php else: ?>
                                <span class="badge-status badge-danger-neon"><i class="fa-solid fa-xmark me-1"></i> Sin Permiso</span>
                            <?php endif; ?>
                        </td>
                        <td class="fs-7 text-muted">
                            <?= !$dbOk ? 'Ejecuta: <code class="text-neon">chmod 664 writable/database.db</code> y asigna propietario <code class="text-neon">www-data</code>' : 'Correcto' ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="fw-bold">Carpeta <code class="text-white">writable/logs/</code></div>
                            <div class="text-muted fs-7">Registro de errores PHP</div>
                        </td>
                        <td>
                            <?php if ($logsOk): ?>
                                <span class="badge-status badge-success-neon"><i class="fa-solid fa-check me-1"></i> Escribible</span>
                            <?php else: ?>
                                <span class="badge-status badge-danger-neon"><i class="fa-solid fa-xmark me-1"></i> Sin Permiso</span>
                            <?php endif; ?>
                        </td>
                        <td class="fs-7 text-muted">
                            <?= !$logsOk ? 'Ejecuta: <code class="text-neon">chmod -R 775 writable/logs</code>' : 'Correcto' ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Sección de Rutas -->
    <div class="mb-4">
        <h5 class="fw-bold mb-3 text-white"><i class="fa-solid fa-folder-tree text-neon me-2"></i> Rutas Absolutas (Para tu archivo .env)</h5>
        
        <div class="mb-3">
            <label class="form-label text-muted fs-7 fw-semibold text-uppercase">1. Ruta de la Base de Datos (database.default.database)</label>
            <div class="path-block text-neon">
                <span id="dbPath"><?= htmlspecialchars($dbPath) ?></span>
                <button class="copy-btn" onclick="copyText('dbPath')"><i class="fa-regular fa-copy"></i> Copiar</button>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label text-muted fs-7 fw-semibold text-uppercase">2. Ruta de la Carpeta Writable</label>
            <div class="path-block text-white">
                <span id="writePath"><?= htmlspecialchars($writablePath ?: 'No detectada automáticamente') ?></span>
                <button class="copy-btn" onclick="copyText('writePath')"><i class="fa-regular fa-copy"></i> Copiar</button>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label text-muted fs-7 fw-semibold text-uppercase">3. Directorio Público (public/)</label>
            <div class="path-block text-muted">
                <span id="pubPath"><?= htmlspecialchars($publicPath) ?></span>
                <button class="copy-btn" onclick="copyText('pubPath')"><i class="fa-regular fa-copy"></i> Copiar</button>
            </div>
        </div>
    </div>

    <div class="alert alert-security d-flex align-items-start gap-3 mb-0" role="alert">
        <i class="fa-solid fa-triangle-exclamation fa-lg mt-1"></i>
        <div>
            <h6 class="alert-heading fw-bold mb-1">¡Advertencia de Seguridad!</h6>
            <p class="mb-0 fs-7">Por motivos de seguridad, **debes eliminar físicamente este archivo (`rutas.php`)** de la carpeta pública de tu hosting tan pronto como hayas resuelto los permisos y configurado la ruta de la base de datos.</p>
        </div>
    </div>
</div>

<script>
function copyText(id) {
    const text = document.getElementById(id).innerText;
    navigator.clipboard.writeText(text).then(() => {
        const btn = document.querySelector(`#${id} + .copy-btn`);
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-check"></i> ¡Copiado!';
        btn.style.background = '#13deb9';
        btn.style.color = '#0f172a';
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.style.background = '';
            btn.style.color = '';
        }, 1500);
    });
}
</script>
</body>
</html>
