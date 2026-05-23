<div class="page-wrapper">
  <!--  Header Start -->
  <header class="topbar">
    <div class="with-vertical">
      <nav class="navbar navbar-expand-lg p-0">
        <ul class="navbar-nav">
          <li class="nav-item">
            <a class="nav-link sidebartoggler nav-icon-hover ms-n3" id="headerCollapse" href="javascript:void(0)">
              <i class="ti ti-menu-2"></i>
            </a>
          </li>
        </ul>

        <div class="d-block d-lg-none">
          <img src="<?= base_url('assets/images/logos/logo.png') ?>" class="dark-logo" width="180" alt="" />
          <img src="<?= base_url('assets/images/logos/logo.png') ?>" class="light-logo" width="180" alt="" />
        </div>

        <button class="navbar-toggler p-0 border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
          <span class="p-2">
            <i class="ti ti-dots fs-7"></i>
          </span>
        </button>

        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
          <div class="d-flex align-items-center justify-content-between">
            <ul class="navbar-nav flex-row ms-auto align-items-center justify-content-center">
              <li class="nav-item dropdown">
                <?php 
                  $user = auth()->user();
                  $avatarPath = $user->avatar ? base_url('uploads/avatars/' . $user->avatar) : base_url('assets/images/profile/default-avatar.png');
                ?>
                <a class="nav-link nav-icon-hover" href="javascript:void(0)" id="drop2" data-bs-toggle="dropdown" aria-expanded="false">
                  <img src="<?= $avatarPath ?>" alt="" width="35" height="35" class="rounded-circle topbar-avatar">
                </a>
                <div class="dropdown-menu content-dd dropdown-menu-end dropdown-menu-animate-up" aria-labelledby="drop2">
                  <div class="profile-dropdown position-relative" data-simplebar>
                    <div class="message-body mt-2">
                      <!-- Perfil Personalizado -->
                      <a href="<?= base_url('users/perfil') ?>" class="py-2 px-7 d-flex align-items-center dropdown-item gap-3">
                        <span class="d-flex align-items-center justify-content-center bg-light-primary rounded-1 p-2 text-primary topbar-icon-wrapper">
                          <i class="ti ti-user-circle fs-6"></i>
                        </span>
                        <div class="w-75">
                          <h6 class="mb-0 fw-semibold"><?= ucfirst($user->username ?? 'Usuario') ?></h6>
                          <span class="d-block text-muted fs-2">Perfil de usuario</span>
                        </div>
                      </a>

                      <!-- Acerca de -->
                      <a href="javascript:void(0)" class="py-2 px-7 d-flex align-items-center dropdown-item gap-3" data-bs-toggle="modal" data-bs-target="#aboutModal">
                        <span class="d-flex align-items-center justify-content-center bg-light-info rounded-1 p-2 text-info topbar-icon-wrapper">
                          <i class="ti ti-info-circle fs-6"></i>
                        </span>
                        <div class="w-75">
                          <h6 class="mb-0 fw-semibold">Acerca de</h6>
                          <span class="d-block text-muted fs-2">Información del sistema</span>
                        </div>
                      </a>

                      <hr class="dropdown-divider mx-7 my-2">

                      <!-- Cerrar Sesión -->
                      <a href="<?= base_url('logout') ?>" class="py-2 px-7 d-flex align-items-center dropdown-item gap-3">
                        <span class="d-flex align-items-center justify-content-center bg-light-danger rounded-1 p-2 text-danger topbar-icon-wrapper">
                          <i class="ti ti-logout fs-6"></i>
                        </span>
                        <div class="w-75">
                          <h6 class="mb-0 fw-semibold text-danger">Cerrar Sesión</h6>

                        </div>
                      </a>
                    </div>
                  </div>
                </div>
              </li>
            </ul>
          </div>
        </div>
      </nav>
    </div>
  </header>
  <!--  Header End -->

  <!-- Modal Acerca de -->
  <div class="modal fade" id="aboutModal" tabindex="-1" aria-labelledby="aboutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
      <div class="modal-content border-0 shadow-lg">
        <div class="modal-header bg-primary text-white border-0">
          <h5 class="modal-title text-white" id="aboutModalLabel">Acerca de</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body text-center p-4">
          <img src="<?= base_url('assets/images/logos/logo.png') ?>" width="150" alt="Logo" class="mb-3">
          <p class="text-muted mb-3">Sistema de Notificaciones</p>
          <div class="bg-light-primary text-primary rounded-pill py-1 px-3 d-inline-block fw-bold fs-2 mb-3">
            Versión 1.2.0
          </div>
          <p class="fs-2 text-muted mb-0">© <?= date('Y') ?> • Freddy De Abreu Alfonso</p>
          <p class="fs-2 text-muted mb-0">Licencia MIT</p>
        </div>
      </div>
    </div>
  </div>
  
  <div class="body-wrapper">
