<div class="container-fluid">
    <div class="card shadow-none position-relative overflow-hidden">
        <div class="card-body px-4 py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h4 class="fw-semibold mb-8">Gestión de Usuarios</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a class="text-muted text-decoration-none" href="<?= base_url() ?>">Inicio</a></li>
                            <li class="breadcrumb-item" aria-current="page">Usuarios</li>
                        </ol>
                    </nav>
                </div>
                <div class="col-auto">
                    <?php if (auth()->user()->can('users.create')): ?>
                    <a href="<?= base_url('users/create') ?>" class="btn btn-primary">
                        <i class="ti ti-user-plus me-1"></i> <span class="d-none d-sm-inline">Nuevo Usuario</span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>


    <div class="card">
        <div class="card-body">
            <!-- Buscador de Usuarios Premium -->
            <div class="d-flex align-items-center mb-4">
                <div class="position-relative w-100" style="max-width: 300px;">
                    <input type="text" id="user-search" class="form-control ps-5" placeholder="Buscar usuario..." style="border-radius: 8px; font-size: 0.85rem; height: 38px;">
                    <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted fs-5"></i>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr class="text-muted fw-semibold">
                            <th scope="col" class="ps-0">Usuario</th>
                            <th scope="col" class="d-none d-md-table-cell text-center">Email</th>
                            <th scope="col" class="d-none d-sm-table-cell text-center">Rol</th>
                            <th scope="col" class="d-none d-lg-table-cell text-center">Último Login</th>
                            <th scope="col" class="d-none d-sm-table-cell text-center">Estado</th>
                            <?php if (auth()->user()->can('users.edit') || auth()->user()->can('users.delete')): ?>
                            <th scope="col" class="text-end pe-4">Acciones</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody class="border-top">
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td class="ps-0">
                                    <div class="d-flex align-items-center">
                                        <?php 
                                            $avatarPath = $user->avatar ? base_url('uploads/avatars/' . $user->avatar) : base_url('assets/images/profile/default-avatar.png');
                                        ?>
                                         <img src="<?= $avatarPath ?>" class="rounded-circle shadow-sm d-none d-sm-block user-avatar-thumbnail" width="40" height="40" alt="" />
                                        <div class="ms-0 ms-sm-3">
                                            <h6 class="fw-semibold mb-0"><?= esc($user->username) ?></h6>
                                            <span class="fs-2 text-muted d-md-none"><?= esc($user->email) ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="d-none d-md-table-cell text-center">
                                    <p class="mb-0"><?= esc($user->email) ?></p>
                                </td>
                                <td class="d-none d-sm-table-cell text-center">
                                    <p class="mb-0 text-muted"><?= ucfirst($user->getGroups()[0] ?? 'Sin rol') ?></p>
                                </td>
                                <td class="d-none d-lg-table-cell text-center">
                                    <p class="mb-0 fs-3 text-muted"><?= $user->last_login ? \CodeIgniter\I18n\Time::parse($user->last_login)->humanize() : 'Nunca' ?></p>
                                </td>
                                <td class="d-none d-sm-table-cell text-center">
                                     <?php if ($user->active): ?>
                                         <span class="badge bg-light-success text-success fw-semibold fs-2 d-inline-block text-center badge-width-fixed">Activo</span>
                                     <?php else: ?>
                                         <span class="badge bg-light-danger text-danger fw-semibold fs-2 d-inline-block text-center badge-width-fixed">Inactivo</span>
                                     <?php endif; ?>
                                </td>
                                <?php if (auth()->user()->can('users.edit') || auth()->user()->can('users.delete')): ?>
                                <td class="text-end pe-4">
                                    <div class="dropdown">
                                        <a class="text-muted" href="javascript:void(0)" role="button" id="dropdownMenuLink-<?= $user->id ?>" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="ti ti-dots-vertical fs-6"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuLink-<?= $user->id ?>">
                                            <?php if (auth()->user()->can('users.edit')): ?>
                                            <a class="dropdown-item d-flex align-items-center gap-2" href="<?= base_url('users/edit/' . $user->id) ?>">
                                                <i class="ti ti-edit fs-4"></i> Editar
                                            </a>
                                            <?php endif; ?>

                                            <?php if (auth()->user()->can('users.delete') && $user->id != auth()->id()): ?>
                                                <a class="dropdown-item d-flex align-items-center gap-2" href="javascript:void(0)" onclick="confirmDelete('<?= base_url('users/delete/' . $user->id) ?>')">
                                                    <i class="ti ti-trash fs-4"></i> Eliminar
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// Búsqueda premium instantánea en cliente para usuarios
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('user-search');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const filter = this.value.toLowerCase().trim();
            const rows = document.querySelectorAll('table tbody tr');
            
            rows.forEach(row => {
                if (row.querySelector('td[colspan]')) return;
                
                const username = row.querySelector('h6.fw-semibold')?.textContent.toLowerCase() || '';
                const email = row.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
                const role = row.querySelector('td:nth-child(3)')?.textContent.toLowerCase() || '';
                
                if (username.includes(filter) || email.includes(filter) || role.includes(filter)) {
                    row.style.display = '';
                    row.style.opacity = '1';
                } else {
                    row.style.display = 'none';
                    row.style.opacity = '0';
                }
            });
        });
    }
});
</script>
