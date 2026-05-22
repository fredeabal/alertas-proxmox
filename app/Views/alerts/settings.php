<div class="container-fluid">
    <!-- Header Hero Card with subtle gradient and modern blur -->
    <div class="card premium-card overflow-hidden mb-4 border-0" style="background: linear-gradient(135deg, rgba(28, 35, 51, 0.6) 0%, rgba(28, 35, 51, 0.3) 100%) !important; backdrop-filter: blur(10px);">
        <div class="card-body px-4 py-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-flex align-items-center">
                        <div class="accent-icon-box bg-primary-subtle text-primary" style="width: 50px; height: 50px; border-radius: 14px;">
                            <i class="ti ti-bell-ringing fs-6 animate-bell"></i>
                        </div>
                        <div class="ms-3">
                            <h4 class="fw-bold mb-1 text-white">Canales de Alertas</h4>
                            <p class="text-muted mb-0 fs-2">Configura tus servidores de envío y automatiza notificaciones en tiempo real por Email, Telegram o Slack.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <!-- Main Content Card with elegant premium shadow -->
            <div class="card premium-card shadow-sm border-0">
                <div class="card-body p-4">
                    
                    <!-- Premium Segmented Tabs (Futuristic controller look) -->
                    <ul class="nav nav-pills premium-tabs nav-justified mb-5" id="alertSettingsTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active d-flex align-items-center justify-content-center py-3 fs-3" id="email-tab" data-bs-toggle="tab" data-bs-target="#email-pane" type="button" role="tab" aria-controls="email-pane" aria-selected="true">
                                <i class="ti ti-mail me-2 fs-5"></i>
                                <span class="fw-semibold">Correo Electrónico (SMTP)</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link d-flex align-items-center justify-content-center py-3 fs-3" id="telegram-tab" data-bs-toggle="tab" data-bs-target="#telegram-pane" type="button" role="tab" aria-controls="telegram-pane" aria-selected="false">
                                <i class="ti ti-brand-telegram me-2 fs-5"></i>
                                <span class="fw-semibold">Telegram Bot</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link d-flex align-items-center justify-content-center py-3 fs-3" id="slack-tab" data-bs-toggle="tab" data-bs-target="#slack-pane" type="button" role="tab" aria-controls="slack-pane" aria-selected="false">
                                <i class="ti ti-brand-slack me-2 fs-5"></i>
                                <span class="fw-semibold">Slack Webhook</span>
                            </button>
                        </li>
                    </ul>

                    <!-- Unified Form to easily save all parameters in one click -->
                    <form action="<?= base_url('alerts-config/store') ?>" method="post">
                        <?= csrf_field() ?>

                        <div class="tab-content" id="alertSettingsTabsContent">
                            
                            <!-- 1. Pestaña de Email (SMTP) -->
                            <div class="tab-pane fade show active" id="email-pane" role="tabpanel" aria-labelledby="email-tab" tabindex="0">
                                
                                <!-- Scoped Header with Switch Toggle -->
                                <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom border-secondary-subtle">
                                    <div class="d-flex align-items-center">
                                        <div class="accent-icon-box accent-email">
                                            <i class="ti ti-mail fs-5"></i>
                                        </div>
                                        <div>
                                            <h5 class="fw-bold mb-0 text-white">Canal de Correo Electrónico (SMTP)</h5>
                                            <p class="text-muted mb-0 fs-2">Envía notificaciones de alerta mediante un servidor de salida seguro.</p>
                                        </div>
                                    </div>
                                    <div class="form-check form-switch fs-4">
                                        <input class="form-check-input" type="checkbox" role="switch" id="email_enabled" name="email_enabled" value="1" <?= ($emailSettings['email_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                                        <label class="form-check-label fs-2 fw-bold text-muted ms-1" for="email_enabled">HABILITADO</label>
                                    </div>
                                </div>

                                <!-- Card Segment: Notification Sender -->
                                <div class="premium-section-card">
                                    <h6 class="fw-bold mb-3 text-white d-flex align-items-center">
                                        <i class="ti ti-mail-forward text-primary me-2 fs-5"></i>
                                        Remitente de Notificación
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-floating mb-3">
                                                <input type="email" class="form-control border-0" id="fromEmail" name="fromEmail" placeholder="noreply@tuempresa.com" value="<?= esc($emailSettings['fromEmail'] ?? '') ?>">
                                                <label for="fromEmail">Email del Remitente</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-floating mb-3">
                                                <input type="text" class="form-control border-0" id="fromName" name="fromName" placeholder="Proxmox Alert" value="<?= esc($emailSettings['fromName'] ?? 'Proxmox Alert') ?>">
                                                <label for="fromName">Nombre del Remitente</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Card Segment: SMTP Configuration -->
                                <div class="premium-section-card">
                                    <h6 class="fw-bold mb-3 text-white d-flex align-items-center">
                                        <i class="ti ti-server-cog text-primary me-2 fs-5"></i>
                                        Servidor de Salida SMTP
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="form-floating mb-3">
                                                <input type="text" class="form-control border-0" id="SMTPHost" name="SMTPHost" placeholder="smtp.gmail.com" value="<?= esc($emailSettings['SMTPHost'] ?? '') ?>">
                                                <label for="SMTPHost">Servidor SMTP</label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-floating mb-3">
                                                <input type="number" class="form-control border-0" id="SMTPPort" name="SMTPPort" placeholder="587" value="<?= esc($emailSettings['SMTPPort'] ?? '587') ?>">
                                                <label for="SMTPPort">Puerto</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-floating mb-3">
                                                <input type="text" class="form-control border-0" id="SMTPUser" name="SMTPUser" placeholder="usuario" value="<?= esc($emailSettings['SMTPUser'] ?? '') ?>">
                                                <label for="SMTPUser">Usuario SMTP</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-floating mb-3 position-relative">
                                                <input type="password" class="form-control border-0" id="SMTPPass" name="SMTPPass" placeholder="contraseña" value="<?= esc($emailSettings['SMTPPass'] ?? '') ?>">
                                                <label for="SMTPPass">Contraseña SMTP</label>
                                                <button class="btn position-absolute top-50 end-0 translate-middle-y me-2 border-0 bg-transparent text-muted password-toggle-btn" type="button" onclick="togglePassword('SMTPPass')">
                                                    <i class="ti ti-eye fs-5"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-0">
                                        <div class="col-md-4">
                                            <div class="form-floating mb-3">
                                                <select class="form-select border-0" id="SMTPCrypto" name="SMTPCrypto">
                                                    <option value="tls" <?= ($emailSettings['SMTPCrypto'] ?? 'tls') === 'tls' ? 'selected' : '' ?>>TLS (Recomendado)</option>
                                                    <option value="ssl" <?= ($emailSettings['SMTPCrypto'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                                                    <option value="" <?= ($emailSettings['SMTPCrypto'] ?? '') === '' ? 'selected' : '' ?>>Ninguno</option>
                                                </select>
                                                <label for="SMTPCrypto">Cifrado de Seguridad</label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-floating mb-3">
                                                <select class="form-select border-0" id="protocol" name="protocol">
                                                    <option value="smtp" <?= ($emailSettings['protocol'] ?? 'smtp') === 'smtp' ? 'selected' : '' ?>>SMTP</option>
                                                    <option value="mail" <?= ($emailSettings['protocol'] ?? '') === 'mail' ? 'selected' : '' ?>>PHP Mail</option>
                                                </select>
                                                <label for="protocol">Protocolo de Envío</label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-floating mb-3">
                                                <select class="form-select border-0" id="mailType" name="mailType">
                                                    <option value="html" <?= ($emailSettings['mailType'] ?? 'html') === 'html' ? 'selected' : '' ?>>HTML</option>
                                                    <option value="text" <?= ($emailSettings['mailType'] ?? '') === 'text' ? 'selected' : '' ?>>Texto Plano</option>
                                                </select>
                                                <label for="mailType">Tipo de Contenido</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Test Action Button with beautiful hover transition -->
                                <div class="d-flex justify-content-end mb-2">
                                    <button type="submit" formaction="<?= base_url('alerts-config/test-email') ?>" class="btn btn-outline-primary font-medium px-4 py-2.5 rounded-3 transition-all hover-scale d-inline-flex align-items-center">
                                        <i class="ti ti-send me-2 fs-4 animate-send"></i>
                                        Enviar Correo de Prueba
                                    </button>
                                </div>
                            </div>

                            <!-- 2. Pestaña de Telegram Bot -->
                            <div class="tab-pane fade" id="telegram-pane" role="tabpanel" aria-labelledby="telegram-tab" tabindex="0">
                                
                                <!-- Scoped Header with Switch Toggle -->
                                <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom border-secondary-subtle">
                                    <div class="d-flex align-items-center">
                                        <div class="accent-icon-box accent-telegram">
                                            <i class="ti ti-brand-telegram fs-5"></i>
                                        </div>
                                        <div>
                                            <h5 class="fw-bold mb-0 text-white">Canal de Telegram Bot</h5>
                                            <p class="text-muted mb-0 fs-2">Envía notificaciones de forma inmediata a un chat, canal o grupo de Telegram.</p>
                                        </div>
                                    </div>
                                    <div class="form-check form-switch fs-4">
                                        <input class="form-check-input" type="checkbox" role="switch" id="telegram_enabled" name="telegram_enabled" value="1" <?= ($telegramSettings['telegram_enabled'] ?? '0') === '1' ? 'checked' : '' ?>>
                                        <label class="form-check-label fs-2 fw-bold text-muted ms-1" for="telegram_enabled">HABILITADO</label>
                                    </div>
                                </div>

                                <!-- Card Segment: Telegram Credentials -->
                                <div class="premium-section-card">
                                    <h6 class="fw-bold mb-3 text-white d-flex align-items-center">
                                        <i class="ti ti-settings-cog text-info me-2 fs-5"></i>
                                        Credenciales de Conexión
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-floating mb-3 position-relative">
                                                <input type="password" class="form-control border-0" id="telegram_bot_token" name="telegram_bot_token" placeholder="123456789:ABCdefGhIJKlmNoPQRsTUVwxyZ" value="<?= esc($telegramSettings['telegram_bot_token'] ?? '') ?>">
                                                <label for="telegram_bot_token">Token del Bot (HTTP API Token)</label>
                                                <button class="btn position-absolute top-50 end-0 translate-middle-y me-2 border-0 bg-transparent text-muted password-toggle-btn" type="button" onclick="togglePassword('telegram_bot_token')">
                                                    <i class="ti ti-eye fs-5"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-floating mb-3">
                                                <input type="text" class="form-control border-0" id="telegram_bot_username" name="telegram_bot_username" placeholder="MiProxmoxAlertBot" value="<?= esc($telegramSettings['telegram_bot_username'] ?? '') ?>">
                                                <label for="telegram_bot_username">Nombre de Usuario del Bot (Username)</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-floating mb-3">
                                                <input type="text" class="form-control border-0" id="telegram_test_chat_id" name="telegram_test_chat_id" placeholder="-100123456789" value="<?= esc($telegramSettings['telegram_test_chat_id'] ?? '') ?>">
                                                <label for="telegram_test_chat_id">Chat ID de Pruebas (ID de Grupo o Canal)</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Elegant premium instructions layout -->
                                <div class="alert premium-info-alert border-0 d-flex align-items-start gap-3 p-4 mb-4" role="alert">
                                    <i class="ti ti-info-circle fs-6 text-info mt-1"></i>
                                    <div>
                                        <h6 class="alert-heading fw-bold mb-1 fs-3 text-info">¿Cómo configurar tu Bot de Telegram?</h6>
                                        <p class="mb-0 fs-2 text-light-emphasis">
                                            1. Inicia una conversación con <a href="https://t.me/BotFather" target="_blank" class="fw-bold text-info text-decoration-none border-bottom border-info border-opacity-25 pb-0.5">@BotFather</a> en Telegram para crear un bot y obtener tu <strong>HTTP API Token</strong>.<br>
                                            2. Agrega tu bot como administrador al grupo o canal de soporte donde quieras recibir las alertas.<br>
                                            3. Para obtener el <strong>Chat ID</strong> de tu grupo/canal, puedes reenviar cualquier mensaje del grupo al bot <a href="https://t.me/RawDataBot" target="_blank" class="fw-bold text-info text-decoration-none border-bottom border-info border-opacity-25 pb-0.5">@RawDataBot</a>, o invocar el endpoint <code>getUpdates</code> del bot.
                                        </p>
                                    </div>
                                </div>

                                <!-- Test Action Button -->
                                <div class="d-flex justify-content-end mb-2">
                                    <button type="submit" formaction="<?= base_url('alerts-config/test-telegram') ?>" class="btn btn-outline-info font-medium px-4 py-2.5 rounded-3 transition-all hover-scale d-inline-flex align-items-center">
                                        <i class="ti ti-brand-telegram me-2 fs-4 animate-send"></i>
                                        Probar Conectividad de Telegram
                                    </button>
                                </div>
                            </div>

                            <!-- 3. Pestaña de Slack Webhook -->
                            <div class="tab-pane fade" id="slack-pane" role="tabpanel" aria-labelledby="slack-tab" tabindex="0">
                                
                                <!-- Scoped Header with Switch Toggle -->
                                <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom border-secondary-subtle">
                                    <div class="d-flex align-items-center">
                                        <div class="accent-icon-box accent-slack">
                                            <i class="ti ti-brand-slack fs-5"></i>
                                        </div>
                                        <div>
                                            <h5 class="fw-bold mb-0 text-white">Canal de Slack Webhook</h5>
                                            <p class="text-muted mb-0 fs-2">Envía notificaciones de alerta estructuradas directamente a un canal de Slack en tiempo real.</p>
                                        </div>
                                    </div>
                                    <div class="form-check form-switch fs-4">
                                        <input class="form-check-input" type="checkbox" role="switch" id="slack_enabled" name="slack_enabled" value="1" <?= ($slackSettings['slack_enabled'] ?? '0') === '1' ? 'checked' : '' ?>>
                                        <label class="form-check-label fs-2 fw-bold text-muted ms-1" for="slack_enabled">HABILITADO</label>
                                    </div>
                                </div>

                                <!-- Card Segment: Endpoint Connection -->
                                <div class="premium-section-card">
                                    <h6 class="fw-bold mb-3 text-white d-flex align-items-center">
                                        <i class="ti ti-link text-warning me-2 fs-5"></i>
                                        Endpoint de Comunicación
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-floating mb-3">
                                                <input type="url" class="form-control border-0" id="slack_webhook_url" name="slack_webhook_url" placeholder="https://hooks.slack.com/services/tu-token-aqui" value="<?= esc($slackSettings['slack_webhook_url'] ?? '') ?>">
                                                <label for="slack_webhook_url">URL del Webhook de Slack (Incoming Webhook URL)</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Elegant premium instructions layout -->
                                <div class="alert premium-warning-alert border-0 d-flex align-items-start gap-3 p-4 mb-4" role="alert">
                                    <i class="ti ti-help fs-6 text-warning mt-1"></i>
                                    <div>
                                        <h6 class="alert-heading fw-bold mb-1 fs-3 text-warning">¿Cómo configurar tu Webhook de Slack?</h6>
                                        <p class="mb-0 fs-2 text-light-emphasis">
                                            1. Dirígete a la consola de tus aplicaciones de Slack e ingresa o crea una App para tu espacio de trabajo.<br>
                                            2. Activa la característica de <strong>Incoming Webhooks</strong> en el menú lateral.<br>
                                            3. Crea un nuevo Webhook apuntando al canal deseado donde quieres que se distribuyan las alertas.<br>
                                            4. Copia la URL de webhook generada y pégala en la casilla superior de configuración.
                                        </p>
                                    </div>
                                </div>

                                <!-- Test Action Button -->
                                <div class="d-flex justify-content-end mb-2">
                                    <button type="submit" formaction="<?= base_url('alerts-config/test-slack') ?>" class="btn btn-outline-warning font-medium px-4 py-2.5 rounded-3 transition-all hover-scale d-inline-flex align-items-center">
                                        <i class="ti ti-brand-slack me-2 fs-4 animate-send"></i>
                                        Probar Webhook de Slack
                                    </button>
                                </div>
                            </div>

                        </div>

                        <!-- Sticky Glass-morphic Footer Action Bar with lock indicator -->
                        <div class="col-12 premium-footer-bar mt-5">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                                <span class="text-muted fs-2 d-none d-md-inline-block">
                                    <i class="ti ti-lock me-1"></i> Todos los datos sensibles (tokens y contraseñas) son cifrados y almacenados de forma segura.
                                </span>
                                <button type="submit" class="btn btn-save-gradient font-medium px-5 py-3 rounded-3 shadow text-white hover-scale d-inline-flex align-items-center ms-auto ms-md-0">
                                    <i class="ti ti-device-floppy me-2 fs-5"></i>
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

<style>
/* Modern styling and theme adjustments for high-end luxury dark premium aesthetics */

/* Layout & dynamic transition utilities */
.transition-all {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
}

.hover-scale:hover {
    transform: translateY(-2px) scale(1.02);
    box-shadow: 0 8px 24px rgba(93, 135, 255, 0.15);
}

/* Bell micro-animation */
.animate-bell {
    transition: transform 0.3s ease;
}
.card:hover .animate-bell {
    animation: ringBell 2s ease infinite;
}
@keyframes ringBell {
    0% { transform: rotate(0); }
    10% { transform: rotate(15deg); }
    20% { transform: rotate(-10deg); }
    30% { transform: rotate(12deg); }
    40% { transform: rotate(-8deg); }
    50% { transform: rotate(8deg); }
    60% { transform: rotate(-4deg); }
    70% { transform: rotate(4deg); }
    80% { transform: rotate(-2deg); }
    90% { transform: rotate(2deg); }
    100% { transform: rotate(0); }
}

/* Button send micro-animation */
.btn:hover .animate-send {
    animation: sendPulse 1s ease infinite;
}
@keyframes sendPulse {
    0%, 100% { transform: scale(1) translateX(0); }
    50% { transform: scale(1.1) translateX(3px); }
}

/* Premium Segmented Pills Navigation */
.premium-tabs {
    background: rgba(255, 255, 255, 0.03) !important;
    padding: 6px !important;
    border-radius: 16px !important;
    border: 1px solid rgba(255, 255, 255, 0.05) !important;
}

.premium-tabs .nav-item {
    margin: 0 4px;
}

.premium-tabs .nav-link {
    border: 1px solid transparent !important;
    border-radius: 12px !important;
    color: #a1aab2 !important;
    font-weight: 500;
    transition: all 0.3s ease;
    background: transparent !important;
}

.premium-tabs .nav-link:hover {
    color: #fff !important;
    background: rgba(255, 255, 255, 0.05) !important;
}

/* Dynamic active gradients for each brand tab */
#email-tab.active {
    background: linear-gradient(135deg, rgba(93, 135, 255, 0.12) 0%, rgba(93, 135, 255, 0.04) 100%) !important;
    border: 1px solid rgba(93, 135, 255, 0.25) !important;
    color: #5d87ff !important;
    box-shadow: 0 4px 20px rgba(93, 135, 255, 0.12) !important;
    font-weight: 600;
}

#telegram-tab.active {
    background: linear-gradient(135deg, rgba(57, 182, 255, 0.12) 0%, rgba(57, 182, 255, 0.04) 100%) !important;
    border: 1px solid rgba(57, 182, 255, 0.25) !important;
    color: #39b6ff !important;
    box-shadow: 0 4px 20px rgba(57, 182, 255, 0.12) !important;
    font-weight: 600;
}

#slack-tab.active {
    background: linear-gradient(135deg, rgba(255, 174, 31, 0.12) 0%, rgba(255, 174, 31, 0.04) 100%) !important;
    border: 1px solid rgba(255, 174, 31, 0.25) !important;
    color: #ffae1f !important;
    box-shadow: 0 4px 20px rgba(255, 174, 31, 0.12) !important;
    font-weight: 600;
}

/* Scoped focus state colors per brand tab */
#email-pane .form-control:focus, #email-pane .form-select:focus {
    border-color: #5d87ff !important;
    box-shadow: 0 0 0 0.25rem rgba(93, 135, 255, 0.15) !important;
}

#telegram-pane .form-control:focus {
    border-color: #39b6ff !important;
    box-shadow: 0 0 0 0.25rem rgba(57, 182, 255, 0.15) !important;
}

#slack-pane .form-control:focus {
    border-color: #ffae1f !important;
    box-shadow: 0 0 0 0.25rem rgba(255, 174, 31, 0.15) !important;
}

/* Scoped switch colors */
#email_enabled:checked {
    background-color: #5d87ff !important;
    border-color: #5d87ff !important;
    box-shadow: 0 0 8px rgba(93, 135, 255, 0.4) !important;
}
#telegram_enabled:checked {
    background-color: #39b6ff !important;
    border-color: #39b6ff !important;
    box-shadow: 0 0 8px rgba(57, 182, 255, 0.4) !important;
}
#slack_enabled:checked {
    background-color: #ffae1f !important;
    border-color: #ffae1f !important;
    box-shadow: 0 0 8px rgba(255, 174, 31, 0.4) !important;
}

/* Card Modern Design */
.premium-card {
    border-radius: 20px !important;
    border: 1px solid rgba(255, 255, 255, 0.06) !important;
    background: #1c2333 !important; /* Carbon dark shade matching the layout base */
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2) !important;
}

.premium-section-card {
    border-radius: 16px !important;
    border: 1px solid rgba(255, 255, 255, 0.04) !important;
    background: rgba(255, 255, 255, 0.01) !important;
    padding: 24px;
    margin-bottom: 24px;
    transition: all 0.3s ease;
}

.premium-section-card:hover {
    border-color: rgba(255, 255, 255, 0.08) !important;
    background: rgba(255, 255, 255, 0.02) !important;
}

/* Elegant glassmorphic floating form controls */
.form-floating > .form-control,
.form-floating > .form-select {
    background-color: rgba(255, 255, 255, 0.02) !important;
    color: #fff !important;
    border-radius: 12px !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
    transition: all 0.25s ease-in-out !important;
}

.form-floating > .form-control:focus,
.form-floating > .form-select:focus {
    background-color: rgba(255, 255, 255, 0.04) !important;
}

.form-floating > label {
    color: #7a828a !important;
}

/* Action button dynamic gradients */
.btn-save-gradient {
    background: linear-gradient(135deg, #5d87ff 0%, #818cf8 100%) !important;
    border: none !important;
    box-shadow: 0 4px 15px rgba(93, 135, 255, 0.25) !important;
}

.btn-save-gradient:hover {
    transform: translateY(-2px) scale(1.02) !important;
    box-shadow: 0 6px 20px rgba(93, 135, 255, 0.35) !important;
    filter: brightness(1.1);
}

/* Modern branded help info cards */
.premium-info-alert {
    background: rgba(57, 182, 255, 0.05) !important;
    border: 1px solid rgba(57, 182, 255, 0.12) !important;
    border-left: 4px solid #39b6ff !important;
    border-radius: 16px !important;
}

.premium-warning-alert {
    background: rgba(255, 174, 31, 0.05) !important;
    border: 1px solid rgba(255, 174, 31, 0.12) !important;
    border-left: 4px solid #ffae1f !important;
    border-radius: 16px !important;
}

/* Dynamic Accent Icon Containers */
.accent-icon-box {
    width: 42px;
    height: 42px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
}

.accent-email {
    background: rgba(93, 135, 255, 0.1) !important;
    color: #5d87ff !important;
}

.accent-telegram {
    background: rgba(57, 182, 255, 0.1) !important;
    color: #39b6ff !important;
}

.accent-slack {
    background: rgba(255, 174, 31, 0.1) !important;
    color: #ffae1f !important;
}

/* Blur-backdrop premium footer bar */
.premium-footer-bar {
    background: rgba(24, 30, 47, 0.75) !important;
    backdrop-filter: blur(12px);
    border-top: 1px solid rgba(255, 255, 255, 0.05);
    border-radius: 0 0 20px 20px;
    margin: 24px -24px -24px -24px;
    padding: 24px;
}

/* Password eye switcher dynamic styling */
.password-toggle-btn {
    opacity: 0.5;
    transition: all 0.2s ease;
}

.password-toggle-btn:hover {
    opacity: 1;
    color: #fff !important;
}

/* Scrollbar tuning for textareas/tabs if needed */
.form-control::-webkit-scrollbar {
    width: 6px;
}
.form-control::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
}
</style>

<script>
// Show/Hide password switch handler with simple state swap
function togglePassword(id) {
    const input = document.getElementById(id);
    if (input) {
        input.type = input.type === 'password' ? 'text' : 'password';
    }
}
</script>
