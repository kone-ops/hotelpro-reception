<x-app-layout>
    <x-slot name="header">Pannes – Service technique</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-0"><i class="bi bi-exclamation-octagon me-2"></i>Pannes</h4>
            <p class="text-muted small mb-0">{{ $hotel->name }}</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('maintenance.pannes.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Signaler une panne</a>
            <a href="{{ route('maintenance.panne-types.index') }}" class="btn btn-outline-secondary">Types et catégories</a>
            <a href="{{ route('maintenance.dashboard') }}" class="btn btn-outline-secondary">Tableau de bord</a>
        </div>
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

    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link {{ $status === 'signalée' ? 'active' : '' }}" href="{{ route('maintenance.pannes.index', ['status' => 'signalée']) }}">
                Signalées <span class="badge bg-warning text-dark">{{ $counts['signalée'] }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $status === 'en_cours' ? 'active' : '' }}" href="{{ route('maintenance.pannes.index', ['status' => 'en_cours']) }}">
                En cours <span class="badge bg-info">{{ $counts['en_cours'] }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $status === 'résolue' ? 'active' : '' }}" href="{{ route('maintenance.pannes.index', ['status' => 'résolue']) }}">
                Résolues <span class="badge bg-success">{{ $counts['résolue'] }}</span>
            </a>
        </li>
    </ul>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            @if($pannes->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Localisation</th>
                                <th>Type / Catégorie</th>
                                <th>Description</th>
                                <th>Signalé par</th>
                                <th>Date</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pannes as $panne)
                                <tr>
                                    <td>{{ $panne->location_label }}</td>
                                    <td>
                                        <span class="fw-medium">{{ $panne->panneType->name ?? '-' }}</span>
                                        <br><span class="text-muted small">{{ $panne->panneCategory->name ?? '-' }}</span>
                                    </td>
                                    <td>{{ \Str::limit($panne->description, 50) }}</td>
                                    <td>{{ $panne->reporter->name ?? '-' }}</td>
                                    <td>{{ $panne->reported_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <a href="{{ route('maintenance.pannes.show', $panne) }}" class="btn btn-sm btn-outline-primary">Détail</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center mt-3">
                    {{ $pannes->withQueryString()->links() }}
                </div>
            @else
                <p class="text-muted text-center py-4 mb-0">
                    Aucune panne {{ $status === 'signalée' ? 'signalée' : ($status === 'en_cours' ? 'en cours de maintenance' : 'résolue') }}.
                    @if($status === 'signalée')
                        <a href="{{ route('maintenance.pannes.create') }}">Signaler une panne</a>
                    @endif
                </p>
            @endif
        </div>
    </div>
</x-app-layout>
