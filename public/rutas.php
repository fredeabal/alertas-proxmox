<?php
$writablePath = realpath(__DIR__ . '/../writable');
$dbPath       = $writablePath ? ($writablePath . '/database.db') : (dirname(__DIR__) . '/writable/database.db');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ruta de Base de Datos - Proxmox Alert</title>
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
        .text-neon {
            color: #13deb9;
            text-shadow: 0 0 10px rgba(19, 222, 185, 0.3);
        }
        .alert-security {
            background-color: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
        }
    </style>
</head>
<body>

<div class="diag-card p-4 p-md-5 m-3">
    <div class="text-center mb-4">
        <i class="fa-solid fa-database fa-3x text-neon mb-3"></i>
        <h3 class="fw-bold">Ruta SQLite</h3>
        <p class="text-muted">Ruta absoluta para tu archivo <code>.env</code></p>
    </div>

    <!-- Sección de Rutas -->
    <div class="mb-4">
        <div class="mb-3">
            <label class="form-label text-muted fs-7 fw-semibold text-uppercase">1. Ruta de la Base de Datos (database.default.database)</label>
            <div class="path-block text-neon">
                <span id="dbPath"><?= htmlspecialchars($dbPath) ?></span>
                <button class="copy-btn" onclick="copyText('dbPath')"><i class="fa-regular fa-copy"></i> Copiar</button>
            </div>
        </div>
    </div>

    <div class="alert alert-security d-flex align-items-start gap-3 mb-0" role="alert">
        <i class="fa-solid fa-triangle-exclamation fa-lg mt-1"></i>
        <div>
            <h6 class="alert-heading fw-bold mb-1">¡Advertencia de Seguridad!</h6>
            <p class="mb-0 fs-7">Por motivos de seguridad, recuerda eliminar este archivo (<code>rutas.php</code>) una vez hayas configurado la base de datos.</p>
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
