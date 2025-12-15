<x-app-layout>
	<x-slot name="header">Clients en séjour</x-slot>
	
	<!-- Statistiques -->
	<div class="row mb-4">
		<div class="col-md-4">
			<div class="card border-0 shadow-sm text-center">
				<div class="card-body">
					<i class="bi bi-people text-primary" style="font-size: 2rem;"></i>
					<h4 class="mt-2">{{ $stats['total'] }}</h4>
					<p class="text-muted mb-0">Clients en séjour</p>
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="card border-0 shadow-sm text-center">
				<div class="card-body">
					<i class="bi bi-box-arrow-in-right text-success" style="font-size: 2rem;"></i>
					<h4 class="mt-2">{{ $stats['today_checkin'] }}</h4>
					<p class="text-muted mb-0">Check-in aujourd'hui</p>
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="card border-0 shadow-sm text-center">
				<div class="card-body">
					<i class="bi bi-box-arrow-right text-warning" style="font-size: 2rem;"></i>
					<h4 class="mt-2">{{ $stats['today_checkout'] }}</h4>
					<p class="text-muted mb-0">Départs aujourd'hui</p>
				</div>
			</div>
		</div>
	</div>

	<!-- Filtres -->
	<div class="card border-0 shadow-sm mb-4">
		<div class="card-body">
			<form method="GET" class="row g-3">
				<div class="col-md-4">
					<label class="form-label">Rechercher</label>
					<input type="text" name="search" class="form-control" placeholder="Nom, prénom, email, téléphone..." value="{{ request('search') }}">
				</div>
				<div class="col-md-4">
					<label class="form-label">Filtrer par chambre</label>
					<select name="room_id" class="form-select" onchange="this.form.submit()">
						<option value="">Toutes les chambres</option>
						@foreach($rooms as $room)
							<option value="{{ $room->id }}" {{ request('room_id') == $room->id ? 'selected' : '' }}>
								Chambre {{ $room->room_number }}
							</option>
						@endforeach
					</select>
				</div>
				<div class="col-md-4">
					<label class="form-label">&nbsp;</label>
					<div class="d-grid">
						<a href="{{ route('reception.guests.staying') }}" class="btn btn-outline-secondary">
							<i class="bi bi-arrow-clockwise me-2"></i>Réinitialiser
						</a>
					</div>
				</div>
			</form>
		</div>
	</div>

	<div class="card border-0 shadow-sm">
		<div class="card-header bg-transparent">
			<h5 class="mb-0">Liste des clients en séjour ({{ $guests->total() }})</h5>
		</div>
		<div class="card-body">
			@if($guests->count() > 0)
				<div class="table-responsive">
					<table class="table table-hover align-middle">
						<thead>
							<tr>
								<th>Client</th>
								<th>Contact</th>
								<th>Chambre</th>
								<th>Arrivée</th>
								<th>Départ</th>
								<th>Check-in</th>
								<th width="150">Actions</th>
							</tr>
						</thead>
						<tbody>
							@foreach($guests as $guest)
								<tr>
									<td>
										<div class="d-flex align-items-center">
											<div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3">
												{{ substr(($guest->data['prenom'] ?? '') . ' ' . ($guest->data['nom'] ?? ''), 0, 1) }}
											</div>
											<div>
												<strong>{{ ($guest->data['prenom'] ?? '') . ' ' . ($guest->data['nom'] ?? 'N/A') }}</strong>
												@if(isset($guest->data['accompagnants']) && is_array($guest->data['accompagnants']) && count($guest->data['accompagnants']) > 0)
													<br><small class="text-muted">+ {{ count($guest->data['accompagnants']) }} accompagnant(s)</small>
												@endif
											</div>
										</div>
									</td>
									<td>
										<div>
											<i class="bi bi-envelope me-1 text-muted"></i>
											<small>{{ $guest->data['email'] ?? 'N/A' }}</small>
										</div>
										<div>
											<i class="bi bi-telephone me-1 text-muted"></i>
											<small>{{ $guest->data['telephone'] ?? 'N/A' }}</small>
										</div>
									</td>
									<td>
										@if($guest->room)
											<span class="badge bg-primary fs-6">
												<i class="bi bi-door-open me-1"></i>Chambre {{ $guest->room->room_number }}
											</span>
										@else
											<span class="badge bg-secondary">Non assignée</span>
										@endif
									</td>
									<td>
										@if($guest->check_in_date)
											{{ $guest->check_in_date->format('d/m/Y') }}
										@else
											<span class="text-muted">-</span>
										@endif
									</td>
									<td>
										@if($guest->check_out_date)
											@if($guest->check_out_date->isToday())
												<span class="badge bg-warning">Aujourd'hui</span>
											@elseif($guest->check_out_date->isPast())
												<span class="badge bg-danger">En retard</span>
											@else
												{{ $guest->check_out_date->format('d/m/Y') }}
											@endif
										@else
											<span class="text-muted">-</span>
										@endif
									</td>
									<td>
										@if($guest->checked_in_at)
											<small>{{ $guest->checked_in_at->format('d/m/Y H:i') }}</small>
											@if($guest->checkedInBy)
												<br><small class="text-muted">par {{ $guest->checkedInBy->name }}</small>
											@endif
										@else
											<span class="text-muted">-</span>
										@endif
									</td>
									<td>
										<div class="btn-group btn-group-sm">
											<a href="{{ route('reception.reservations.show', $guest->id) }}" class="btn btn-outline-info" title="Voir détails">
												<i class="bi bi-eye"></i>
											</a>
											{{-- Bouton Check-out : uniquement si chambre assignée --}}
											@if($guest->room_id)
												<form action="{{ route('reception.reservations.check-out', $guest->id) }}" method="POST" style="display: inline;">
													@csrf
													<button type="submit" class="btn btn-outline-warning" title="Check-out" onclick="return confirm('Effectuer le check-out de ce client ? La chambre sera libérée.')">
														<i class="bi bi-box-arrow-right"></i>
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
				
				<!-- Pagination -->
				<div class="mt-3">
					{{ $guests->links() }}
				</div>
			@else
				<div class="text-center py-5">
					<i class="bi bi-people text-muted" style="font-size: 4rem;"></i>
					<h5 class="text-muted mt-3">Aucun client en séjour</h5>
					<p class="text-muted">Aucun client ne correspond aux filtres appliqués.</p>
				</div>
			@endif
		</div>
	</div>
</x-app-layout>

<style>
.avatar-sm {
	width: 40px;
	height: 40px;
	font-size: 16px;
}
</style>

