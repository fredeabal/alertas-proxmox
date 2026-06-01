<!DOCTYPE html>
<html lang="es" dir="ltr" data-bs-theme="dark" data-color-theme="Blue_Theme">

<head>
  <!-- Required meta tags -->
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <!-- Favicon icon-->
  <link rel="shortcut icon" type="image/png" href="<?= base_url('assets/images/logos/favicon.png') ?>" />

  <!-- Core Css -->
  <link rel="stylesheet" href="<?= base_url('assets/css/styles.css') ?>" />

  <!-- Tabler Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css" />

  <title><?= $title ?? 'Instalador Proxmox Alert' ?></title>

  <style>
    body {
      background: radial-gradient(circle at 50% 50%, rgba(20, 26, 45, 1) 0%, rgba(10, 12, 22, 1) 100%) !important;
    }
    .auth-bg {
      background: transparent !important;
    }
    .step-circle {
      width: 38px;
      height: 38px;
      border-radius: 50%;
      background-color: rgba(255, 255, 255, 0.05);
      border: 2px solid var(--bs-border-color);
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      font-size: 1rem;
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .step-item {
      opacity: 0.5;
      transition: all 0.4s ease;
    }
    .step-item.active {
      opacity: 1;
    }
    .step-item.active .step-circle {
      background-color: var(--bs-primary);
      border-color: var(--bs-primary);
      color: #fff;
      box-shadow: 0 0 15px rgba(93, 135, 255, 0.4);
      transform: scale(1.1);
    }
    .step-item.completed {
      opacity: 1;
    }
    .step-item.completed .step-circle {
      background-color: var(--bs-success);
      border-color: var(--bs-success);
      color: #fff;
      box-shadow: 0 0 15px rgba(19, 222, 185, 0.4);
    }
    .step-line {
      height: 2px;
      background-color: rgba(255, 255, 255, 0.08);
      flex-grow: 1;
      margin: 0 15px;
      transition: all 0.4s ease;
    }
    .step-line.active {
      background-color: var(--bs-success);
      box-shadow: 0 0 5px rgba(19, 222, 185, 0.3);
    }
    .wizard-step {
      animation: fadeIn 0.5s ease;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .list-group-item {
      background-color: rgba(255, 255, 255, 0.02) !important;
      border-color: rgba(255, 255, 255, 0.05) !important;
      transition: background-color 0.2s ease;
    }
    .list-group-item:hover {
      background-color: rgba(255, 255, 255, 0.04) !important;
    }
  </style>
</head>

<body>
  <div id="main-wrapper">
    <div class="position-relative overflow-hidden auth-bg min-vh-100 w-100 d-flex align-items-center justify-content-center py-5">
      <div class="d-flex align-items-center justify-content-center w-100">
        <div class="row justify-content-center w-100 px-3">
          <div class="col-md-10 col-lg-8 col-xxl-6">
            <div class="card shadow-lg border border-opacity-10 mb-0">
              <div class="card-body p-4 p-md-5">
                <!-- Logo -->
                <div class="text-center mb-5 w-100">
                  <img src="<?= base_url('assets/images/logos/logo.png') ?>" width="250" alt="Logo" />
                  <h5 class="fw-semibold text-muted mt-3 fs-3">Asistente de Configuración y Onboarding</h5>
                </div>

                <!-- Wizard Header (Pasos) -->
                <div class="d-flex align-items-center justify-content-between mb-5 px-3">
                  <div class="d-flex align-items-center step-item active" id="step-header-1">
                    <div class="step-circle">1</div>
                    <span class="d-none d-sm-inline ms-2 fw-semibold fs-2">Requisitos</span>
                  </div>
                  <div class="step-line" id="step-line-1"></div>
                  <div class="d-flex align-items-center step-item" id="step-header-2">
                    <div class="step-circle">2</div>
                    <span class="d-none d-sm-inline ms-2 fw-semibold fs-2">Configuración</span>
                  </div>
                  <div class="step-line" id="step-line-2"></div>
                  <div class="d-flex align-items-center step-item" id="step-header-3">
                    <div class="step-circle">3</div>
                    <span class="d-none d-sm-inline ms-2 fw-semibold fs-2">Administrador</span>
                  </div>
                </div>

                <form action="<?= base_url('install/submit') ?>" method="post" id="install-form">
                  <?= csrf_field() ?>

                  <!-- PASO 1: REQUISITOS DEL SISTEMA -->
                  <div class="wizard-step" id="step-content-1">
                    <h5 class="fw-bold mb-3"><i class="ti ti-device-laptop me-2 text-primary"></i>1. Comprobando Requisitos del Sistema</h5>
                    <p class="text-muted fs-3 mb-4">Verificamos que tu servidor sea completamente apto para ejecutar Proxmox Alert sin fallos.</p>
                    
                    <div class="list-group mb-4">
                      <?php foreach ($requirements as $key => $req): ?>
                        <div class="list-group-item d-flex align-items-center justify-content-between py-3">
                          <div>
                            <h6 class="fw-semibold mb-1"><?= $req['name'] ?></h6>
                            <small class="text-muted d-block"><?= $req['help'] ?></small>
                          </div>
                          <div class="text-end d-flex align-items-center gap-2">
                            <span class="fs-2 text-muted"><?= $req['current'] ?></span>
                            <?php if ($req['status']): ?>
                              <span class="badge bg-light-success text-success rounded-pill px-2 py-1"><i class="ti ti-circle-check fs-4"></i></span>
                            <?php else: ?>
                              <span class="badge bg-light-danger text-danger rounded-pill px-2 py-1"><i class="ti ti-circle-x fs-4"></i></span>
                            <?php endif; ?>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>

                    <?php if ($systemApt): ?>
                      <div class="alert bg-light-success text-success border border-success border-opacity-30 d-flex align-items-center" role="alert">
                        <i class="ti ti-circle-check fs-6 me-2"></i>
                        <div>¡Tu sistema cumple con todos los requisitos necesarios para continuar!</div>
                      </div>
                    <?php else: ?>
                      <div class="alert bg-light-danger text-danger border border-danger border-opacity-30 d-flex align-items-center" role="alert">
                        <i class="ti ti-alert-triangle fs-6 me-2"></i>
                        <div>El servidor no cumple con los requisitos mínimos. Por favor, corrige los elementos marcados en rojo antes de continuar.</div>
                      </div>
                    <?php endif; ?>

                    <div class="text-end mt-4 pt-3 border-top border-opacity-10">
                      <button type="button" class="btn btn-primary px-4 py-2 font-medium" onclick="nextStep(2)" <?= !$systemApt ? 'disabled' : '' ?>>
                        Comenzar Asistente <i class="ti ti-arrow-right ms-1"></i>
                      </button>
                    </div>
                  </div>

                  <!-- PASO 2: CONFIGURACIÓN DE LA APP -->
                  <div class="wizard-step d-none" id="step-content-2">
                    <h5 class="fw-bold mb-3"><i class="ti ti-settings-automation me-2 text-primary"></i>2. Configuración de la Aplicación</h5>
                    <p class="text-muted fs-3 mb-4">Ingresa la dirección web desde la cual accederás a tu panel de Proxmox Alert.</p>
                    
                    <div class="mb-4">
                      <label for="app_url" class="form-label fw-bold">URL Base de la Aplicación (baseURL)</label>
                      <input type="url" class="form-control form-control-lg" id="app_url" name="app_url" value="<?= old('app_url', $suggestedUrl) ?>" required>
                      <div class="form-text text-muted mt-2">
                        <i class="ti ti-info-circle me-1"></i> Debe comenzar con <code>http://</code> o <code>https://</code> y terminar con barra inclinada (ej: <code>https://tudominio.com/</code>).
                      </div>
                    </div>

                    <div class="alert bg-light-info text-info border border-info border-opacity-30 p-3 mb-4" role="alert">
                      <div class="d-flex">
                        <i class="ti ti-shield-check fs-6 me-2 mt-1"></i>
                        <div class="fs-2">
                          <strong>Automatizaciones de Seguridad activas en este proceso:</strong>
                          <ul class="mb-0 mt-2 pl-3">
                            <li>Generación robusta y aleatoria de tu clave de encriptación interna de 32 bytes.</li>
                            <li>Creación automática y segura de tu base de datos SQLite en <code>writable/database.db</code>.</li>
                            <li>Creación aleatoria del Token de Ping seguro para proteger la ejecución de tareas programadas (Crons).</li>
                          </ul>
                        </div>
                      </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4 pt-3 border-top border-opacity-10">
                      <button type="button" class="btn btn-outline-primary px-4 py-2 font-medium" onclick="prevStep(1)">
                        <i class="ti ti-arrow-left me-1"></i> Requisitos
                      </button>
                      <button type="button" class="btn btn-primary px-4 py-2 font-medium" onclick="nextStep(3)">
                        Siguiente Paso <i class="ti ti-arrow-right ms-1"></i>
                      </button>
                    </div>
                  </div>

                  <!-- PASO 3: CUENTA DE ADMINISTRADOR -->
                  <div class="wizard-step d-none" id="step-content-3">
                    <h5 class="fw-bold mb-3"><i class="ti ti-user-plus me-2 text-primary"></i>3. Crear Administrador Primario</h5>
                    <p class="text-muted fs-3 mb-4">Configura las credenciales maestras del Super Administrador con acceso total a la plataforma.</p>

                    <div class="mb-3">
                      <label for="admin_name" class="form-label fw-bold">Nombre de Usuario (Username)</label>
                      <input type="text" class="form-control" id="admin_name" name="admin_name" placeholder="Ej: admin" value="<?= old('admin_name') ?>" required>
                    </div>

                    <div class="mb-3">
                      <label for="admin_email" class="form-label fw-bold">Correo Electrónico (Email)</label>
                      <input type="email" class="form-control" id="admin_email" name="admin_email" placeholder="admin@tudominio.com" value="<?= old('admin_email') ?>" required>
                    </div>

                    <div class="mb-4">
                      <label for="admin_password" class="form-label fw-bold">Contraseña del Administrador</label>
                      <input type="password" class="form-control" id="admin_password" name="admin_password" required minlength="8" placeholder="Mínimo 8 caracteres">
                    </div>

                    <div class="alert bg-light-warning text-warning border border-warning border-opacity-30 d-flex align-items-center mb-4" role="alert">
                      <i class="ti ti-alert-circle fs-6 me-2"></i>
                      <div class="fs-2">¡Por favor, recuerda y guarda estas credenciales en un lugar seguro! Serán las únicas llaves de acceso para comenzar a operar el panel.</div>
                    </div>

                    <div class="d-flex justify-content-between mt-4 pt-3 border-top border-opacity-10">
                      <button type="button" class="btn btn-outline-primary px-4 py-2 font-medium" onclick="prevStep(2)">
                        <i class="ti ti-arrow-left me-1"></i> Configuración
                      </button>
                      <button type="button" class="btn btn-success px-4 py-2 font-medium" onclick="confirmInstallation()">
                        <i class="ti ti-device-floppy me-1"></i> Finalizar e Instalar
                      </button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Import Js Files -->
  <script src="<?= base_url('assets/js/vendor.min.js') ?>"></script>
  <script src="<?= base_url('assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js') ?>"></script>
  <script src="<?= base_url('assets/libs/simplebar/dist/simplebar.min.js') ?>"></script>
  <script src="<?= base_url('assets/js/theme/app.dark.init.js') ?>"></script>
  <script src="<?= base_url('assets/js/theme/theme.js') ?>"></script>
  <script src="<?= base_url('assets/js/theme/app.min.js') ?>"></script>

  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script>
    let currentStep = 1;

    function nextStep(step) {
      if (step === 2 && currentStep === 1) {
        document.getElementById('step-content-1').classList.add('d-none');
        document.getElementById('step-content-2').classList.remove('d-none');
        document.getElementById('step-header-1').classList.add('completed');
        document.getElementById('step-header-2').classList.add('active');
        document.getElementById('step-line-1').classList.add('active');
        currentStep = 2;
      } else if (step === 3 && currentStep === 2) {
        const appUrl = document.getElementById('app_url').value;
        if (!appUrl) {
          Swal.fire({ icon: 'error', title: 'Falta información', text: 'La URL de la aplicación es obligatoria.', confirmButtonColor: '#5d87ff' });
          return;
        }
        if (!appUrl.startsWith('http://') && !appUrl.startsWith('https://')) {
          Swal.fire({ icon: 'error', title: 'Formato incorrecto', text: 'La URL debe comenzar con http:// o https://.', confirmButtonColor: '#5d87ff' });
          return;
        }

        document.getElementById('step-content-2').classList.add('d-none');
        document.getElementById('step-content-3').classList.remove('d-none');
        document.getElementById('step-header-2').classList.add('completed');
        document.getElementById('step-header-3').classList.add('active');
        document.getElementById('step-line-2').classList.add('active');
        currentStep = 3;
      }
    }

    function prevStep(step) {
      if (step === 1 && currentStep === 2) {
        document.getElementById('step-content-2').classList.add('d-none');
        document.getElementById('step-content-1').classList.remove('d-none');
        document.getElementById('step-header-2').classList.remove('active');
        document.getElementById('step-header-1').classList.remove('completed');
        document.getElementById('step-line-1').classList.remove('active');
        currentStep = 1;
      } else if (step === 2 && currentStep === 3) {
        document.getElementById('step-content-3').classList.add('d-none');
        document.getElementById('step-content-2').classList.remove('d-none');
        document.getElementById('step-header-3').classList.remove('active');
        document.getElementById('step-header-2').classList.remove('completed');
        document.getElementById('step-line-2').classList.remove('active');
        currentStep = 2;
      }
    }

    function confirmInstallation() {
      const name = document.getElementById('admin_name').value.trim();
      const email = document.getElementById('admin_email').value.trim();
      const pass = document.getElementById('admin_password').value;

      if (!name || !email || !pass) {
        Swal.fire({ icon: 'error', title: 'Campos incompletos', text: 'Por favor, rellena todos los datos del administrador.', confirmButtonColor: '#5d87ff' });
        return;
      }

      if (pass.length < 8) {
        Swal.fire({ icon: 'error', title: 'Contraseña débil', text: 'La contraseña del administrador debe tener al menos 8 caracteres.', confirmButtonColor: '#5d87ff' });
        return;
      }

      Swal.fire({
        title: '¿Confirmar Instalación?',
        text: 'Se creará tu archivo de entorno .env, se estructurará tu Base de Datos SQLite y se registrará tu cuenta de administrador primario.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#13deb9',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, instalar ahora',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
      }).then((result) => {
        if (result.isConfirmed) {
          Swal.fire({
            title: 'Instalando Proxmox Alert',
            html: 'Por favor, espera unos segundos. Configurando el entorno y base de datos...',
            allowOutsideClick: false,
            didOpen: () => {
              Swal.showLoading();
            }
          });
          document.getElementById('install-form').submit();
        }
      });
    }

    // Alertas de sesión mediante SweetAlert2
    const Toast = Swal.mixin({
      toast: true,
      position: 'bottom',
      showConfirmButton: false,
      timer: 5000,
      timerProgressBar: true
    });

    <?php if (session('errors') !== null) : ?>
      <?php $allErrors = ""; foreach(session('errors') as $e) { $allErrors .= "• " . $e . "<br>"; } ?>
      Toast.fire({ icon: 'error', title: '¡Error de Validación!', html: <?= json_encode('<div class="text-start fs-2">' . $allErrors . '</div>') ?> });
    <?php endif ?>
  </script>
</body>

</html>
