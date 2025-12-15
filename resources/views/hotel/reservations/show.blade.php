<x-app-layout>
	<x-slot name="header">Détails de la pré-réservation #{{ $reservation->id }}</x-slot>
	
	<div class="row">
		<div class="col-md-8">
			<!-- Informations client -->
			<div class="card border-0 shadow-sm mb-4">
				<div class="card-header bg-transparent">
					<div class="d-flex justify-content-between align-items-center">
						<h5 class="mb-0"><i class="bi bi-person me-2"></i>Informations client</h5>
						<span class="badge bg-{{ $reservation->status === 'validated' ? 'success' : ($reservation->status === 'rejected' ? 'danger' : 'warning') }}">
							{{ ucfirst($reservation->status) }}
						</span>
					</div>
				</div>
				<div class="card-body">
					<div class="row">
						@foreach($reservation->data as $key => $value)
							@if(!in_array($key, ['client']) && $value)
								<div class="col-md-6 mb-3">
									<strong class="text-muted">{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong><br>
									@if($key === 'accompagnants' && is_array($value))
										<div class="ms-2">
											@foreach($value as $index => $accompagnant)
												<div class="text-muted small">
													<i class="bi bi-person me-1"></i>
													{{ $accompagnant['prenom'] ?? '' }} {{ $accompagnant['nom'] ?? '' }}
												</div>
											@endforeach
										</div>
									@else
										<span>{{ is_array($value) ? json_encode($value) : $value }}</span>
									@endif
								</div>
							@endif
						@endforeach
					</div>
				</div>
			</div>
		</div>
		
		<div class="col-md-4">
			<!-- Actions -->
			<div class="card border-0 shadow-sm mb-4">
				<div class="card-header bg-transparent">
					<h6 class="mb-0"><i class="bi bi-lightning me-2"></i>Actions</h6>
				</div>
				<div class="card-body">
					<!-- Bouton Modifier (toujours disponible) -->
					<div class="d-grid mb-2">
						<a href="{{ route('hotel.reservations.edit', $reservation) }}" class="btn btn-outline-primary">
							<i class="bi bi-pencil-square me-2"></i>Modifier les informations
						</a>
					</div>
					
					@if($reservation->status === 'pending')
						<div class="d-grid gap-2">
							<form action="{{ route('hotel.reservations.validate', $reservation) }}" method="POST">
								@csrf
								<button type="submit" class="btn btn-success w-100" onclick="return confirm('Valider cette pré-réservation ?')">
									<i class="bi bi-check-lg me-2"></i>Valider
								</button>
							</form>
							<form action="{{ route('hotel.reservations.reject', $reservation) }}" method="POST">
								@csrf
								<button type="submit" class="btn btn-danger w-100" onclick="return confirm('Rejeter cette pré-réservation ?')">
									<i class="bi bi-x-lg me-2"></i>Rejeter
								</button>
							</form>
						</div>
					@elseif($reservation->status === 'validated')
						<div class="alert alert-success">
							<i class="bi bi-check-circle me-2"></i>Pré-réservation validée (irréversible)
						</div>
					@elseif($reservation->status === 'rejected')
						<div class="alert alert-danger">
							<i class="bi bi-x-circle me-2"></i>Pré-réservation rejetée (irréversible)
						</div>
					@endif
					
					<hr>
					<a href="{{ route('hotel.reservations.index') }}" class="btn btn-outline-secondary w-100">
						<i class="bi bi-arrow-left me-2"></i>Retour à la liste
					</a>
				</div>
			</div>
			
			<!-- Informations -->
			<div class="card border-0 shadow-sm">
				<div class="card-header bg-transparent">
					<h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informations</h6>
				</div>
				<div class="card-body">
					<div class="mb-3">
						<strong>ID:</strong><br>
						<code>#{{ $reservation->id }}</code>
					</div>
					<div class="mb-3">
						<strong>Hôtel:</strong><br>
						<span class="text-muted">{{ $reservation->hotel->name }}</span>
					</div>
					<div class="mb-3">
						<strong>Date de soumission:</strong><br>
						<span class="text-muted">{{ $reservation->created_at->format('d/m/Y H:i') }}</span>
					</div>
					@if($reservation->validated_at)
						<div class="mb-3">
							<strong>Date de validation:</strong><br>
							<span class="text-muted">{{ $reservation->validated_at->format('d/m/Y H:i') }}</span>
						</div>
					@endif
				</div>
			</div>
		</div>
	</div>
</x-app-layout>
