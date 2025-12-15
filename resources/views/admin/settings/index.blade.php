<x-app-layout>
    <x-slot name="header">
        Paramètres de l'Application
    </x-slot>

    <div class="row">
        <div class="col-md-12 mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0"><i class="bi bi-gear me-2"></i>Paramètres de l'Application</h2>
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-danger" onclick="confirmReset()">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Réinitialiser
                    </button>
                    <button type="button" class="btn btn-outline-warning" onclick="clearCache()">
                        <i class="bi bi-trash me-1"></i>Vider le cache
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <form action="{{ route('super.settings.update') }}" method="POST" id="settingsForm">
                @csrf
                @method('PUT')

                <div class="accordion" id="settingsAccordion">
                    @php
                        $groups = [
                            'features' => ['Fonctionnalités', 'bi-stars', 'primary'],
                            'security' => ['Sécurité', 'bi-shield-check', 'danger'],
                            'cache' => ['Cache & Performance', 'bi-lightning', 'warning'],
                            'notifications' => ['Notifications', 'bi-bell', 'info'],
                            'ui' => ['Interface', 'bi-palette', 'secondary'],
                            'public_form' => ['Formulaire Public', 'bi-file-earmark-text', 'success'],
                            'export' => ['Export de Données', 'bi-download', 'primary'],
                            'performance' => ['Optimisation', 'bi-speedometer2', 'secondary'],
                        ];
                        $index = 0;
                    @endphp

                    @foreach($groups as $groupKey => [$groupName, $icon, $color])
                        @if(isset($settings[$groupKey]))
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading{{ $index }}">
                                    <button class="accordion-button {{ $index === 0 ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $index }}">
                                        <i class="{{ $icon }} text-{{ $color }} me-2"></i>
                                        <strong class="text-{{ $color }}">{{ $groupName }}</strong>
                                        <span class="badge bg-{{ $color }} ms-2">{{ $settings[$groupKey]->count() }}</span>
                                    </button>
                                </h2>
                                <div id="collapse{{ $index }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" data-bs-parent="#settingsAccordion">
                                    <div class="accordion-body">
                                        <div class="row">
                                            @foreach($settings[$groupKey] as $setting)
                                                <div class="col-md-6 mb-3">
                                                    <div class="card border-0 shadow-sm h-100">
                                                        <div class="card-body">
                                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                                <div>
                                                                    <label class="form-label fw-bold mb-0">
                                                                        {{ $setting->label }}
                                                                    </label>
                                                                    @if($setting->description)
                                                                        <br><small class="text-muted">
                                                                            {{ $setting->description }}
                                                                        </small>
                                                                    @endif
                                                                </div>
                                                            </div>

                                                            @if($setting->type === 'boolean')
                                                                <div class="form-check form-switch">
                                                                    <input class="form-check-input" type="checkbox" 
                                                                        name="settings[{{ $setting->key }}]" 
                                                                        id="setting_{{ $setting->id }}"
                                                                        value="1"
                                                                        {{ $setting->value == '1' || $setting->value === 'true' ? 'checked' : '' }}>
                                                                    <label class="form-check-label" for="setting_{{ $setting->id }}">
                                                                        <span class="status-label">{{ ($setting->value == '1' || $setting->value === 'true') ? 'Activé' : 'Désactivé' }}</span>
                                                                    </label>
                                                                </div>
                                                            @elseif($setting->type === 'integer')
                                                                <input type="number" 
                                                                    name="settings[{{ $setting->key }}]" 
                                                                    class="form-control" 
                                                                    value="{{ $setting->value }}">
                                                            @elseif($setting->type === 'array')
                                                                <input type="text" 
                                                                    name="settings[{{ $setting->key }}]" 
                                                                    class="form-control" 
                                                                    value="{{ $setting->value }}"
                                                                    placeholder="Valeurs séparées par virgule">
                                                            @else
                                                                <input type="text" 
                                                                    name="settings[{{ $setting->key }}]" 
                                                                    class="form-control" 
                                                                    value="{{ $setting->value }}">
                                                            @endif

                                                            <small class="text-muted mt-2 d-block">
                                                                <code>{{ $setting->key }}</code>
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @php $index++; @endphp
                        @endif
                    @endforeach
                </div>

                <div class="mt-4 d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary" onclick="window.history.back()">
                        <i class="bi bi-arrow-left me-1"></i>Retour
                    </button>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-check-lg me-1"></i>Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        // Mise à jour dynamique des labels
        document.querySelectorAll('.form-check-input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const label = this.parentElement.querySelector('.status-label');
                if (label) {
                    label.textContent = this.checked ? 'Activé' : 'Désactivé';
                }
            });
        });

        function confirmReset() {
            if (confirm('⚠️ Êtes-vous sûr de vouloir réinitialiser TOUS les paramètres aux valeurs par défaut ?\n\nCette action est irréversible !')) {
                window.location.href = '{{ route('super.settings.reset') }}';
            }
        }

        function clearCache() {
            if (confirm('Vider le cache de l\'application ?')) {
                window.location.href = '{{ route('super.settings.clear-cache') }}';
            }
        }

        // Confirmation avant de quitter si modifications
        let formModified = false;
        document.getElementById('settingsForm').addEventListener('change', function() {
            formModified = true;
        });

        document.getElementById('settingsForm').addEventListener('submit', function() {
            formModified = false;
        });

        window.addEventListener('beforeunload', function(e) {
            if (formModified) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
    </script>
    @endpush
</x-app-layout>


