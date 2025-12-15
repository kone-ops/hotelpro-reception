@extends('layouts.app')

@section('title', 'Monitoring des Imprimantes - Système Révolutionnaire')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-tv"></i> 
                        Monitoring des Imprimantes en Temps Réel
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-success btn-sm" onclick="scanNetwork()">
                            <i class="fas fa-search"></i> Scanner le Réseau
                        </button>
                        <button type="button" class="btn btn-tool" data-card-widget="refresh">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Cartes de statut des imprimantes -->
                    <div class="row" id="printers-status">
                        <!-- Les cartes seront générées dynamiquement -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour le scan réseau -->
<div class="modal fade" id="scanNetworkModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Scan Réseau Automatique</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Sous-réseau à scanner:</label>
                    <input type="text" class="form-control" id="scan-subnet" value="192.168.1" placeholder="192.168.1">
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Port de début:</label>
                            <input type="number" class="form-control" id="scan-start-port" value="9100">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Port de fin:</label>
                            <input type="number" class="form-control" id="scan-end-port" value="9103">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Timeout (secondes):</label>
                    <input type="number" class="form-control" id="scan-timeout" value="1" min="1" max="10">
                </div>
                
                <div id="scan-results" style="display: none;">
                    <h6>Résultats du scan:</h6>
                    <div id="scan-results-list"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-primary" onclick="performScan()">
                    <i class="fas fa-search"></i> Lancer le Scan
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour créer une imprimante depuis le scan -->
<div class="modal fade" id="createPrinterModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Créer une Imprimante</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="createPrinterForm">
                    <div class="form-group">
                        <label>Nom de l'imprimante:</label>
                        <input type="text" class="form-control" id="printer-name" required>
                    </div>
                    <div class="form-group">
                        <label>Type:</label>
                        <select class="form-control" id="printer-type" required>
                            <option value="thermique">Thermique</option>
                            <option value="laser">Laser</option>
                            <option value="jet_encre">Jet d'encre</option>
                            <option value="multifonction">Multifonction</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Module:</label>
                        <select class="form-control" id="printer-module" required>
                            <option value="reception">Réception</option>
                            <option value="caisse">Caisse</option>
                            <option value="factures">Factures</option>
                            <option value="rapports">Rapports</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Technologie:</label>
                        <select class="form-control" id="printer-technologie">
                            <option value="thermique">Thermique (ESC/POS)</option>
                            <option value="laser">Laser</option>
                            <option value="jet_encre">Jet d'encre</option>
                        </select>
                    </div>
                    <input type="hidden" id="printer-ip">
                    <input type="hidden" id="printer-port">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" onclick="createPrinterFromScan()">
                    <i class="fas fa-plus"></i> Créer l'Imprimante
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Actualisation automatique du statut des imprimantes
setInterval(loadPrintersStatus, 10000); // Toutes les 10 secondes

$(document).ready(function() {
    loadPrintersStatus();
});

function loadPrintersStatus() {
    $.get('/api/printers', function(data) {
        if (data.success) {
            const container = $('#printers-status');
            container.empty();
            
            data.data.forEach(function(printer) {
                const card = createPrinterCard(printer);
                container.append(card);
            });
        }
    });
}

function createPrinterCard(printer) {
    const statusColor = getStatusColor(printer);
    const statusIcon = getStatusIcon(printer);
    const statusText = getStatusText(printer);
    
    return `
        <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
            <div class="card ${statusColor}">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="${statusIcon}"></i> ${printer.name}
                    </h5>
                    <div class="card-tools">
                        <button class="btn btn-tool btn-sm" onclick="testConnection(${printer.id})" title="Tester la connexion">
                            <i class="fas fa-wifi"></i>
                        </button>
                        <button class="btn btn-tool btn-sm" onclick="printTest(${printer.id})" title="Test d'impression">
                            <i class="fas fa-print"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <p><strong>Adresse:</strong> ${printer.ip_address}:${printer.port}</p>
                    <p><strong>Type:</strong> ${printer.type}</p>
                    <p><strong>Module:</strong> ${printer.module}</p>
                    <p><strong>Statut:</strong> ${statusText}</p>
                    <p><strong>Technologie:</strong> ${printer.technologie || 'thermique'}</p>
                    <div class="progress mb-2">
                        <div class="progress-bar" style="width: ${getHealthPercentage(printer)}%"></div>
                    </div>
                    <small class="text-muted">Santé: ${getHealthPercentage(printer)}%</small>
                </div>
                <div class="card-footer">
                    <button class="btn btn-primary btn-sm" onclick="showPrinterDetails(${printer.id})">
                        <i class="fas fa-eye"></i> Détails
                    </button>
                    <button class="btn btn-success btn-sm" onclick="uploadLogo(${printer.id})">
                        <i class="fas fa-image"></i> Logo
                    </button>
                </div>
            </div>
        </div>
    `;
}

function getStatusColor(printer) {
    if (!printer.is_active) return 'card-danger';
    if (printer.disponible) return 'card-success';
    if (printer.test_statut === 'echec') return 'card-warning';
    return 'card-info';
}

function getStatusIcon(printer) {
    if (!printer.is_active) return 'fas fa-times-circle';
    if (printer.disponible) return 'fas fa-check-circle';
    if (printer.test_statut === 'echec') return 'fas fa-exclamation-triangle';
    return 'fas fa-question-circle';
}

function getStatusText(printer) {
    if (!printer.is_active) return 'Inactive';
    if (printer.disponible) return 'En ligne';
    if (printer.test_statut === 'echec') return 'Erreur de connexion';
    if (printer.test_statut === 'succes') return 'Test réussi';
    return 'Non testé';
}

function getHealthPercentage(printer) {
    let health = 0;
    if (printer.is_active) health += 40;
    if (printer.disponible) health += 40;
    if (printer.test_statut === 'succes') health += 20;
    return health;
}

function testConnection(printerId) {
    $.post(`/printers/${printerId}/test-connection-advanced`, function(data) {
        if (data.success) {
            toastr.success(`Test de connexion ${data.connected ? 'réussi' : 'échoué'}`);
            loadPrintersStatus();
        } else {
            toastr.error('Erreur: ' + data.message);
        }
    });
}

function printTest(printerId) {
    $.post(`/printers/${printerId}/print-test-advanced`, function(data) {
        if (data.success) {
            toastr.success('Test d\'impression envoyé');
        } else {
            toastr.error('Erreur: ' + data.message);
        }
    });
}

function scanNetwork() {
    $('#scanNetworkModal').modal('show');
}

function performScan() {
    const subnet = $('#scan-subnet').val();
    const startPort = $('#scan-start-port').val();
    const endPort = $('#scan-end-port').val();
    const timeout = $('#scan-timeout').val();
    
    const params = {
        subnet: subnet,
        start_port: startPort,
        end_port: endPort,
        timeout: timeout
    };
    
    $.post('/printers/scan-network', params, function(data) {
        if (data.success) {
            const resultsContainer = $('#scan-results-list');
            resultsContainer.empty();
            
            if (data.found_printers.length > 0) {
                data.found_printers.forEach(function(printer) {
                    const item = `
                        <div class="alert alert-success">
                            <strong>${printer.name}</strong><br>
                            IP: ${printer.ip} | Port: ${printer.port}<br>
                            <button class="btn btn-sm btn-primary mt-2" onclick="createPrinterFromScanResult('${printer.ip}', ${printer.port})">
                                <i class="fas fa-plus"></i> Créer cette imprimante
                            </button>
                        </div>
                    `;
                    resultsContainer.append(item);
                });
            } else {
                resultsContainer.append('<div class="alert alert-info">Aucune imprimante trouvée sur le réseau.</div>');
            }
            
            $('#scan-results').show();
        } else {
            toastr.error('Erreur lors du scan: ' + data.message);
        }
    });
}

function createPrinterFromScanResult(ip, port) {
    $('#printer-ip').val(ip);
    $('#printer-port').val(port);
    $('#printer-name').val(`Imprimante ${ip}:${port}`);
    $('#createPrinterModal').modal('show');
}

function createPrinterFromScan() {
    const formData = {
        ip: $('#printer-ip').val(),
        port: $('#printer-port').val(),
        name: $('#printer-name').val(),
        type: $('#printer-type').val(),
        module: $('#printer-module').val(),
        technologie: $('#printer-technologie').val()
    };
    
    $.post('/printers/create-from-scan', formData, function(data) {
        if (data.success) {
            toastr.success('Imprimante créée avec succès');
            $('#createPrinterModal').modal('hide');
            loadPrintersStatus();
        } else {
            toastr.error('Erreur: ' + data.message);
        }
    });
}

function showPrinterDetails(printerId) {
    // Rediriger vers la page de détails de l'imprimante
    window.location.href = `/super/printers/${printerId}`;
}

function uploadLogo(printerId) {
    // Ouvrir le modal d'upload de logo
    window.location.href = `/super/printers/${printerId}/edit#logo-section`;
}
</script>
@endpush













