<x-app-layout>
    <x-slot name="header">Types et catégories de pannes</x-slot>

    <div class="mb-4">
        <a href="{{ route('maintenance.pannes.index') }}" class="btn btn-outline-secondary btn-sm mb-2"><i class="bi bi-arrow-left me-1"></i>Retour aux pannes</a>
        <h4 class="mb-0">Types et catégories de pannes</h4>
        <p class="text-muted small mb-0">{{ $hotel->name }}</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <span>Catégories</span>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">Ajouter</button>
                </div>
                <div class="card-body">
                    @forelse($categories as $cat)
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span>{{ $cat->name }}</span>
                            <div class="d-flex gap-1">
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editCategoryModal{{ $cat->id }}">Modifier</button>
                                <form action="{{ route('maintenance.panne-categories.destroy', $cat) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cette catégorie ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer</button>
                                </form>
                            </div>
                        </div>
                        <div class="modal fade" id="editCategoryModal{{ $cat->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('maintenance.panne-categories.update', $cat) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title">Modifier la catégorie</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <label class="form-label">Nom</label>
                                            <input type="text" name="name" class="form-control" value="{{ $cat->name }}" required maxlength="100">
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted small mb-0">Aucune catégorie. Ajoutez-en une pour pouvoir créer des types.</p>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <span>Types de pannes</span>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addTypeModal" {{ $categories->isEmpty() ? 'disabled' : '' }}>Ajouter</button>
                </div>
                <div class="card-body">
                    @forelse($types as $t)
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span><strong>{{ $t->name }}</strong> <span class="text-muted small">– {{ $t->panneCategory->name ?? '-' }}</span></span>
                            <div class="d-flex gap-1">
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editTypeModal{{ $t->id }}">Modifier</button>
                                <form action="{{ route('maintenance.panne-types.destroy', $t) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce type ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer</button>
                                </form>
                            </div>
                        </div>
                        <div class="modal fade" id="editTypeModal{{ $t->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('maintenance.panne-types.update', $t) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title">Modifier le type</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <label class="form-label">Nom</label>
                                            <input type="text" name="name" class="form-control mb-2" value="{{ $t->name }}" required maxlength="100">
                                            <label class="form-label">Catégorie</label>
                                            <select name="panne_category_id" class="form-select" required>
                                                @foreach($categories as $c)
                                                    <option value="{{ $c->id }}" {{ $t->panne_category_id == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted small mb-0">Aucun type. Créez d'abord une catégorie puis ajoutez des types.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Ajouter catégorie -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('maintenance.panne-categories.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Nouvelle catégorie</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label">Nom</label>
                        <input type="text" name="name" class="form-control" required maxlength="100" placeholder="Ex: Électricité, Plomberie">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Créer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Ajouter type -->
    <div class="modal fade" id="addTypeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('maintenance.panne-types.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Nouveau type de panne</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label">Nom</label>
                        <input type="text" name="name" class="form-control mb-2" required maxlength="100" placeholder="Ex: Climatisation, Fuite">
                        <label class="form-label">Catégorie</label>
                        <select name="panne_category_id" class="form-select" required>
                            <option value="">Choisir…</option>
                            @foreach($categories as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Créer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
