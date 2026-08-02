<div class="container-fluid">
    <div class="card shadow-none position-relative overflow-hidden">
        <div class="card-body px-4 py-3">
            <div class="row align-items-center">
                <div class="col-12">
                    <h4 class="fw-semibold mb-8">Mi Perfil</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a class="text-muted text-decoration-none" href="<?= base_url() ?>">Inicio</a></li>
                            <li class="breadcrumb-item" aria-current="page">Perfil</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <form action="<?= base_url('users/perfil/update') ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="row">
            <div class="col-lg-4 d-flex align-items-stretch">
                <div class="card w-100 position-relative overflow-hidden">
                    <div class="card-body p-4 text-center d-flex flex-column justify-content-center">
                        <?php 
                            $avatarPath = $user->avatar ? base_url('uploads/avatars/' . $user->avatar) : base_url('assets/images/profile/default-avatar.png');
                        ?>
                        <div class="mb-3">
                            <img id="avatarPreview" src="<?= $avatarPath ?>" alt="Avatar" class="img-fluid rounded-circle shadow-sm border border-2 p-1 user-avatar-preview" width="120" height="120">
                        </div>
                        
                        <h4 class="fw-semibold mb-3"><?= esc($user->username) ?></h4>

                        <label for="avatar" class="btn btn-primary btn-sm mb-2 cursor-pointer">
                            <i class="ti ti-upload me-1"></i> Cambiar Imagen
                        </label>
                        <input class="d-none" type="file" id="avatar" name="avatar" accept="image/*" onchange="previewImage(this)">
                        <div class="form-text fs-2">JPG, PNG o GIF. Máx 2MB</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8 d-flex align-items-stretch">
                <div class="card w-100 position-relative overflow-hidden">
                    <div class="card-body p-4">
                        <h5 class="mb-3">Detalles de la Cuenta</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="username" name="username" placeholder=" " value="<?= esc($user->username) ?>" required>
                                    <label for="username">Nombre de Usuario</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input type="email" class="form-control" id="email" name="email" placeholder=" " value="<?= esc($user->email) ?>" required>
                                    <label for="email">Correo Electrónico</label>
                                </div>
                            </div>
                        </div>

                        <h5 class="mt-4 mb-3">Seguridad</h5>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <div class="form-floating position-relative">
                                    <input type="password" class="form-control" id="password" name="password" placeholder=" ">
                                    <label for="password">Nueva contraseña (dejar en blanco para no cambiar)</label>
                                    <button class="btn position-absolute top-50 end-0 translate-middle-y me-2 border-0" type="button" id="toggle-password" onclick="togglePassword()">
                                        <i class="ti ti-eye fs-5"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <h5 class="mt-4 mb-3">Apariencia</h5>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <div class="form-floating">
                                    <select class="form-select" id="theme-selector" name="theme">
                                        <option value="system">Automático (Sistema)</option>
                                        <option value="dark">Tema Oscuro</option>
                                        <option value="light">Tema Claro</option>
                                    </select>
                                    <label for="theme-selector">Tema / Apariencia</label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 border-top pt-3 text-center">
                            <button type="submit" class="btn btn-primary font-medium px-4 d-inline-flex align-items-center justify-content-center">
                                <i class="ti ti-device-floppy me-2 fs-4"></i> Guardar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const themeSelector = document.getElementById('theme-selector');
    if (themeSelector) {
        themeSelector.value = localStorage.getItem('theme') || 'system';
        
        themeSelector.addEventListener('change', function() {
            const val = this.value;
            if (val === 'system') {
                localStorage.removeItem('theme');
                const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                document.documentElement.setAttribute('data-bs-theme', systemTheme);
            } else {
                localStorage.setItem('theme', val);
                document.documentElement.setAttribute('data-bs-theme', val);
            }
        });
    }
});

function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatarPreview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function togglePassword() {
    const input = document.getElementById('password');
    const btn = document.getElementById('toggle-password');
    if (input && btn) {
        const type = input.type === 'password' ? 'text' : 'password';
        input.type = type;
        btn.innerHTML = type === 'password' ? '<i class="ti ti-eye fs-5"></i>' : '<i class="ti ti-eye-off fs-5"></i>';
    }
}
</script>
