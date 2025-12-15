@extends('layouts.app')

@section('title', 'Sélection d\'Imprimante')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-print"></i> 
                        Sélection d'Imprimante - 
                        {{ $printType === 'qr' ? 'QR Code' : 'Fiche de Police' }}
                    </h3>
                </div>
                
                <div class="card-body">
                    @if($printers->isEmpty())
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Aucune imprimante active trouvée dans votre hôtel.
                            <a href="{{ auth()->user()->hasRole('super-admin') ? route('super.printers.create') : route('hotel.printers.create') }}" class="btn btn-sm btn-primary ms-2">
                                <i class="fas fa-plus"></i> Ajouter une imprimante
                            </a>
                        </div>
                    @else
                        <div class="row">
                            @foreach($printers as $printer)
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="card h-100 printer-card" data-printer-id="{{ $printer->id }}">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h5 class="card-title mb-0">{{ $printer->name }}</h5>
                                                <span class="badge bg-{{ $printer->is_active ? 'success' : 'danger' }}">
                                                    {{ $printer->is_active ? 'En ligne' : 'Hors ligne' }}
                                                </span>
                                            </div>
                                            
                                            <div class="mb-2">
                                                <small class="text-muted">
                                                    <i class="bi bi-geo-alt"></i> {{ $printer->ip_address }}:{{ $printer->port ?? 9100 }}
                                                </small>
                                            </div>
                                            
                                            <div class="mb-2">
                                                <span class="badge bg-{{ $printer->type === 'ticket' ? 'info' : 'warning' }}">
                                                    {{ $printer->type === 'ticket' ? 'Ticket' : 'A4' }}
                                                </span>
                                            </div>
                                            
                                            <div class="d-flex gap-2">
                                                <button type="button" 
                                                        class="btn btn-primary btn-sm flex-fill select-printer-btn"
                                                        data-printer-id="{{ $printer->id }}"
                                                        data-printer-name="{{ $printer->name }}">
                                                    <i class="fas fa-print"></i> Imprimer
                                                </button>
                                                
                                                <a href="{{ auth()->user()->hasRole('super-admin') ? route('super.printers.test', $printer) : route('hotel.printers.test', $printer) }}" 
                                                   class="btn btn-outline-info btn-sm"
                                                   title="Tester la connexion">
                                                    <i class="bi bi-wifi"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    
                    <div class="d-flex justify-content-between mt-4">
                        @if($returnUrl)
                            <a href="{{ $returnUrl }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Retour
                            </a>
                        @else
                            <a href="{{ auth()->user()->hasRole('super-admin') ? route('super.printers.index') : route('hotel.printers.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Retour aux imprimantes
                            </a>
                        @endif
                        
                        <a href="{{ auth()->user()->hasRole('super-admin') ? route('super.printers.create') : route('hotel.printers.create') }}" class="btn btn-outline-primary">
                            <i class="fas fa-plus"></i> Ajouter une imprimante
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmation -->
<div class="modal fade" id="printConfirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmer l'impression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir imprimer sur l'imprimante <strong id="selected-printer-name"></strong> ?</p>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    L'impression sera envoyée immédiatement à l'imprimante sélectionnée.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="confirm-print-btn">
                    <i class="fas fa-print"></i> Confirmer l'impression
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let selectedPrinterId = null;
    let selectedPrinterName = null;
    
    // Gérer la sélection d'imprimante
    document.querySelectorAll('.select-printer-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            selectedPrinterId = this.dataset.printerId;
            selectedPrinterName = this.dataset.printerName;
            
            document.getElementById('selected-printer-name').textContent = selectedPrinterName;
            
            const modal = new bootstrap.Modal(document.getElementById('printConfirmModal'));
            modal.show();
        });
    });
    
    // Confirmer l'impression
    document.getElementById('confirm-print-btn').addEventListener('click', function() {
        if (!selectedPrinterId) return;
        
        const btn = this;
        const originalText = btn.innerHTML;
        
        // Désactiver le bouton et afficher le spinner
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Impression en cours...';
        
        // Envoyer la requête d'impression
        fetch('{{ route("hotel.print-selection.process") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                printer_id: selectedPrinterId,
                print_type: '{{ $printType }}',
                reservation_id: '{{ $reservationId }}',
                return_url: '{{ $returnUrl }}'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Afficher le message de succès
                const alert = document.createElement('div');
                alert.className = 'alert alert-success alert-dismissible fade show';
                alert.innerHTML = `
                    <i class="bi bi-check-circle me-2"></i>
                    ${data.message} sur l'imprimante ${data.printer_name}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                
                document.querySelector('.card-body').insertBefore(alert, document.querySelector('.row'));
                
                // Fermer le modal
                bootstrap.Modal.getInstance(document.getElementById('printConfirmModal')).hide();
                
                // Rediriger après 2 secondes
                setTimeout(() => {
                    @if($returnUrl)
                        window.location.href = '{{ $returnUrl }}';
                    @else
                        window.location.href = '{{ auth()->user()->hasRole("super-admin") ? route("super.printers.index") : route("hotel.printers.index") }}';
                    @endif
                }, 2000);
                
            } else {
                alert('Erreur: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur lors de l\'impression');
        })
        .finally(() => {
            // Réactiver le bouton
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    });
});
</script>
@endpush
