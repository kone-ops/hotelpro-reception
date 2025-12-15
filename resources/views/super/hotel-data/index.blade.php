<x-app-layout>
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-0">
                <i class="bi bi-database me-2"></i>{{ __('Gestion des Données Hôtels') }}
            </h2>
            <a href="{{ route('super.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Retour au tableau de bord
            </a>
        </div>

    <div class="py-4">
        <div class="container-fluid">
            
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <!-- Alert de danger -->
            <div class="alert alert-warning shadow-sm border-start border-5 border-warning" role="alert">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size: 2rem;"></i>
                    </div>
                    <div>
                        <h5 class="alert-heading mb-2">
                            <i class="bi bi-shield-exclamation me-2"></i>Attention : Zone Sensible
                        </h5>
                        <p class="mb-0">
                            Les opérations de <strong>purge</strong> et de <strong>réinitialisation</strong> sont <strong class="text-danger">irréversibles</strong>. 
                            Assurez-vous d'avoir <strong>exporté les données</strong> avant toute action destructive.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Statistiques globales -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-primary shadow h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-building" style="font-size: 2.5rem;"></i>
                            <h3 class="mt-2 mb-1">{{ $hotels->count() }}</h3>
                            <p class="mb-0">Hôtels Totaux</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-success shadow h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-calendar-check" style="font-size: 2.5rem;"></i>
                            <h3 class="mt-2 mb-1">{{ $hotels->sum('reservations_count') }}</h3>
                            <p class="mb-0">Réservations</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-secondary shadow h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-people-fill" style="font-size: 2.5rem;"></i>
                            <h3 class="mt-2 mb-1">{{ $hotels->sum('users_count') }}</h3>
                            <p class="mb-0">Utilisateurs</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-warning shadow h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-gear-fill" style="font-size: 2.5rem;"></i>
                            <h3 class="mt-2 mb-1">{{ $hotels->count() }}</h3>
                            <p class="mb-0">Bases de Données</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Liste des hôtels -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-white">
                        <i class="bi bi-building me-2 text"></i>Liste des Hôtels ({{ $hotels->count() }})
                    </h5>
                    <div>
                        <a href="{{ route('super.hotels.index') }}" class="btn btn-light btn-sm">
                            <i class="bi bi-gear me-1"></i>Gérer les hôtels
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($hotels->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">
                                        <i class="bi bi-building me-2"></i>Hôtel
                                    </th>
                                    <th class="text-center">
                                        <i class="bi bi-calendar-check me-2"></i>Réservations
                                    </th>
                                    <th class="text-center">
                                        <i class="bi bi-people-fill me-2"></i>Utilisateurs
                                    </th>
                                    <th class="text-center">
                                        <i class="bi bi-geo-alt me-2"></i>Localisation
                                    </th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($hotels as $hotel)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle bg-primary text-white me-3" style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                                                {{ strtoupper(substr($hotel->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $hotel->name }}</div>
                                                <small class="text-muted">
                                                    @if($hotel->email)
                                                        <i class="bi bi-envelope me-1"></i>{{ $hotel->email }}
                                                    @else
                                                        <span class="text-muted fst-italic">Email non renseigné</span>
                                                    @endif
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary rounded-pill px-3 py-2" style="font-size: 0.95rem;">
                                            {{ $hotel->reservations_count }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success rounded-pill px-3 py-2" style="font-size: 0.95rem;">
                                            {{ $hotel->users_count }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($hotel->city || $hotel->country)
                                            <span class="text-muted">
                                                <i class="bi bi-pin-map-fill me-1"></i>
                                                {{ $hotel->city }}@if($hotel->city && $hotel->country), @endif{{ $hotel->country }}
                                            </span>
                                        @else
                                            <span class="text-muted fst-italic">Non renseignée</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('super.hotel-data.show', $hotel) }}" 
                                               class="btn btn-primary btn-sm"
                                               title="Voir les détails complets">
                                                <i class="bi bi-eye me-1"></i>
                                            </a>
                                            <a href="{{ route('super.hotel-data.export', $hotel) }}" 
                                               class="btn btn-success btn-sm"
                                               title="Exporter les données">
                                                <i class="bi bi-download"></i>
                                            </a>
                                            <a href="{{ route('super.reports.hotel', $hotel) }}" 
                                               class="btn btn-info btn-sm"
                                               title="Voir les rapports">
                                                <i class="bi bi-graph-up"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
                        <p class="text-muted mt-3">Aucun hôtel trouvé.</p>
                        <a href="{{ route('super.hotels.index') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Ajouter un hôtel
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Documentation et Guide -->
            <div class="row mt-4">
                <div class="col-md-6 mb-3">
                    <div class="card shadow-sm border-info h-100">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0 text-black"><i class="bi bi-book me-2"></i>Documentation</h6>
                        </div>
                        <div class="card-body">
                            <h6 class="card-title">Actions Disponibles</h6>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="bi bi-eye text-primary me-2"></i>
                                    <strong>Détails :</strong> Voir toutes les informations de l'hôtel
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-download text-success me-2"></i>
                                    <strong>Export :</strong> Télécharge les données en JSON
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-upload text-info me-2"></i>
                                    <strong>Import :</strong> Importe des données depuis JSON
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-arrow-clockwise text-warning me-2"></i>
                                    <strong>Reset :</strong> Supprime uniquement les données transactionnelles
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-trash text-danger me-2"></i>
                                    <strong>Purge :</strong> Supprime TOUTES les données (⚠️ Irréversible)
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="card shadow-sm border-warning h-100">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0 text-black"><i class="bi bi-shield-exclamation me-2"></i>Sécurité & Bonnes Pratiques</h6>
                        </div>
                        <div class="card-body">
                            <h6 class="card-title">Recommandations</h6>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    Exportez régulièrement les données importantes
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    Vérifiez l'export avant toute action destructive
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                                    Le <strong>Reset</strong> conserve la configuration
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-x-circle text-danger me-2"></i>
                                    La <strong>Purge</strong> est irréversible
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-shield-check text-info me-2"></i>
                                    Les actions sont loguées pour audit
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Légende des statuts -->
            <div class="card shadow-sm mt-4 border-secondary">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0 text-black"><i class="bi bi-info-circle me-2"></i>Légende</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Données Conservées (Reset)</h6>
                            <ul class="list-unstyled">
                                <li><i class="bi bi-check-circle text-success me-2"></i>Imprimantes et configurations</li>
                                <li><i class="bi bi-check-circle text-success me-2"></i>Paramètres système</li>
                                <li><i class="bi bi-check-circle text-success me-2"></i>Utilisateurs et rôles</li>
                                <li><i class="bi bi-check-circle text-success me-2"></i>Types de chambres</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Données Supprimées (Reset)</h6>
                            <ul class="list-unstyled">
                                <li><i class="bi bi-x-circle text-danger me-2"></i>Réservations</li>
                                <li><i class="bi bi-x-circle text-danger me-2"></i>Documents d'identité</li>
                                <li><i class="bi bi-x-circle text-danger me-2"></i>Signatures</li>
                                <li><i class="bi bi-x-circle text-danger me-2"></i>Logs (impression + activité)</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <style>
        .avatar-circle {
            font-size: 1.2rem;
        }
        
        .table tbody tr {
            transition: all 0.2s ease;
        }
        
        .table tbody tr:hover {
            background-color: rgba(13, 110, 253, 0.05);
            transform: translateX(3px);
        }
        
        .btn-group .btn {
            border-radius: 0;
        }
        
        .btn-group .btn:first-child {
            border-top-left-radius: 0.25rem;
            border-bottom-left-radius: 0.25rem;
        }
        
        .btn-group .btn:last-child {
            border-top-right-radius: 0.25rem;
            border-bottom-right-radius: 0.25rem;
        }
        
        .card {
            transition: all 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }
    </style>
</x-app-layout>
