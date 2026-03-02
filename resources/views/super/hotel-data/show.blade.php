<x-app-layout>
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-0">
                <i class="bi bi-building me-2"></i>{{ $hotel->name }} - Détails Complets
            </h2>
            <a href="{{ route('super.hotel-data.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Retour
            </a>
        </div>

    <div class="py-4">
        <div class="container-fluid">
            
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <!-- Informations Générales de l'Hôtel -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informations Générales</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%"><i class="bi bi-building text-primary me-2"></i>Nom :</th>
                                    <td><strong>{{ $hotel->name }}</strong></td>
                                </tr>
                                <tr>
                                    <th><i class="bi bi-envelope text-primary me-2"></i>Email :</th>
                                    <td>{{ $hotel->email ?? 'Non renseigné' }}</td>
                                </tr>
                                <tr>
                                    <th><i class="bi bi-telephone text-primary me-2"></i>Téléphone :</th>
                                    <td>{{ $hotel->phone ?? 'Non renseigné' }}</td>
                                </tr>
                                <tr>
                                    <th><i class="bi bi-geo-alt text-primary me-2"></i>Adresse :</th>
                                    <td>{{ $hotel->address ?? 'Non renseignée' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%"><i class="bi bi-pin-map text-primary me-2"></i>Ville :</th>
                                    <td>{{ $hotel->city ?? 'Non renseignée' }}</td>
                                </tr>
                                <tr>
                                    <th><i class="bi bi-flag text-primary me-2"></i>Pays :</th>
                                    <td>{{ $hotel->country ?? 'Non renseigné' }}</td>
                                </tr>
                                <tr>
                                    <th><i class="bi bi-palette text-primary me-2"></i>Couleurs :</th>
                                    <td>
                                        @if($hotel->primary_color)
                                            <span class="badge" style="background-color: {{ $hotel->primary_color }}; color: white;">
                                                Primaire: {{ $hotel->primary_color }}
                                            </span>
                                        @endif
                                        @if($hotel->secondary_color)
                                            <span class="badge" style="background-color: {{ $hotel->secondary_color }}; color: white;">
                                                Secondaire: {{ $hotel->secondary_color }}
                                            </span>
                                        @endif
                                        @if(!$hotel->primary_color && !$hotel->secondary_color)
                                            Non définies
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th><i class="bi bi-calendar text-primary me-2"></i>Créé le :</th>
                                    <td>{{ $hotel->created_at->format('d/m/Y à H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($hotel->oracle_dsn)
                    <div class="alert alert-info mt-3 mb-0">
                        <strong><i class="bi bi-database me-2"></i>Connexion Oracle :</strong> Configurée
                        <span class="ms-3">DSN: {{ $hotel->oracle_dsn }}</span>
                    </div>
                    @endif

                    @if($hotel->public_form_url)
                    <div class="alert alert-success mt-3 mb-0">
                        <strong><i class="bi bi-link-45deg me-2"></i>URL Formulaire Public :</strong> 
                        <a href="{{ $hotel->public_form_url }}" target="_blank">{{ $hotel->public_form_url }}</a>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Statistiques en Cartes -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-primary shadow-sm h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-calendar-check stat-card-icon"></i>
                            <h3 class="mt-2 mb-1">{{ $stats['reservations'] }}</h3>
                            <p class="mb-0">Enregistrements</p>
                            <small class="d-block mt-2">
                                <span class="badge bg-light text-primary">{{ $stats['reservations_pending'] }} en attente</span>
                                <span class="badge bg-light text-success">{{ $stats['reservations_validated'] }} validées</span>
                                <span class="badge bg-light text-danger">{{ $stats['reservations_rejected'] }} rejetées</span>
                            </small>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-success shadow-sm h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-people-fill stat-card-icon"></i>
                            <h3 class="mt-2 mb-1">{{ $stats['users'] }}</h3>
                            <p class="mb-0">Utilisateurs</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-warning shadow-sm h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-door-open stat-card-icon"></i>
                            <h3 class="mt-2 mb-1">{{ $stats['room_types'] }}</h3>
                            <p class="mb-0">Types de Chambres</p>
                            <small class="d-block mt-2">{{ $stats['rooms'] }} chambres au total</small>
                        </div>
                    </div>
                </div>

                {{-- Carte Imprimantes masquée : module imprimantes retiré --}}
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-secondary shadow-sm h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-file-earmark-text stat-card-icon"></i>
                            <h3 class="mt-2 mb-1">{{ $stats['identity_documents'] }}</h3>
                            <p class="mb-0">Documents d'Identité</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-dark shadow-sm h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-pen stat-card-icon"></i>
                            <h3 class="mt-2 mb-1">{{ $stats['signatures'] }}</h3>
                            <p class="mb-0">Signatures</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-danger shadow-sm h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-gear stat-card-icon"></i>
                            <h3 class="mt-2 mb-1">{{ $stats['settings'] }}</h3>
                            <p class="mb-0">Paramètres</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-info shadow-sm h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-bucket stat-card-icon"></i>
                            <h3 class="mt-2 mb-1">{{ $stats['housekeeping_tasks'] ?? 0 }}</h3>
                            <p class="mb-0">Tâches étages</p>
                            <small class="d-block mt-2">{{ $stats['room_state_history'] ?? 0 }} historiques d'états</small>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-success shadow-sm h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-bucket stat-card-icon"></i>
                            <h3 class="mt-2 mb-1">{{ $stats['laundry_collections'] ?? 0 }}</h3>
                            <p class="mb-0">Collectes buanderie</p>
                            <small class="d-block mt-2">{{ $stats['laundry_item_types'] ?? 0 }} types de linge</small>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-secondary shadow-sm h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-clock-history stat-card-icon"></i>
                            <h3 class="mt-2 mb-1">{{ ($hasPrintersModule ?? false) ? ($stats['print_logs'] + $stats['activity_logs']) : $stats['activity_logs'] }}</h3>
                            <p class="mb-0">Logs</p>
                            <small class="d-block mt-2">
                                @if($hasPrintersModule ?? false)
                                    {{ $stats['print_logs'] }} impression / {{ $stats['activity_logs'] }} activité
                                @else
                                    {{ $stats['activity_logs'] }} activité
                                @endif
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Utilisateurs -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-people-fill me-2"></i>Utilisateurs ({{ $users->count() }})</h5>
                    <a href="{{ route('super.users.index', ['hotel' => $hotel->id]) }}" class="btn btn-light btn-sm">
                        <i class="bi bi-eye me-1"></i>Voir tous
                    </a>
                </div>
                <div class="card-body p-0">
                    @if($users->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover table-striped align-middle mb-0 super-admin-table" aria-label="Utilisateurs de l'hôtel">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col"><i class="bi bi-person me-1 text-primary"></i>Nom</th>
                                    <th scope="col"><i class="bi bi-envelope me-1 text-primary"></i>Email</th>
                                    <th scope="col"><i class="bi bi-person-badge me-1 text-primary"></i>Rôles</th>
                                    <th scope="col"><i class="bi bi-calendar3 me-1 text-primary"></i>Créé le</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                <tr>
                                    <td><i class="bi bi-person-circle me-2"></i>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @foreach($user->roles as $role)
                                            <span class="badge bg-primary">{{ $role->name }}</span>
                                        @endforeach
                                    </td>
                                    <td>{{ $user->created_at->format('d/m/Y') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-inbox empty-state-icon"></i>
                        <p class="mt-2">Aucun utilisateur</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Types de Chambres -->
            @if($hotel->roomTypes->count() > 0)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-door-open me-2"></i>Types de Chambres ({{ $hotel->roomTypes->count() }})</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($hotel->roomTypes as $roomType)
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h6 class="card-title">{{ $roomType->name }}</h6>
                                    <p class="card-text text-muted small mb-1">
                                        <i class="bi bi-people me-1"></i>Capacité: {{ $roomType->capacity ?? 'N/A' }}
                                    </p>
                                    <p class="card-text text-muted small">
                                        <i class="bi bi-door-closed me-1"></i>Chambres: {{ $hotel->rooms->where('room_type_id', $roomType->id)->count() }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            {{-- Section Imprimantes masquée : module imprimantes retiré --}}

            <!-- Paramètres -->
            @if($settings->count() > 0)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="bi bi-gear me-2"></i>Paramètres Système ({{ $settings->count() }})</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($settings as $setting)
                        <div class="col-md-6 mb-3">
                            <div class="card border-start border-4 border-danger">
                                <div class="card-body py-2">
                                    <strong>{{ $setting->key }}</strong>
                                    <p class="mb-0 text-muted small">
                                        {{ Str::limit($setting->value, 50) }}
                                        <span class="badge bg-light text-dark ms-2">{{ $setting->type }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Enregistrements récents -->
            @if($recentReservations->count() > 0)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-calendar-check me-2"></i>10 Derniers Pré-enregistrements</h5>
                    <a href="{{ route('super.reservations.index', ['hotel' => $hotel->id]) }}" class="btn btn-light btn-sm">
                        <i class="bi bi-eye me-1"></i>Voir toutes
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover table-striped align-middle mb-0 super-admin-table" aria-label="Derniers pré-enregistrements">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col"><i class="bi bi-hash me-1 text-primary"></i>ID</th>
                                    <th scope="col"><i class="bi bi-person me-1 text-primary"></i>Client</th>
                                    <th scope="col"><i class="bi bi-flag me-1 text-primary"></i>Statut</th>
                                    <th scope="col"><i class="bi bi-tag me-1 text-primary"></i>Type</th>
                                    <th scope="col"><i class="bi bi-calendar3 me-1 text-primary"></i>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentReservations as $reservation)
                                <tr>
                                    <td><code>#{{ $reservation->id }}</code></td>
                                    <td>
                                        @if(isset($reservation->data['nom']) && isset($reservation->data['prenom']))
                                            {{ $reservation->data['nom'] }} {{ $reservation->data['prenom'] }}
                                        @else
                                            Non renseigné
                                        @endif
                                    </td>
                                    <td>
                                        @if($reservation->status === 'pending')
                                            <span class="badge bg-warning text-dark"><i class="bi bi-clock"></i> En attente</span>
                                        @elseif($reservation->status === 'validated')
                                            <span class="badge bg-success"><i class="bi bi-check-circle"></i> Validée</span>
                                        @elseif($reservation->status === 'rejected')
                                            <span class="badge bg-danger"><i class="bi bi-x-circle"></i> Rejetée</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $reservation->status }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($reservation->group_code)
                                            <span class="badge bg-info"><i class="bi bi-people"></i> Groupe</span>
                                        @else
                                            <span class="badge bg-secondary"><i class="bi bi-person"></i> Individuel</span>
                                        @endif
                                    </td>
                                    <td>{{ $reservation->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- Actions de Gestion des Données -->
            <div class="card shadow-sm border-warning mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Actions de Gestion des Données</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Export -->
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 border-success">
                                <div class="card-body">
                                    <h6 class="card-title text-success">
                                        <i class="bi bi-download me-2"></i>Export Complet
                                    </h6>
                                    <p class="card-text small">Téléchargez TOUTES les données de l'hôtel (types de chambres, chambres, utilisateurs, enregistrements + documents, imprimantes, paramètres, formulaires, etc.) au format JSON pour une sauvegarde complète. Les signatures ne sont pas incluses (données binaires).</p>
                                    <a href="{{ route('super.hotel-data.export', $hotel) }}" class="btn btn-success btn-sm">
                                        <i class="bi bi-download me-1"></i>Exporter tout (JSON)
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Import -->
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 border-info">
                                <div class="card-body">
                                    <h6 class="card-title text-info">
                                        <i class="bi bi-upload me-2"></i>Import Complet
                                    </h6>
                                    <p class="card-text small">Importez TOUTES les données depuis un fichier JSON exporté (types de chambres, chambres, utilisateurs, enregistrements + documents, imprimantes, paramètres, formulaires). Les données sont ajoutées aux données existantes. Les signatures ne sont pas importées (données binaires).</p>
                                    <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#importModal">
                                        <i class="bi bi-upload me-1"></i>Importer tout
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Reset -->
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 border-warning">
                                <div class="card-body">
                                    <h6 class="card-title text-warning">
                                        <i class="bi bi-arrow-clockwise me-2"></i>Réinitialisation
                                    </h6>
                                    <p class="card-text small">Supprime les données transactionnelles (conserve config).</p>
                                    <button onclick="openResetModal()" class="btn btn-warning btn-sm">
                                        <i class="bi bi-arrow-clockwise me-1"></i>Réinitialiser
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Purge -->
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 border-danger">
                                <div class="card-body">
                                    <h6 class="card-title text-danger">
                                        <i class="bi bi-trash me-2"></i>Purge Complète (DANGER)
                                    </h6>
                                    <p class="card-text small">⚠️ Supprime TOUTES les données : enregistrements, chambres, types de chambres, utilisateurs, paramètres, logs, etc. Action TOTALEMENT irréversible!</p>
                                    <button onclick="openPurgeModal()" class="btn btn-danger btn-sm">
                                        <i class="bi bi-trash me-1"></i>Purger tout
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Modal d'Import -->
    <div class="modal fade" id="importModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('super.hotel-data.import', $hotel) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title"><i class="bi bi-upload me-2"></i>Importer des Données</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Import automatique complet :</strong> Toutes les données du fichier JSON exporté seront automatiquement restaurées
                        </div>
                        
                        <div class="table-responsive mb-3">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th colspan="2" class="text-center bg-info text-white">
                                            <i class="bi bi-arrow-left-right me-2"></i>Correspondance Export ↔ Import
                                        </th>
                                    </tr>
                                    <tr>
                                        <th width="50%">Exporté</th>
                                        <th width="50%">Importé</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><i class="bi bi-door-closed text-primary me-1"></i>Types de chambres</td>
                                        <td><i class="bi bi-check2 text-success me-1"></i>Recréés</td>
                                    </tr>
                                    <tr>
                                        <td><i class="bi bi-key text-primary me-1"></i>Chambres</td>
                                        <td><i class="bi bi-check2 text-success me-1"></i>Recréées</td>
                                    </tr>
                                    <tr>
                                        <td><i class="bi bi-people text-primary me-1"></i>Utilisateurs + rôles</td>
                                        <td><i class="bi bi-check2 text-success me-1"></i>Recréés (password: <code>password123</code>)</td>
                                    </tr>
                                    <tr>
                                        <td><i class="bi bi-calendar-check text-primary me-1"></i>Enregistrements + documents</td>
                                        <td><i class="bi bi-check2 text-success me-1"></i>Recréées avec documents d'identité</td>
                                    </tr>
                                    <tr>
                                        <td><i class="bi bi-printer text-primary me-1"></i>Imprimantes</td>
                                        <td><i class="bi bi-check2 text-success me-1"></i>Recréées</td>
                                    </tr>
                                    <tr>
                                        <td><i class="bi bi-gear text-primary me-1"></i>Paramètres</td>
                                        <td><i class="bi bi-check2 text-success me-1"></i>Recréés</td>
                                    </tr>
                                    <tr>
                                        <td><i class="bi bi-ui-checks text-primary me-1"></i>Champs de formulaire</td>
                                        <td><i class="bi bi-check2 text-success me-1"></i>Recréés</td>
                                    </tr>
                                </tbody>
                            </table>
                            <small class="text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                <strong>Note :</strong> Les signatures ne sont pas exportées/importées car elles contiennent des données binaires. Seuls les documents d'identité (fichiers) sont conservés.
                            </small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="bi bi-file-earmark-arrow-up me-1"></i>Sélectionner le fichier JSON d'export
                            </label>
                            <input type="file" name="import_file" id="importFileInput" accept=".json" required class="form-control form-control-lg">
                            <small class="text-muted">
                                <i class="bi bi-info-circle me-1"></i>Format : <code>hotel_X_NomHotel_2025-11-13_143022.json</code>
                            </small>
                            <div id="fileInfo" class="mt-2 d-none">
                                <div class="alert alert-success py-2 mb-0">
                                    <i class="bi bi-check-circle me-1"></i>
                                    <strong>Fichier prêt :</strong> <span id="fileName"></span> (<span id="fileSize"></span>)
                            </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-warning mb-3">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Mode d'import :</strong> Les données seront <strong>AJOUTÉES</strong> aux données existantes.
                            <hr class="my-2">
                            <small>
                                💡 <strong>Pour une restauration propre :</strong>
                                <ol class="mb-0 ps-3">
                                    <li>Cliquez sur "Purger tout" (vider complètement l'hôtel)</li>
                                    <li>Puis cliquez sur "Importer tout" (restaurer depuis le JSON)</li>
                                </ol>
                            </small>
                        </div>
                        
                        <div class="bg-light p-3 rounded border">
                            <p class="fw-bold mb-2 small">
                                <i class="bi bi-question-circle text-primary me-1"></i>Comment ça marche ?
                            </p>
                            <ol class="small mb-0 ps-3">
                                <li>Cliquez sur "Exporter tout (JSON)" dans la section Export ci-dessus</li>
                                <li>Téléchargez et conservez le fichier <code>.json</code></li>
                                <li>Sélectionnez ce même fichier ici</li>
                                <li>Cliquez sur "Importer toutes les données"</li>
                                <li>Attendez la confirmation de succès ✅</li>
                            </ol>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <div class="me-auto">
                            <small class="text-muted">
                                <i class="bi bi-lightbulb me-1"></i>
                                <strong>Astuce :</strong> Export → Purge → Import = Restauration complète
                            </small>
                        </div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-info" id="importBtn">
                            <i class="bi bi-upload me-1"></i>
                            <span class="btn-text">Importer toutes les données</span>
                            <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de réinitialisation -->
    <div class="modal fade" id="resetModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('super.hotel-data.reset', $hotel) }}" method="POST">
                    @csrf
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Réinitialiser {{ $hotel->name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Réinitialisation partielle :</strong> Suppression des données transactionnelles uniquement
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p class="fw-bold text-danger mb-2">
                                    <i class="bi bi-trash me-1"></i>Sera supprimé :
                                </p>
                                <ul class="small">
                                    <li><strong>{{ $stats['reservations'] }}</strong> enregistrements</li>
                                    <li><strong>{{ $stats['identity_documents'] }}</strong> documents d'identité</li>
                                    <li><strong>{{ $stats['signatures'] }}</strong> signatures</li>
                                    <li><strong>{{ $stats['housekeeping_tasks'] ?? 0 }}</strong> tâches étages (housekeeping)</li>
                                    <li><strong>{{ $stats['room_state_history'] ?? 0 }}</strong> historiques d'états chambres</li>
                                    <li><strong>{{ $stats['laundry_collections'] ?? 0 }}</strong> collectes buanderie</li>
                                    <li><strong>{{ $stats['client_linen'] ?? 0 }}</strong> linge client</li>
                                    @if($hasPrintersModule ?? false)
                                    <li><strong>{{ $stats['print_logs'] }}</strong> logs d'impression</li>
                                    @endif
                                    <li><strong>{{ $stats['activity_logs'] }}</strong> logs d'activité</li>
                                </ul>
                                <p class="small text-muted mb-0">
                                    <i class="bi bi-info-circle me-1"></i>Données clients, transactions, étages et buanderie
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="fw-bold text-success mb-2">
                                    <i class="bi bi-shield-check me-1"></i>Sera conservé :
                                </p>
                                <ul class="small">
                                    <li><strong>{{ $stats['room_types'] }}</strong> types de chambres</li>
                                    <li><strong>{{ $stats['rooms'] }}</strong> chambres</li>
                                    <li><strong>{{ $stats['users'] }}</strong> utilisateurs</li>
                                    @if($hasPrintersModule ?? false)
                                    <li><strong>{{ $stats['printers'] }}</strong> imprimantes</li>
                                    @endif
                                    <li><strong>{{ $stats['settings'] }}</strong> paramètres</li>
                                    <li>Champs de formulaire</li>
                        </ul>
                                <p class="small text-muted mb-0">
                                    <i class="bi bi-info-circle me-1"></i>Structure et configuration
                                </p>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="bi bi-lightbulb me-1"></i>
                            <strong>Utilité :</strong> Nettoyer les anciens enregistrements tout en gardant la configuration de l'hôtel intacte (chambres, utilisateurs, etc.)
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tapez exactement <span class="text-warning">RESET</span> pour confirmer</label>
                            <input type="text" name="confirmation" required class="form-control form-control-lg" placeholder="RESET" autocomplete="off">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-warning" id="resetBtn">
                            <i class="bi bi-arrow-clockwise me-1"></i>
                            <span class="btn-text">Réinitialiser</span>
                            <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de purge -->
    <div class="modal fade" id="purgeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('super.hotel-data.purge', $hotel) }}" method="POST">
                    @csrf
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title"><i class="bi bi-trash me-2"></i>⚠️ DANGER - Purge Complète</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger">
                            <strong>⚠️ DANGER EXTRÊME :</strong> Cette action est TOTALEMENT IRRÉVERSIBLE et va supprimer ABSOLUMENT TOUTES les données de cet hôtel !
                        </div>
                        <p class="fw-bold text-danger mb-2">Ce qui sera DÉFINITIVEMENT supprimé :</p>
                        <ul class="mb-3">
                            <li><strong>{{ $stats['reservations'] }}</strong> enregistrements avec tous leurs documents</li>
                            <li><strong>{{ $stats['rooms'] }}</strong> chambres</li>
                            <li><strong>{{ $stats['room_types'] }}</strong> types de chambres</li>
                            <li><strong>{{ $stats['housekeeping_tasks'] ?? 0 }}</strong> tâches étages (housekeeping)</li>
                            <li><strong>{{ $stats['room_state_history'] ?? 0 }}</strong> historiques d'états chambres</li>
                            <li><strong>{{ $stats['laundry_collections'] ?? 0 }}</strong> collectes buanderie</li>
                                    <li><strong>{{ $stats['client_linen'] ?? 0 }}</strong> linge client</li>
                            <li><strong>{{ $stats['laundry_item_types'] ?? 0 }}</strong> types de linge</li>
                            <li><strong>{{ $stats['users'] }}</strong> utilisateurs (SUPPRIMÉS, pas désactivés)</li>
                            @if($hasPrintersModule ?? false)
                            <li><strong>{{ $stats['printers'] }}</strong> imprimantes</li>
                            @endif
                            <li><strong>{{ $stats['settings'] }}</strong> paramètres</li>
                            <li><strong>{{ $stats['identity_documents'] }}</strong> documents d'identité</li>
                            <li><strong>{{ $stats['signatures'] }}</strong> signatures</li>
                            <li>Tous les champs de formulaire personnalisés</li>
                            <li>Tous les logs ({{ ($hasPrintersModule ?? false) ? ($stats['print_logs'] + $stats['activity_logs']) : $stats['activity_logs'] }})</li>
                        </ul>
                        <div class="alert alert-warning">
                            <i class="bi bi-info-circle me-2"></i><strong>Note :</strong> L'hôtel lui-même ne sera PAS supprimé, mais il sera complètement vide.
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tapez exactement <span class="text-danger">PURGE</span> pour confirmer</label>
                            <input type="text" name="confirmation" required class="form-control" placeholder="PURGE" autocomplete="off">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Votre mot de passe (sécurité supplémentaire)</label>
                            <input type="password" name="password" required class="form-control" placeholder="••••••••" autocomplete="current-password">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-danger">Je confirme la purge</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

    <script>
        function openResetModal() {
    const modalEl = document.getElementById('resetModal');
    if (document.activeElement && document.activeElement.blur) document.activeElement.blur();
    modalEl.addEventListener('shown.bs.modal', function onShown() {
        modalEl.removeEventListener('shown.bs.modal', onShown);
        const t = modalEl.querySelector('button[data-bs-dismiss="modal"]') || modalEl.querySelector('.btn') || modalEl;
        if (t && typeof t.focus === 'function') t.focus();
    });
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
        }

        function openPurgeModal() {
    const modalEl = document.getElementById('purgeModal');
    if (document.activeElement && document.activeElement.blur) document.activeElement.blur();
    modalEl.addEventListener('shown.bs.modal', function onShown() {
        modalEl.removeEventListener('shown.bs.modal', onShown);
        const t = modalEl.querySelector('button[data-bs-dismiss="modal"]') || modalEl.querySelector('.btn') || modalEl;
        if (t && typeof t.focus === 'function') t.focus();
    });
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
}

// Gérer la soumission des formulaires avec feedback visuel
document.addEventListener('DOMContentLoaded', function() {
    // Formulaire d'import
    const importForm = document.querySelector('#importModal form');
    if (importForm) {
        importForm.addEventListener('submit', function(e) {
            const importBtn = document.getElementById('importBtn');
            const btnText = importBtn.querySelector('.btn-text');
            const spinner = importBtn.querySelector('.spinner-border');
            
            // Afficher le spinner et désactiver le bouton
            btnText.textContent = 'Importation en cours...';
            spinner.classList.remove('d-none');
            importBtn.disabled = true;
            
            console.log('Import en cours...');
        });
    }
    
    // Formulaire de réinitialisation
    const resetForm = document.querySelector('#resetModal form');
    if (resetForm) {
        resetForm.addEventListener('submit', function(e) {
            const resetBtn = document.getElementById('resetBtn');
            const btnText = resetBtn.querySelector('.btn-text');
            const spinner = resetBtn.querySelector('.spinner-border');
            
            // Afficher le spinner et désactiver le bouton
            btnText.textContent = 'Réinitialisation en cours...';
            spinner.classList.remove('d-none');
            resetBtn.disabled = true;
            
            console.log('Réinitialisation en cours...');
        });
    }
    
    // Formulaire de purge
    const purgeForm = document.querySelector('#purgeModal form');
    if (purgeForm) {
        purgeForm.addEventListener('submit', function(e) {
            console.log('Purge en cours...');
        });
    }
    
    // Validation du fichier avant soumission
    const importFileInput = document.getElementById('importFileInput');
    if (importFileInput) {
        importFileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            const fileInfo = document.getElementById('fileInfo');
            const fileName = document.getElementById('fileName');
            const fileSize = document.getElementById('fileSize');
            
            if (file) {
                console.log('Fichier sélectionné:', file.name, 'Taille:', file.size, 'bytes');
                
                // Vérifier que c'est bien un fichier JSON
                if (!file.name.endsWith('.json')) {
                    alert('⚠️ Attention : Le fichier doit être au format JSON');
                    e.target.value = '';
                    fileInfo.classList.add('d-none');
                    return;
                }
                
                // Vérifier la taille (max 20MB)
                if (file.size > 20 * 1024 * 1024) {
                    alert('❌ Erreur : Le fichier est trop volumineux (max 20 MB)');
                    e.target.value = '';
                    fileInfo.classList.add('d-none');
                    return;
                }
                
                // Afficher les informations du fichier
                fileName.textContent = file.name;
                fileSize.textContent = formatFileSize(file.size);
                fileInfo.classList.remove('d-none');
                
                console.log('✅ Fichier valide');
            } else {
                fileInfo.classList.add('d-none');
            }
        });
    }
});

// Fonction pour formater la taille du fichier
function formatFileSize(bytes) {
    if (bytes < 1024) return bytes + ' B';
    else if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(2) + ' KB';
    else return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
        }
    </script>

