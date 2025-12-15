<x-app-layout>
    <x-slot name="header">Création en Lot de Chambres</x-slot>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="modern-card">
                <div class="card-header">
                    <i class="bi bi-magic"></i> Créer Plusieurs Chambres Rapidement
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="bi bi-lightbulb icon-lg me-2"></i>
                        <strong>Création Rapide</strong><br>
                        <small>Créez plusieurs chambres d'un même type avec une numérotation automatique. Parfait pour ajouter un étage complet ou une aile !</small>
                    </div>

                    <form action="{{ route('hotel.rooms.bulk-store') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="bi bi-door-open icon-sm me-2"></i>Type de Chambre *
                            </label>
                            <select name="room_type_id" 
                                    class="form-select form-select-lg @error('room_type_id') is-invalid @enderror" 
                                    id="roomTypeSelect"
                                    required>
                                <option value="">-- Sélectionnez un type --</option>
                                @foreach($roomTypes as $type)
                                    <option value="{{ $type->id }}" 
                                            data-price="{{ $type->price }}"
                                            {{ old('room_type_id') == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }} - {{ number_format($type->price, 0, ',', ' ') }} FCFA/nuit
                                    </option>
                                @endforeach
                            </select>
                            @error('room_type_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-4">

                        <div class="bg-light p-4 rounded-3 mb-4">
                            <h6 class="mb-3">
                                <i class="bi bi-gear icon-md me-2 text-primary"></i>Configuration de la Génération
                            </h6>
                            
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Quantité *</label>
                                    <input type="number" 
                                           name="quantity" 
                                           class="form-control @error('quantity') is-invalid @enderror" 
                                           placeholder="10"
                                           min="1"
                                           max="100"
                                           value="{{ old('quantity', 10) }}"
                                           id="quantity"
                                           oninput="updatePreview()"
                                           required>
                                    @error('quantity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Max 100</small>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Préfixe</label>
                                    <input type="text" 
                                           name="prefix" 
                                           class="form-control @error('prefix') is-invalid @enderror" 
                                           placeholder="Ex: A, S, 1"
                                           maxlength="10"
                                           value="{{ old('prefix') }}"
                                           id="prefix"
                                           oninput="updatePreview()">
                                    @error('prefix')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Optionnel</small>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Commence à *</label>
                                    <input type="number" 
                                           name="start_number" 
                                           class="form-control @error('start_number') is-invalid @enderror" 
                                           placeholder="101"
                                           min="1"
                                           value="{{ old('start_number', 101) }}"
                                           id="startNumber"
                                           oninput="updatePreview()"
                                           required>
                                    @error('start_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Premier numéro</small>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Étage</label>
                                    <input type="text" 
                                           name="floor" 
                                           class="form-control @error('floor') is-invalid @enderror" 
                                           placeholder="Ex: 1, RDC"
                                           value="{{ old('floor') }}">
                                    @error('floor')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Optionnel</small>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Statut Initial *</label>
                                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                        <option value="available" {{ old('status', 'available') == 'available' ? 'selected' : '' }}>
                                            ✅ Disponible
                                        </option>
                                        <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>
                                            🔧 En Maintenance
                                        </option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Aperçu -->
                        <div class="alert alert-success" id="preview">
                            <i class="bi bi-eye icon-md me-2"></i>
                            <strong>Aperçu :</strong>
                            <div id="previewContent" class="mt-2">
                                <small class="d-inline-flex gap-1 flex-wrap" id="previewBadges"></small>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex gap-2">
                            <a href="{{ route('hotel.rooms.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Retour
                            </a>
                            <button type="submit" class="btn btn-success btn-modern ms-auto">
                                <i class="bi bi-magic"></i> Générer les Chambres
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Aide -->
            <div class="alert alert-info mt-4">
                <i class="bi bi-info-circle icon-lg me-2"></i>
                <strong>Exemples :</strong>
                <ul class="mb-0 mt-2">
                    <li><strong>Étage 1 :</strong> Préfixe vide, commence à 101, quantité 15 → 101, 102... 115</li>
                    <li><strong>Suites :</strong> Préfixe "S", commence à 1, quantité 5 → S1, S2, S3, S4, S5</li>
                    <li><strong>Aile A :</strong> Préfixe "A", commence à 201, quantité 20 → A201... A220</li>
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
function updatePreview() {
    const quantity = parseInt(document.getElementById('quantity').value) || 0;
    const prefix = document.getElementById('prefix').value || '';
    const startNumber = parseInt(document.getElementById('startNumber').value) || 1;
    const previewBadges = document.getElementById('previewBadges');
    
    if (quantity === 0) {
        previewBadges.innerHTML = '<i class="text-muted">Entrez une quantité pour voir l\'aperçu</i>';
        return;
    }
    
    if (quantity > 20) {
        previewBadges.innerHTML = `
            <strong>${quantity} chambres</strong> seront créées : 
            <span class="badge bg-success">${prefix}${startNumber}</span> 
            à 
            <span class="badge bg-success">${prefix}${startNumber + quantity - 1}</span>
        `;
        return;
    }
    
    let html = '';
    for (let i = 0; i < Math.min(quantity, 15); i++) {
        html += `<span class="badge bg-success me-1 mb-1">${prefix}${startNumber + i}</span>`;
    }
    
    if (quantity > 15) {
        html += ` <span class="text-muted">... +${quantity - 15} autres</span>`;
    }
    
    previewBadges.innerHTML = html;
}

// Initialiser l'aperçu au chargement
document.addEventListener('DOMContentLoaded', updatePreview);
</script>

