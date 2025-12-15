<x-app-layout>
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-0">
                <i class="bi bi-speedometer2 me-2"></i>{{ __('Tableau de bord Réception') }}
            </h2>
            <p class="text-muted small mb-0">{{ auth()->user()->hotel->name }}</p>
        </div>
        <div>
            <button class="btn btn-outline-secondary btn-sm" onclick="window.location.reload();">
                <i class="bi bi-arrow-clockwise me-1"></i>Actualiser
            </button>
        </div>
    </div>
    <div class="py-4">
        <div class="container-fluid">
            
            <!-- Statistiques rapides -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
				<div class="card-body text-center">
                            <i class="bi bi-box-arrow-in-right" style="font-size: 2rem;"></i>
                            <h3 class="mt-2 mb-0">{{ $stats['arrivees_aujourd_hui'] }}</h3>
                            <p class="mb-0">Arrivées aujourd'hui</p>
				</div>
			</div>
		</div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
				<div class="card-body text-center">
                            <i class="bi bi-box-arrow-right" style="font-size: 2rem;"></i>
                            <h3 class="mt-2 mb-0">{{ $stats['departs_aujourd_hui'] }}</h3>
                            <p class="mb-0">Départs aujourd'hui</p>
				</div>
			</div>
		</div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
				<div class="card-body text-center">
                            <i class="bi bi-door-open" style="font-size: 2rem;"></i>
                            <h3 class="mt-2 mb-0">{{ $stats['chambres_occupees'] }} / {{ $stats['chambres_total'] }}</h3>
                            <p class="mb-0">Occupation</p>
				</div>
			</div>
		</div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
				<div class="card-body text-center">
                            <i class="bi bi-clock" style="font-size: 2rem;"></i>
                            <h3 class="mt-2 mb-0">{{ $stats['en_attente'] }}</h3>
                            <p class="mb-0">En attente</p>
				</div>
			</div>
		</div>
	</div>

            <!-- Nouvelles demandes (prioritaire) -->
            @if($nouvellesDemandes->count() > 0)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-bell-fill me-2"></i>
                        Nouvelles demandes à traiter ({{ $nouvellesDemandes->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr >
                                    <th class="text-black">Reçue</th>
                                    <th class="text-black">Client</th>
                                    <th class="text-black">Contact</th>
                                    <th class="text-black">Dates</th>
                                    <th class="text-black">Chambre</th>
                                    <th class="text-black text-center">Actions</th>
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
                                    <td class="text-center text-black">
                                        <div class="btn-group" role="group">
                                        @if($reservation->status === 'pending')
													<li>
														<form action="{{ route('reception.reservations.validate', $reservation->id) }}" method="POST" style="display: inline;">
															@csrf
															<button type="submit" class="dropdown-item btn btn-success btn-sm" onclick="return confirm('Valider cette réservation ?')" style="border: none; background: none; width: 100%; text-align: left; cursor: pointer;">
                                                                <i class="bi bi-check-circle btn btn-success btn-sm"></i>
															</button>
														</form>
													</li>
													<li>
														<form action="{{ route('reception.reservations.reject', $reservation->id) }}" method="POST" style="display: inline;">
															@csrf
															<button type="submit" class="dropdown-item btn btn-danger btn-sm" onclick="return confirm('Rejeter cette réservation ?')" style="border: none; background: none; width: 100%; text-align: left; cursor: pointer;">
                                                                <i class="bi bi-x-circle btn btn-danger btn-sm"></i>
															</button>
														</form>
													</li>
												@endif

                                            <a href="{{ route('reception.reservations.show', $reservation->id) }}" class="btn btn-info btn-sm" title="Voir détails">
                                                <i class="bi bi-eye"></i>
                                            </a>
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
                        Arrivées du jour ({{ $arriveesAujourdhui->count() }})
                    </h5>
				</div>
				<div class="card-body">
					<div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
								<tr>
									<th class="text-black">Client</th>
                                    <th class="text-black">Contact</th>
                                    <th class="text-black">Chambre</th>
                                    <th class="text-black">Nuits</th>
									<th class="text-black">Statut</th>
                                    <th class="text-center text-black">Actions</th>
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
                                    <td colspan="6" class="text-center text-muted py-4">
                                        Aucune arrivée prévue aujourd'hui
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
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-black">Chambre</th>
                                    <th class="text-black">Client</th>
                                    <th class="text-black">Contact</th>
                                    <th class="text-black">Nuits</th>
                                    <th class="text-black">Statut</th>
                                    <th class="text-center text-black">Actions</th>
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
                                    <td colspan="6" class="text-center text-muted py-4">
                                        Aucun départ prévu aujourd'hui
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
        if (!confirm('Valider cette réservation ?')) return;
        
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
                alert('✅ Réservation validée !');
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
                alert('✅ Réservation rejetée');
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
    
    // Auto-refresh toutes les 60 secondes
    setInterval(function() {
        window.location.reload();
    }, 60000);
    </script>
    @endpush
</x-app-layout>
