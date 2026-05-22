<div class="container-fluid">

    <!-- Breadcrumb -->
    <div class="card shadow-none position-relative overflow-hidden mb-4">
        <div class="card-body px-4 py-3">
            <div class="row align-items-center">
                <div class="col-12">
                    <h4 class="fw-semibold mb-8">Configuración de Alertas</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a class="text-muted text-decoration-none" href="<?= base_url() ?>">Inicio</a></li>
                            <li class="breadcrumb-item" aria-current="page">Alertas</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-4">

                    <!-- Nav Tabs -->
                    <ul class="nav nav-tabs mb-4" id="alertSettingsTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="email-tab" data-bs-toggle="tab" data-bs-target="#email-pane" type="button" role="tab" aria-selected="true">
                                <i class="ti ti-mail me-1"></i> Correo
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="telegram-tab" data-bs-toggle="tab" data-bs-target="#telegram-pane" type="button" role="tab" aria-selected="false">
                                <i class="ti ti-brand-telegram me-1"></i> Telegram
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="slack-tab" data-bs-toggle="tab" data-bs-target="#slack-pane" type="button" role="tab" aria-selected="false">
                                <i class="ti ti-brand-slack me-1"></i> Slack
                            </button>
                        </li>
                    </ul>

                    <form action="<?= base_url('alerts-config/store') ?>" method="post">
                        <?= csrf_field() ?>

                        <div class="tab-content" id="alertSettingsTabsContent">

                            <!-- ================================================ -->
                            <!-- Pestaña: Correo -->
                            <!-- ================================================ -->
                            <div class="tab-pane fade show active" id="email-pane" role="tabpanel" aria-labelledby="email-tab" tabindex="0">

                                <div class="d-flex align-items-center justify-content-between mb-4">
                                    <p class="text-muted mb-0 fs-2">Configura el servidor SMTP para el envío de alertas por correo.</p>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" role="switch" id="email_enabled" name="email_enabled" value="1" <?= (old('email_enabled', $emailSettings['email_enabled'] ?? '1')) === '1' ? 'checked' : '' ?>>
                                        <label class="form-check-label fw-semibold" for="email_enabled">Habilitado</label>
                                    </div>
                                </div>

                                <!-- Remitente -->
                                <p class="fw-semibold text-muted mb-3 fs-2 text-uppercase letter-spacing-1">Remitente</p>
                                <div class="row mb-4">
                                    <div class="col-md-6 mb-3">
                                        <div class="form-floating">
                                            <input type="email" class="form-control" id="fromEmail" name="fromEmail" placeholder="noreply@tuempresa.com" value="<?= esc(old('fromEmail', $emailSettings['fromEmail'] ?? '')) ?>">
                                            <label for="fromEmail">Email del Remitente</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="fromName" name="fromName" placeholder="Proxmox Alert" value="<?= esc(old('fromName', $emailSettings['fromName'] ?? 'Proxmox Alert')) ?>">
                                            <label for="fromName">Nombre del Remitente</label>
                                        </div>
                                    </div>
                                </div>

                                <hr class="my-4">

                                <!-- Servidor -->
                                <p class="fw-semibold text-muted mb-3 fs-2 text-uppercase letter-spacing-1">Servidor SMTP</p>
                                <div class="row mb-3">
                                    <div class="col-md-8 mb-3">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="SMTPHost" name="SMTPHost" placeholder="smtp.gmail.com" value="<?= esc(old('SMTPHost', $emailSettings['SMTPHost'] ?? '')) ?>">
                                            <label for="SMTPHost">Host</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="form-floating">
                                            <input type="number" class="form-control" id="SMTPPort" name="SMTPPort" placeholder="587" value="<?= esc(old('SMTPPort', $emailSettings['SMTPPort'] ?? '587')) ?>">
                                            <label for="SMTPPort">Puerto</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="SMTPUser" name="SMTPUser" placeholder="usuario" value="<?= esc(old('SMTPUser', $emailSettings['SMTPUser'] ?? '')) ?>">
                                            <label for="SMTPUser">Usuario</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-floating position-relative">
                                            <input type="password" class="form-control" id="SMTPPass" name="SMTPPass" placeholder="contraseña" value="<?= esc(old('SMTPPass', $emailSettings['SMTPPass'] ?? '')) ?>">
                                            <label for="SMTPPass">Contraseña</label>
                                            <button class="btn position-absolute top-50 end-0 translate-middle-y me-2 border-0 bg-transparent" type="button" onclick="togglePassword('SMTPPass')">
                                                <i class="ti ti-eye fs-5 text-muted"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="form-floating">
                                            <select class="form-select" id="SMTPCrypto" name="SMTPCrypto">
                                                <?php $smtpCrypto = old('SMTPCrypto', $emailSettings['SMTPCrypto'] ?? 'tls'); ?>
                                                <option value="tls" <?= $smtpCrypto === 'tls' ? 'selected' : '' ?>>TLS (Recomendado)</option>
                                                <option value="ssl" <?= $smtpCrypto === 'ssl' ? 'selected' : '' ?>>SSL</option>
                                                <option value=""   <?= $smtpCrypto === ''    ? 'selected' : '' ?>>Ninguno</option>
                                            </select>
                                            <label for="SMTPCrypto">Cifrado</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="form-floating">
                                            <select class="form-select" id="protocol" name="protocol">
                                                <?php $protocol = old('protocol', $emailSettings['protocol'] ?? 'smtp'); ?>
                                                <option value="smtp" <?= $protocol === 'smtp' ? 'selected' : '' ?>>SMTP</option>
                                                <option value="mail" <?= $protocol === 'mail' ? 'selected' : '' ?>>PHP Mail</option>
                                            </select>
                                            <label for="protocol">Protocolo</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="form-floating">
                                            <select class="form-select" id="mailType" name="mailType">
                                                <?php $mailType = old('mailType', $emailSettings['mailType'] ?? 'html'); ?>
                                                <option value="html" <?= $mailType === 'html' ? 'selected' : '' ?>>HTML</option>
                                                <option value="text" <?= $mailType === 'text' ? 'selected' : '' ?>>Texto Plano</option>
                                            </select>
                                            <label for="mailType">Formato</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" formaction="<?= base_url('alerts-config/test-email') ?>" class="btn btn-outline-primary px-4">
                                        <i class="ti ti-send me-1"></i> Enviar correo de prueba
                                    </button>
                                </div>
                            </div>

                            <!-- ================================================ -->
                            <!-- Pestaña: Telegram -->
                            <!-- ================================================ -->
                            <div class="tab-pane fade" id="telegram-pane" role="tabpanel" aria-labelledby="telegram-tab" tabindex="0">

                                <div class="d-flex align-items-center justify-content-between mb-4">
                                    <p class="text-muted mb-0 fs-2">Conecta un Bot de Telegram para recibir alertas en tiempo real en tu grupo o canal.</p>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" role="switch" id="telegram_enabled" name="telegram_enabled" value="1" <?= (old('telegram_enabled', $telegramSettings['telegram_enabled'] ?? '0')) === '1' ? 'checked' : '' ?>>
                                        <label class="form-check-label fw-semibold" for="telegram_enabled">Habilitado</label>
                                    </div>
                                </div>

                                <p class="fw-semibold text-muted mb-3 fs-2 text-uppercase letter-spacing-1">Credenciales</p>
                                <div class="row mb-4">
                                    <div class="col-12 mb-3">
                                        <div class="form-floating position-relative">
                                            <input type="password" class="form-control" id="telegram_bot_token" name="telegram_bot_token" placeholder="Token" value="<?= esc(old('telegram_bot_token', $telegramSettings['telegram_bot_token'] ?? '')) ?>">
                                            <label for="telegram_bot_token">Token del Bot (HTTP API Token)</label>
                                            <button class="btn position-absolute top-50 end-0 translate-middle-y me-2 border-0 bg-transparent" type="button" onclick="togglePassword('telegram_bot_token')">
                                                <i class="ti ti-eye fs-5 text-muted"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="telegram_bot_username" name="telegram_bot_username" placeholder="@MiBot" value="<?= esc(old('telegram_bot_username', $telegramSettings['telegram_bot_username'] ?? '')) ?>">
                                            <label for="telegram_bot_username">Username del Bot</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="telegram_test_chat_id" name="telegram_test_chat_id" placeholder="-100123456789" value="<?= esc(old('telegram_test_chat_id', $telegramSettings['telegram_test_chat_id'] ?? '')) ?>">
                                            <label for="telegram_test_chat_id">Chat ID</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex align-items-start gap-2 p-3 bg-light-primary rounded mb-4">
                                    <i class="ti ti-info-circle text-primary fs-5 mt-1 flex-shrink-0"></i>
                                    <p class="mb-0 fs-2 text-muted">
                                        Crea un bot con <a href="https://t.me/BotFather" target="_blank" class="fw-semibold text-primary text-decoration-none">@BotFather</a>, agrégalo a tu grupo/canal como administrador, y obtén el Chat ID con <a href="https://t.me/RawDataBot" target="_blank" class="fw-semibold text-primary text-decoration-none">@RawDataBot</a>.
                                    </p>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" formaction="<?= base_url('alerts-config/test-telegram') ?>" class="btn btn-outline-primary px-4">
                                        <i class="ti ti-brand-telegram me-1"></i> Probar Telegram
                                    </button>
                                </div>
                            </div>

                            <!-- ================================================ -->
                            <!-- Pestaña: Slack -->
                            <!-- ================================================ -->
                            <div class="tab-pane fade" id="slack-pane" role="tabpanel" aria-labelledby="slack-tab" tabindex="0">

                                <div class="d-flex align-items-center justify-content-between mb-4">
                                    <p class="text-muted mb-0 fs-2">Envía alertas a un canal de Slack mediante un Incoming Webhook.</p>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" role="switch" id="slack_enabled" name="slack_enabled" value="1" <?= (old('slack_enabled', $slackSettings['slack_enabled'] ?? '0')) === '1' ? 'checked' : '' ?>>
                                        <label class="form-check-label fw-semibold" for="slack_enabled">Habilitado</label>
                                    </div>
                                </div>

                                <p class="fw-semibold text-muted mb-3 fs-2 text-uppercase letter-spacing-1">Webhook</p>
                                <div class="row mb-4">
                                    <div class="col-12 mb-3">
                                        <div class="form-floating">
                                            <input type="url" class="form-control" id="slack_webhook_url" name="slack_webhook_url" placeholder="https://hooks.slack.com/services/..." value="<?= esc(old('slack_webhook_url', $slackSettings['slack_webhook_url'] ?? '')) ?>">
                                            <label for="slack_webhook_url">Incoming Webhook URL</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex align-items-start gap-2 p-3 bg-light-primary rounded mb-4">
                                    <i class="ti ti-info-circle text-primary fs-5 mt-1 flex-shrink-0"></i>
                                    <p class="mb-0 fs-2 text-muted">
                                        En la consola de tu App de Slack, activa <strong>Incoming Webhooks</strong>, crea uno nuevo apuntando al canal deseado y pega la URL aquí.
                                    </p>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" formaction="<?= base_url('alerts-config/test-slack') ?>" class="btn btn-outline-primary px-4">
                                        <i class="ti ti-brand-slack me-1"></i> Probar Slack
                                    </button>
                                </div>
                            </div>

                        </div><!-- /tab-content -->

                        <!-- Footer -->
                        <div class="mt-4 pt-4 border-top d-flex">
                            <button type="submit" class="btn btn-primary font-medium px-5">
                                <i class="ti ti-device-floppy me-2 fs-4"></i> Guardar Cambios
                            </button>
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
