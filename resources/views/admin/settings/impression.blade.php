@extends('layouts.app')

@section('title', 'Paramètres d\'Impression')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Paramètres d'Impression</h3>
                    <div class="btn-group">
                        <a href="{{ auth()->user()->hasRole('super-admin') ? route('super.settings.impression.reset') : route('hotel.settings.impression.reset') }}" class="btn btn-outline-warning" 
                           onclick="return confirm('Réinitialiser les paramètres par défaut ?')">
                            <i class="bi bi-arrow-counterclockwise"></i> Réinitialiser
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ auth()->user()->hasRole('super-admin') ? route('super.settings.impression.update') : route('hotel.settings.impression.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        @if(auth()->user()->hasRole('super-admin') && isset($hotels))
                            <div class="mb-4 card border-0 shadow-sm">
                                <div class="card-body">
                                    <label for="hotel_id" class="form-label fw-bold">
                                        <i class="bi bi-building text-primary me-2"></i>
                                        Sélectionner l'hôtel
                                    </label>
                                    <select class="form-select" id="hotel_id" name="hotel_id_select" 
                                            onchange="window.location.href='{{ route('super.settings.impression') }}?hotel_id=' + this.value">
                                        <option value="">Paramètres globaux (par défaut)</option>
                                        @foreach($hotels as $hotel)
                                            <option value="{{ $hotel->id }}" 
                                                    {{ (isset($hotelId) && $hotelId == $hotel->id) ? 'selected' : '' }}>
                                                {{ $hotel->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted d-block mt-2">Les paramètres sont spécifiques à chaque hôtel</small>
                                </div>
                            </div>
                            
                            @if(isset($hotelId))
                                <input type="hidden" name="hotel_id" value="{{ $hotelId }}">
                            @endif
                        @endif
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm mb-4">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h5 class="card-title mb-0">
                                                <i class="bi bi-pen text-primary me-2"></i>
                                                Signature Formulaire Public
                                            </h5>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="settings[signature_formulaire]" 
                                                       id="signature_formulaire"
                                                       value="1"
                                                       {{ ($settingValues['signature_formulaire'] ?? $settingValues['signature_obligatoire'] ?? '0') == '1' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="signature_formulaire">
                                                    {{ ($settingValues['signature_formulaire'] ?? $settingValues['signature_obligatoire'] ?? '0') == '1' ? 'Activé' : 'Désactivé' }}
                                                </label>
                                            </div>
                                        </div>
                                        <p class="text-muted mb-0">
                                            <i class="bi bi-info-circle me-1"></i>
                                            Exiger une signature numérique dans le formulaire de pré-réservation
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm mb-4">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h5 class="card-title mb-0">
                                                <i class="bi bi-file-earmark-text text-primary me-2"></i>
                                                Signature Fiche de Police
                                            </h5>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="settings[signature_fiche_police]" 
                                                       id="signature_fiche_police"
                                                       value="1"
                                                       {{ ($settingValues['signature_fiche_police'] ?? '1') == '1' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="signature_fiche_police">
                                                    {{ ($settingValues['signature_fiche_police'] ?? '1') == '1' ? 'Activé' : 'Désactivé' }}
                                                </label>
                                            </div>
                                        </div>
                                        <p class="text-muted mb-0">
                                            <i class="bi bi-info-circle me-1"></i>
                                            Afficher la signature du client sur la fiche de police
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm mb-4">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h5 class="card-title mb-0">
                                                <i class="bi bi-printer text-success me-2"></i>
                                                Impression Automatique Fiche Police
                                            </h5>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="settings[auto_print_police]" 
                                                       id="auto_print_police"
                                                       value="1"
                                                       {{ $settingValues['auto_print_police'] == '1' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="auto_print_police">
                                                    {{ $settingValues['auto_print_police'] == '1' ? 'Activé' : 'Désactivé' }}
                                                </label>
                                            </div>
                                        </div>
                                        <p class="text-muted mb-0">
                                            Imprimer automatiquement la fiche police après validation
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm mb-4">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h5 class="card-title mb-0">
                                                <i class="bi bi-hand-index text-warning me-2"></i>
                                                Impression Manuelle Uniquement
                                            </h5>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="settings[manual_print_only]" 
                                                       id="manual_print_only"
                                                       value="1"
                                                       {{ $settingValues['manual_print_only'] == '1' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="manual_print_only">
                                                    {{ $settingValues['manual_print_only'] == '1' ? 'Activé' : 'Désactivé' }}
                                                </label>
                                            </div>
                                        </div>
                                        <p class="text-muted mb-0">
                                            Désactiver l'impression automatique et afficher un bouton manuel
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm mb-4">
                                    <div class="card-body">
                                        <h5 class="card-title mb-3">
                                            <i class="bi bi-info-circle text-info me-2"></i>
                                            Informations
                                        </h5>
                                        <div class="alert alert-info">
                                            <small>
                                                <strong>Note:</strong> Ces paramètres contrôlent le comportement d'impression 
                                                dans votre système de gestion d'hôtel. Assurez-vous que les imprimantes 
                                                sont correctement configurées avant d'activer l'impression automatique.
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ auth()->user()->hasRole('super-admin') ? route('super.printers.index') : route('hotel.printers.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Retour aux imprimantes
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Enregistrer les paramètres
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Mise à jour des labels des switches
document.querySelectorAll('.form-check-input').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const label = this.nextElementSibling;
        label.textContent = this.checked ? 'Activé' : 'Désactivé';
    });
});
</script>
@endpush
