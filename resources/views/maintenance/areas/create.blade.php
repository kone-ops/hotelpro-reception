@php $prefix = $context['areasRoutePrefix'] ?? 'maintenance.areas'; @endphp
<x-app-layout>
    <x-slot name="header">Ajouter un espace – {{ $categoryLabel }}</x-slot>

    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route($prefix . '.index') }}">Espaces</a></li>
                <li class="breadcrumb-item"><a href="{{ route($prefix . '.category', $category) }}">{{ $categoryLabel }}</a></li>
                <li class="breadcrumb-item active">Ajouter</li>
            </ol>
        </nav>
        <h4 class="mb-0 d-flex align-items-center">
            @if($category === 'espaces_publics')<i class="bi bi-people-fill me-2 text-primary"></i>
            @elseif($category === 'espaces_techniques')<i class="bi bi-gear-fill me-2 text-secondary"></i>
            @elseif($category === 'espaces_exterieurs')<i class="bi bi-tree-fill me-2 text-success"></i>
            @elseif($category === 'loisirs')<i class="bi bi-emoji-smile me-2 text-info"></i>
            @elseif($category === 'administration')<i class="bi bi-briefcase-fill me-2 text-dark"></i>
            @else<i class="bi bi-grid me-2 text-muted"></i>
            @endif
            Nouvel espace – {{ $categoryLabel }}
        </h4>
        <p class="text-muted small mb-0">{{ $hotel->name }}</p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="alert alert-info border-0 shadow-sm mb-3" role="status">
        <i class="bi bi-info-circle me-2"></i>
        <strong>Ajout au catalogue.</strong> Vous ajoutez un <strong>nouvel espace</strong> à la liste (ex. un nouveau lieu à gérer et à suivre). Ce formulaire ne sert pas à signaler une panne ; pour cela, utilisez <a href="{{ route('maintenance.pannes.create') }}" class="alert-link">Signaler une panne</a>.
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            @php
                $categoryChoices = [
                    'espaces_publics' => [
                        ['value' => 'Réception', 'icon' => 'bi-door-open', 'label' => 'Réception'],
                        ['value' => 'Hall d\'accueil', 'icon' => 'bi-building', 'label' => 'Hall d\'accueil'],
                        ['value' => 'Restaurant', 'icon' => 'bi-cup-hot', 'label' => 'Restaurant'],
                        ['value' => 'Toilettes publiques', 'icon' => 'bi-droplet', 'label' => 'Toilettes publiques'],
                        ['value' => 'Salle de conférence', 'icon' => 'bi-people', 'label' => 'Salle de conférence'],
                        ['value' => 'Ascenseurs', 'icon' => 'bi-arrow-up-down', 'label' => 'Ascenseurs'],
                    ],
                    'espaces_techniques' => [
                        ['value' => 'Local technique', 'icon' => 'bi-gear', 'label' => 'Local technique'],
                        ['value' => 'Chaufferie', 'icon' => 'bi-fire', 'label' => 'Chaufferie'],
                        ['value' => 'Climatisation', 'icon' => 'bi-snow', 'label' => 'Climatisation'],
                        ['value' => 'Électricité', 'icon' => 'bi-lightning', 'label' => 'Électricité'],
                        ['value' => 'Plomberie', 'icon' => 'bi-droplet', 'label' => 'Plomberie'],
                        ['value' => 'Toiture', 'icon' => 'bi-building', 'label' => 'Toiture'],
                        ['value' => 'Gaines', 'icon' => 'bi-box', 'label' => 'Gaines'],
                        ['value' => 'Local poubelles', 'icon' => 'bi-trash', 'label' => 'Local poubelles'],
                    ],
                    'espaces_exterieurs' => [
                        ['value' => 'Parking', 'icon' => 'bi-p-square', 'label' => 'Parking'],
                        ['value' => 'Jardin', 'icon' => 'bi-flower2', 'label' => 'Jardin'],
                        ['value' => 'Terrasse', 'icon' => 'bi-sun', 'label' => 'Terrasse'],
                        ['value' => 'Piscine extérieure', 'icon' => 'bi-water', 'label' => 'Piscine extérieure'],
                        ['value' => 'Cour', 'icon' => 'bi-house-door', 'label' => 'Cour'],
                        ['value' => 'Entrée principale', 'icon' => 'bi-door-open', 'label' => 'Entrée principale'],
                        ['value' => 'Abri vélos', 'icon' => 'bi-bicycle', 'label' => 'Abri vélos'],
                    ],
                    'loisirs' => [
                        ['value' => 'Piscine', 'icon' => 'bi-water', 'label' => 'Piscine'],
                        ['value' => 'Salle de sport', 'icon' => 'bi-heart-pulse', 'label' => 'Salle de sport'],
                        ['value' => 'Spa', 'icon' => 'bi-droplet', 'label' => 'Spa'],
                        ['value' => 'Sauna', 'icon' => 'bi-thermometer-sun', 'label' => 'Sauna'],
                        ['value' => 'Court de tennis', 'icon' => 'bi-circle', 'label' => 'Court de tennis'],
                        ['value' => 'Aire jeux enfants', 'icon' => 'bi-emoji-smile', 'label' => 'Aire jeux enfants'],
                        ['value' => 'Bar / Lounge', 'icon' => 'bi-cup-straw', 'label' => 'Bar / Lounge'],
                    ],
                    'administration' => [
                        ['value' => 'Bureau direction', 'icon' => 'bi-briefcase', 'label' => 'Bureau direction'],
                        ['value' => 'Comptabilité', 'icon' => 'bi-calculator', 'label' => 'Comptabilité'],
                        ['value' => 'Réserve', 'icon' => 'bi-box-seam', 'label' => 'Réserve'],
                        ['value' => 'Archives', 'icon' => 'bi-archive', 'label' => 'Archives'],
                        ['value' => 'Local personnel', 'icon' => 'bi-person-workspace', 'label' => 'Local personnel'],
                        ['value' => 'Standard téléphonique', 'icon' => 'bi-telephone', 'label' => 'Standard téléphonique'],
                    ],
                ];
                $choices = $categoryChoices[$category] ?? null;
            @endphp
            <style>
                .dropdown-space-choices .dropdown-item:hover {
                    background-color: #0d6efd;
                    color: #fff;
                }
                .dropdown-space-choices .dropdown-item:hover .bi {
                    color: #fff !important;
                }
            </style>
            <form action="{{ route($prefix . '.store', $category) }}" method="POST" id="formArea">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Type du nouveau espace</label>
                    <p class="small text-muted mb-2">Saisissez ou choisissez le type de lieu que vous ajoutez au catalogue {{ $categoryLabel }}.</p>
                    @if($choices)
                        <input type="hidden" name="name" id="spaceNameInput" value="{{ old('name') }}" required>
                        <div class="dropdown dropdown-space-choices">
                            <button class="form-control dropdown-toggle text-start d-flex align-items-center" type="button" id="dropdownSpaceName" data-bs-toggle="dropdown" aria-expanded="false" style="min-height: 38px;">
                                <span id="spaceNameDisplay">
                                    @if(old('name'))
                                        @foreach($choices as $opt)
                                            @if($opt['value'] === old('name'))
                                                <i class="bi {{ $opt['icon'] }} me-2 text-primary"></i>{{ $opt['label'] }}
                                                @break
                                            @endif
                                        @endforeach
                                    @else
                                        <span class="text-muted">Choisir un type d'espace...</span>
                                    @endif
                                </span>
                            </button>
                            <ul class="dropdown-menu w-100" aria-labelledby="dropdownSpaceName">
                                @foreach($choices as $opt)
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center space-choice-option" href="#" data-value="{{ $opt['value'] }}" data-icon="{{ $opt['icon'] }}" data-label="{{ $opt['label'] }}">
                                            <i class="bi {{ $opt['icon'] }} me-2 text-primary"></i>{{ $opt['label'] }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <script>
                            document.querySelectorAll('.space-choice-option').forEach(function(el) {
                                el.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    var val = el.getAttribute('data-value');
                                    var icon = el.getAttribute('data-icon');
                                    var label = el.getAttribute('data-label');
                                    document.getElementById('spaceNameInput').value = val;
                                    document.getElementById('spaceNameDisplay').innerHTML = '<i class="bi ' + icon + ' me-2 text-primary"></i>' + label;
                                });
                            });
                        </script>
                    @else
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required maxlength="255" placeholder="Ex: Hall, Piscine, Toiture...">
                    @endif
                </div>
                <div class="mb-3">
                    <label class="form-label">Description (optionnel)</label>
                    <textarea name="description" class="form-control" rows="3" maxlength="1000" placeholder="Précisions éventuelles...">{{ old('description') }}</textarea>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                    <a href="{{ route($prefix . '.category', $category) }}" class="btn btn-outline-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
