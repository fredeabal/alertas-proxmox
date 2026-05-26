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
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="discord-tab" data-bs-toggle="tab" data-bs-target="#discord-pane" type="button" role="tab" aria-selected="false">
                                <i class="ti ti-brand-discord me-1"></i> Discord
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

                                <!-- Destinatario -->
                                <p class="fw-semibold text-muted mb-3 fs-2 text-uppercase letter-spacing-1">Destinatario</p>
                                <div class="row mb-4">
                                    <div class="col-md-12 mb-3">
                                        <div class="form-floating">
                                            <input type="email" class="form-control" id="recipientEmail" name="recipientEmail" placeholder="admin@tudominio.com" value="<?= esc(old('recipientEmail', $emailSettings['recipientEmail'] ?? '')) ?>">
                                            <label for="recipientEmail">Correo Electrónico de Destino (Donde llegarán las alertas)</label>
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

                                <div class="d-flex justify-content-end gap-2">
                                    <button type="submit" formaction="<?= base_url('alerts-config/test-email') ?>" class="btn btn-outline-primary px-4">
                                        <i class="ti ti-send me-1"></i> Enviar correo de prueba
                                    </button>
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="ti ti-device-floppy me-1"></i> Guardar Cambios
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

                                <div class="accordion mb-4" id="accordionTelegramHelp">
                                    <div class="accordion-item border-0 shadow-sm rounded-3 overflow-hidden">
                                        <h2 class="accordion-header" id="headingTelegramHelp">
                                            <button class="accordion-button collapsed bg-light-primary text-primary fw-semibold p-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTelegramHelp" aria-expanded="false" aria-controls="collapseTelegramHelp">
                                                <i class="ti ti-help-circle fs-5 me-2"></i> ¿Cómo configurar el Bot de Telegram?
                                            </button>
                                        </h2>
                                        <div id="collapseTelegramHelp" class="accordion-collapse collapse" aria-labelledby="headingTelegramHelp" data-bs-parent="#accordionTelegramHelp">
                                            <div class="accordion-body bg-white text-muted fs-3 p-4">
                                                <h5 class="fw-semibold text-primary mb-2"><i class="ti ti-robot fs-4 me-1"></i> 1. Crear un Bot</h5>
                                                <ol class="mb-4 ps-3">
                                                    <li class="mb-1">Abre Telegram y busca a <a href="https://t.me/BotFather" target="_blank" class="fw-semibold text-primary text-decoration-none">@BotFather</a>.</li>
                                                    <li class="mb-1">Envíale el comando <strong>/newbot</strong>.</li>
                                                    <li class="mb-1">Elige un nombre para tu bot (ej. "Notificaciones Proxmox").</li>
                                                    <li class="mb-1">Elige un nombre de usuario que termine en "bot" (ej. "proxmox_alerts_bot").</li>
                                                </ol>

                                                <h5 class="fw-semibold text-primary mb-2"><i class="ti ti-key fs-4 me-1"></i> 2. Obtener el Token del Bot</h5>
                                                <p class="mb-4">Después de crearlo, BotFather te dará un token con este formato:<br> 
                                                <code class="fs-2 text-primary">xxxxxxxxx:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx</code><br> 
                                                Copia este token y pégalo arriba en el campo <strong>Token del Bot</strong>.</p>

                                                <h5 class="fw-semibold text-primary mb-2"><i class="ti ti-message-circle-2 fs-4 me-1"></i> 3. Obtener el Chat ID (Chat Privado)</h5>
                                                <ol class="mb-4 ps-3">
                                                    <li class="mb-1">Busca a <a href="https://t.me/userinfobot" target="_blank" class="text-decoration-none fw-semibold">@userinfobot</a> o <a href="https://t.me/getmyid_bot" target="_blank" class="text-decoration-none fw-semibold">@getmyid_bot</a> en Telegram.</li>
                                                    <li class="mb-1">Envíales cualquier mensaje y te responderán con tu Chat ID.</li>
                                                </ol>

                                                <h5 class="fw-semibold text-primary mb-2"><i class="ti ti-users fs-4 me-1"></i> 4. Para Grupos o Canales</h5>
                                                <ol class="mb-4 ps-3">
                                                    <li class="mb-1">Añade tu bot al grupo o canal como <strong>administrador</strong>.</li>
                                                    <li class="mb-1">Añade al bot <a href="https://t.me/RawDataBot" target="_blank" class="text-decoration-none fw-semibold">@RawDataBot</a> a ese mismo grupo para obtener el Chat ID del grupo.</li>
                                                    <li class="mb-1">Los IDs de grupos son números negativos (ej. <code class="fs-2 text-primary px-2 py-1 rounded d-inline-block mt-2 mb-2">-1001234567890</code>).</li>
                                                </ol>

                                                <div class="bg-light-primary p-3 rounded-3 mt-4 border border-primary-subtle">
                                                    <p class="mb-2 fw-semibold text-primary fs-4"><i class="ti ti-bulb me-1"></i> Resumen Rápido</p>
                                                    <ul class="mb-0 ps-3 text-dark">
                                                        <li class="mb-1"><strong>Token del Bot:</strong> Identifica a tu bot (te lo da BotFather).</li>
                                                        <li><strong>Chat ID:</strong> A dónde enviar los mensajes (tu ID personal o el del grupo).</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end gap-2">
                                    <button type="submit" formaction="<?= base_url('alerts-config/test-telegram') ?>" class="btn btn-outline-primary px-4">
                                        <i class="ti ti-brand-telegram me-1"></i> Probar Telegram
                                    </button>
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="ti ti-device-floppy me-1"></i> Guardar Cambios
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

                                <div class="accordion mb-4" id="accordionSlackHelp">
                                    <div class="accordion-item border-0 shadow-sm rounded-3 overflow-hidden">
                                        <h2 class="accordion-header" id="headingSlackHelp">
                                            <button class="accordion-button collapsed bg-light-primary text-primary fw-semibold p-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSlackHelp" aria-expanded="false" aria-controls="collapseSlackHelp">
                                                <i class="ti ti-help-circle fs-5 me-2"></i> ¿Cómo configurar el Webhook de Slack?
                                            </button>
                                        </h2>
                                        <div id="collapseSlackHelp" class="accordion-collapse collapse" aria-labelledby="headingSlackHelp" data-bs-parent="#accordionSlackHelp">
                                            <div class="accordion-body bg-white text-muted fs-3 p-4">
                                                <h5 class="fw-semibold text-primary mb-2"><i class="ti ti-plug fs-4 me-1"></i> 1. Crear una App en Slack</h5>
                                                <ol class="mb-4 ps-3">
                                                    <li class="mb-1">Ve a la consola web de Slack: <a href="https://api.slack.com/apps" target="_blank" class="fw-semibold text-primary text-decoration-none">Slack API Apps</a>.</li>
                                                    <li class="mb-1">Haz clic en el botón verde <strong>Create New App</strong> y selecciona <strong>From scratch</strong>.</li>
                                                    <li class="mb-1">Elige un nombre para la App (ej. "Proxmox Alerts") y selecciona tu Workspace.</li>
                                                </ol>

                                                <h5 class="fw-semibold text-primary mb-2"><i class="ti ti-toggle-right fs-4 me-1"></i> 2. Activar Webhooks</h5>
                                                <ol class="mb-4 ps-3">
                                                    <li class="mb-1">En el menú lateral izquierdo de tu nueva App, haz clic en <strong>Incoming Webhooks</strong>.</li>
                                                    <li class="mb-1">Activa el interruptor principal cambiándolo a <strong>On</strong>.</li>
                                                </ol>

                                                <h5 class="fw-semibold text-primary mb-2"><i class="ti ti-link fs-4 me-1"></i> 3. Generar y Copiar la URL</h5>
                                                <p class="mb-2">Al final de esa misma página, pulsa el botón:</p>
                                                <p class="mb-3"><code class="fs-2 text-dark bg-light py-1 rounded d-inline-block border">Add New Webhook to Workspace</code></p>
                                                <p class="mb-0">Selecciona el canal donde recibirás las alertas (ej. <code class="fs-2 text-primary">#alertas-servidores</code>), autoriza, y copia la URL que empieza por <code class="fs-2 text-primary">https://hooks.slack.com/...</code> para pegarla en el campo superior.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end gap-2">
                                    <button type="submit" formaction="<?= base_url('alerts-config/test-slack') ?>" class="btn btn-outline-primary px-4">
                                        <i class="ti ti-brand-slack me-1"></i> Probar Slack
                                    </button>
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="ti ti-device-floppy me-1"></i> Guardar Cambios
                                    </button>
                                </div>
                            </div>

                        </div><!-- /tab-content -->

                        <!-- ================================================ -->
                        <!-- Pestaña: Discord -->
                        <!-- ================================================ -->
                        <div class="tab-pane fade" id="discord-pane" role="tabpanel" aria-labelledby="discord-tab" tabindex="0">

                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <p class="text-muted mb-0 fs-2">Envía alertas a un canal de Discord mediante Webhooks.</p>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" role="switch" id="discord_enabled" name="discord_enabled" value="1" <?= (old('discord_enabled', $discordSettings['discord_enabled'] ?? '0')) === '1' ? 'checked' : '' ?>>
                                    <label class="form-check-label fw-semibold" for="discord_enabled">Habilitado</label>
                                </div>
                            </div>

                            <p class="fw-semibold text-muted mb-3 fs-2 text-uppercase letter-spacing-1">Webhook</p>
                            <div class="row mb-4">
                                <div class="col-12 mb-3">
                                    <div class="form-floating">
                                        <input type="url" class="form-control" id="discord_webhook_url" name="discord_webhook_url" placeholder="https://discord.com/api/webhooks/..." value="<?= esc(old('discord_webhook_url', $discordSettings['discord_webhook_url'] ?? '')) ?>">
                                        <label for="discord_webhook_url">Discord Webhook URL</label>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion mb-4" id="accordionDiscordHelp">
                                <div class="accordion-item border-0 shadow-sm rounded-3 overflow-hidden">
                                    <h2 class="accordion-header" id="headingDiscordHelp">
                                        <button class="accordion-button collapsed bg-light-primary text-primary fw-semibold p-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDiscordHelp" aria-expanded="false" aria-controls="collapseDiscordHelp">
                                            <i class="ti ti-help-circle fs-5 me-2"></i> ¿Cómo configurar el Webhook de Discord?
                                        </button>
                                    </h2>
                                    <div id="collapseDiscordHelp" class="accordion-collapse collapse" aria-labelledby="headingDiscordHelp" data-bs-parent="#accordionDiscordHelp">
                                        <div class="accordion-body bg-white text-muted fs-3 p-4">
                                            <h5 class="fw-semibold text-primary mb-2"><i class="ti ti-server fs-4 me-1"></i> 1. Ir a los Ajustes del Servidor</h5>
                                            <ol class="mb-4 ps-3">
                                                <li class="mb-1">Abre Discord y selecciona tu servidor.</li>
                                                <li class="mb-1">Haz clic derecho en el nombre del servidor y elige <strong>Ajustes del servidor</strong> (Server Settings), luego ve a <strong>Integraciones</strong>.</li>
                                            </ol>

                                            <h5 class="fw-semibold text-primary mb-2"><i class="ti ti-plug fs-4 me-1"></i> 2. Crear un Webhook</h5>
                                            <ol class="mb-4 ps-3">
                                                <li class="mb-1">Haz clic en <strong>Ver Webhooks</strong> y luego en <strong>Nuevo Webhook</strong>.</li>
                                                <li class="mb-1">Ponle un nombre (ej. "Proxmox Alert") y elige el canal en el menú desplegable donde quieres recibir las notificaciones.</li>
                                            </ol>

                                            <h5 class="fw-semibold text-primary mb-2"><i class="ti ti-link fs-4 me-1"></i> 3. Copiar la URL</h5>
                                            <p class="mb-2">Pulsa el botón:</p>
                                            <p class="mb-3"><code class="fs-2 text-dark bg-light px-2 py-1 rounded d-inline-block border">Copiar URL del Webhook</code></p>
                                            <p class="mb-0">Pega la URL que empieza por <code class="fs-2 text-primary">https://discord.com/api/webhooks/...</code> en el campo superior y dale a guardar.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <button type="submit" formaction="<?= base_url('alerts-config/test-discord') ?>" class="btn btn-outline-primary px-4">
                                    <i class="ti ti-brand-discord me-1"></i> Probar Discord
                                </button>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="ti ti-device-floppy me-1"></i> Guardar Cambios
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

// Activar la pestaña correcta si el controlador indicó cuál estaba activa
<?php $activeTab = session('active_tab'); ?>
<?php if ($activeTab): ?>
document.addEventListener('DOMContentLoaded', function () {
    const tabEl = document.getElementById('<?= esc($activeTab) ?>-tab');
    if (tabEl) {
        bootstrap.Tab.getOrCreateInstance(tabEl).show();
    }
});
<?php endif; ?>
</script>
