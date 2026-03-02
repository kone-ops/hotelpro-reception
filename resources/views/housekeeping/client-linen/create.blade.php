<x-app-layout>
    <x-slot name="header">Linge client – Dépôt (trouvé en chambre)</x-slot>

    <div class="mb-4">
        <h4 class="mb-0"><i class="bi bi-basket me-2"></i>Enregistrer un dépôt de linge client</h4>
        <p class="text-muted small mb-0">{{ $hotel->name }} – Linge trouvé en chambre, à récupérer par la buanderie</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent">
            <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Nouveau dépôt – Linge client trouvé en chambre</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('housekeeping.client-linen.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Chambre <span class="text-danger">*</span></label>
                        <select name="room_id" class="form-select" required>
                            <option value="">— Choisir une chambre —</option>
                            @foreach($rooms as $r)
                                <option value="{{ $r->id }}" {{ old('room_id') == $r->id ? 'selected' : '' }}>
                                    Chambre {{ $r->room_number }}@if($r->floor) – Étage {{ $r->floor }}@endif
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Chambre où le linge a été trouvé ou concernée</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nom du client (optionnel)</label>
                        <input type="text" name="client_name" class="form-control" value="{{ old('client_name') }}" placeholder="Ex: M. Dupont" maxlength="255">
                        <small class="text-muted">Si connu (ancien occupant, client actuel…)</small>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description du linge <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control" rows="3" required placeholder="Ex: chemise blanche, pantalon noir, 2 serviettes…">{{ old('description') }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Notes (optionnel)</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Emplacement dans la chambre, état, remarques…">{{ old('notes') }}</textarea>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Enregistrer le dépôt</button>
                        <a href="{{ route('housekeeping.dashboard') }}" class="btn btn-outline-secondary">Annuler</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <p class="text-muted small mt-3">
        <i class="bi bi-info-circle me-1"></i>La buanderie sera notifiée et retrouvera ce linge dans la liste « Linge client – Chambre ». Le client pourra le récupérer via la réception une fois traité.
    </p>
</x-app-layout>
