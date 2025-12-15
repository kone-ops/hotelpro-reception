<x-app-layout>
	<x-slot name="header">Administration de l'hôtel</x-slot>
	
	<div class="row">
		<div class="col-md-3 mb-4">
			<div class="card border-0 shadow-sm">
				<div class="card-body text-center">
					<i class="bi bi-calendar-check text-info" style="font-size: 2rem;"></i>
					<h5 class="card-title mt-2">Total</h5>
					<h3 class="text-info">{{ $stats['total'] }}</h3>
					<a href="{{ route('hotel.reservations.index') }}" class="btn btn-sm btn-outline-info mt-2">Voir toutes</a>
				</div>
			</div>
		</div>
		<div class="col-md-3 mb-4">
			<div class="card border-0 shadow-sm">
				<div class="card-body text-center">
					<i class="bi bi-clock text-warning" style="font-size: 2rem;"></i>
					<h5 class="card-title mt-2">En attente</h5>
					<h3 class="text-warning">{{ $stats['pending'] }}</h3>
					<a href="{{ route('hotel.reservations.index', ['status' => 'pending']) }}" class="btn btn-sm btn-outline-warning mt-2">Traiter</a>
				</div>
			</div>
		</div>
		<div class="col-md-3 mb-4">
			<div class="card border-0 shadow-sm">
				<div class="card-body text-center">
					<i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
					<h5 class="card-title mt-2">Validées</h5>
					<h3 class="text-success">{{ $stats['validated'] }}</h3>
					<a href="{{ route('hotel.reservations.index', ['status' => 'validated']) }}" class="btn btn-sm btn-outline-success mt-2">Voir</a>
				</div>
			</div>
		</div>
		<div class="col-md-3 mb-4">
			<div class="card border-0 shadow-sm">
				<div class="card-body text-center">
					<i class="bi bi-people text-primary" style="font-size: 2rem;"></i>
					<h5 class="card-title mt-2">Groupes</h5>
					<h3 class="text-primary">{{ $stats['groups'] }}</h3>
					<small class="text-muted">Actifs</small>
				</div>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-md-8">
			<div class="card border-0 shadow-sm">
				<div class="card-header bg-transparent d-flex justify-content-between align-items-center">
					<h5 class="mb-0">Réservations récentes</h5>
					<a href="{{ route('hotel.reservations.index') }}" class="btn btn-sm btn-outline-primary">Voir toutes</a>
				</div>
				<div class="card-body">
					@if($recentReservations->count() > 0)
						<div class="table-responsive">
							<table class="table table-hover">
								<thead>
									<tr>
										<th>Client</th>
										<th>Email</th>
										<th>Date</th>
										<th>Statut</th>
										<th>Actions</th>
									</tr>
								</thead>
								<tbody>
									@foreach($recentReservations as $reservation)
										<tr>
											<td>{{ $reservation->data['nom'] ?? 'N/A' }}</td>
											<td>{{ $reservation->data['email'] ?? 'N/A' }}</td>
											<td>{{ $reservation->created_at->format('d/m/Y') }}</td>
											<td>
												<span class="badge bg-{{ $reservation->status === 'validated' ? 'success' : ($reservation->status === 'rejected' ? 'danger' : 'warning') }}">
													{{ ucfirst($reservation->status) }}
												</span>
											</td>
											<td>
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
						<div class="text-center py-4">
							<i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
							<p class="text-muted mt-3">Aucune Réservation pour le moment</p>
						</div>
					@endif
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="card border-0 shadow-sm">
				<div class="card-header bg-transparent">
					<h5 class="mb-0">Actions rapides</h5>
				</div>
				<div class="card-body">
					<div class="d-grid gap-2">
						<a href="{{ route('hotel.qr') }}" class="btn btn-primary">
							<i class="bi bi-qr-code me-2"></i>Générer QR Code
						</a>
						<a href="{{ route('hotel.reservations.index') }}" class="btn btn-outline-primary">
							<i class="bi bi-list-ul me-2"></i>Voir toutes les réservations
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
