<?php
$writablePath = realpath(__DIR__ . '/../writable');
$dbPath       = $writablePath ? ($writablePath . '/database.db') : (dirname(__DIR__) . '/writable/database.db');
?>
<!DOCTYPE html>
<html lang="es" dir="ltr" data-bs-theme="dark" data-color-theme="Blue_Theme">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>Ruta de Base de Datos - Proxmox Alert</title>

    <!-- Core Css -->
    <link rel="stylesheet" href="/assets/css/styles.css" />
</head>

<body>
    <div id="main-wrapper">
        <div
            class="position-relative overflow-hidden auth-bg min-vh-100 w-100 d-flex align-items-center justify-content-center">
            <div class="d-flex align-items-center justify-content-center w-100">
                <div class="row justify-content-center w-100">
                    <div class="col-12 col-lg-11 col-xl-10 col-xxl-9 auth-card">

                        <div class="card mb-0 shadow-lg border-0 rounded-4">
                            <div class="card-body p-sm-5">
                                <div class="text-center mb-5">
                                    <div class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle mb-3"
                                        style="width: 80px; height: 80px;">
                                        <i class="fas fa-database fa-2x"></i>
                                    </div>
                                    <h3 class="fw-bold mb-2">Ruta SQLite</h3>
                                    <p class="text-muted fs-4">Ruta absoluta para tu archivo <span
                                            class="badge bg-primary-subtle text-primary fs-3 ms-1">.env</span></p>
                                </div>

                                <div class="mb-5">
                                    <div class="d-flex align-items-center bg-dark-subtle rounded-3 p-1 ps-3">
                                        <input type="text"
                                            class="form-control border-0 bg-transparent text-primary font-monospace shadow-none px-0"
                                            id="dbPathInput" value="<?= htmlspecialchars($dbPath) ?>" readonly>
                                        <button
                                            class="btn btn-link text-muted shadow-none text-decoration-none flex-shrink-0"
                                            type="button" onclick="copyText('dbPathInput')" id="copyBtn"
                                            title="Copiar ruta">
                                            <i class="far fa-copy fs-5"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="d-flex align-items-start gap-2 mt-2 text-muted">
                                    <p class="mb-0 fs-3 text-center">
                                        <strong class="text-danger"> Recuerda eliminar el archivo
                                            <strong>rutas.php</strong> una vez configurada la base de datos.
                                    </p>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Import Js Files -->
    <script src="/assets/js/vendor.min.js"></script>
    <script src="/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/libs/simplebar/dist/simplebar.min.js"></script>
    <script src="/assets/js/theme/app.dark.init.js"></script>
    <script src="/assets/js/theme/theme.js"></script>
    <script src="/assets/js/theme/app.min.js"></script>

    <script>
    function copyText(id) {
        const copyText = document.getElementById(id);
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(copyText.value);
    }
    </script>
</body>

</html>