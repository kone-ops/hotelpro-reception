<x-app-layout>
    <x-slot name="header">{{ __('Tableau de bord Réception') }}</x-slot>

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <p class="text-muted small mb-0"><i class="bi bi-building me-1"></i>{{ auth()->user()->hotel->name }}</p>
        <div class="d-flex gap-2">
            <a href="{{ route('reception.reservations.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-list-ul me-1"></i>Tous les enregistrements
            </a>
            <a href="{{ route('reception.rooms.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-door-open me-1"></i>État des chambres
            </a>
            <a href="{{ route('reception.guests.staying') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-person-check me-1"></i>Clients en séjour
            </a>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.location.reload();">
                <i class="bi bi-arrow-clockwise me-1"></i>Actualiser
            </button>
        </div>
    </div>

    <div class="py-2">
        <div class="container-fluid">

            <!-- Statistiques rapides -->
            <div class="row mb-3">
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm stat-card">
                        <div class="card-body text-center">
                            <i class="bi bi-box-arrow-in-right stat-card-icon text-primary"></i>
                            <h3 class="mt-1 mb-0">{{ $stats['arrivees_aujourd_hui'] }}</h3>
                            <p class="mb-0 small">Enregistrés</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm stat-card">
                        <div class="card-body text-center">
                            <i class="bi bi-box-arrow-right stat-card-icon text-primary"></i>
                            <h3 class="mt-1 mb-0">{{ $stats['departs_aujourd_hui'] }}</h3>
                            <p class="mb-0 small">Départs aujourd'hui</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm stat-card">
                        <div class="card-body text-center">
                            <i class="bi bi-door-open stat-card-icon text-primary"></i>
                            <h3 class="mt-1 mb-0">{{ $stats['chambres_occupees'] }} / {{ $stats['chambres_total'] }}</h3>
                            <p class="mb-0 small">Occupation</p>
                        </div>
                    </div>
                </div>
                <!-- <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm stat-card">
                        <div class="card-body text-center">
                            <i class="bi bi-clock stat-card-icon text-primary"></i>
                            <h3 class="mt-1 mb-0">{{ $stats['en_attente'] }}</h3>
                            <p class="mb-0 small">En attente</p>
                        </div>
                    </div>
                </div> -->
            </div>

            <!-- Action rapide : Linge client -->
            <div class="card border-0 shadow-sm mb-3 border-primary border">
                <div class="card-body py-2 d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-basket text-primary me-2 stat-card-icon"></i>
                        <div>
                            <h5 class="mb-1">Linge client – Dépôt à la réception</h5>
                            <p class="text-muted small mb-0">Enregistrer un dépôt de linge laissé par un client (à laver / à repasser). La buanderie sera notifiée.</p>
                        </div>
                    </div>
                    <a href="{{ route('reception.client-linen.index') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i>Enregistrer un linge client
                    </a>
                </div>
            </div>

            <!-- Nouvelles demandes (prioritaire) -->
            @if($nouvellesDemandes->count() > 0)
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header py-2">
                    <h5 class="mb-0 card-title">
                        <i class="bi bi-bell-fill me-2"></i>
                        Enregistrement(s) à traiter ({{ $nouvellesDemandes->count() }})
                    </h5>
                </div>
                <div class="card-body py-2">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover table-striped align-middle mb-0 app-table table-compact" aria-label="Nouvelles demandes à traiter">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col"><i class="bi bi-clock me-1 text-muted"></i>Reçue</th>
                                    <th scope="col"><i class="bi bi-person me-1 text-muted"></i>Client</th>
                                    <th scope="col"><i class="bi bi-telephone me-1 text-muted"></i>Contact</th>
                                    <th scope="col"><i class="bi bi-calendar-range me-1 text-muted"></i>Dates</th>
                                    <th scope="col"><i class="bi bi-door-open me-1 text-muted"></i>Chambre</th>
                                    <th scope="col" class="text-center w-actions">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($nouvellesDemandes as $reservation)
                                <tr>
                                    <td>
                                        <strong class="text-danger">{{ $reservation->created_at->diffForHumans() }}</strong>
                                    </td>
                                    <td>
                                        <strong class="text-black">{{ $reservation->client_full_name }}</strong><br>
                                        <small class="text-black">{{ $reservation->data['nombre_adultes'] }} adulte(s), {{ $reservation->data['nombre_enfants'] ?? 0 }} enfant(s)</small>
                                    </td>
                                    <td>
                                        <small class="text-black">
                                            <i class="text-black bi bi-envelope me-1"></i>{{ $reservation->client_email }}<br>
                                            <i class="text-black bi bi-telephone me-1"></i>{{ $reservation->client_phone }}
                                        </small>
                                    </td>
                                    <td class="text-black">
                                        {{ $reservation->check_in_date->format('d/m') }} → {{ $reservation->check_out_date->format('d/m') }}<br>
                                        <small class="text-black badge bg-info">{{ $reservation->check_in_date->diffInDays($reservation->check_out_date) }} nuits</small>
                                    </td>
                                    <td>
                                        <small class="text-black">{{ $reservation->roomType->name ?? 'Non spécifié' }}</small>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex flex-wrap gap-1 justify-content-center">
                                            <a href="{{ route('reception.reservations.show', $reservation->id) }}" class="btn btn-outline-primary btn-sm" title="Voir détails">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @if($reservation->status === 'pending')
                                                <form action="{{ route('reception.reservations.validate', $reservation->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Valider cet enregistrement ?')" title="Valider">
                                                        <i class="bi bi-check-circle"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('reception.reservations.reject', $reservation->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Rejeter cet enregistrement ?')" title="Rejeter">
                                                        <i class="bi bi-x-circle"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- Arrivées du jour -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-box-arrow-in-right me-2 "></i>
                        Enregistrements du jour ({{ $arriveesAujourdhui->count() }})
                    </h5>
				</div>
				<div class="card-body">
					<div class="table-responsive">
                        <table class="table table-sm table-hover table-striped align-middle mb-0 app-table" aria-label="Arrivées du jour">
                            <thead class="table-light">
								<tr>
									<th scope="col" class="text-black"><i class="bi bi-person me-1 text-muted"></i>Client</th>
                                    <th scope="col" class="text-black"><i class="bi bi-telephone me-1 text-muted"></i>Contact</th>
                                    <th scope="col" class="text-black"><i class="bi bi-door-open me-1 text-muted"></i>Chambre</th>
                                    <th scope="col" class="text-black"><i class="bi bi-moon me-1 text-muted"></i>Nuits</th>
									<th scope="col" class="text-black"><i class="bi bi-tag me-1 text-muted"></i>Statut</th>
                                    <th scope="col" class="text-center w-actions">Actions</th>
								</tr>
							</thead>
							<tbody>
                                @forelse($arriveesAujourdhui as $reservation)
								<tr>
                                    <td class="text-black">
                                        <strong class="text-black">{{ $reservation->client_full_name }}</strong><br>
                                        <small class="text-black">{{ $reservation->data['nombre_adultes'] }} adulte(s), {{ $reservation->data['nombre_enfants'] ?? 0 }} enfant(s)</small>
                                    </td>
                                    <td>
                                        <small class="text-black">
                                            <i class="text-black bi bi-envelope me-1"></i>{{ $reservation->client_email }}<br>
                                            <i class="text-black bi bi-telephone me-1"></i>{{ $reservation->client_phone }}
                                        </small>
                                    </td>
                                    <td class="text-black">
                                        @if($reservation->room)
                                            <span class="text-black badge bg-info"><i class="text-black bi bi-door-closed me-1"></i>{{ $reservation->room->room_number }}</span>
                                        @else
                                            <span class="text-black badge bg-warning">Non assignée</span>
                                        @endif
                                    </td>
                                    <td class="text-black">{{ $reservation->check_in_date->diffInDays($reservation->check_out_date) }}</td>
                                    <td class="text-black">
                                        @if($reservation->status === 'checked_in')
                                            <span class="text-black badge bg-success">Présent</span>
                                        @elseif($reservation->status === 'validated')
                                            <span class="text-black badge bg-info">Validée</span>
                                        @else
                                            <span class="text-black badge bg-warning">En attente</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('reception.reservations.show', $reservation->id) }}" class="btn btn-primary btn-sm" title="Voir détails">
                                            <i class="bi bi-eye"></i> Voir
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6">
                                        <x-super.empty-table icon="bi-box-arrow-in-right" title="Aucune arrivée prévue aujourd'hui" message="Les arrivées du jour s'affichent ici." />
									</td>
								</tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Départs du jour -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-box-arrow-right me-2"></i>
                        Départs du jour ({{ $departsAujourdhui->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover table-striped align-middle mb-0 app-table" aria-label="Départs du jour">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" class="text-black"><i class="bi bi-door-open me-1 text-muted"></i>Chambre</th>
                                    <th scope="col" class="text-black"><i class="bi bi-person me-1 text-muted"></i>Client</th>
                                    <th scope="col" class="text-black"><i class="bi bi-telephone me-1 text-muted"></i>Contact</th>
                                    <th scope="col" class="text-black"><i class="bi bi-moon me-1 text-muted"></i>Nuits</th>
                                    <th scope="col" class="text-black"><i class="bi bi-tag me-1 text-muted"></i>Statut</th>
                                    <th scope="col" class="text-center w-actions">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($departsAujourdhui as $reservation)
                                <tr class="text-black">
                                    <td>
                                        @if($reservation->room)
                                            <span class="text-black badge bg-info"><i class="text-black bi bi-door-closed me-1"></i>{{ $reservation->room->room_number }}</span>
                                        @else
                                            <span class="text-black badge bg-secondary">-</span>
                                        @endif
                                    </td>
                                    <td><strong class="text-black">{{ $reservation->client_full_name }}</strong></td>
                                    <td>
                                        <small class="text-black">
                                            <i class="text-black bi bi-envelope me-1"></i>{{ $reservation->client_email }}<br>
                                            <i class="text-black bi bi-telephone me-1"></i>{{ $reservation->client_phone }}
                                        </small>
                                    </td>
                                    <td class="text-black">{{ $reservation->check_in_date->diffInDays($reservation->check_out_date) }}</td>
                                    <td class="text-black">
                                        @if($reservation->status === 'checked_out')
                                            <span class="text-black badge bg-secondary">Parti</span>
                                        @else
                                            <span class="text-black badge bg-success">Présent</span>
                                        @endif
                                    </td>
                                    <td class="text-center text-black">
                                        <a href="{{ route('reception.reservations.show', $reservation->id) }}" class="btn btn-primary btn-sm" title="Voir détails">
                                            <i class="bi bi-eye"></i> Voir
                                        </a>
									</td>
								</tr>
                                @empty
								<tr>
                                    <td colspan="6">
                                        <x-super.empty-table icon="bi-box-arrow-right" title="Aucun départ prévu aujourd'hui" message="Les départs du jour s'affichent ici." />
									</td>
								</tr>
                                @endforelse
							</tbody>
						</table>
					</div>
				</div>
			</div>
            
		</div>
	</div>
    
    @push('scripts')
    <script>
    // Valider demande
    function valider(reservationId) {
        if (!confirm('Valider cet enregistrement ?')) return;
        
        fetch(`/reception/pre-reservations/${reservationId}/validate`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✅ Enregistrement validé !');
                window.location.reload();
            } else {
                alert('❌ Erreur: ' + data.message);
            }
        })
        .catch(error => {
            alert('❌ Erreur de connexion');
            console.error(error);
        });
    }
    
    // Rejeter demande
    function rejeter(reservationId) {
        const raison = prompt('Raison du rejet (optionnel):');
        if (raison === null) return;
        
        fetch(`/reception/pre-reservations/${reservationId}/reject`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ raison: raison })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✅ Enregistrement rejeté');
                window.location.reload();
            } else {
                alert('❌ Erreur: ' + data.message);
            }
        })
        .catch(error => {
            alert('❌ Erreur de connexion');
            console.error(error);
        });
    }
    
    // Actualisation automatique toutes les 2 minutes (optionnel)
    setInterval(function() {
        window.location.reload();
    }, 120000);
    </script>
    @endpush
</x-app-layout>
