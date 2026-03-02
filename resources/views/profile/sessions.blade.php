<x-app-layout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0"><i class="bi bi-shield-check me-2"></i>Mes Sessions Actives</h4>
            <small class="text-muted">Gérez vos sessions actives sur différents appareils</small>
        </div>
        @if($activeCount > 1)
            <form method="POST" action="{{ route('sessions.destroy-others') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir déconnecter toutes les autres sessions ?')">
                    <i class="bi bi-x-circle me-1"></i>Déconnecter toutes les autres
                </button>
            </form>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-x-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('session_removed'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            Une de vos sessions a été automatiquement déconnectée car vous avez atteint la limite de {{ $maxSessions }} sessions simultanées.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Informations générales -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-1">
                        <i class="bi bi-info-circle me-2 text-primary"></i>Informations
                    </h5>
                    <p class="text-muted mb-0">
                        Vous avez <strong>{{ $activeCount }}</strong> session(s) active(s) sur <strong>{{ $maxSessions }}</strong> autorisée(s).
                    </p>
                </div>
                <div class="col-md-6 text-md-end mt-3 mt-md-0">
                    @if($activeCount >= $maxSessions)
                        <span class="badge bg-warning">
                            <i class="bi bi-exclamation-triangle me-1"></i>Limite atteinte
                        </span>
                    @else
                        <span class="badge bg-success">
                            <i class="bi bi-check-circle me-1"></i>{{ $maxSessions - $activeCount }} session(s) disponible(s)
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des sessions -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Sessions Actives</h5>
        </div>
        <div class="card-body p-0">
            @if($sessions->isEmpty())
                <div class="text-center p-5">
                    <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                    <p class="text-muted mt-3">Aucune session active</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped align-middle mb-0 app-table" aria-label="Sessions actives">
                        <thead class="table-light">
                            <tr>
                                <th scope="col"><i class="bi bi-device-hdd me-1 text-muted"></i>Appareil / Navigateur</th>
                                <th scope="col" class="d-none d-lg-table-cell"><i class="bi bi-geo-alt me-1 text-muted"></i>Localisation</th>
                                <th scope="col" class="d-none d-md-table-cell"><i class="bi bi-hdd-network me-1 text-muted"></i>Adresse IP</th>
                                <th scope="col"><i class="bi bi-clock me-1 text-muted"></i>Dernière activité</th>
                                <th scope="col"><i class="bi bi-tag me-1 text-muted"></i>Statut</th>
                                <th scope="col" class="text-end" style="width: 100px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sessions as $session)
                                <tr class="{{ $session->is_current ? 'table-primary' : '' }} {{ $session->is_suspicious ? 'table-warning' : '' }}">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @php
                                                $deviceIcon = match($session->device_type) {
                                                    'mobile' => 'phone',
                                                    'tablet' => 'tablet',
                                                    default => 'laptop'
                                                };
                                            @endphp
                                            <i class="bi bi-{{ $deviceIcon }} me-2 text-primary"></i>
                                            <div>
                                                <strong>{{ $session->device_name ?? 'Appareil inconnu' }}</strong>
                                                <br>
                                                <small class="text-muted">
                                                    {{ $session->browser ?? 'Navigateur inconnu' }} sur {{ $session->platform ?? 'OS inconnu' }}
                                                </small>
                                                @if($session->is_trusted_device)
                                                    <br>
                                                    <span class="badge bg-success mt-1">
                                                        <i class="bi bi-shield-check me-1"></i>Appareil de confiance
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($session->country)
                                            <div>
                                                <i class="bi bi-geo-alt me-1"></i>
                                                <strong>{{ $session->city ?? $session->region ?? $session->country }}</strong>
                                                @if($session->city && $session->region)
                                                    <br>
                                                    <small class="text-muted">{{ $session->region }}, {{ $session->country }}</small>
                                                @elseif($session->region)
                                                    <br>
                                                    <small class="text-muted">{{ $session->country }}</small>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted">Localisation inconnue</span>
                                        @endif
                                    </td>
                                    <td class="d-none d-md-table-cell">
                                        <code>{{ $session->ip_address ?? 'N/A' }}</code>
                                    </td>
                                    <td>
                                        @if($session->last_activity)
                                            <div>
                                                <strong>{{ $session->last_activity->diffForHumans() }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $session->last_activity->format('d/m/Y H:i') }}</small>
                                            </div>
                                        @else
                                            <span class="text-muted">Jamais</span>
                                        @endif
                                        @if($session->first_seen_at)
                                            <br>
                                            <small class="text-muted">
                                                <i class="bi bi-clock-history me-1"></i>
                                                Première connexion: {{ $session->first_seen_at->format('d/m/Y') }}
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($session->is_current)
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle me-1"></i>Session actuelle
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">Autre session</span>
                                        @endif
                                        
                                        @if($session->is_suspicious)
                                            <br>
                                            <span class="badge bg-warning mt-1" data-bs-toggle="tooltip" data-bs-placement="top" 
                                                  title="{{ implode(', ', $session->suspicious_reasons ?? []) }}">
                                                <i class="bi bi-exclamation-triangle me-1"></i>Suspect
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($session->is_current)
                                            <span class="text-muted small">Session actuelle</span>
                                        @else
                                            <div class="btn-group" role="group">
                                                @if(!$session->is_trusted_device)
                                                    <form method="POST" action="{{ route('sessions.trust', $session->session_id) }}" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-success" 
                                                                data-bs-toggle="tooltip" title="Marquer comme appareil de confiance">
                                                            <i class="bi bi-shield-check"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                <form method="POST" action="{{ route('sessions.destroy', $session->session_id) }}" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                            onclick="return confirm('Êtes-vous sûr de vouloir déconnecter cette session ?')">
                                                        <i class="bi bi-x-circle me-1"></i>Déconnecter
                                                    </button>
                                                </form>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- Aide -->
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-body">
            <h6 class="mb-3"><i class="bi bi-question-circle me-2 text-info"></i>Informations</h6>
            <ul class="mb-0 small text-muted">
                <li>La <strong>session actuelle</strong> est celle que vous utilisez en ce moment.</li>
                <li>Vous pouvez avoir jusqu'à <strong>{{ $maxSessions }} sessions simultanées</strong>.</li>
                <li>Si vous atteignez la limite, la session la plus ancienne sera automatiquement déconnectée.</li>
                <li>Les sessions <span class="badge bg-warning">suspectes</span> présentent des caractéristiques inhabituelles (nouvel appareil, nouvelle IP, etc.).</li>
                <li>Vous pouvez marquer un appareil comme <span class="badge bg-success">de confiance</span> pour recevoir moins d'alertes.</li>
                <li>Si vous remarquez une session suspecte, déconnectez-la immédiatement et changez votre mot de passe.</li>
            </ul>
        </div>
    </div>

    <script>
        // Initialiser les tooltips Bootstrap
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    </script>
</x-app-layout>
