<x-app-layout>
	<x-slot name="header">Rapport - {{ $hotel->name }}</x-slot>
	
	<!-- Statistiques de l'hôtel -->
	<div class="row mb-4">
		<div class="col-md-3">
			<div class="card border-0 shadow-sm text-center">
				<div class="card-body">
					<i class="bi bi-calendar-check text-primary" style="font-size: 2rem;"></i>
					<h4 class="mt-2">{{ $hotel_stats['total_reservations'] }}</h4>
					<p class="text-muted mb-0">Total réservations</p>
				</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="card border-0 shadow-sm text-center">
				<div class="card-body">
					<i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
					<h4 class="mt-2">{{ $hotel_stats['validated_reservations'] }}</h4>
					<p class="text-muted mb-0">Validées</p>
				</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="card border-0 shadow-sm text-center">
				<div class="card-body">
					<i class="bi bi-clock text-warning" style="font-size: 2rem;"></i>
					<h4 class="mt-2">{{ $hotel_stats['pending_reservations'] }}</h4>
					<p class="text-muted mb-0">En attente</p>
				</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="card border-0 shadow-sm text-center">
				<div class="card-body">
					<i class="bi bi-people text-info" style="font-size: 2rem;"></i>
					<h4 class="mt-2">{{ $hotel_stats['total_users'] }}</h4>
					<p class="text-muted mb-0">Utilisateurs</p>
				</div>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-md-8">
			<!-- Réservations récentes -->
			<div class="card border-0 shadow-sm mb-4">
				<div class="card-header bg-transparent">
					<h5 class="mb-0">Réservations récentes</h5>
				</div>
				<div class="card-body">
					@if($recent_reservations->count() > 0)
						<div class="table-responsive">
							<table class="table table-hover">
								<thead>
									<tr>
										<th>Date</th>
										<th>Client</th>
										<th>Email</th>
										<th>Statut</th>
									</tr>
								</thead>
								<tbody>
									@foreach($recent_reservations as $reservation)
										<tr>
											<td>{{ $reservation->created_at->format('d/m/Y H:i') }}</td>
											<td>{{ $reservation->data['nom'] ?? 'N/A' }}</td>
											<td>{{ $reservation->data['email'] ?? 'N/A' }}</td>
											<td>
												<span class="badge bg-{{ $reservation->status === 'validated' ? 'success' : 'warning' }}">
													{{ ucfirst($reservation->status) }}
												</span>
											</td>
										</tr>
									@endforeach
								</tbody>
							</table>
						</div>
					@else
						<div class="text-center py-4">
							<i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
							<h5 class="text-muted mt-3">Aucune réservation</h5>
							<p class="text-muted">Les réservations de cet hôtel apparaîtront ici.</p>
						</div>
					@endif
				</div>
			</div>
		</div>
		
		<div class="col-md-4">
			<!-- Informations hôtel -->
			<div class="card border-0 shadow-sm mb-4">
				<div class="card-header bg-transparent">
					<h6 class="mb-0">Informations hôtel</h6>
				</div>
				<div class="card-body">
					<div class="mb-3">
						<strong>Nom:</strong><br>
						<span class="text-muted">{{ $hotel->name }}</span>
					</div>
					@if($hotel->address)
						<div class="mb-3">
							<strong>Adresse:</strong><br>
							<span class="text-muted">{{ $hotel->address }}</span>
						</div>
					@endif
					@if($hotel->city)
						<div class="mb-3">
							<strong>Ville:</strong><br>
							<span class="text-muted">{{ $hotel->city }}, {{ $hotel->country }}</span>
						</div>
					@endif
					<div class="mb-3">
						<strong>Performance:</strong><br>
						@if($hotel_stats['total_reservations'] > 0)
							<div class="progress mt-2">
								<div class="progress-bar" role="progressbar" style="width: {{ ($hotel_stats['validated_reservations'] / $hotel_stats['total_reservations']) * 100 }}%">
									{{ round(($hotel_stats['validated_reservations'] / $hotel_stats['total_reservations']) * 100, 1) }}%
								</div>
							</div>
						@else
							<span class="text-muted">Aucune donnée</span>
						@endif
					</div>
				</div>
			</div>
			
			<!-- Actions -->
			<div class="card border-0 shadow-sm">
				<div class="card-header bg-transparent">
					<h6 class="mb-0">Actions</h6>
				</div>
				<div class="card-body">
					<div class="d-grid gap-2">
						<a href="{{ route('super.hotels.show', $hotel) }}" class="btn btn-outline-primary">
							<i class="bi bi-eye me-2"></i>Voir l'hôtel
						</a>
					<a href="{{ route('super.hotel-data.show', $hotel) }}" class="btn btn-outline-secondary">
							<i class="bi bi-pencil me-2"></i>Modifier
						</a>
						<a href="{{ route('super.users.index', ['hotel' => $hotel->id]) }}" class="btn btn-outline-info">
							<i class="bi bi-people me-2"></i>Utilisateurs
						</a>
						<a href="{{ route('super.reports.index') }}" class="btn btn-outline-warning">
							<i class="bi bi-arrow-left me-2"></i>Retour aux rapports
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</x-app-layout>
