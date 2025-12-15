@extends('layouts.app')

@section('title', 'Dashboard des Impressions - Système Révolutionnaire')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-print"></i> 
                        Dashboard des Impressions - Système Révolutionnaire
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="refresh">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Statistiques en temps réel -->
                    <div class="row mb-4">
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3 id="total-impressions">0</h3>
                                    <p>Total Impressions</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-print"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3 id="success-rate">0%</h3>
                                    <p>Taux de Succès</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3 id="pending-impressions">0</h3>
                                    <p>En Attente</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3 id="failed-impressions">0</h3>
                                    <p>Échecs</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filtres -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <select class="form-control" id="filter-printer">
                                <option value="">Toutes les imprimantes</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" id="filter-status">
                                <option value="">Tous les statuts</option>
                                <option value="en_attente">En attente</option>
                                <option value="en_cours">En cours</option>
                                <option value="succes">Succès</option>
                                <option value="echec">Échec</option>
                                <option value="annule">Annulé</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="date" class="form-control" id="filter-date" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-primary" onclick="refreshData()">
                                <i class="fas fa-sync-alt"></i> Actualiser
                            </button>
                        </div>
                    </div>

                    <!-- Tableau des logs d'impression -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="print-logs-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Date</th>
                                    <th>Imprimante</th>
                                    <th>Type</th>
                                    <th>Statut</th>
                                    <th>Tentatives</th>
                                    <th>Durée</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="print-logs-body">
                                <!-- Les données seront chargées via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour les détails d'impression -->
<div class="modal fade" id="printLogModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Détails de l'Impression</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="printLogDetails">
                <!-- Les détails seront chargés ici -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-primary" id="retryPrintBtn" style="display: none;">
                    <i class="fas fa-redo"></i> Relancer
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentPrintLogId = null;

// Actualisation automatique des données
setInterval(refreshData, 30000); // Toutes les 30 secondes

// Chargement initial des données
$(document).ready(function() {
    loadPrinters();
    refreshData();
    
    // Événements des filtres
    $('#filter-printer, #filter-status, #filter-date').on('change', refreshData);
});

function loadPrinters() {
    $.get('/api/printers', function(data) {
        const select = $('#filter-printer');
        select.empty().append('<option value="">Toutes les imprimantes</option>');
        
        data.data.forEach(function(printer) {
            select.append(`<option value="${printer.id}">${printer.name}</option>`);
        });
    });
}

function refreshData() {
    // Charger les statistiques
    loadStatistics();
    
    // Charger les logs d'impression
    loadPrintLogs();
}

function loadStatistics() {
    $.get('/print-logs/statistics', function(data) {
        if (data.success) {
            $('#total-impressions').text(data.data.total);
            $('#success-rate').text(data.data.taux_succes + '%');
            $('#pending-impressions').text(data.data.en_attente + data.data.en_cours);
            $('#failed-impressions').text(data.data.echec);
        }
    });
}

function loadPrintLogs() {
    const params = {
        printer_id: $('#filter-printer').val(),
        statut: $('#filter-status').val(),
        date_debut: $('#filter-date').val(),
        date_fin: $('#filter-date').val()
    };
    
    $.get('/api/print-logs', params, function(data) {
        if (data.success) {
            const tbody = $('#print-logs-body');
            tbody.empty();
            
            data.data.data.forEach(function(log) {
                const row = `
                    <tr>
                        <td>${log.id}</td>
                        <td>${new Date(log.created_at).toLocaleString()}</td>
                        <td>${log.printer ? log.printer.name : 'N/A'}</td>
                        <td>${log.type_document_label}</td>
                        <td>
                            <span class="badge badge-${getStatusColor(log.statut)}">
                                ${log.statut_label}
                            </span>
                        </td>
                        <td>${log.tentatives}</td>
                        <td>${log.duree_impression_formatee}</td>
                        <td>
                            <button class="btn btn-sm btn-info" onclick="showPrintLogDetails(${log.id})">
                                <i class="fas fa-eye"></i>
                            </button>
                            ${log.statut === 'echec' || log.statut === 'annule' ? 
                                `<button class="btn btn-sm btn-warning" onclick="retryPrint(${log.id})">
                                    <i class="fas fa-redo"></i>
                                </button>` : ''
                            }
                        </td>
                    </tr>
                `;
                tbody.append(row);
            });
        }
    });
}

function getStatusColor(status) {
    const colors = {
        'en_attente': 'warning',
        'en_cours': 'info',
        'succes': 'success',
        'echec': 'danger',
        'annule': 'secondary'
    };
    return colors[status] || 'secondary';
}

function showPrintLogDetails(logId) {
    $.get(`/api/print-logs/${logId}`, function(data) {
        if (data.success) {
            const log = data.data;
            currentPrintLogId = logId;
            
            const details = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Informations Générales</h6>
                        <p><strong>ID:</strong> ${log.id}</p>
                        <p><strong>Référence:</strong> ${log.reference}</p>
                        <p><strong>Type:</strong> ${log.type_document_label}</p>
                        <p><strong>Statut:</strong> 
                            <span class="badge badge-${getStatusColor(log.statut)}">
                                ${log.statut_label}
                            </span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <h6>Détails Techniques</h6>
                        <p><strong>Imprimante:</strong> ${log.printer ? log.printer.name : 'N/A'}</p>
                        <p><strong>Utilisateur:</strong> ${log.user ? log.user.name : 'N/A'}</p>
                        <p><strong>Tentatives:</strong> ${log.tentatives}</p>
                        <p><strong>Durée:</strong> ${log.duree_impression_formatee}</p>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <h6>Contenu Imprimé</h6>
                        <pre class="bg-light p-3">${log.contenu}</pre>
                    </div>
                </div>
                ${log.erreur ? `
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6>Erreur</h6>
                            <div class="alert alert-danger">${log.erreur}</div>
                        </div>
                    </div>
                ` : ''}
            `;
            
            $('#printLogDetails').html(details);
            
            // Afficher le bouton relancer si nécessaire
            if (log.statut === 'echec' || log.statut === 'annule') {
                $('#retryPrintBtn').show();
            } else {
                $('#retryPrintBtn').hide();
            }
            
            $('#printLogModal').modal('show');
        }
    });
}

function retryPrint(logId) {
    if (confirm('Voulez-vous vraiment relancer cette impression ?')) {
        $.post(`/print-logs/${logId}/retry`, function(data) {
            if (data.success) {
                toastr.success('Impression relancée avec succès');
                refreshData();
                $('#printLogModal').modal('hide');
            } else {
                toastr.error('Erreur: ' + data.message);
            }
        });
    }
}

// Événement du bouton relancer dans le modal
$('#retryPrintBtn').on('click', function() {
    if (currentPrintLogId) {
        retryPrint(currentPrintLogId);
    }
});
</script>
@endpush













