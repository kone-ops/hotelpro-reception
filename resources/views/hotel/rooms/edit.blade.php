<x-app-layout>
    <x-slot name="header">Modifier la Chambre {{ $room->room_number }}</x-slot>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="modern-card">
                <div class="card-header">
                    <i class="bi bi-pencil"></i> Modifier : Chambre {{ $room->room_number }}
                </div>
                <div class="card-body">
                    <form action="{{ route('hotel.rooms.update', $room) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-hash icon-sm me-2"></i>Numéro de Chambre *
                                </label>
                                <input type="text" 
                                       name="room_number" 
                                       class="form-control form-control-lg @error('room_number') is-invalid @enderror" 
                                       value="{{ old('room_number', $room->room_number) }}" 
                                       required>
                                @error('room_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-door-open icon-sm me-2"></i>Type de Chambre *
                                </label>
                                <select name="room_type_id" 
                                        class="form-select form-select-lg @error('room_type_id') is-invalid @enderror" 
                                        required>
                                    @foreach($roomTypes as $type)
                                        <option value="{{ $type->id }}" {{ old('room_type_id', $room->room_type_id) == $type->id ? 'selected' : '' }}>
                                            {{ $type->name }} ({{ number_format($type->price, 0, ',', ' ') }} FCFA)
                                        </option>
                                    @endforeach
                                </select>
                                @error('room_type_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-building icon-sm me-2"></i>Étage
                                </label>
                                <input type="text" 
                                       name="floor" 
                                       class="form-control form-control-lg @error('floor') is-invalid @enderror" 
                                       value="{{ old('floor', $room->floor) }}">
                                @error('floor')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-toggles icon-sm me-2"></i>Statut *
                                </label>
                                <select name="status" 
                                        class="form-select form-select-lg @error('status') is-invalid @enderror" 
                                        required>
                                    <option value="available" {{ old('status', $room->status) == 'available' ? 'selected' : '' }}>
                                        ✅ Disponible
                                    </option>
                                    <option value="occupied" {{ old('status', $room->status) == 'occupied' ? 'selected' : '' }}>
                                        🔴 Occupée
                                    </option>
                                    <option value="reserved" {{ old('status', $room->status) == 'reserved' ? 'selected' : '' }}>
                                        📅 Réservée
                                    </option>
                                    <option value="maintenance" {{ old('status', $room->status) == 'maintenance' ? 'selected' : '' }}>
                                        🔧 En Maintenance
                                    </option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="bi bi-journal-text icon-sm me-2"></i>Notes Internes
                            </label>
                            <textarea name="notes" 
                                      class="form-control @error('notes') is-invalid @enderror" 
                                      rows="3">{{ old('notes', $room->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-4">

                        <div class="d-flex gap-2">
                            <a href="{{ route('hotel.rooms.index') }}" class="btn btn-outline-secondary">
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

