<div class="container-fluid">
    <div class="card shadow-none position-relative overflow-hidden mb-4">
        <div class="card-body px-4 py-3">
            <div class="row align-items-center">
                <div class="col-12">
                    <h4 class="fw-semibold mb-8">Configuración de Inteligencia Artificial</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a class="text-muted text-decoration-none" href="<?= base_url() ?>">Inicio</a></li>
                            <li class="breadcrumb-item" aria-current="page">Gestión de IA</li>
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
                    <?php if (session()->getFlashdata('errors')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                    <li><?= esc($error) ?></li>
                                <?php endforeach ?>
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif ?>

                    <form action="<?= base_url('ai/store') ?>" method="post">
                        <?= csrf_field() ?>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="provider" name="provider" onchange="updateProviderInfo()">
                                        <option value="">Seleccionar proveedor...</option>
                                        <option value="gemini" data-model="gemini-2.0-flash" <?= ($settings['provider'] ?? '') === 'gemini' ? 'selected' : '' ?>>Google Gemini</option>
                                        <option value="chatgpt" data-model="gpt-4o-mini" <?= ($settings['provider'] ?? '') === 'chatgpt' ? 'selected' : '' ?>>OpenAI ChatGPT</option>
                                        <option value="ollama" data-model="llama3" <?= ($settings['provider'] ?? '') === 'ollama' ? 'selected' : '' ?>>Ollama (Local)</option>
                                    </select>
                                    <label for="provider">Proveedor de IA</label>
                                </div>
                            </div>

                            <!-- Autenticación (Dinámica) -->
                            <div class="col-md-6 d-none" id="api_key_section">
                                <div class="form-floating mb-3 position-relative">
                                    <input type="password" class="form-control" id="api_key" name="api_key" placeholder="API Key" value="">
                                    <label for="api_key">API Key / Token</label>
                                    <button class="btn position-absolute top-50 end-0 translate-middle-y me-2 border-0" type="button" onclick="togglePassword()">
                                        <i class="ti ti-eye fs-5"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="col-md-6 d-none" id="ollama_section">
                                <div class="row g-2">
                                    <div class="col-8">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="ollama_host" name="ollama_host" placeholder="http://localhost" value="<?= esc($settings['ollama_host'] ?? 'http://localhost') ?>">
                                            <label for="ollama_host">Host Ollama</label>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="form-floating">
                                            <input type="number" class="form-control" id="ollama_port" name="ollama_port" placeholder="11434" value="<?= esc($settings['ollama_port'] ?? '11434') ?>">
                                            <label for="ollama_port">Puerto</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="form-floating mb-2">
                                    <select class="form-select" id="model" name="model">
                                        <option value="<?= esc($settings['model'] ?? '') ?>" selected><?= esc($settings['model'] ?? 'Seleccionar modelo...') ?></option>
                                    </select>
                                    <label for="model">Modelo</label>
                                </div>
                                <div class="form-text text-muted" id="model_help">
                                    <i class="ti ti-info-circle me-1"></i> Los modelos se sincronizan automáticamente al configurar el proveedor y la autenticación.
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mt-4 border-top pt-4">
                            <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center justify-content-end gap-2">
                                <button type="submit" formaction="<?= base_url('ai/test') ?>" class="btn btn-outline-primary font-medium px-4" id="test-btn">
                                    <div class="d-flex align-items-center justify-content-center">
                                        <i class="ti ti-wand me-2 fs-4"></i>
                                        Probar Generación
                                    </div>
                                </button>
                                <button type="submit" class="btn btn-primary font-medium px-4">
                                    <div class="d-flex align-items-center justify-content-center">
                                        <i class="ti ti-device-floppy me-2 fs-4"></i>
                                        Guardar Cambios
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
function togglePassword() {
    const input = document.getElementById('api_key');
    input.type = input.type === 'password' ? 'text' : 'password';
}

function updateProviderInfo() {
    const select = document.getElementById('provider');
    const provider = select.value;
    const modelSelect = document.getElementById('model');
    const modelHelp = document.getElementById('model_help');
    const apiKeySection = document.getElementById('api_key_section');
    const ollamaSection = document.getElementById('ollama_section');
    const testBtn = document.getElementById('test-btn');
    
    // Guardamos el modelo actual antes de limpiar
    const savedModel = '<?= esc($settings['model'] ?? '') ?>';
    const currentModel = modelSelect.value || savedModel;
    
    // Opciones estáticas básicas
    const geminiModels = ['gemini-2.0-flash', 'gemini-1.5-flash-latest', 'gemini-1.5-flash', 'gemini-1.5-pro'];
    const chatGptModels = ['gpt-4o-mini', 'gpt-4o', 'gpt-3.5-turbo'];
    
    modelHelp.innerHTML = '<i class="ti ti-info-circle me-1"></i> Los modelos se sincronizan automáticamente al configurar el proveedor.';

    // Reset visibilidad usando clases de Bootstrap para estabilidad
    apiKeySection.classList.add('d-none');
    ollamaSection.classList.add('d-none');
    testBtn.classList.add('d-none');

    if (provider === 'gemini' || provider === 'chatgpt') {
        apiKeySection.classList.remove('d-none');
        testBtn.classList.remove('d-none');
        
        const models = (provider === 'gemini') ? geminiModels : chatGptModels;
        modelSelect.innerHTML = '';
        
        // Añadir opción guardada si no está en la lista
        if (currentModel && !models.includes(currentModel)) {
            modelSelect.add(new Option(currentModel, currentModel, true, true));
        }
        
        models.forEach(m => {
            const opt = new Option(m, m);
            if (m === currentModel) opt.selected = true;
            modelSelect.add(opt);
        });
        
    } else if (provider === 'ollama') {
        ollamaSection.classList.remove('d-none');
        testBtn.classList.remove('d-none');
    }

    // Sincronizar automáticamente si hay datos suficientes
    if (provider) fetchModels();
}

function fetchModels() {
    const provider = document.getElementById('provider').value;
    const apiKey = document.getElementById('api_key').value;
    const host = document.getElementById('ollama_host').value;
    const port = document.getElementById('ollama_port').value;
    const modelSelect = document.getElementById('model');
    const modelHelp = document.getElementById('model_help');
    
    if (!provider) return;

    const currentModel = modelSelect.value;
    modelHelp.innerHTML = '<span class="text-info"><i class="ti ti-loader"></i> Sincronizando modelos...</span>';

    const formData = new FormData();
    formData.append('provider', provider);
    formData.append('api_key', apiKey);
    formData.append('host', host);
    formData.append('port', port);
    formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

    fetch('<?= base_url("ai/get-models") ?>', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            '<?= config('Security')->headerName ?>': '<?= csrf_hash() ?>'
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            modelSelect.innerHTML = '';
            data.models.forEach(m => {
                const opt = new Option(m, m);
                if (m === currentModel) opt.selected = true;
                modelSelect.add(opt);
            });
            
            if (currentModel && !data.models.includes(currentModel)) {
                const opt = new Option(currentModel, currentModel);
                opt.selected = true;
                modelSelect.add(opt);
            }
            
            modelHelp.innerHTML = `<span class="text-success"><i class="ti ti-check"></i> ${data.models.length} modelos sincronizados correctamente.</span>`;
        } else {
            modelHelp.innerHTML = `<span class="text-danger"><i class="ti ti-alert-circle"></i> ${data.message}</span>`;
        }
    })
    .catch(err => {
        modelHelp.innerHTML = `<span class="text-danger"><i class="ti ti-alert-circle"></i> Error de conexión con el servidor.</span>`;
    });
}

// Detectar cambios para recargar modelos automáticamente
document.getElementById('api_key').addEventListener('blur', fetchModels);
document.getElementById('ollama_host').addEventListener('blur', fetchModels);
document.getElementById('ollama_port').addEventListener('blur', fetchModels);

// Ejecutar al cargar
document.addEventListener('DOMContentLoaded', updateProviderInfo);
</script>
