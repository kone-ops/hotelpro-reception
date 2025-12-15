<x-app-layout>
    <x-slot name="header">Créer un Type de Chambre</x-slot>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="modern-card">
                <div class="card-header">
                    <i class="bi bi-plus-circle"></i> Nouveau Type de Chambre
                </div>
                <div class="card-body">
                    <form action="{{ route('hotel.room-types.store') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="bi bi-tag icon-sm me-2"></i>Nom du Type *
                            </label>
                            <input type="text" 
                                   name="name" 
                                   class="form-control form-control-lg @error('name') is-invalid @enderror" 
                                   placeholder="Ex: Chambre Simple, Suite Deluxe, Chambre Familiale"
                                   value="{{ old('name') }}" 
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Choisissez un nom descriptif et unique</small>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-currency-euro icon-sm me-2"></i>Prix par Nuit *
                                </label>
                                <div class="input-group input-group-lg">
                                    <input type="number" 
                                           name="price" 
                                           class="form-control @error('price') is-invalid @enderror" 
                                           placeholder="50000"
                                           step="0.01"
                                           min="0"
                                           value="{{ old('price') }}" 
                                           required>
                                    <span class="input-group-text">FCFA</span>
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-people icon-sm me-2"></i>Capacité (personnes)
                                </label>
                                <input type="number" 
                                       name="capacity" 
                                       class="form-control form-control-lg @error('capacity') is-invalid @enderror" 
                                       placeholder="2"
                                       min="1"
                                       value="{{ old('capacity', 2) }}">
                                @error('capacity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Nombre de personnes pouvant séjourner</small>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="bi bi-file-text icon-sm me-2"></i>Description
                            </label>
                            <textarea name="description" 
                                      class="form-control @error('description') is-invalid @enderror" 
                                      rows="4"
                                      placeholder="Décrivez les équipements, la superficie, les commodités, etc.">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Informations supplémentaires sur ce type de chambre</small>
                        </div>

                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input type="checkbox" 
                                       name="is_available" 
                                       class="form-check-input" 
                                       id="isAvailable" 
                                       {{ old('is_available', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="isAvailable">
                                    <i class="bi bi-check-circle icon-sm me-2"></i>
                                    <strong>Type disponible pour les réservations</strong>
                                </label>
                            </div>
                            <small class="text-muted ms-4">Si désactivé, ce type ne sera pas proposé aux clients</small>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex gap-2">
                            <a href="{{ route('hotel.room-types.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Retour
                            </a>
                            <button type="submit" class="btn btn-primary btn-modern ms-auto">
                                <i class="bi bi-check-circle"></i> Créer le Type
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Astuce -->
            <div class="alert alert-info mt-4">
                <i class="bi bi-lightbulb icon-lg me-2"></i>
                <strong>Astuce :</strong> Après avoir créé ce type, vous pourrez ajouter les chambres depuis la page de gestion des chambres.
            </div>
        </div>
    </div>
</x-app-layout>

