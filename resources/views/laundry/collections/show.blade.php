<x-app-layout>
    <x-slot name="header">Collecte linge – Chambre {{ $collection->room->room_number }}</x-slot>

    <div class="mb-4">
        <a href="{{ route('laundry.collections.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Retour aux collectes
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
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Quantités par type de linge</h5>
                    @if($collection->status === 'pending')
                        <span class="badge bg-warning">En attente</span>
                    @elseif($collection->status === 'in_wash')
                        <span class="badge bg-info">En lavage</span>
                    @else
                        <span class="badge bg-success">Terminée</span>
                    @endif
                </div>
                <div class="card-body">
                    @if($itemTypes->isEmpty())
                        <p class="text-muted">Aucun type de linge défini. <a href="{{ route('laundry.item-types.index') }}">Créer des types de linge</a> pour saisir les quantités.</p>
                    @else
                        <form action="{{ route('laundry.collections.update', $collection) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="table-responsive">
                                <table class="table table-sm table-hover align-middle mb-0 app-table" aria-label="Quantités par type de linge">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col"><i class="bi bi-tag me-1 text-muted"></i>Type de linge</th>
                                            <th scope="col" style="width: 120px;"><i class="bi bi-hash me-1 text-muted"></i>Quantité</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($itemTypes as $it)
                                            @php
                                                $line = $collection->lines->firstWhere('laundry_item_type_id', $it->id);
                                                $qty = $line ? $line->quantity : 0;
                                            @endphp
                                            <tr>
                                                <td>{{ $it->name }} @if($it->code)<span class="text-muted">({{ $it->code }})</span>@endif</td>
                                                <td>
                                                    <input type="number" name="lines[{{ $it->id }}]" value="{{ $qty }}" min="0" class="form-control form-control-sm">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="Optionnel">{{ $collection->notes }}</textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Enregistrer les quantités</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent"><h6 class="mb-0">Détails</h6></div>
                <div class="card-body">
                    <p><strong>Chambre :</strong> {{ $collection->room->room_number }} ({{ $collection->room->roomType->name ?? '-' }})</p>
                    <p><strong>Collectée le :</strong> {{ $collection->collected_at->format('d/m/Y à H:i') }}</p>
                    <p><strong>Collectée par :</strong> {{ $collection->collectedByUser->name ?? '-' }}</p>
                </div>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><h6 class="mb-0">Changer le statut</h6></div>
                <div class="card-body">
                    <form action="{{ route('laundry.collections.update-status', $collection) }}" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="status" value="in_wash">
                        <button type="submit" class="btn btn-info btn-sm me-1" {{ $collection->status !== 'pending' ? 'disabled' : '' }}>En lavage</button>
                    </form>
                    <form action="{{ route('laundry.collections.update-status', $collection) }}" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="status" value="done">
                        <button type="submit" class="btn btn-success btn-sm" {{ $collection->status === 'done' ? 'disabled' : '' }}>Terminée</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
