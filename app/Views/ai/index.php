<div class="container-fluid">
    <div class="card shadow-none position-relative overflow-hidden mb-4">
        <div class="card-body px-4 py-3">
            <div class="row align-items-center">
                <div class="col-12">
                    <h4 class="fw-semibold mb-8"><i class="ti ti-robot me-2"></i>Configuración de Inteligencia Artificial</h4>
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
                    <form action="<?= base_url('ai/store') ?>" method="post">
                        <?= csrf_field() ?>
                        
                        <h5 class="mb-3">Proveedor de IA</h5>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="provider" name="provider" onchange="updateProviderInfo()">
                                        <option value="">Seleccionar proveedor...</option>
                                        <option value="gemini" data-model="gemini-2.0-flash" <?= ($settings['provider'] ?? '') === 'gemini' ? 'selected' : '' ?>>Google Gemini (OpenAI Compatible)</option>
                                        <option value="chatgpt" data-model="gpt-4o-mini" <?= ($settings['provider'] ?? '') === 'chatgpt' ? 'selected' : '' ?>>OpenAI ChatGPT</option>
                                        <option value="ollama" data-model="llama3" <?= ($settings['provider'] ?? '') === 'ollama' ? 'selected' : '' ?>>Ollama (Local)</option>
                                    </select>
                                    <label for="provider">Proveedor</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="model" name="model" placeholder="gemini-2.0-flash" value="<?= esc($settings['model'] ?? '') ?>">
                                    <label for="model">Modelo</label>
                                    <div class="form-text" id="model_help">Ej: gemini-2.0-flash, gpt-4o-mini, llama3, etc.</div>
                                </div>
                            </div>
                        </div>

                        <div id="api_key_section" class="row mb-4" style="display: none;">
                            <div class="col-12">
                                <h5 class="mb-3 border-top pt-4">Autenticación</h5>
                                <div class="form-floating mb-3 position-relative">
                                    <input type="password" class="form-control" id="api_key" name="api_key" placeholder="API Key" value="<?= esc($settings['api_key'] ?? '') ?>">
                                    <label for="api_key">API Key / Token</label>
                                    <button class="btn position-absolute top-50 end-0 translate-middle-y me-2 border-0" type="button" onclick="togglePassword()">
                                        <i class="ti ti-eye fs-5"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div id="ollama_section" class="row mb-4" style="display: none;">
                            <div class="col-12">
                                <h5 class="mb-3 border-top pt-4">Configuración Ollama</h5>
                            </div>
                            <div class="col-md-8">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="ollama_host" name="ollama_host" placeholder="http://localhost" value="<?= esc($settings['ollama_host'] ?? 'http://localhost') ?>">
                                    <label for="ollama_host">Host de Ollama (IP o Dominio)</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <input type="number" class="form-control" id="ollama_port" name="ollama_port" placeholder="11434" value="<?= esc($settings['ollama_port'] ?? '11434') ?>">
                                    <label for="ollama_port">Puerto</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mt-4 border-top pt-4">
                            <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center justify-content-end gap-2">
                                    <button type="submit" formaction="<?= base_url('ai/test') ?>" class="btn btn-outline-primary font-medium px-4" id="test-btn">
                                        <div class="d-flex align-items-center justify-content-center">
                                            <i class="ti ti-sparkles me-2 fs-4"></i>
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
    const option = select.options[select.selectedIndex];
    const provider = select.value;
    const modelInput = document.getElementById('model');
    
    // Auto-completar modelo si está vacío
    if (provider && !modelInput.value) {
        modelInput.value = option.getAttribute('data-model');
    }

    const apiKeySection = document.getElementById('api_key_section');
    const ollamaSection = document.getElementById('ollama_section');
    const testBtn = document.getElementById('test-btn');

    if (provider === 'gemini' || provider === 'chatgpt') {
        apiKeySection.style.display = 'flex';
        ollamaSection.style.display = 'none';
        testBtn.style.display = 'block';
    } else if (provider === 'ollama') {
        apiKeySection.style.display = 'none';
        ollamaSection.style.display = 'flex';
        testBtn.style.display = 'block';
    } else {
        apiKeySection.style.display = 'none';
        ollamaSection.style.display = 'none';
        testBtn.style.display = 'none';
    }
}

// Ejecutar al cargar
document.addEventListener('DOMContentLoaded', updateProviderInfo);
</script>
