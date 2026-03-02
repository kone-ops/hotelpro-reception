<x-app-layout>
    <x-slot name="header">Modifier le Type de Chambre</x-slot>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="modern-card">
                <div class="card-header">
                    <i class="bi bi-pencil"></i> Modifier : {{ $roomType->name }}
                </div>
                <div class="card-body">
                    <form action="{{ route('hotel.room-types.update', $roomType) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="bi bi-tag icon-sm me-2"></i>Nom du Type *
                            </label>
                            <input type="text" 
                                   name="name" 
                                   class="form-control form-control-lg @error('name') is-invalid @enderror" 
                                   value="{{ old('name', $roomType->name) }}" 
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
                                           step="0.01"
                                           min="0"
                                           value="{{ old('price', $roomType->price) }}" 
                                           required>
                                    <span class="input-group-text">FCFA</span>
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-people icon-sm me-2"></i>Capacité
                                </label>
                                <input type="number" 
                                       name="capacity" 
                                       class="form-control form-control-lg @error('capacity') is-invalid @enderror" 
                                       min="1"
                                       value="{{ old('capacity', $roomType->capacity) }}">
                                @error('capacity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="bi bi-file-text icon-sm me-2"></i>Description
                            </label>
                            <textarea name="description" 
                                      class="form-control @error('description') is-invalid @enderror" 
                                      rows="4">{{ old('description', $roomType->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input type="checkbox" 
                                       name="is_available" 
                                       class="form-check-input" 
                                       id="isAvailable" 
                                       {{ old('is_available', $roomType->is_available) ? 'checked' : '' }}>
                                <label class="form-check-label" for="isAvailable">
                                    <i class="bi bi-check-circle icon-sm me-2"></i>
                                    <strong>Type disponible pour les enregistrements</strong>
                                </label>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex gap-2">
                            <a href="{{ route('hotel.room-types.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Retour
                            </a>
                            <button type="submit" class="btn btn-primary btn-modern ms-auto">
                                <i class="bi bi-check-circle"></i> Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

