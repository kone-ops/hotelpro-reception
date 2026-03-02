<x-app-layout>
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="mb-0">
                <i class="bi bi-database-fill me-2"></i>Gestion Globale de la Base de Données
            </h2>
            <a href="{{ route('super.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Retour
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

            <!-- Alert de danger critique -->
            <div class="alert alert-danger shadow-sm border-start border-5 border-danger" role="alert">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="bi bi-exclamation-triangle-fill text-danger stat-card-icon"></i>
                    </div>
                    <div>
                        <h5 class="alert-heading mb-2">
                            <i class="bi bi-shield-exclamation me-2"></i>⚠️ ZONE CRITIQUE - ACTION IRRÉVERSIBLE
                        </h5>
                        <p class="mb-0">
                            La <strong class="text-danger">purge globale</strong> supprimera <strong>TOUTES</strong> les données de l'application 
                            sauf les comptes super-admin. Cette action est <strong class="text-danger">DÉFINITIVE</strong> et ne peut pas être annulée.
                            <br><strong>Un export automatique sera créé avant la purge pour votre sécurité.</strong>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Statistiques globales -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-primary shadow h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-building stat-card-icon"></i>
                            <h5 class="card-title mt-2">Hôtels</h5>
                            <h3 class="mb-0">{{ number_format($stats['total_hotels']) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-success shadow h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-people stat-card-icon"></i>
                            <h5 class="card-title mt-2">Utilisateurs</h5>
                            <h3 class="mb-0">{{ number_format($stats['total_users']) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-warning shadow h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-calendar-check stat-card-icon"></i>
                            <h5 class="card-title mt-2">Enregistrements</h5>
                            <h3 class="mb-0">{{ number_format($stats['total_reservations']) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-info shadow h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-door-open stat-card-icon"></i>
                            <h5 class="card-title mt-2">Chambres</h5>
                            <h3 class="mb-0">{{ number_format($stats['total_rooms']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-secondary shadow h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-shield-check stat-card-icon"></i>
                            <h5 class="card-title mt-2">Super-Admins</h5>
                            <h3 class="mb-0">{{ number_format($stats['total_super_admins']) }}</h3>
                        </div>
                    </div>
                </div>
                @if($hasPrintersModule ?? false)
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-dark shadow h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-printer stat-card-icon"></i>
                            <h5 class="card-title mt-2">Imprimantes</h5>
                            <h3 class="mb-0">{{ number_format($stats['total_printers'] ?? 0) }}</h3>
                        </div>
                    </div>
                </div>
                @endif
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-danger shadow h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-people-fill stat-card-icon"></i>
                            <h5 class="card-title mt-2">Groupes</h5>
                            <h3 class="mb-0">{{ number_format($stats['total_groups']) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-primary shadow h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-person-badge stat-card-icon"></i>
                            <h5 class="card-title mt-2">Clients</h5>
                            <h3 class="mb-0">{{ number_format($stats['total_clients']) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-info shadow h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-bucket stat-card-icon"></i>
                            <h5 class="card-title mt-2">Tâches étages</h5>
                            <h3 class="mb-0">{{ number_format($stats['total_housekeeping_tasks'] ?? 0) }}</h3>
                            <small class="opacity-75">{{ number_format($stats['total_room_state_history'] ?? 0) }} historiques d'états</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-success shadow h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-droplet stat-card-icon"></i>
                            <h5 class="card-title mt-2">Buanderie</h5>
                            <h3 class="mb-0">{{ number_format($stats['total_laundry_collections'] ?? 0) }}</h3>
                            <small class="opacity-75">{{ number_format($stats['total_laundry_item_types'] ?? 0) }} types de linge</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Liste des Super-Admins -->
            @if($stats['super_admins']->count() > 0)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0"><i class="bi bi-shield-check me-2"></i>Comptes Super-Admin</h5>
                    <small class="text-muted">Ces comptes seront conservés lors de la purge globale</small>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover table-striped align-middle mb-0 super-admin-table" aria-label="Comptes Super-Admin conservés lors de la purge globale">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col"><i class="bi bi-person me-1 text-primary"></i>Nom</th>
                                    <th scope="col"><i class="bi bi-envelope me-1 text-primary"></i>Email</th>
                                    <th scope="col"><i class="bi bi-flag me-1 text-primary"></i>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stats['super_admins'] as $admin)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3">
                                                {{ substr($admin->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <strong>{{ $admin->name }}</strong>
                                                @if($admin->id === auth()->id())
                                                    <span class="badge bg-success ms-2">Vous</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $admin->email }}</td>
                                    <td>
                                        <span class="badge bg-success">Conservé</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- Actions -->
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="bi bi-download me-2"></i>Export Global</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">
                                Exporter toutes les données de la base de données dans un fichier JSON.
                                Cette sauvegarde peut être utilisée pour restaurer les données plus tard.
                            </p>
                            <a href="{{ route('super.database.export') }}" class="btn btn-info">
                                <i class="bi bi-download me-2"></i>Exporter Toutes les Données
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="card border-0 shadow-sm h-100 border-warning">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0"><i class="bi bi-upload me-2"></i>Import Global</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">
                                Importer des données depuis un fichier JSON exporté précédemment.
                                Une sauvegarde automatique sera créée avant l'import.
                            </p>
                            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#importGlobalModal">
                                <i class="bi bi-upload me-2"></i>Importer des Données
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="card border-0 shadow-sm h-100 border-danger">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0"><i class="bi bi-trash-fill me-2"></i>Purge Globale</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-danger fw-bold">
                                ⚠️ Cette action supprimera TOUTES les données sauf les comptes super-admin.
                                Un export automatique sera créé avant la purge.
                            </p>
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#purgeGlobalModal">
                                <i class="bi bi-trash-fill me-2"></i>Purge Globale
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmation pour purge globale -->
    <div class="modal fade" id="purgeGlobalModal" tabindex="-1" aria-labelledby="purgeGlobalModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="purgeGlobalModalLabel">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>Confirmation Purge Globale
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('super.database.purge') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-danger">
                            <h6 class="alert-heading">⚠️ ATTENTION - ACTION IRRÉVERSIBLE</h6>
                            <p class="mb-0">
                                Cette action va supprimer <strong>TOUTES</strong> les données de l'application :
                            </p>
                            <ul class="mb-0 mt-2">
                                <li>Tous les hôtels</li>
                                <li>Tous les utilisateurs (sauf les super-admins)</li>
                                <li>Tous les enregistrements</li>
                                <li>Toutes les chambres et types de chambres</li>
                                <li>Tous les clients, groupes, imprimantes, paramètres</li>
                                <li>Tous les logs</li>
                            </ul>
                            <p class="mb-0 mt-2">
                                <strong>Seuls les comptes super-admin seront conservés.</strong>
                            </p>
                            <p class="mb-0 mt-2">
                                <strong class="text-success">Un export automatique sera créé avant la purge.</strong>
                            </p>
                        </div>

                        <div class="mb-3">
                            <label for="confirmation" class="form-label fw-bold">
                                Tapez <strong class="text-danger">PURGE_GLOBAL</strong> pour confirmer :
                            </label>
                            <input type="text" class="form-control" id="confirmation" name="confirmation" required 
                                   placeholder="PURGE_GLOBAL" autocomplete="off">
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label fw-bold">
                                <i class="bi bi-lock me-1"></i>Votre mot de passe :
                            </label>
                            <input type="password" class="form-control" id="password" name="password" required 
                                   placeholder="Entrez votre mot de passe pour confirmer">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i>Annuler
                        </button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash-fill me-1"></i>Confirmer la Purge Globale
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal d'import global -->
    <div class="modal fade" id="importGlobalModal" tabindex="-1" aria-labelledby="importGlobalModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="importGlobalModalLabel">
                        <i class="bi bi-upload me-2"></i>Import Global
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('super.database.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <h6 class="alert-heading">⚠️ ATTENTION</h6>
                            <p class="mb-0">
                                Cette action va importer des données depuis un fichier JSON. 
                                Les IDs seront recréés automatiquement pour éviter les conflits.
                                <strong>Une sauvegarde automatique sera créée avant l'import.</strong>
                            </p>
                        </div>

                        <div class="mb-3">
                            <label for="import_file" class="form-label fw-bold">
                                <i class="bi bi-file-earmark-json me-1"></i>Fichier JSON à importer :
                            </label>
                            <input type="file" class="form-control" id="import_file" name="import_file" required 
                                   accept=".json,application/json" />
                            <small class="text-muted">Format: JSON, Taille max: 100MB</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i>Annuler
                        </button>
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-upload me-1"></i>Confirmer l'Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

