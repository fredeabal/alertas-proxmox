<div class="container-fluid">
    <div class="card shadow-none position-relative overflow-hidden mb-4">
        <div class="card-body px-4 py-3">
            <div class="row align-items-center">
                <div class="col-12">
                    <h4 class="fw-semibold mb-8">Configuración de Alertas</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a class="text-muted text-decoration-none" href="<?= base_url() ?>">Inicio</a></li>
                            <li class="breadcrumb-item" aria-current="page">Gestión de Canales de Alerta</li>
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
                    <!-- Nav Tabs (Pestañas Premium) -->
                    <ul class="nav nav-tabs nav-justified mb-4" id="alertSettingsTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active d-flex align-items-center justify-content-center py-3 fs-3" id="email-tab" data-bs-toggle="tab" data-bs-target="#email-pane" type="button" role="tab" aria-controls="email-pane" aria-selected="true">
                                <i class="ti ti-mail me-2 fs-5"></i>
                                <span>Correo Electrónico (SMTP)</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link d-flex align-items-center justify-content-center py-3 fs-3" id="telegram-tab" data-bs-toggle="tab" data-bs-target="#telegram-pane" type="button" role="tab" aria-controls="telegram-pane" aria-selected="false">
                                <i class="ti ti-brand-telegram me-2 fs-5 text-info"></i>
                                <span>Telegram Bot</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link d-flex align-items-center justify-content-center py-3 fs-3" id="slack-tab" data-bs-toggle="tab" data-bs-target="#slack-pane" type="button" role="tab" aria-controls="slack-pane" aria-selected="false">
                                <i class="ti ti-brand-slack me-2 fs-5 text-warning"></i>
                                <span>Slack Webhook</span>
                            </button>
                        </li>
                    </ul>

                    <!-- Single Form Wrapping All Tabs for Easy Saving -->
                    <form action="<?= base_url('alerts-config/store') ?>" method="post">
                        <?= csrf_field() ?>

                        <div class="tab-content" id="alertSettingsTabsContent">
                            
                            <!-- 1. Pestaña de Email (SMTP) -->
                            <div class="tab-pane fade show active" id="email-pane" role="tabpanel" aria-labelledby="email-tab" tabindex="0">
                                <div class="d-flex align-items-center justify-content-between mb-4">
                                    <h5 class="card-title fw-semibold mb-0 d-flex align-items-center">
                                        <i class="ti ti-mail text-primary me-2 fs-5"></i>
                                        Configuración de Correo Electrónico
                                    </h5>
                                    <div class="form-check form-switch fs-4">
                                        <input class="form-check-input" type="checkbox" role="switch" id="email_enabled" name="email_enabled" value="1" <?= ($emailSettings['email_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                                        <label class="form-check-label fs-3 fw-semibold text-muted" for="email_enabled">Habilitar Correo</label>
                                    </div>
                                </div>

                                <h5 class="card-title fw-semibold mb-4 mt-3 d-flex align-items-center">
                                    <i class="ti ti-mail-forward text-primary me-2 fs-5"></i>
                                    Remitente de Notificación
                                </h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="email" class="form-control border border-secondary-subtle" id="fromEmail" name="fromEmail" placeholder="noreply@tuempresa.com" value="<?= esc($emailSettings['fromEmail'] ?? '') ?>">
                                            <label for="fromEmail">Email del Remitente</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control border border-secondary-subtle" id="fromName" name="fromName" placeholder="Proxmox Alert" value="<?= esc($emailSettings['fromName'] ?? 'Proxmox Alert') ?>">
                                            <label for="fromName">Nombre del Remitente</label>
                                        </div>
                                    </div>
                                </div>

                                <h5 class="card-title fw-semibold mb-4 mt-3 d-flex align-items-center">
                                    <i class="ti ti-server-cog text-primary me-2 fs-5"></i>
                                    Servidor de Salida SMTP
                                </h5>
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control border border-secondary-subtle" id="SMTPHost" name="SMTPHost" placeholder="smtp.gmail.com" value="<?= esc($emailSettings['SMTPHost'] ?? '') ?>">
                                            <label for="SMTPHost">Servidor SMTP</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-floating mb-3">
                                            <input type="number" class="form-control border border-secondary-subtle" id="SMTPPort" name="SMTPPort" placeholder="587" value="<?= esc($emailSettings['SMTPPort'] ?? '587') ?>">
                                            <label for="SMTPPort">Puerto</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control border border-secondary-subtle" id="SMTPUser" name="SMTPUser" placeholder="usuario" value="<?= esc($emailSettings['SMTPUser'] ?? '') ?>">
                                            <label for="SMTPUser">Usuario SMTP</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3 position-relative">
                                            <input type="password" class="form-control border border-secondary-subtle" id="SMTPPass" name="SMTPPass" placeholder="contraseña" value="<?= esc($emailSettings['SMTPPass'] ?? '') ?>">
                                            <label for="SMTPPass">Contraseña SMTP</label>
                                            <button class="btn position-absolute top-50 end-0 translate-middle-y me-2 border-0 bg-transparent text-muted" type="button" onclick="togglePassword('SMTPPass')">
                                                <i class="ti ti-eye fs-5"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-4">
                                        <div class="form-floating mb-3">
                                            <select class="form-select border border-secondary-subtle" id="SMTPCrypto" name="SMTPCrypto">
                                                <option value="tls" <?= ($emailSettings['SMTPCrypto'] ?? 'tls') === 'tls' ? 'selected' : '' ?>>TLS (Recomendado)</option>
                                                <option value="ssl" <?= ($emailSettings['SMTPCrypto'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                                                <option value="" <?= ($emailSettings['SMTPCrypto'] ?? '') === '' ? 'selected' : '' ?>>Ninguno</option>
                                            </select>
                                            <label for="SMTPCrypto">Cifrado de Seguridad</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-floating mb-3">
                                            <select class="form-select border border-secondary-subtle" id="protocol" name="protocol">
                                                <option value="smtp" <?= ($emailSettings['protocol'] ?? 'smtp') === 'smtp' ? 'selected' : '' ?>>SMTP</option>
                                                <option value="mail" <?= ($emailSettings['protocol'] ?? '') === 'mail' ? 'selected' : '' ?>>PHP Mail</option>
                                            </select>
                                            <label for="protocol">Protocolo de Envío</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-floating mb-3">
                                            <select class="form-select border border-secondary-subtle" id="mailType" name="mailType">
                                                <option value="html" <?= ($emailSettings['mailType'] ?? 'html') === 'html' ? 'selected' : '' ?>>HTML</option>
                                                <option value="text" <?= ($emailSettings['mailType'] ?? '') === 'text' ? 'selected' : '' ?>>Texto Plano</option>
                                            </select>
                                            <label for="mailType">Tipo de Contenido</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end mb-2">
                                    <button type="submit" formaction="<?= base_url('alerts-config/test-email') ?>" class="btn btn-outline-info font-medium px-4">
                                        <i class="ti ti-send me-2 fs-4"></i>
                                        Probar Configuración de Correo
                                    </button>
                                </div>
                            </div>

                            <!-- 2. Pestaña de Telegram Bot -->
                            <div class="tab-pane fade" id="telegram-pane" role="tabpanel" aria-labelledby="telegram-tab" tabindex="0">
                                <div class="d-flex align-items-center justify-content-between mb-4">
                                    <h5 class="card-title fw-semibold mb-0 d-flex align-items-center">
                                        <i class="ti ti-brand-telegram text-info me-2 fs-5"></i>
                                        Configuración del Bot de Telegram
                                    </h5>
                                    <div class="form-check form-switch fs-4">
                                        <input class="form-check-input" type="checkbox" role="switch" id="telegram_enabled" name="telegram_enabled" value="1" <?= ($telegramSettings['telegram_enabled'] ?? '0') === '1' ? 'checked' : '' ?>>
                                        <label class="form-check-label fs-3 fw-semibold text-muted" for="telegram_enabled">Habilitar Telegram</label>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-floating mb-3 position-relative">
                                            <input type="password" class="form-control border border-secondary-subtle" id="telegram_bot_token" name="telegram_bot_token" placeholder="123456789:ABCdefGhIJKlmNoPQRsTUVwxyZ" value="<?= esc($telegramSettings['telegram_bot_token'] ?? '') ?>">
                                            <label for="telegram_bot_token">Token del Bot (HTTP API Token)</label>
                                            <button class="btn position-absolute top-50 end-0 translate-middle-y me-2 border-0 bg-transparent text-muted" type="button" onclick="togglePassword('telegram_bot_token')">
                                                <i class="ti ti-eye fs-5"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control border border-secondary-subtle" id="telegram_bot_username" name="telegram_bot_username" placeholder="MiProxmoxAlertBot" value="<?= esc($telegramSettings['telegram_bot_username'] ?? '') ?>">
                                            <label for="telegram_bot_username">Nombre de Usuario del Bot (Username)</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control border border-secondary-subtle" id="telegram_test_chat_id" name="telegram_test_chat_id" placeholder="-100123456789" value="<?= esc($telegramSettings['telegram_test_chat_id'] ?? '') ?>">
                                            <label for="telegram_test_chat_id">Chat ID (ID del Grupo, Canal o Privado)</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="alert alert-info border-0 rounded-4 d-flex align-items-start gap-3 p-4 mb-4" role="alert">
                                    <i class="ti ti-info-circle fs-6 text-info mt-1"></i>
                                    <div>
                                        <h6 class="alert-heading fw-bold mb-1 fs-3">¿Cómo funciona Telegram Bot?</h6>
                                        <p class="mb-0 fs-2 text-muted">
                                            1. Crea un bot hablando con <a href="https://t.me/BotFather" target="_blank" class="fw-bold text-info">@BotFather</a> en Telegram para obtener el <strong>Token</strong>.<br>
                                            2. Agrega tu bot como administrador a tu grupo o canal de soporte.<br>
                                            3. Para obtener el <strong>Chat ID</strong> de tu grupo/canal, puedes reenviar un mensaje del grupo al bot <a href="https://t.me/RawDataBot" target="_blank" class="fw-bold text-info">@RawDataBot</a>, o usar tu propio bot tras enviarle un mensaje e invocando el endpoint de <code>getUpdates</code>.
                                        </p>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end mb-2">
                                    <button type="submit" formaction="<?= base_url('alerts-config/test-telegram') ?>" class="btn btn-outline-info font-medium px-4">
                                        <i class="ti ti-brand-telegram me-2 fs-4"></i>
                                        Probar Conectividad de Telegram
                                    </button>
                                </div>
                            </div>

                            <!-- 3. Pestaña de Slack Webhook -->
                            <div class="tab-pane fade" id="slack-pane" role="tabpanel" aria-labelledby="slack-tab" tabindex="0">
                                <div class="d-flex align-items-center justify-content-between mb-4">
                                    <h5 class="card-title fw-semibold mb-0 d-flex align-items-center">
                                        <i class="ti ti-brand-slack text-warning me-2 fs-5"></i>
                                        Configuración de Slack Webhook
                                    </h5>
                                    <div class="form-check form-switch fs-4">
                                        <input class="form-check-input" type="checkbox" role="switch" id="slack_enabled" name="slack_enabled" value="1" <?= ($slackSettings['slack_enabled'] ?? '0') === '1' ? 'checked' : '' ?>>
                                        <label class="form-check-label fs-3 fw-semibold text-muted" for="slack_enabled">Habilitar Slack</label>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-12">
                                        <div class="form-floating mb-3">
                                            <input type="url" class="form-control border border-secondary-subtle" id="slack_webhook_url" name="slack_webhook_url" placeholder="https://hooks.slack.com/services/tu-token-aqui" value="<?= esc($slackSettings['slack_webhook_url'] ?? '') ?>">
                                            <label for="slack_webhook_url">URL del Webhook de Slack (Incoming Webhook URL)</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="alert alert-warning border-0 rounded-4 d-flex align-items-start gap-3 p-4 mb-4" role="alert">
                                    <i class="ti ti-help fs-6 text-warning mt-1"></i>
                                    <div>
                                        <h6 class="alert-heading fw-bold mb-1 fs-3">¿Cómo obtener tu Webhook de Slack?</h6>
                                        <p class="mb-0 fs-2 text-muted">
                                            1. Ve a la consola de tus aplicaciones de Slack y selecciona o crea tu App.<br>
                                            2. Activa la opción de <strong>Incoming Webhooks</strong>.<br>
                                            3. Añade un nuevo Webhook al espacio de trabajo seleccionando el canal al que deseas enviar las alertas.<br>
                                            4. Copia la URL generada y pégala en el campo de arriba.
                                        </p>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end mb-2">
                                    <button type="submit" formaction="<?= base_url('alerts-config/test-slack') ?>" class="btn btn-outline-info font-medium px-4">
                                        <i class="ti ti-brand-slack me-2 fs-4"></i>
                                        Probar Webhook de Slack
                                    </button>
                                </div>
                            </div>

                        </div>

                        <!-- Global Footer Action Bar -->
                        <div class="col-12 mt-4 border-top pt-4">
                            <div class="d-flex align-items-center justify-content-end">
                                <button type="submit" class="btn btn-primary font-medium px-5 py-2.5 rounded-3 shadow-sm transition-all hover-scale">
                                    <i class="ti ti-device-floppy me-2 fs-4"></i>
                                    Guardar Todos los Cambios
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
