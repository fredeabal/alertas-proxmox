<div class="container-fluid">

    <!-- Breadcrumb Header -->
    <div class="card shadow-none position-relative overflow-hidden mb-4">
        <div class="card-body px-4 py-3">
            <div class="row align-items-center">
                <div class="col-12">
                    <h4 class="fw-semibold mb-8">Configuración de Alertas</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a class="text-muted text-decoration-none" href="<?= base_url() ?>">Inicio</a></li>
                            <li class="breadcrumb-item" aria-current="page">Canales de Notificación</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">

                    <!-- Nav Tabs -->
                    <ul class="nav nav-tabs border-bottom mb-0" id="alertSettingsTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active d-flex align-items-center gap-2 px-4 py-3" id="email-tab"
                                data-bs-toggle="tab" data-bs-target="#email-pane"
                                type="button" role="tab" aria-controls="email-pane" aria-selected="true">
                                <i class="ti ti-mail fs-5"></i>
                                <span class="fw-semibold">Correo Electrónico</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link d-flex align-items-center gap-2 px-4 py-3" id="telegram-tab"
                                data-bs-toggle="tab" data-bs-target="#telegram-pane"
                                type="button" role="tab" aria-controls="telegram-pane" aria-selected="false">
                                <i class="ti ti-brand-telegram fs-5"></i>
                                <span class="fw-semibold">Telegram</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link d-flex align-items-center gap-2 px-4 py-3" id="slack-tab"
                                data-bs-toggle="tab" data-bs-target="#slack-pane"
                                type="button" role="tab" aria-controls="slack-pane" aria-selected="false">
                                <i class="ti ti-brand-slack fs-5"></i>
                                <span class="fw-semibold">Slack</span>
                            </button>
                        </li>
                    </ul>

                    <!-- Unified Form -->
                    <form action="<?= base_url('alerts-config/store') ?>" method="post">
                        <?= csrf_field() ?>

                        <div class="tab-content pt-4" id="alertSettingsTabsContent">

                            <!-- ============================================================ -->
                            <!-- 1. Pestaña: Correo Electrónico (SMTP) -->
                            <!-- ============================================================ -->
                            <div class="tab-pane fade show active" id="email-pane" role="tabpanel" aria-labelledby="email-tab" tabindex="0">

                                <!-- Encabezado con toggle -->
                                <div class="d-flex align-items-center justify-content-between mb-4">
                                    <h5 class="mb-0 fw-semibold d-flex align-items-center gap-2">
                                        <i class="ti ti-mail text-primary fs-5"></i>
                                        Servidor de Correo (SMTP)
                                    </h5>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" role="switch" id="email_enabled" name="email_enabled" value="1" <?= ($emailSettings['email_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                                        <label class="form-check-label fw-semibold" for="email_enabled">Habilitado</label>
                                    </div>
                                </div>

                                <!-- Sección: Remitente -->
                                <div class="card shadow-none border mb-4">
                                    <div class="card-header bg-light-primary py-2 px-3">
                                        <h6 class="card-title fw-semibold text-primary mb-0">
                                            <i class="ti ti-mail-forward fs-5 me-2"></i>Remitente de Notificación
                                        </h6>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <div class="form-floating">
                                                    <input type="email" class="form-control" id="fromEmail" name="fromEmail"
                                                        placeholder="noreply@tuempresa.com"
                                                        value="<?= esc($emailSettings['fromEmail'] ?? '') ?>">
                                                    <label for="fromEmail">Email del Remitente</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <div class="form-floating">
                                                    <input type="text" class="form-control" id="fromName" name="fromName"
                                                        placeholder="Proxmox Alert"
                                                        value="<?= esc($emailSettings['fromName'] ?? 'Proxmox Alert') ?>">
                                                    <label for="fromName">Nombre del Remitente</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Sección: Servidor SMTP -->
                                <div class="card shadow-none border mb-4">
                                    <div class="card-header bg-light-primary py-2 px-3">
                                        <h6 class="card-title fw-semibold text-primary mb-0">
                                            <i class="ti ti-server-cog fs-5 me-2"></i>Servidor de Salida SMTP
                                        </h6>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="row">
                                            <div class="col-md-8 mb-3">
                                                <div class="form-floating">
                                                    <input type="text" class="form-control" id="SMTPHost" name="SMTPHost"
                                                        placeholder="smtp.gmail.com"
                                                        value="<?= esc($emailSettings['SMTPHost'] ?? '') ?>">
                                                    <label for="SMTPHost">Servidor SMTP (Host)</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-floating">
                                                    <input type="number" class="form-control" id="SMTPPort" name="SMTPPort"
                                                        placeholder="587"
                                                        value="<?= esc($emailSettings['SMTPPort'] ?? '587') ?>">
                                                    <label for="SMTPPort">Puerto</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <div class="form-floating">
                                                    <input type="text" class="form-control" id="SMTPUser" name="SMTPUser"
                                                        placeholder="usuario@correo.com"
                                                        value="<?= esc($emailSettings['SMTPUser'] ?? '') ?>">
                                                    <label for="SMTPUser">Usuario SMTP</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <div class="form-floating position-relative">
                                                    <input type="password" class="form-control" id="SMTPPass" name="SMTPPass"
                                                        placeholder="contraseña"
                                                        value="<?= esc($emailSettings['SMTPPass'] ?? '') ?>">
                                                    <label for="SMTPPass">Contraseña SMTP</label>
                                                    <button class="btn position-absolute top-50 end-0 translate-middle-y me-2 border-0 bg-transparent"
                                                        type="button" onclick="togglePassword('SMTPPass')">
                                                        <i class="ti ti-eye fs-5 text-muted"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <div class="form-floating">
                                                    <select class="form-select" id="SMTPCrypto" name="SMTPCrypto">
                                                        <option value="tls" <?= ($emailSettings['SMTPCrypto'] ?? 'tls') === 'tls' ? 'selected' : '' ?>>TLS (Recomendado)</option>
                                                        <option value="ssl" <?= ($emailSettings['SMTPCrypto'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                                                        <option value=""   <?= ($emailSettings['SMTPCrypto'] ?? '') === ''    ? 'selected' : '' ?>>Ninguno</option>
                                                    </select>
                                                    <label for="SMTPCrypto">Cifrado</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-floating">
                                                    <select class="form-select" id="protocol" name="protocol">
                                                        <option value="smtp" <?= ($emailSettings['protocol'] ?? 'smtp') === 'smtp' ? 'selected' : '' ?>>SMTP</option>
                                                        <option value="mail" <?= ($emailSettings['protocol'] ?? '') === 'mail' ? 'selected' : '' ?>>PHP Mail</option>
                                                    </select>
                                                    <label for="protocol">Protocolo</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-floating">
                                                    <select class="form-select" id="mailType" name="mailType">
                                                        <option value="html" <?= ($emailSettings['mailType'] ?? 'html') === 'html' ? 'selected' : '' ?>>HTML</option>
                                                        <option value="text" <?= ($emailSettings['mailType'] ?? '') === 'text' ? 'selected' : '' ?>>Texto Plano</option>
                                                    </select>
                                                    <label for="mailType">Tipo de Contenido</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Acción: Prueba de correo -->
                                <div class="d-flex justify-content-end">
                                    <button type="submit" formaction="<?= base_url('alerts-config/test-email') ?>"
                                        class="btn btn-outline-primary d-flex align-items-center gap-2 px-4">
                                        <i class="ti ti-send fs-4"></i>
                                        Enviar Correo de Prueba
                                    </button>
                                </div>

                            </div><!-- /email-pane -->

                            <!-- ============================================================ -->
                            <!-- 2. Pestaña: Telegram Bot -->
                            <!-- ============================================================ -->
                            <div class="tab-pane fade" id="telegram-pane" role="tabpanel" aria-labelledby="telegram-tab" tabindex="0">

                                <!-- Encabezado con toggle -->
                                <div class="d-flex align-items-center justify-content-between mb-4">
                                    <h5 class="mb-0 fw-semibold d-flex align-items-center gap-2">
                                        <i class="ti ti-brand-telegram text-primary fs-5"></i>
                                        Configuración del Bot de Telegram
                                    </h5>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" role="switch" id="telegram_enabled" name="telegram_enabled" value="1" <?= ($telegramSettings['telegram_enabled'] ?? '0') === '1' ? 'checked' : '' ?>>
                                        <label class="form-check-label fw-semibold" for="telegram_enabled">Habilitado</label>
                                    </div>
                                </div>

                                <!-- Sección: Credenciales -->
                                <div class="card shadow-none border mb-4">
                                    <div class="card-header bg-light-primary py-2 px-3">
                                        <h6 class="card-title fw-semibold text-primary mb-0">
                                            <i class="ti ti-key fs-5 me-2"></i>Credenciales del Bot
                                        </h6>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="row">
                                            <div class="col-12 mb-3">
                                                <div class="form-floating position-relative">
                                                    <input type="password" class="form-control" id="telegram_bot_token"
                                                        name="telegram_bot_token"
                                                        placeholder="123456789:ABCdefGhIJKlmNoPQRsTUVwxyZ"
                                                        value="<?= esc($telegramSettings['telegram_bot_token'] ?? '') ?>">
                                                    <label for="telegram_bot_token">Token del Bot (HTTP API Token)</label>
                                                    <button class="btn position-absolute top-50 end-0 translate-middle-y me-2 border-0 bg-transparent"
                                                        type="button" onclick="togglePassword('telegram_bot_token')">
                                                        <i class="ti ti-eye fs-5 text-muted"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <div class="form-floating">
                                                    <input type="text" class="form-control" id="telegram_bot_username"
                                                        name="telegram_bot_username"
                                                        placeholder="MiProxmoxAlertBot"
                                                        value="<?= esc($telegramSettings['telegram_bot_username'] ?? '') ?>">
                                                    <label for="telegram_bot_username">Username del Bot</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <div class="form-floating">
                                                    <input type="text" class="form-control" id="telegram_test_chat_id"
                                                        name="telegram_test_chat_id"
                                                        placeholder="-100123456789"
                                                        value="<?= esc($telegramSettings['telegram_test_chat_id'] ?? '') ?>">
                                                    <label for="telegram_test_chat_id">Chat ID (Grupo, Canal o Privado)</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Info box -->
                                <div class="alert bg-light-primary border-0 d-flex align-items-start gap-3 p-4 mb-4" role="alert">
                                    <i class="ti ti-info-circle text-primary fs-5 mt-1 flex-shrink-0"></i>
                                    <div>
                                        <h6 class="fw-semibold text-primary mb-1">¿Cómo configurar tu Bot de Telegram?</h6>
                                        <p class="mb-0 fs-2 text-muted">
                                            1. Habla con <a href="https://t.me/BotFather" target="_blank" class="fw-semibold text-primary text-decoration-none">@BotFather</a> en Telegram para crear un bot y obtener tu <strong>Token</strong>.<br>
                                            2. Agrega el bot como administrador al grupo o canal donde quieras recibir las alertas.<br>
                                            3. Para obtener el <strong>Chat ID</strong> reenvía un mensaje del grupo al bot <a href="https://t.me/RawDataBot" target="_blank" class="fw-semibold text-primary text-decoration-none">@RawDataBot</a> o usa el endpoint <code>getUpdates</code>.
                                        </p>
                                    </div>
                                </div>

                                <!-- Acción: Prueba Telegram -->
                                <div class="d-flex justify-content-end">
                                    <button type="submit" formaction="<?= base_url('alerts-config/test-telegram') ?>"
                                        class="btn btn-outline-primary d-flex align-items-center gap-2 px-4">
                                        <i class="ti ti-brand-telegram fs-4"></i>
                                        Probar Conectividad de Telegram
                                    </button>
                                </div>

                            </div><!-- /telegram-pane -->

                            <!-- ============================================================ -->
                            <!-- 3. Pestaña: Slack Webhook -->
                            <!-- ============================================================ -->
                            <div class="tab-pane fade" id="slack-pane" role="tabpanel" aria-labelledby="slack-tab" tabindex="0">

                                <!-- Encabezado con toggle -->
                                <div class="d-flex align-items-center justify-content-between mb-4">
                                    <h5 class="mb-0 fw-semibold d-flex align-items-center gap-2">
                                        <i class="ti ti-brand-slack text-primary fs-5"></i>
                                        Configuración de Slack Webhook
                                    </h5>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" role="switch" id="slack_enabled" name="slack_enabled" value="1" <?= ($slackSettings['slack_enabled'] ?? '0') === '1' ? 'checked' : '' ?>>
                                        <label class="form-check-label fw-semibold" for="slack_enabled">Habilitado</label>
                                    </div>
                                </div>

                                <!-- Sección: Webhook URL -->
                                <div class="card shadow-none border mb-4">
                                    <div class="card-header bg-light-primary py-2 px-3">
                                        <h6 class="card-title fw-semibold text-primary mb-0">
                                            <i class="ti ti-link fs-5 me-2"></i>Endpoint de Conexión
                                        </h6>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="row">
                                            <div class="col-12 mb-3">
                                                <div class="form-floating">
                                                    <input type="url" class="form-control" id="slack_webhook_url"
                                                        name="slack_webhook_url"
                                                        placeholder="https://hooks.slack.com/services/tu-token-aqui"
                                                        value="<?= esc($slackSettings['slack_webhook_url'] ?? '') ?>">
                                                    <label for="slack_webhook_url">URL del Webhook de Slack (Incoming Webhook)</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Info box -->
                                <div class="alert bg-light-primary border-0 d-flex align-items-start gap-3 p-4 mb-4" role="alert">
                                    <i class="ti ti-help text-primary fs-5 mt-1 flex-shrink-0"></i>
                                    <div>
                                        <h6 class="fw-semibold text-primary mb-1">¿Cómo obtener tu Webhook de Slack?</h6>
                                        <p class="mb-0 fs-2 text-muted">
                                            1. Ve a la consola de aplicaciones de Slack y selecciona o crea tu App.<br>
                                            2. Activa la opción <strong>Incoming Webhooks</strong> en el menú de características.<br>
                                            3. Añade un nuevo Webhook apuntando al canal donde quieres recibir las alertas.<br>
                                            4. Copia la URL generada y pégala en el campo de arriba.
                                        </p>
                                    </div>
                                </div>

                                <!-- Acción: Prueba Slack -->
                                <div class="d-flex justify-content-end">
                                    <button type="submit" formaction="<?= base_url('alerts-config/test-slack') ?>"
                                        class="btn btn-outline-primary d-flex align-items-center gap-2 px-4">
                                        <i class="ti ti-brand-slack fs-4"></i>
                                        Probar Webhook de Slack
                                    </button>
                                </div>

                            </div><!-- /slack-pane -->

                        </div><!-- /tab-content -->

                        <!-- Footer: Guardar -->
                        <div class="mt-4 pt-4 border-top">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                                <span class="text-muted fs-2 d-none d-sm-inline-flex align-items-center gap-1">
                                    <i class="ti ti-lock"></i>
                                    Los tokens y contraseñas se almacenan de forma segura.
                                </span>
                                <button type="submit" class="btn btn-primary font-medium px-5">
                                    <div class="d-flex align-items-center justify-content-center gap-2">
                                        <i class="ti ti-device-floppy fs-4"></i>
                                        Guardar Todos los Cambios
                                    </div>
                                </button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(id) {
    const input = document.getElementById(id);
    if (input) {
        input.type = input.type === 'password' ? 'text' : 'password';
    }
}
</script>
