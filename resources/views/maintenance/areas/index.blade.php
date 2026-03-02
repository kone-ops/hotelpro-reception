@php $prefix = $context['areasRoutePrefix'] ?? 'maintenance.areas'; $dashboardRoute = $context['dashboardRoute'] ?? 'maintenance.dashboard'; @endphp
<x-app-layout>
    <x-slot name="header">Espaces</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0"><i class="bi bi-building me-2"></i>Espaces à suivre</h4>
            <p class="text-muted small mb-0">{{ $hotel->name }} – Espaces publics, techniques, extérieurs, loisirs, administration</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <button type="button" class="btn btn-outline-info btn-sm" data-bs-toggle="collapse" data-bs-target="#aideProcedures" title="Procédures d’utilisation">
                <i class="bi bi-question-circle me-1"></i>Procédures
            </button>
            <a href="{{ route($dashboardRoute) }}" class="btn btn-outline-secondary">
                <i class="bi bi-speedometer2 me-1"></i>Tableau de bord
            </a>
        </div>
    </div>

    <div class="collapse mb-3" id="aideProcedures">
        <div class="card border-0 shadow-sm bg-light">
            <div class="card-body small">
                <h6 class="card-title"><i class="bi bi-journal-text me-1"></i>Guide des procédures</h6>
                <ul class="mb-0">
                    <li><strong>Accéder :</strong> Menu Service technique → Espaces (ou tableau de bord → Espaces).</li>
                    <li><strong>Choisir une catégorie</strong> (Espaces publics, techniques, extérieurs, Loisirs, Administration) → <em>Voir et gérer</em>.</li>
                    <li><strong>Ajouter un espace :</strong> Dans la liste de la catégorie → <em>Ajouter un espace</em> → Nom (obligatoire), Description (optionnel) → Enregistrer.</li>
                    <li><strong>Changer l’état :</strong> Utiliser les boutons Normal, Problème, Maintenance, Hors service sur chaque ligne.</li>
                    <li><strong>Supprimer :</strong> Icône corbeille à côté de l’espace → confirmer.</li>
                </ul>
                <p class="mb-0 mt-2 text-muted">Structure BDD et procédures détaillées (intervention directe en base) : document <code>docs/PROCEDURES_ESPACES_SERVICE_TECHNIQUE_BDD.md</code>.</p>
            </div>
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

    <div class="row g-3">
        @foreach($categoriesStats as $key => $stat)
            @php
                $toFollow = $stat['issue'] + $stat['maintenance'] + $stat['out_of_service'];
            @endphp
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title d-flex align-items-center mb-0">
                            @if($key === 'espaces_publics')<i class="bi bi-people-fill me-2 text-primary"></i>
                            @elseif($key === 'espaces_techniques')<i class="bi bi-gear-fill me-2 text-secondary"></i>
                            @elseif($key === 'espaces_exterieurs')<i class="bi bi-tree-fill me-2 text-success"></i>
                            @elseif($key === 'loisirs')<i class="bi bi-emoji-smile me-2 text-info"></i>
                            @elseif($key === 'administration')<i class="bi bi-briefcase-fill me-2 text-dark"></i>
                            @else<i class="bi bi-grid me-2 text-muted"></i>
                            @endif
                            {{ $stat['label'] }}
                        </h5>
                        <div class="mt-auto pt-3">
                            <p class="text-muted small mb-2">{{ $stat['total'] }} espace(s) · {{ $toFollow }} à suivre</p>
                            <div class="d-flex flex-wrap gap-2 small mb-2">
                                @if($stat['issue'] > 0)<span class="badge bg-warning text-dark">{{ $stat['issue'] }} problème(s)</span>@endif
                                @if($stat['maintenance'] > 0)<span class="badge bg-info">{{ $stat['maintenance'] }} en maintenance</span>@endif
                                @if($stat['out_of_service'] > 0)<span class="badge bg-danger">{{ $stat['out_of_service'] }} hors service</span>@endif
                            </div>
                            <a href="{{ route($prefix . '.category', $key) }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-list-ul me-1"></i>Voir et gérer
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</x-app-layout>
