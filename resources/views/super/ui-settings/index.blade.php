<x-app-layout>
    <x-slot name="header">Paramètres de l'Interface</x-slot>
    
    <meta name="csrf-token" content="{{ csrf_token() }}">

@php
function getCategoryIcon($category) {
    $icons = [
        'fonts' => 'fonts',
        'spacing' => 'arrows-angle-expand',
        'icons' => 'star',
        'buttons' => 'mouse2',
        'forms' => 'input-cursor-text',
        'navigation' => 'compass',
    ];
    return $icons[$category] ?? 'gear';
}

function getCategoryLabel($category) {
    $labels = [
        'fonts' => 'Polices & Typographie',
        'spacing' => 'Espacements & Dimensions',
        'icons' => 'Icônes',
        'buttons' => 'Boutons',
        'forms' => 'Formulaires',
        'navigation' => 'Navigation',
    ];
    return $labels[$category] ?? ucfirst($category);
}
@endphp
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="bi bi-sliders me-2"></i>
                        Personnalisation de l'Interface
                    </h3>
                    <div class="btn-group">
                        <button type="button" id="previewBtn" class="btn btn-outline-info">
                            <i class="bi bi-eye me-1"></i> Aperçu en direct
                        </button>
                        <form action="{{ route('super.ui-settings.reset') }}" method="POST" style="display: inline;" id="resetForm">
                            @csrf
                            <button type="submit" class="btn btn-outline-warning" 
                                    onclick="return confirm('Réinitialiser tous les paramètres aux valeurs par défaut ?')">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Réinitialiser
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Information :</strong> Ces paramètres permettent de personnaliser l'apparence de l'interface pour tous les utilisateurs. 
                        Les modifications sont appliquées immédiatement après la sauvegarde.
                    </div>

                    <form action="{{ route('super.ui-settings.update') }}" method="POST" id="uiSettingsForm">
                        @csrf
                        @method('PUT')

                        @foreach($settings as $category => $categorySettings)
                            <div class="mb-4">
                                <h4 class="border-bottom pb-2 mb-3">
                                    <i class="bi bi-{{ getCategoryIcon($category) }} me-2 text-primary"></i>
                                    {{ getCategoryLabel($category) }}
                                </h4>
                                
                                <div class="row">
                                    @foreach($categorySettings as $setting)
                                        <div class="col-md-6 col-lg-4 mb-3">
                                            <div class="card border h-100">
                                                <div class="card-body">
                                                    <label for="{{ $setting->key }}" class="form-label fw-bold">
                                                        {{ $setting->label }}
                                                    </label>
                                                    
                                                    @if($setting->description)
                                                        <p class="text-muted small mb-2">{{ $setting->description }}</p>
                                                    @endif
                                                    
                                                    <div class="input-group">
                                                        <input 
                                                            type="number" 
                                                            step="0.01"
                                                            class="form-control setting-input" 
                                                            id="{{ $setting->key }}" 
                                                            name="settings[{{ $setting->key }}]" 
                                                            value="{{ $setting->value }}"
                                                            min="{{ $setting->min_value }}"
                                                            max="{{ $setting->max_value }}"
                                                            data-key="{{ $setting->key }}"
                                                            data-unit="{{ $setting->unit }}"
                                                        >
                                                        <span class="input-group-text">{{ $setting->unit }}</span>
                                                    </div>
                                                    
                                                    <div class="d-flex justify-content-between mt-2">
                                                        <small class="text-muted">Min: {{ $setting->min_value }}{{ $setting->unit }}</small>
                                                        <small class="text-muted">Max: {{ $setting->max_value }}{{ $setting->unit }}</small>
                                                    </div>
                                                    
                                                    <input 
                                                        type="range" 
                                                        class="form-range mt-2 setting-range" 
                                                        min="{{ $setting->min_value }}" 
                                                        max="{{ $setting->max_value }}" 
                                                        step="0.01"
                                                        value="{{ $setting->value }}"
                                                        data-target="{{ $setting->key }}"
                                                    >
                                                    
                                                    <div class="d-flex gap-2 mt-2">
                                                        <button 
                                                            type="button" 
                                                            class="btn btn-sm btn-success flex-fill save-single"
                                                            data-key="{{ $setting->key }}"
                                                            title="Sauvegarder ce paramètre"
                                                        >
                                                            <i class="bi bi-check-lg me-1"></i> Sauvegarder
                                                        </button>
                                                        <button 
                                                            type="button" 
                                                            class="btn btn-sm btn-outline-secondary reset-single"
                                                            data-key="{{ $setting->key }}"
                                                            data-default="{{ $setting->default_value }}"
                                                            title="Réinitialiser à la valeur par défaut"
                                                        >
                                                            <i class="bi bi-arrow-counterclockwise"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                        
                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('super.dashboard') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Retour au tableau de bord
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Enregistrer les modifications
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Synchroniser les inputs range avec les inputs number
    document.querySelectorAll('.setting-range').forEach(range => {
        range.addEventListener('input', function() {
            const targetId = this.dataset.target;
            const input = document.getElementById(targetId);
            if (input) {
                input.value = this.value;
                updatePreview();
            }
        });
    });
    
    // Synchroniser les inputs number avec les ranges
    document.querySelectorAll('.setting-input').forEach(input => {
        input.addEventListener('input', function() {
            const key = this.dataset.key;
            const range = document.querySelector(`.setting-range[data-target="${key}"]`);
            if (range) {
                range.value = this.value;
            }
            updatePreview();
        });
    });
    
    // Boutons de sauvegarde individuelle
    document.querySelectorAll('.save-single').forEach(btn => {
        btn.addEventListener('click', function() {
            const key = this.dataset.key;
            const input = document.getElementById(key);
            const range = document.querySelector(`.setting-range[data-target="${key}"]`);
            
            if (!input) return;
            
            const value = parseFloat(input.value);
            const setting = {
                key: key,
                min: parseFloat(input.min),
                max: parseFloat(input.max)
            };
            
            // Valider les limites
            if (value < setting.min) {
                input.value = setting.min;
            } else if (value > setting.max) {
                input.value = setting.max;
            }
            
            // Sauvegarder via AJAX
            const formData = new FormData();
            formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value);
            formData.append('_method', 'PUT');
            formData.append('settings[' + key + ']', input.value);
            
            // Afficher un indicateur de chargement
            const originalHtml = this.innerHTML;
            this.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Enregistrement...';
            this.disabled = true;
            
            fetch('{{ route("super.ui-settings.update") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData
            })
            .then(response => {
                // Vérifier le type de contenu de la réponse
                const contentType = response.headers.get('content-type');
                
                // Si ce n'est pas du JSON, c'est probablement une redirection HTML
                if (!contentType || !contentType.includes('application/json')) {
                    // Si c'est une redirection (302, 303, etc.), considérer comme succès
                    if (response.status >= 200 && response.status < 400) {
                        return { success: true, message: 'Paramètre sauvegardé avec succès' };
                    }
                    throw new Error('Réponse inattendue du serveur (status: ' + response.status + ')');
                }
                
                // Vérifier si la réponse est OK
                if (!response.ok) {
                    // Essayer de parser l'erreur JSON
                    return response.json().then(err => {
                        const errorMsg = err.message || (err.errors ? JSON.stringify(err.errors) : 'Erreur lors de la sauvegarde');
                        throw new Error(errorMsg);
                    }).catch(() => {
                        throw new Error('Erreur ' + response.status + ': ' + response.statusText);
                    });
                }
                // Parser la réponse JSON en nettoyant d'abord les caractères invisibles
                return response.text().then(text => {
                    // Nettoyer les caractères BOM et autres caractères invisibles
                    const cleanedText = text.trim().replace(/^\uFEFF/, '').replace(/^[\u200B-\u200D\uFEFF]/, '');
                    try {
                        return JSON.parse(cleanedText);
                    } catch (e) {
                        console.error('Erreur de parsing JSON:', e, 'Texte reçu:', cleanedText);
                        throw new Error('Réponse JSON invalide du serveur');
                    }
                });
            })
            .then(data => {
                // Vérifier si la sauvegarde a réussi
                if (data.success !== false) {
                    // Afficher un message de succès
                    this.innerHTML = '<i class="bi bi-check-lg me-1"></i> Sauvé!';
                    this.classList.remove('btn-success');
                    this.classList.add('btn-outline-success');
                    
                    // Appliquer immédiatement
                    const cssVar = key.replace(/_/g, '-');
                    const unit = input.dataset.unit || '';
                    document.documentElement.style.setProperty(`--${cssVar}`, input.value + unit);
                    
                    setTimeout(() => {
                        this.innerHTML = originalHtml;
                        this.classList.remove('btn-outline-success');
                        this.classList.add('btn-success');
                        this.disabled = false;
                    }, 2000);
                } else {
                    throw new Error(data.message || 'Erreur lors de la sauvegarde');
                }
            })
            .catch(error => {
                console.error('Erreur lors de la sauvegarde:', error);
                const errorMessage = error.message || 'Erreur lors de la sauvegarde';
                
                this.innerHTML = '<i class="bi bi-x-lg me-1"></i> Erreur';
                this.classList.remove('btn-success');
                this.classList.add('btn-danger');
                this.title = errorMessage;
                
                // Afficher une alerte pour informer l'utilisateur
                alert('Erreur: ' + errorMessage);
                
                setTimeout(() => {
                    this.innerHTML = originalHtml;
                    this.classList.remove('btn-danger');
                    this.classList.add('btn-success');
                    this.disabled = false;
                    this.title = '';
                }, 3000);
            });
        });
    });
    
    // Boutons de réinitialisation individuelle
    document.querySelectorAll('.reset-single').forEach(btn => {
        btn.addEventListener('click', function() {
            const key = this.dataset.key;
            const defaultValue = this.dataset.default;
            const input = document.getElementById(key);
            const range = document.querySelector(`.setting-range[data-target="${key}"]`);
            
            if (input) {
                input.value = defaultValue;
                if (range) range.value = defaultValue;
                updatePreview();
            }
        });
    });
    
    // Prévisualisation en direct
    let previewMode = false;
    const previewBtn = document.getElementById('previewBtn');
    
    previewBtn.addEventListener('click', function() {
        previewMode = !previewMode;
        if (previewMode) {
            this.innerHTML = '<i class="bi bi-eye-slash me-1"></i> Désactiver aperçu';
            this.classList.remove('btn-outline-info');
            this.classList.add('btn-info');
            updatePreview();
        } else {
            this.innerHTML = '<i class="bi bi-eye me-1"></i> Aperçu en direct';
            this.classList.remove('btn-info');
            this.classList.add('btn-outline-info');
            resetPreview();
        }
    });
    
    function updatePreview() {
        if (!previewMode) return;
        
        const root = document.documentElement;
        
        document.querySelectorAll('.setting-input').forEach(input => {
            const key = input.dataset.key;
            const value = input.value;
            const unit = input.dataset.unit || '';
            const cssVar = key.replace(/_/g, '-');
            
            root.style.setProperty(`--${cssVar}`, value + unit);
        });
    }
    
    function resetPreview() {
        const root = document.documentElement;
        document.querySelectorAll('.setting-input').forEach(input => {
            const key = input.dataset.key;
            const cssVar = key.replace(/_/g, '-');
            root.style.removeProperty(`--${cssVar}`);
        });
    }
});
</script>
</x-app-layout>


