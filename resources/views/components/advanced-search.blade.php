@props(['action' => '', 'hotel' => null])

<div class="card border-0 shadow-sm mb-4" x-data="{ showFilters: false }">
    <div class="card-header bg-transparent">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-search me-2"></i>Recherche avancée</h5>
            <button type="button" class="btn btn-sm btn-outline-primary" @click="showFilters = !showFilters">
                <i class="bi bi-funnel me-1"></i>
                <span x-text="showFilters ? 'Masquer' : 'Afficher'"></span>
            </button>
        </div>
    </div>
    
    <div class="card-body" x-show="showFilters" x-transition>
        <form action="{{ $action }}" method="GET">
            <div class="row">
                <!-- Recherche textuelle -->
                <div class="col-md-4 mb-3">
                    <label class="form-label">Recherche</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Nom, email, téléphone..." value="{{ request('search') }}">
                    </div>
                    <small class="text-muted">Rechercher par nom, email, téléphone ou code groupe</small>
                </div>

                <!-- Statut -->
                <div class="col-md-2 mb-3">
                    <label class="form-label">Statut</label>
                    <select name="status" class="form-select">
                        <option value="">Tous</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                        <option value="validated" {{ request('status') === 'validated' ? 'selected' : '' }}>Validée</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejetée</option>
                    </select>
                </div>

                <!-- Type -->
                <div class="col-md-2 mb-3">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        <option value="">Tous</option>
                        <option value="individuel" {{ request('type') === 'individuel' ? 'selected' : '' }}>Individuel</option>
                        <option value="groupe" {{ request('type') === 'groupe' ? 'selected' : '' }}>Groupe</option>
                    </select>
                </div>

                <!-- Date de -->
                <div class="col-md-2 mb-3">
                    <label class="form-label">Date de</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>

                <!-- Date à -->
                <div class="col-md-2 mb-3">
                    <label class="form-label">Date à</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search me-1"></i>Rechercher
                </button>
                <a href="{{ $action }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i>Réinitialiser
                </a>
                <button type="button" class="btn btn-outline-info ms-auto" onclick="exportToExcel()">
                    <i class="bi bi-file-earmark-excel me-1"></i>Exporter Excel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Alpine.js pour l'interactivité -->
@once
    @push('scripts')
    <script defer src="{{ asset('assets/vendor/alpinejs/alpinejs.min.js') }}" onerror="this.onerror=null;this.src='https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js'"></script>
    <script>
        function exportToExcel() {
            // Récupérer les paramètres de recherche actuels
            const params = new URLSearchParams(window.location.search);
            params.append('export', 'excel');
            window.location.href = '{{ $action }}?' + params.toString();
        }
    </script>
    @endpush
@endonce





