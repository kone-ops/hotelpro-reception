<x-app-layout>
    <x-slot name="header">Optimisation Système</x-slot>

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

    <!-- Statistiques système -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-hdd"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small">Taille du cache</div>
                            <h4 class="mb-0" id="stat-cache-size">{{ $stats['cache_size'] ?? 'N/A' }}</h4>
                            <small class="text-muted" id="stat-cache-update" style="font-size: 0.7rem;"></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                                <i class="bi bi-file-text"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small">Taille des logs</div>
                            <h4 class="mb-0" id="stat-log-size">{{ $stats['log_size'] ?? 'N/A' }}</h4>
                            <small class="text-muted" id="stat-log-update" style="font-size: 0.7rem;"></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="stat-icon bg-info bg-opacity-10 text-info">
                                <i class="bi bi-database"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small">Taille de la base de données</div>
                            <h4 class="mb-0" id="stat-database-size">{{ $stats['database_size'] ?? 'N/A' }}</h4>
                            <small class="text-muted" id="stat-database-update" style="font-size: 0.7rem;"></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="stat-icon bg-secondary bg-opacity-10 text-secondary">
                                <i class="bi bi-people"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small">Sessions actives</div>
                            <h4 class="mb-0" id="stat-session-count">{{ $stats['session_count'] ?? 0 }}</h4>
                            <small class="text-muted" id="stat-old-sessions">
                                @if(isset($stats['old_sessions']) && $stats['old_sessions'] > 0)
                                    {{ $stats['old_sessions'] }} anciennes
                                @endif
                            </small>
                            <small class="text-muted d-block" id="stat-session-update" style="font-size: 0.7rem;"></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions d'optimisation -->
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-trash me-2"></i>Vider les caches</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        Vide tous les caches de l'application (cache Laravel, config, routes, vues).
                        Cela peut améliorer les performances après des modifications de configuration.
                    </p>
                    <form method="POST" action="{{ route('super.optimization.clear-caches') }}" onsubmit="return confirm('Êtes-vous sûr de vouloir vider tous les caches ?')">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-trash me-2"></i>Vider les caches
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-speedometer2 me-2"></i>Optimiser la base de données</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        Optimise les tables de la base de données pour améliorer les performances.
                        Cette opération peut prendre quelques minutes selon la taille de la base.
                    </p>
                    <form method="POST" action="{{ route('super.optimization.optimize-database') }}" onsubmit="return confirm('Êtes-vous sûr de vouloir optimiser la base de données ? Cette opération peut prendre quelques minutes.')">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-speedometer2 me-2"></i>Optimiser la base de données
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0"><i class="bi bi-broom me-2"></i>Nettoyer les anciennes données</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        Supprime les sessions expirées, les logs d'activité anciens et les notifications lues anciennes.
                        Cette action est irréversible.
                    </p>
                    <form method="POST" action="{{ route('super.optimization.clean-old-data') }}" onsubmit="return confirm('Êtes-vous sûr de vouloir nettoyer les anciennes données ? Cette action est irréversible.')">
                        @csrf
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-broom me-2"></i>Nettoyer les anciennes données
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="bi bi-lightning-charge me-2"></i>Optimisation complète</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        Effectue toutes les opérations d'optimisation en une seule fois :
                        vidage des caches, optimisation de la base de données et nettoyage des anciennes données.
                    </p>
                    <form method="POST" action="{{ route('super.optimization.full') }}" onsubmit="return confirm('Êtes-vous sûr de vouloir effectuer une optimisation complète ? Cette opération peut prendre plusieurs minutes.')">
                        @csrf
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-lightning-charge me-2"></i>Optimisation complète
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Avertissement -->
    <div class="alert alert-info">
        <i class="bi bi-info-circle me-2"></i>
        <strong>Note :</strong> Les opérations d'optimisation peuvent prendre du temps selon la taille de votre base de données.
        Il est recommandé d'effectuer ces opérations pendant les périodes de faible activité.
    </div>

    <!-- Indicateur de mise à jour -->
    <div class="text-center mb-3">
        <small class="text-muted">
            <i class="bi bi-arrow-clockwise" id="refresh-icon"></i>
            <span id="last-update-text">Mise à jour automatique toutes les 30 secondes</span>
        </small>
    </div>
</x-app-layout>

<style>
.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

#refresh-icon {
    animation: spin 2s linear infinite;
    display: inline-block;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

#refresh-icon.paused {
    animation: none;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let updateInterval = null;
    const UPDATE_INTERVAL = 30000; // 30 secondes
    const refreshIcon = document.getElementById('refresh-icon');
    const lastUpdateText = document.getElementById('last-update-text');

    // Fonction pour formater la date
    function formatUpdateTime(date) {
        const now = new Date();
        const diff = Math.floor((now - date) / 1000);
        
        if (diff < 10) return 'À l\'instant';
        if (diff < 60) return `Il y a ${diff} secondes`;
        if (diff < 3600) return `Il y a ${Math.floor(diff / 60)} minutes`;
        return date.toLocaleTimeString('fr-FR');
    }

    // Fonction pour mettre à jour les statistiques
    async function updateStats() {
        try {
            refreshIcon.classList.remove('paused');
            
            const response = await fetch('{{ route("super.optimization.stats") }}', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                credentials: 'same-origin'
            });

            if (!response.ok) {
                throw new Error('Erreur lors de la récupération des stats');
            }

            const data = await response.json();
            
            if (data.success && data.stats) {
                const stats = data.stats;
                
                // Mettre à jour les valeurs
                document.getElementById('stat-cache-size').textContent = stats.cache_size || 'N/A';
                document.getElementById('stat-log-size').textContent = stats.log_size || 'N/A';
                document.getElementById('stat-database-size').textContent = stats.database_size || 'N/A';
                document.getElementById('stat-session-count').textContent = stats.session_count || 0;
                
                // Mettre à jour les anciennes sessions
                const oldSessionsEl = document.getElementById('stat-old-sessions');
                if (stats.old_sessions > 0) {
                    oldSessionsEl.textContent = `${stats.old_sessions} anciennes`;
                    oldSessionsEl.style.display = 'block';
                } else {
                    oldSessionsEl.textContent = '';
                    oldSessionsEl.style.display = 'none';
                }
                
                // Mettre à jour le timestamp
                if (stats.timestamp) {
                    const updateDate = new Date(stats.timestamp);
                    lastUpdateText.textContent = `Dernière mise à jour : ${formatUpdateTime(updateDate)}`;
                }
                
                // Ajouter une animation de mise à jour
                document.querySelectorAll('[id^="stat-"]').forEach(el => {
                    if (el.id.includes('-size') || el.id.includes('-count')) {
                        el.style.transition = 'all 0.3s ease';
                        el.style.transform = 'scale(1.05)';
                        setTimeout(() => {
                            el.style.transform = 'scale(1)';
                        }, 300);
                    }
                });
            }
        } catch (error) {
            console.error('Erreur lors de la mise à jour des stats:', error);
            lastUpdateText.textContent = 'Erreur lors de la mise à jour';
        } finally {
            refreshIcon.classList.add('paused');
        }
    }

    // Démarrer la mise à jour automatique
    function startAutoUpdate() {
        // Mise à jour immédiate
        updateStats();
        
        // Puis toutes les 30 secondes
        updateInterval = setInterval(updateStats, UPDATE_INTERVAL);
    }

    // Arrêter la mise à jour automatique
    function stopAutoUpdate() {
        if (updateInterval) {
            clearInterval(updateInterval);
            updateInterval = null;
        }
    }

    // Démarrer au chargement de la page
    startAutoUpdate();

    // Arrêter quand la page n'est plus visible (optimisation)
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            stopAutoUpdate();
        } else {
            startAutoUpdate();
        }
    });

    // Mettre à jour manuellement au clic sur l'icône
    refreshIcon.addEventListener('click', function() {
        updateStats();
    });
    refreshIcon.style.cursor = 'pointer';
    refreshIcon.title = 'Cliquer pour actualiser maintenant';
});
</script>

