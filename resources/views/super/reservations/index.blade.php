<x-app-layout>
	<x-slot name="header">Tous les enregistrements</x-slot>
	
	<!-- Statistiques -->
	<div class="row mb-4">
		<div class="col-md-2 mb-3">
			<div class="card border-0 shadow-sm h-100">
				<div class="card-body">
					<div class="d-flex align-items-center">
						<div class="flex-shrink-0">
							<div class="stat-icon bg-primary bg-opacity-10 text-primary">
								<i class="bi bi-calendar-check"></i>
							</div>
						</div>
						<div class="flex-grow-1 ms-3">
							<div class="text-muted small">Total</div>
							<h3 class="mb-0">{{ $stats['total'] }}</h3>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<div class="col-md-2 mb-3">
			<div class="card border-0 shadow-sm h-100">
				<div class="card-body">
					<div class="d-flex align-items-center">
						<div class="flex-shrink-0">
							<div class="stat-icon bg-warning bg-opacity-10 text-warning">
								<i class="bi bi-clock"></i>
							</div>
						</div>
						<div class="flex-grow-1 ms-3">
							<div class="text-muted small">En attente</div>
							<h3 class="mb-0">{{ $stats['pending'] }}</h3>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<div class="col-md-2 mb-3">
			<div class="card border-0 shadow-sm h-100">
				<div class="card-body">
					<div class="d-flex align-items-center">
						<div class="flex-shrink-0">
							<div class="stat-icon bg-success bg-opacity-10 text-success">
								<i class="bi bi-check-circle"></i>
							</div>
						</div>
						<div class="flex-grow-1 ms-3">
							<div class="text-muted small">Validées</div>
							<h3 class="mb-0">{{ $stats['validated'] }}</h3>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<div class="col-md-2 mb-3">
			<div class="card border-0 shadow-sm h-100">
				<div class="card-body">
					<div class="d-flex align-items-center">
						<div class="flex-shrink-0">
							<div class="stat-icon bg-info bg-opacity-10 text-info">
								<i class="bi bi-door-open"></i>
							</div>
						</div>
						<div class="flex-grow-1 ms-3">
							<div class="text-muted small">En séjour</div>
							<h3 class="mb-0">{{ $stats['checked_in'] ?? 0 }}</h3>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<div class="col-md-2 mb-3">
			<div class="card border-0 shadow-sm h-100">
				<div class="card-body">
					<div class="d-flex align-items-center">
						<div class="flex-shrink-0">
							<div class="stat-icon bg-secondary bg-opacity-10 text-secondary">
								<i class="bi bi-door-closed"></i>
							</div>
						</div>
						<div class="flex-grow-1 ms-3">
							<div class="text-muted small">Parti</div>
							<h3 class="mb-0">{{ $stats['checked_out'] ?? 0 }}</h3>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<div class="col-md-2 mb-3">
			<div class="card border-0 shadow-sm h-100">
				<div class="card-body">
					<div class="d-flex align-items-center">
						<div class="flex-shrink-0">
							<div class="stat-icon bg-danger bg-opacity-10 text-danger">
								<i class="bi bi-x-circle"></i>
							</div>
						</div>
						<div class="flex-grow-1 ms-3">
							<div class="text-muted small">Rejetées</div>
							<h3 class="mb-0">{{ $stats['rejected'] }}</h3>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<!-- Filtres -->
	<div class="card border-0 shadow-sm mb-4">
		<div class="card-header bg-transparent">
			<div class="d-flex justify-content-between align-items-center">
				<h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filtres</h5>
				<button type="button" class="btn btn-sm btn-outline-secondary" onclick="resetFilters()">
					<i class="bi bi-arrow-counterclockwise me-1"></i>Reinitialiser
				</button>
			</div>
		</div>
		<div class="card-body">
			<form method="GET" action="{{ route('super.reservations.index') }}" id="filterForm">
				<div class="row">
					<div class="col-md-3 mb-3">
						<label class="form-label small">Hotel</label>
						<select name="hotel_id" class="form-select form-select-sm">
							<option value="">Tous les hotels</option>
							@foreach($hotels as $hotel)
								<option value="{{ $hotel->id }}" {{ request('hotel_id') == $hotel->id ? 'selected' : '' }}>
									{{ $hotel->name }}
								</option>
							@endforeach
						</select>
					</div>
					
					<div class="col-md-2 mb-3">
						<label class="form-label small">Statut</label>
						<select name="status" class="form-select form-select-sm">
							<option value="">Tous les statuts</option>
							<option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
							<option value="validated" {{ request('status') == 'validated' ? 'selected' : '' }}>Valid�e</option>
							<option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejet�e</option>
						</select>
					</div>
					
					<div class="col-md-2 mb-3">
						<label class="form-label small">Type</label>
						<select name="type" class="form-select form-select-sm">
							<option value="">Tous les types</option>
							<option value="Individuel" {{ request('type') == 'Individuel' ? 'selected' : '' }}>Individuel</option>
							<option value="Groupe" {{ request('type') == 'Groupe' ? 'selected' : '' }}>Groupe</option>
						</select>
					</div>
					
					<div class="col-md-2 mb-3">
						<label class="form-label small">Du</label>
						<input type="date" name="date_debut" class="form-control form-control-sm" value="{{ request('date_debut') }}">
					</div>
					
					<div class="col-md-2 mb-3">
						<label class="form-label small">Au</label>
						<input type="date" name="date_fin" class="form-control form-control-sm" value="{{ request('date_fin') }}">
					</div>
					
					<div class="col-md-1 mb-3 d-flex align-items-end">
						<button type="submit" class="btn btn-primary btn-sm w-100">
							<i class="bi bi-search"></i>
						</button>
					</div>
				</div>
				
				<div class="row">
					<div class="col-md-12">
						<label class="form-label small">Recherche</label>
						<div class="input-group input-group-sm">
							<span class="input-group-text"><i class="bi bi-search"></i></span>
							<input type="text" name="search" class="form-control" placeholder="Nom, email, téléphone..." value="{{ request('search') }}">
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
	
	<!-- Tableau des pr�-r�servations -->
	<div class="card border-0 shadow-sm">
		<div class="card-header bg-transparent">
			<div class="d-flex justify-content-between align-items-center">
				<h5 class="mb-0">
					<i class="bi bi-list-ul me-2"></i>Liste des enregistrements
					<span class="badge bg-secondary ms-2">{{ $reservations->count() }}</span>
				</h5>
				<div>
					<button type="button" class="btn btn-sm btn-outline-secondary me-2" id="selectAllReservationsBtn" onclick="toggleSelectAllReservations()">
						<i class="bi bi-check-square me-2"></i><span id="selectAllReservationsText">Tout sélectionner</span>
					</button>
					<button type="button" class="btn btn-sm btn-danger" id="deleteMultipleReservationsBtn" style="display: none;">
						<i class="bi bi-trash me-2"></i>Supprimer la sélection
					</button>
				</div>
			</div>
		</div>
		<div class="card-body p-0">
			@if($reservations->count() > 0)
			<div class="table-responsive">
				<table id="ReservationsTable" class="table table-sm table-hover table-striped align-middle mb-0 super-admin-table" aria-label="Liste des enregistrements par hôtel et statut">
					<thead class="table-light">
						<tr>
							<th scope="col" width="50" class="ps-3">
								<label class="visually-hidden" for="selectAllReservationsCheckbox">Tout sélectionner</label>
								<input type="checkbox" class="form-check-input" id="selectAllReservationsCheckbox" onchange="toggleSelectAllReservations()" aria-label="Tout sélectionner">
							</th>
							<th scope="col"><i class="bi bi-hash me-1 text-primary"></i>ID</th>
							<th scope="col"><i class="bi bi-building me-1 text-primary"></i>Hotel</th>
							<th scope="col"><i class="bi bi-person me-1 text-primary"></i>Client</th>
							<th scope="col" class="d-none d-lg-table-cell"><i class="bi bi-envelope me-1 text-primary"></i>Contact</th>
							<th scope="col"><i class="bi bi-tag me-1 text-primary"></i>Type</th>
							<th scope="col"><i class="bi bi-calendar3 me-1 text-primary"></i>Date</th>
							<th scope="col"><i class="bi bi-flag me-1 text-primary"></i>Statut</th>
							<th scope="col" class="d-none d-md-table-cell"><i class="bi bi-people me-1 text-primary"></i>Accompagnants</th>
							<th scope="col" width="100" class="text-end pe-3">Actions</th>
						</tr>
					</thead>
					<tbody>
						@foreach($reservations as $reservation)
							<tr>
								<td class="ps-3">
									<label class="visually-hidden" for="reservation-{{ $reservation->id }}">Sélectionner enregistrement #{{ $reservation->id }}</label>
									<input type="checkbox" class="form-check-input reservation-checkbox" value="{{ $reservation->id }}" id="reservation-{{ $reservation->id }}" aria-label="Sélectionner enregistrement #{{ $reservation->id }}">
								</td>
								<td><code>#{{ $reservation->id }}</code></td>
								<td>
									@if($reservation->hotel)
										<strong>{{ $reservation->hotel->name }}</strong><br>
										<small class="text-muted">{{ $reservation->hotel->city ?? '' }}</small>
									@else
										<span class="text-muted">Hotel supprimé</span>
									@endif
								</td>
								<td>
									<div class="fw-bold">{{ $reservation->data['nom'] ?? $reservation->data['prenom'] ?? 'N/A' }}</div>
									<small class="text-muted">{{ $reservation->data['prenom'] ?? '' }}</small>
								</td>
								<td class="d-none d-lg-table-cell small">
									<div><i class="bi bi-envelope me-1 text-muted"></i>{{ $reservation->data['email'] ?? 'N/A' }}</div>
									<div><i class="bi bi-telephone me-1 text-muted"></i>{{ $reservation->data['telephone'] ?? 'N/A' }}</div>
								</td>
								<td>
									<span class="badge bg-{{ ($reservation->data['type_reservation'] ?? 'Individuel') == 'Groupe' ? 'info' : 'secondary' }}">
										{{ $reservation->data['type_reservation'] ?? 'Individuel' }}
									</span>
								</td>
								<td>
									<div>{{ $reservation->created_at->format('d/m/Y') }}</div>
									<small class="text-muted">{{ $reservation->created_at->format('H:i') }}</small>
								</td>
								<td>
									@if($reservation->status === 'pending')
										<span class="badge bg-warning">En attente</span>
									@elseif($reservation->status === 'validated')
										<span class="badge bg-success">Valid�e</span>
									@else
										<span class="badge bg-danger">Rejet�e</span>
									@endif
                                </td>
                                <td class="d-none d-md-table-cell">
                                    @if(isset($reservation->data['accompagnants']) && is_array($reservation->data['accompagnants']))
                                        <span class="badge bg-info">
                                            {{ count($reservation->data['accompagnants']) }} accompagnant(s)
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
								<td class="text-end pe-3">
									<a href="{{ route('super.reservations.show', $reservation) }}" class="btn btn-sm btn-outline-primary">
										<i class="bi bi-eye"></i>
									</a>
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>
			@else
				<div class="card-body">
					<x-super.empty-table
						icon="bi-calendar-x"
						title="Aucun enregistrement"
						message="Aucun enregistrement ne correspond aux filtres ou à la période sélectionnée."
					/>
				</div>
			@endif
		</div>
	</div>
</x-app-layout>

<script>
// Gestion de la sélection multiple pour les enregistrements
document.addEventListener('DOMContentLoaded', function() {
	const checkboxes = document.querySelectorAll('.reservation-checkbox');
	const deleteMultipleBtn = document.getElementById('deleteMultipleReservationsBtn');
	const selectAllCheckbox = document.getElementById('selectAllReservationsCheckbox');
	const selectAllBtn = document.getElementById('selectAllReservationsBtn');
	const selectAllText = document.getElementById('selectAllReservationsText');

	function updateDeleteButton() {
		const checked = document.querySelectorAll('.reservation-checkbox:checked');
		if (checked.length > 0 && deleteMultipleBtn) {
			deleteMultipleBtn.style.display = 'block';
			deleteMultipleBtn.innerHTML = `<i class="bi bi-trash me-2"></i>Supprimer ${checked.length} sélectionné(s)`;
		} else if (deleteMultipleBtn) {
			deleteMultipleBtn.style.display = 'none';
		}
		
		// Mettre à jour le checkbox "Tout sélectionner"
		if (selectAllCheckbox && checkboxes.length > 0) {
			const allChecked = checked.length === checkboxes.length && checkboxes.length > 0;
			selectAllCheckbox.checked = allChecked;
		}
		
		// Mettre à jour le texte du bouton
		if (selectAllText && checkboxes.length > 0) {
			const allChecked = checked.length === checkboxes.length;
			selectAllText.textContent = allChecked ? 'Tout désélectionner' : 'Tout sélectionner';
			if (selectAllBtn) {
				selectAllBtn.innerHTML = allChecked 
					? `<i class="bi bi-square me-2"></i><span id="selectAllReservationsText">Tout désélectionner</span>`
					: `<i class="bi bi-check-square me-2"></i><span id="selectAllReservationsText">Tout sélectionner</span>`;
			}
		}
	}
	
	// Fonction pour tout sélectionner/désélectionner
	window.toggleSelectAllReservations = function() {
		const checkboxes = document.querySelectorAll('.reservation-checkbox');
		const checked = document.querySelectorAll('.reservation-checkbox:checked');
		const allChecked = checked.length === checkboxes.length && checkboxes.length > 0;
		
		checkboxes.forEach(checkbox => {
			checkbox.checked = !allChecked;
		});
		
		updateDeleteButton();
	}
	
	// Attacher les événements aux checkboxes
	if (checkboxes.length > 0) {
		checkboxes.forEach(checkbox => {
			checkbox.addEventListener('change', updateDeleteButton);
		});
	}
	
	// Suppression multiple
	if (deleteMultipleBtn) {
		deleteMultipleBtn.addEventListener('click', function() {
			const checked = Array.from(document.querySelectorAll('.reservation-checkbox:checked'))
				.map(cb => cb.value);
			
			if (checked.length === 0) {
				alert('Aucun enregistrement sélectionné');
				return;
			}

			if (confirm(`Êtes-vous sûr de vouloir supprimer ${checked.length} enregistrement(s) ? Cette action est irréversible.`)) {
				const form = document.createElement('form');
				form.method = 'POST';
				form.action = '{{ route("super.reservations.destroy-multiple") }}';
				
				// Token CSRF
				const csrfInput = document.createElement('input');
				csrfInput.type = 'hidden';
				csrfInput.name = '_token';
				csrfInput.value = '{{ csrf_token() }}';
				form.appendChild(csrfInput);

				// IDs des enregistrements
				checked.forEach(id => {
					const input = document.createElement('input');
					input.type = 'hidden';
					input.name = 'reservation_ids[]';
					input.value = id;
					form.appendChild(input);
				});

				document.body.appendChild(form);
				form.submit();
			}
		});
	}
});
</script>
