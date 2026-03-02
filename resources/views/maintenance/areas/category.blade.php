@php
    $prefix = $context['areasRoutePrefix'] ?? 'maintenance.areas';
    $isReception = ($prefix === 'reception.areas');
@endphp
<x-app-layout>
    <x-slot name="header">{{ $categoryLabel }}</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 d-flex align-items-center">
                @if($category === 'espaces_publics')<i class="bi bi-people-fill me-2 text-primary"></i>
                @elseif($category === 'espaces_techniques')<i class="bi bi-gear-fill me-2 text-secondary"></i>
                @elseif($category === 'espaces_exterieurs')<i class="bi bi-tree-fill me-2 text-success"></i>
                @elseif($category === 'loisirs')<i class="bi bi-emoji-smile me-2 text-info"></i>
                @elseif($category === 'administration')<i class="bi bi-briefcase-fill me-2 text-dark"></i>
                @else<i class="bi bi-grid me-2 text-muted"></i>
                @endif
                {{ $categoryLabel }}
            </h4>
            <p class="text-muted small mb-0">{{ $hotel->name }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route($prefix . '.index') }}" class="btn btn-outline-secondary"><i class="bi bi-grid-3x3-gap me-1"></i>Tous les espaces</a>
            <a href="{{ route($prefix . '.create', $category) }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>Ajouter un espace
            </a>
        </div>
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

    <div class="row g-2 mb-4">
        <div class="col"><div class="card border-0 shadow-sm"><div class="card-body py-2 text-center"><i class="bi bi-exclamation-triangle text-warning me-1"></i><span class="text-warning">{{ $stats['issue'] }}</span> problème(s)</div></div></div>
        @unless($isReception)
        <div class="col"><div class="card border-0 shadow-sm"><div class="card-body py-2 text-center"><i class="bi bi-wrench text-info me-1"></i><span class="text-info">{{ $stats['maintenance'] }}</span> en maintenance</div></div></div>
        @endunless
        <div class="col"><div class="card border-0 shadow-sm"><div class="card-body py-2 text-center"><i class="bi bi-slash-circle text-danger me-1"></i><span class="text-danger">{{ $stats['out_of_service'] }}</span> hors service</div></div></div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent">Liste ({{ $areas->count() }})</div>
        <div class="card-body">
            @if($areas->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped align-middle mb-0 app-table" aria-label="Espaces de la catégorie">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="table-cell-nom"><i class="bi bi-tag me-1 text-muted"></i>Nom</th>
                                <th scope="col" class="d-none d-md-table-cell table-cell-desc"><i class="bi bi-text-paragraph me-1 text-muted"></i>Description</th>
                                <th scope="col" class="table-cell-state"><i class="bi bi-toggles me-1 text-muted"></i>État</th>
                                <th scope="col" class="text-end table-actions-cell" style="width: 200px;"><i class="bi bi-gear me-1 text-muted"></i>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($areas as $area)
                                <tr>
                                    <td class="table-cell-nom" title="{{ $area->name }}">
                                        <strong>{{ $area->name }}</strong>
                                    </td>
                                    <td class="d-none d-md-table-cell table-cell-desc small text-muted" title="{{ $area->description ?? '' }}">
                                        <span class="text-truncate d-inline-block" style="max-width: 260px;">{{ $area->description ?? '—' }}</span>
                                    </td>
                                    <td class="table-cell-state">
                                        @if($area->technical_state === 'issue')<span class="badge bg-warning text-dark">Pannes signalées</span>
                                        @elseif($area->technical_state === 'maintenance')<span class="badge bg-info">En maintenance</span>
                                        @elseif($area->technical_state === 'out_of_service')<span class="badge bg-danger">Hors service</span>
                                        @else<span class="badge bg-success">Normal</span>
                                        @endif
                                    </td>
                                    <td class="text-end table-actions-cell">
                                        <div class="btn-group btn-group-sm" role="group" aria-label="Changer l'état">
                                            <form action="{{ route($prefix . '.update-state', $area) }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="technical_state" value="normal">
                                                <button type="submit" class="btn btn-outline-success" title="Remettre à Normal"><i class="bi bi-check-circle"></i></button>
                                            </form>
                                            <form action="{{ route($prefix . '.update-state', $area) }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="technical_state" value="issue">
                                                <button type="submit" class="btn btn-outline-warning" title="Marquer : Pannes signalées"><i class="bi bi-exclamation-triangle"></i></button>
                                            </form>
                                            @unless($isReception)
                                            <form action="{{ route($prefix . '.update-state', $area) }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="technical_state" value="maintenance">
                                                <button type="submit" class="btn btn-outline-info" title="Marquer : En maintenance"><i class="bi bi-wrench"></i></button>
                                            </form>
                                            @endunless
                                            @unless($isReception)
                                            <form action="{{ route($prefix . '.update-state', $area) }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="technical_state" value="out_of_service">
                                                <button type="submit" class="btn btn-outline-danger" title="Marquer : Hors service"><i class="bi bi-slash-circle"></i></button>
                                            </form>
                                            @endunless
                                        </div>
                                        @unless($isReception)
                                        <form action="{{ route($prefix . '.destroy', $area) }}" method="POST" class="d-inline actions-separator" onsubmit="return confirm('Supprimer cet espace ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-secondary btn-sm" title="Supprimer l'espace"><i class="bi bi-trash"></i></button>
                                        </form>
                                        @endunless
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <x-super.empty-table
                    icon="bi-folder"
                    title="Aucun espace"
                    message="Aucun espace dans cette catégorie."
                >
                    <x-slot:action>
                        <a href="{{ route($prefix . '.create', $category) }}" class="btn btn-primary">Ajouter un espace</a>
                    </x-slot:action>
                </x-super.empty-table>
            @endif
        </div>
    </div>
</x-app-layout>
