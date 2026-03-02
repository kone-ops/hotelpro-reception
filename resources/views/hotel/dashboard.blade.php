<x-app-layout>
	<x-slot name="header">Administration de l'hôtel</x-slot>
	
	<div class="row">
		<div class="col-md-3 mb-3">
			<div class="card border-0 shadow-sm stat-card">
				<div class="card-body text-center">
					<i class="bi bi-calendar-check text-info stat-card-icon"></i>
					<h5 class="card-title mt-1">Total</h5>
					<h3 class="text-info">{{ $stats['total'] }}</h3>
					<a href="{{ route('hotel.reservations.index') }}" class="btn btn-sm btn-outline-info mt-1">Voir toutes</a>
				</div>
			</div>
		</div>
		<div class="col-md-3 mb-3">
			<div class="card border-0 shadow-sm stat-card">
				<div class="card-body text-center">
					<i class="bi bi-clock text-warning stat-card-icon"></i>
					<h5 class="card-title mt-1">En attente</h5>
					<h3 class="text-warning">{{ $stats['pending'] }}</h3>
					<a href="{{ route('hotel.reservations.index', ['status' => 'pending']) }}" class="btn btn-sm btn-outline-warning mt-1">Traiter</a>
				</div>
			</div>
		</div>
		<div class="col-md-3 mb-3">
			<div class="card border-0 shadow-sm stat-card">
				<div class="card-body text-center">
					<i class="bi bi-check-circle text-success stat-card-icon"></i>
					<h5 class="card-title mt-1">Validées</h5>
					<h3 class="text-success">{{ $stats['validated'] }}</h3>
					<a href="{{ route('hotel.reservations.index', ['status' => 'validated']) }}" class="btn btn-sm btn-outline-success mt-1">Voir</a>
				</div>
			</div>
		</div>
		<div class="col-md-3 mb-3">
			<div class="card border-0 shadow-sm stat-card">
				<div class="card-body text-center">
					<i class="bi bi-people text-primary stat-card-icon"></i>
					<h5 class="card-title mt-1">Groupes</h5>
					<h3 class="text-primary">{{ $stats['groups'] }}</h3>
					<small class="text-muted">Actifs</small>
				</div>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-md-8">
			<div class="card border-0 shadow-sm">
				<div class="card-header bg-transparent py-2 d-flex justify-content-between align-items-center">
					<h5 class="mb-0 card-title">Enregistrements récents</h5>
					<a href="{{ route('hotel.reservations.index') }}" class="btn btn-sm btn-outline-primary">Voir toutes</a>
				</div>
				<div class="card-body py-2">
					@if($recentReservations->count() > 0)
						<div class="table-responsive">
							<table class="table table-sm table-hover table-striped align-middle mb-0 app-table table-compact" aria-label="Enregistrements récents">
								<thead class="table-light">
									<tr>
										<th scope="col"><i class="bi bi-person me-1 text-muted"></i>Client</th>
										<th scope="col" class="d-none d-md-table-cell"><i class="bi bi-envelope me-1 text-muted"></i>Email</th>
										<th scope="col"><i class="bi bi-calendar-event me-1 text-muted"></i>Date</th>
										<th scope="col"><i class="bi bi-tag me-1 text-muted"></i>Statut</th>
										<th scope="col" class="text-end w-actions">Actions</th>
									</tr>
								</thead>
								<tbody>
									@foreach($recentReservations as $reservation)
										<tr>
											<td>{{ $reservation->data['nom'] ?? 'N/A' }}</td>
											<td class="d-none d-md-table-cell">{{ $reservation->data['email'] ?? 'N/A' }}</td>
											<td>{{ $reservation->created_at->format('d/m/Y') }}</td>
											<td>
												<span class="badge bg-{{ $reservation->status === 'validated' ? 'success' : ($reservation->status === 'rejected' ? 'danger' : 'warning') }}">
													{{ ucfirst($reservation->status) }}
												</span>
											</td>
											<td class="text-end">
												<a href="{{ route('hotel.reservations.show', $reservation) }}" class="btn btn-sm btn-outline-primary">
													<i class="bi bi-eye"></i>
												</a>
											</td>
										</tr>
									@endforeach
								</tbody>
							</table>
						</div>
					@else
						<x-super.empty-table icon="bi-calendar-x" title="Aucun enregistrement" message="Aucun enregistrement pour le moment." />
					@endif
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="card border-0 shadow-sm">
				<div class="card-header bg-transparent py-2">
					<h5 class="mb-0 card-title">Actions rapides</h5>
				</div>
				<div class="card-body py-2">
					<div class="d-grid gap-2">
						<a href="{{ route('hotel.qr') }}" class="btn btn-primary">
							<i class="bi bi-qr-code me-2"></i>Générer QR Code
						</a>
						<a href="{{ route('hotel.reservations.index') }}" class="btn btn-outline-primary">
							<i class="bi bi-list-ul me-2"></i>Voir tous les enregistrements
						</a>
						<a href="{{ route('hotel.rooms.index') }}" class="btn btn-outline-secondary">
							<i class="bi bi-door-open me-2"></i>Gérer les chambres
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</x-app-layout>
