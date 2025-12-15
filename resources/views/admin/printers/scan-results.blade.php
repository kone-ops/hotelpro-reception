@extends('layouts.app')

@section('title', 'Résultats du Scan Réseau')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-10 offset-lg-1">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="bi bi-hdd-network me-2"></i>Résultats du Scan</h2>
                <div class="btn-group">
                    <a href="{{ auth()->user()->hasRole('super-admin') ? route('super.printers.showScan') : route('hotel.printers.showScan') }}" 
                       class="btn btn-info">
                        <i class="bi bi-arrow-clockwise me-2"></i>Nouveau Scan
                    </a>
                    <a href="{{ auth()->user()->hasRole('super-admin') ? route('super.printers.index') : route('hotel.printers.index') }}" 
                       class="btn btn-secondary">
                        <i class="bi bi-list me-2"></i>Retour à la liste
                    </a>
                </div>
            </div>

            <!-- Scan Info Card -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informations du Scan</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <p class="mb-2"><strong><i class="bi bi-diagram-3 me-2"></i>Sous-réseau:</strong> {{ $subnet }}.0/24</p>
                        </div>
                        <div class="col-md-2">
                            <p class="mb-2"><strong><i class="bi bi-graph-up me-2"></i>Plage:</strong> {{ $start_ip }}-{{ $end_ip }}</p>
                        </div>
                        <div class="col-md-3">
                            <p class="mb-2"><strong><i class="bi bi-speedometer me-2"></i>Type:</strong> {{ $scan_type ?? 'Standard' }}</p>
                        </div>
                        <div class="col-md-2">
                            <p class="mb-2"><strong><i class="bi bi-clock me-2"></i>Heure:</strong> {{ $scan_date->format('H:i:s') }}</p>
                        </div>
                        <div class="col-md-2">
                            <p class="mb-2"><strong><i class="bi bi-check-circle me-2"></i>Trouvées:</strong> <span class="badge bg-{{ $found > 0 ? 'success' : 'warning' }} fs-6">{{ $found }}</span></p>
                        </div>
                    </div>
                </div>
            </div>

            @if($success && $found > 0)
                <!-- Results Card - Success -->
                <div class="card shadow-lg">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-check-circle-fill me-2"></i>{{ $message }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th><i class="bi bi-hdd-network me-2"></i>Adresse IP</th>
                                        <th><i class="bi bi-door-open me-2"></i>Port</th>
                                        <th><i class="bi bi-info-circle me-2"></i>Description</th>
                                        <th><i class="bi bi-activity me-2"></i>Statut</th>
                                        <th class="text-center"><i class="bi bi-gear me-2"></i>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($foundPrinters as $index => $printer)
                                        <tr class="table-{{ $index === 0 ? 'primary' : '' }}">
                                            <td>
                                                <code class="fs-6">{{ $printer['ip'] }}</code>
                                                @if($index === 0)
                                                    <span class="badge bg-success ms-2">Nouveau</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-dark">{{ $printer['port'] }}</span>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $printer['description'] ?? '-' }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-success">
                                                    <i class="bi bi-wifi me-1"></i>{{ $printer['status'] }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <form action="{{ auth()->user()->hasRole('super-admin') ? route('super.printers.quick-add') : route('hotel.printers.quick-add') }}" 
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('Ajouter l\'imprimante {{ $printer['ip'] }}:{{ $printer['port'] }} ?');">
                                                    @csrf
                                                    <input type="hidden" name="ip_address" value="{{ $printer['ip'] }}">
                                                    <input type="hidden" name="port" value="{{ $printer['port'] }}">
                                                    <input type="hidden" name="description" value="{{ $printer['description'] ?? '' }}">
                                                    @if(auth()->user()->hasRole('super-admin') && $hotel_id)
                                                        <input type="hidden" name="hotel_id" value="{{ $hotel_id }}">
                                                    @endif
                                                    <button type="submit" class="btn btn-sm btn-primary">
                                                        <i class="bi bi-plus-lg me-1"></i>Ajouter
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Summary -->
                        <div class="alert alert-success mt-4">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <i class="bi bi-lightbulb-fill me-2"></i>
                                    <strong>{{ $found }} imprimante(s) détectée(s)</strong>
                                    <br><small>Cliquez sur "Ajouter" pour enregistrer une imprimante dans votre système.</small>
                                </div>
                                <div class="col-md-4 text-end">
                                    <a href="{{ auth()->user()->hasRole('super-admin') ? route('super.printers.index') : route('hotel.printers.index') }}" 
                                       class="btn btn-success">
                                        <i class="bi bi-list me-2"></i>Voir la Liste
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            @elseif($success && $found == 0)
                <!-- No New Results Card -->
                <div class="card">
                    <div class="card-header {{ ($existing_count ?? 0) > 0 ? 'bg-info text-white' : 'bg-warning text-dark' }}">
                        <h5 class="mb-0">
                            <i class="bi bi-{{ ($existing_count ?? 0) > 0 ? 'info-circle' : 'exclamation-triangle' }}-fill me-2"></i>
                            {{ ($existing_count ?? 0) > 0 ? 'Aucune nouvelle imprimante' : 'Aucune imprimante détectée' }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-{{ ($existing_count ?? 0) > 0 ? 'info' : 'warning' }}">
                            <h5 class="mb-3">{{ $message }}</h5>
                            
                            @if(($existing_count ?? 0) > 0)
                                <p class="mb-2">
                                    <i class="bi bi-check-circle me-2"></i>
                                    <strong>Bonne nouvelle :</strong> Vos imprimantes sont déjà configurées !
                                </p>
                                <p class="mb-0">
                                    <small>
                                        Les imprimantes détectées sur le réseau sont déjà dans votre système. 
                                        Elles n'apparaissent pas ici pour éviter les doublons.
                                    </small>
                                </p>
                                
                                <div class="mt-3">
                                    <a href="{{ auth()->user()->hasRole('super-admin') ? route('super.printers.index') : route('hotel.printers.index') }}" 
                                       class="btn btn-info">
                                        <i class="bi bi-list me-2"></i>Voir mes imprimantes ({{ $existing_count }})
                                    </a>
                                </div>
                            @else
                                <p class="mb-2"><strong>Vérifiez que :</strong></p>
                                <ul class="mb-0">
                                    <li>Les imprimantes sont allumées</li>
                                    <li>Les imprimantes sont sur le réseau {{ $subnet }}.x</li>
                                    <li>Le pare-feu Windows n'est pas actif</li>
                                    <li>Les ports 9100, 9101, 9102, 515, 631 sont accessibles</li>
                                    <li>Vous êtes sur le même réseau que les imprimantes</li>
                                </ul>
                            @endif
                        </div>

                        <div class="text-center mt-4">
                            <a href="{{ auth()->user()->hasRole('super-admin') ? route('super.printers.showScan') : route('hotel.printers.showScan') }}" 
                               class="btn btn-primary btn-lg">
                                <i class="bi bi-arrow-clockwise me-2"></i>Relancer le Scan
                            </a>
                        </div>
                    </div>
                </div>

            @else
                <!-- Error Card -->
                <div class="card border-danger">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-x-circle-fill me-2"></i>Erreur lors du scan
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-octagon me-2"></i>
                            <strong>{{ $message }}</strong>
                        </div>

                        <div class="alert alert-info">
                            <p class="mb-2"><strong>Vérifications à effectuer :</strong></p>
                            <ul class="mb-0">
                                <li>Le serveur Laravel est-il actif ?</li>
                                <li>Les routes sont-elles correctes ? <code>php artisan route:list</code></li>
                                <li>Consultez les logs : <code>storage/logs/laravel.log</code></li>
                                <li>Vérifiez la connexion réseau</li>
                            </ul>
                        </div>

                        <div class="text-center mt-4">
                            <a href="{{ auth()->user()->hasRole('super-admin') ? route('super.printers.showScan') : route('hotel.printers.showScan') }}" 
                               class="btn btn-warning btn-lg">
                                <i class="bi bi-arrow-clockwise me-2"></i>Réessayer
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Help Card -->
            <div class="card mt-4 border-info">
                <div class="card-header bg-info text-white">
                    <i class="bi bi-question-circle me-2"></i>Besoin d'aide ?
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="bi bi-book me-2"></i>Documentation</h6>
                            <ul class="small">
                                <li>Ports scannés : 9100, 9101, 9102, 515, 631</li>
                                <li>Timeout par IP : 1 seconde</li>
                                <li>Méthode : fsockopen (natif PHP)</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="bi bi-terminal me-2"></i>Debugging</h6>
                            <ul class="small">
                                <li>Logs : <code>storage/logs/laravel.log</code></li>
                                <li>Routes : <code>php artisan route:list</code></li>
                                <li>Config : Vérifier paramètres réseau</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

