<!-- Sidebar Start -->
<aside class="left-sidebar with-vertical">
  <div>
    <div class="brand-logo d-flex align-items-center justify-content-between">
      <a href="<?= base_url() ?>" class="text-nowrap logo-img">
        <img src="<?= base_url('assets/images/logos/logo.png') ?>" class="dark-logo" width="180" alt="Logo-Dark" />
        <img src="<?= base_url('assets/images/logos/logo.png') ?>" class="light-logo" width="180" alt="Logo-light" />
        <img src="<?= base_url('assets/images/logos/logo-mini.png') ?>" class="logo-mini" width="30" alt="Logo-Mini" />
      </a>
      <a href="javascript:void(0)" class="sidebartoggler ms-auto text-decoration-none fs-5 d-block d-xl-none">
        <i class="ti ti-x"></i>
      </a>
    </div>

    <nav class="sidebar-nav scroll-sidebar" data-simplebar>
      <ul id="sidebarnav">
        <!-- ---------------------------------- -->
        <!-- Home -->
        <!-- ---------------------------------- -->
        <li class="nav-small-cap">
          <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
          <span class="hide-menu">Inicio</span>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="<?= base_url() ?>" aria-expanded="false">
            <span>
              <i class="ti ti-layout-dashboard"></i>
            </span>
            <span class="hide-menu">Dashboard</span>
          </a>
        </li>


        <?php if (auth()->user()->can('users.view')): ?>
        <li class="sidebar-item">
          <a class="sidebar-link" href="<?= base_url('users') ?>" aria-expanded="false">
            <span>
              <i class="ti ti-users"></i>
            </span>
            <span class="hide-menu">Usuarios</span>
          </a>
        </li>
        <?php endif; ?>

        <?php if (auth()->user()->can('empresas.view')): ?>
        <li class="sidebar-item">
          <a class="sidebar-link" href="<?= base_url('companies') ?>" aria-expanded="false">
            <span>
              <i class="ti ti-building-skyscraper"></i>
            </span>
            <span class="hide-menu">Empresas</span>
          </a>
        </li>
        <?php endif; ?>

        <?php if (auth()->user()->can('email.manage')): ?>
        <li class="sidebar-item">
          <a class="sidebar-link" href="<?= base_url('alerts-config') ?>" aria-expanded="false">
            <span>
              <i class="ti ti-bell"></i>
            </span>
            <span class="hide-menu">Alertas</span>
          </a>
        </li>
        <?php endif; ?>

        <?php if (auth()->user()->can('ai.view')): ?>
        <li class="sidebar-item">
          <a class="sidebar-link" href="<?= base_url('ai') ?>" aria-expanded="false">
            <span>
              <i class="fa-solid fa-microchip-ai fs-5"></i>
            </span>
            <span class="hide-menu">IA</span>
          </a>
        </li>
        <?php endif; ?>
        
      </ul>
    </nav>
  </div>
</aside>
<!--  Sidebar End -->
