@extends('layouts.app')

@section('title', 'Scanner le Réseau')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-10 offset-lg-1">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="bi bi-search me-2"></i>Scanner le Réseau</h2>
                <a href="{{ auth()->user()->hasRole('super-admin') ? route('super.printers.index') : route('hotel.printers.index') }}" 
                   class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Retour à la liste
                </a>
            </div>

            <!-- Scan Form Card -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-hdd-network-fill me-2"></i>Configuration du Scan</h5>
                </div>
                <div class="card-body">
                    <form id="scanForm" method="POST" action="{{ auth()->user()->hasRole('super-admin') ? route('super.printers.scan') : route('hotel.printers.scan') }}">
                        @csrf
                        
                        @if(auth()->user()->hasRole('super-admin'))
                            <div class="mb-3">
                                <label for="hotel_id" class="form-label">
                                    <i class="bi bi-building me-2"></i>Hôtel
                                </label>
                                <select name="hotel_id" id="hotel_id" class="form-select" required>
                                    <option value="">Sélectionnez un hôtel</option>
                                    @foreach(\App\Models\Hotel::orderBy('name')->get() as $hotel)
                                        <option value="{{ $hotel->id }}">{{ $hotel->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="subnet" class="form-label">
                                        <i class="bi bi-diagram-3 me-2"></i>Sous-réseau à scanner
                                    </label>
                                    <input type="text" class="form-control" id="subnet" name="subnet" 
                                           value="192.168.1" placeholder="192.168.1" required>
                                    <small class="text-muted">Exemple: 192.168.1</small>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label for="start_ip" class="form-label">IP début</label>
                                    <input type="number" class="form-control" id="start_ip" name="start_ip" 
                                           value="1" min="1" max="254" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label for="end_ip" class="form-label">IP fin</label>
                                    <input type="number" class="form-control" id="end_ip" name="end_ip" 
                                           value="50" min="1" max="254" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="scan_mode" class="form-label">
                                        <i class="bi bi-speedometer me-2"></i>Mode de scan
                                    </label>
                                    <select class="form-select" id="scan_mode" name="scan_mode" onchange="updateScanMode()">
                                        <option value="fast" selected>Rapide (Port 9100 uniquement)</option>
                                        <option value="full">Complet (5 ports - max 50 IPs)</option>
                                    </select>
                                    <small class="text-muted" id="scanModeHelp">~15 secondes</small>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-warning" id="scanWarning">
                            <i class="bi bi-lightning-charge me-2"></i>
                            <strong>Mode Rapide:</strong> Scan des 4 ports les plus courants (9100, 9101, 515, 631).
                            <br><small class="text-muted">Détecte la plupart des imprimantes hôtelières. Utilisez le mode "Complet" pour une détection exhaustive.</small>
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg" id="scanButton">
                                <i class="bi bi-search me-2"></i>Lancer le Scan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="alert alert-info">
                <i class="bi bi-clock me-2"></i>
                <strong>Temps estimé :</strong>
                <ul class="mb-0 mt-2">
                    <li><strong>Mode Rapide</strong> (1-50 IPs) : 15-30 secondes</li>
                    <li><strong>Mode Complet</strong> (1-50 IPs) : 60-120 secondes</li>
                </ul>
                <small class="text-muted mt-2 d-block">⚠️ La page affichera automatiquement les résultats. Soyez patient !</small>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function updateScanMode() {
    const scanMode = document.getElementById('scan_mode').value;
    const endIp = document.getElementById('end_ip');
    const scanModeHelp = document.getElementById('scanModeHelp');
    const scanWarning = document.getElementById('scanWarning');
    
    if (scanMode === 'full') {
        // Mode complet: limiter à 50 IPs max
        if (parseInt(endIp.value) > 50) {
            endIp.value = 50;
        }
        endIp.max = 50;
        scanModeHelp.textContent = '~60-120 secondes (max 50 IPs)';
        scanWarning.innerHTML = `
            <i class="bi bi-hdd-network me-2"></i>
            <strong>Mode Complet:</strong> Scan de tous les ports d'imprimantes (ESC/POS, IPP, LPD, JetDirect, etc.).
            <br><small class="text-muted">Détection exhaustive de tous types d'imprimantes. Limité à 50 IPs maximum pour éviter les timeouts.</small>
        `;
        scanWarning.className = 'alert alert-info';
    } else {
        // Mode rapide: peut scanner jusqu'à 254 IPs
        endIp.max = 254;
        scanModeHelp.textContent = '~15-30 secondes';
        scanWarning.innerHTML = `
            <i class="bi bi-lightning-charge me-2"></i>
            <strong>Mode Rapide:</strong> Scan des 4 ports les plus courants (9100, 9101, 515, 631).
            <br><small class="text-muted">Détecte la plupart des imprimantes hôtelières. Utilisez le mode "Complet" pour une détection exhaustive.</small>
        `;
        scanWarning.className = 'alert alert-warning';
    }
}
</script>
@endpush

@endsection

