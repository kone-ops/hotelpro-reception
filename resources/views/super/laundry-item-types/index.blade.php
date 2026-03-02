<x-app-layout>
    <x-slot name="header">Types de linge — {{ $hotel->name }}</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0"><i class="bi bi-tags me-2"></i>Types de linge (Buanderie)</h4>
            <p class="text-muted small mb-0">Définir les types de linge pour l'hôtel <strong>{{ $hotel->name }}</strong>. Ils seront utilisés dans les collectes de linge par le rôle Buanderie.</p>
        </div>
        <a href="{{ route('super.hotels.show', $hotel) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Retour à l'hôtel
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-5 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Ajouter un type de linge</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('super.hotels.laundry-item-types.store', $hotel) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Nom <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" required maxlength="255" placeholder="ex. Draps housse" value="{{ old('name') }}">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Code (optionnel)</label>
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" maxlength="50" placeholder="ex. DRAP" value="{{ old('code') }}">
                            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ordre d'affichage</label>
                            <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}" min="0">
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Créer le type</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-list me-2"></i>Liste des types ({{ $itemTypes->count() }})</h6>
                </div>
                <div class="card-body p-0">
                    @if($itemTypes->isEmpty())
                        <div class="card-body">
                            <x-super.empty-table
                                icon="bi-tags"
                                title="Aucun type de linge"
                                message="Ajoutez des types de linge pour que la buanderie puisse saisir les quantités dans les collectes."
                            />
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-hover table-striped align-middle mb-0 super-admin-table" aria-label="Types de linge pour la buanderie">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col"><i class="bi bi-tag me-1 text-primary"></i>Nom</th>
                                        <th scope="col"><i class="bi bi-upc me-1 text-primary"></i>Code</th>
                                        <th scope="col"><i class="bi bi-sort-numeric-down me-1 text-primary"></i>Ordre</th>
                                        <th scope="col" class="text-end" width="200">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($itemTypes as $it)
                                        <tr>
                                            <td>{{ $it->name }}</td>
                                            <td><code>{{ $it->code ?? '-' }}</code></td>
                                            <td>{{ $it->sort_order }}</td>
                                            <td class="text-end">
                                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal{{ $it->id }}">Modifier</button>
                                                <form action="{{ route('super.hotels.laundry-item-types.destroy', [$hotel, $it]) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce type de linge ? Les lignes de collecte qui l\'utilisent pourront être affectées.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer</button>
                                                </form>
                                            </td>
                                        </tr>
                                        <div class="modal fade" id="editModal{{ $it->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="{{ route('super.hotels.laundry-item-types.update', [$hotel, $it]) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Modifier le type de linge</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label">Nom</label>
                                                                <input type="text" name="name" class="form-control" required value="{{ old('name', $it->name) }}">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Code</label>
                                                                <input type="text" name="code" class="form-control" value="{{ old('code', $it->code) }}">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Ordre</label>
                                                                <input type="number" name="sort_order" class="form-control" value="{{ $it->sort_order }}" min="0">
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
