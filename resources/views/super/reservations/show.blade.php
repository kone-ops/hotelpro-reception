<x-app-layout>
	<x-slot name="header">Détails Pré-réservation #{{ $reservation->id }}</x-slot>
	
	<div class="mb-3">
		<a href="{{ route('super.reservations.index') }}" class="btn btn-outline-secondary">
			<i class="bi bi-arrow-left me-2"></i>Retour à la liste
		</a>
	</div>
	
	<div class="row">
		<div class="col-md-8">
			<!-- Informations Hôtel -->
			<div class="card border-0 shadow-sm mb-4">
				<div class="card-header bg-transparent">
					<h5 class="mb-0"><i class="bi bi-building me-2"></i>Hôtel</h5>
				</div>
				<div class="card-body">
					<h4>{{ $reservation->hotel->name }}</h4>
					<p class="text-muted mb-0">
						<i class="bi bi-geo-alt me-1"></i>
						{{ $reservation->hotel->address }}, {{ $reservation->hotel->city }}, {{ $reservation->hotel->country }}
					</p>
				</div>
			</div>
			
			<!-- Informations Client -->
			<div class="card border-0 shadow-sm mb-4">
				<div class="card-header bg-transparent">
					<h5 class="mb-0"><i class="bi bi-person me-2"></i>Informations Client</h5>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col-md-6 mb-3">
							<strong>Nom complet:</strong><br>
							<span class="text-muted">{{ $reservation->data['prenom'] ?? '' }} {{ $reservation->data['nom'] ?? 'Non renseigné' }}</span>
						</div>
						<div class="col-md-6 mb-3">
							<strong>Email:</strong><br>
							<span class="text-muted">{{ $reservation->data['email'] ?? 'Non renseigné' }}</span>
						</div>
						<div class="col-md-6 mb-3">
							<strong>Téléphone:</strong><br>
							<span class="text-muted">{{ $reservation->data['telephone'] ?? 'Non renseigné' }}</span>
						</div>
						<div class="col-md-6 mb-3">
							<strong>Date de naissance:</strong><br>
							<span class="text-muted">{{ $reservation->data['date_naissance'] ?? 'Non renseigné' }}</span>
						</div>
						<div class="col-md-6 mb-3">
							<strong>Nationalité:</strong><br>
							<span class="text-muted">{{ $reservation->data['nationalite'] ?? 'Non renseigné' }}</span>
						</div>
						<div class="col-md-6 mb-3">
							<strong>Pièce d'identité:</strong><br>
							<span class="text-muted">{{ $reservation->data['type_piece_identite'] ?? 'Non renseigné' }}</span>
							@if(isset($reservation->data['numero_piece_identite']))
								<br><code>{{ $reservation->data['numero_piece_identite'] }}</code>
							@endif
						</div>
					</div>
					
					@if(isset($reservation->data['accompagnants']) && is_array($reservation->data['accompagnants']) && count($reservation->data['accompagnants']) > 0)
						<div class="mt-3 pt-3 border-top">
							<strong><i class="bi bi-people me-2"></i>Accompagnants:</strong>
							<div class="ms-3 mt-2">
								@foreach($reservation->data['accompagnants'] as $index => $accompagnant)
									<div class="text-muted mb-1">
										<i class="bi bi-person me-1"></i>
										{{ $accompagnant['prenom'] ?? '' }} {{ $accompagnant['nom'] ?? '' }}
									</div>
								@endforeach
							</div>
						</div>
					@endif
				</div>
			</div>
			
			<!-- Document d'identité -->
			@if($reservation->identityDocument)
				<div class="card border-0 shadow-sm mb-4">
					<div class="card-header bg-transparent">
						<h5 class="mb-0"><i class="bi bi-card-image me-2"></i>Document d'Identité</h5>
					</div>
					<div class="card-body">
						<!-- Informations de délivrance -->
						<div class="row mb-3">
							<div class="col-md-6">
								<strong><i class="bi bi-geo-alt me-1"></i>Lieu de délivrance:</strong><br>
								<span class="text-muted">{{ $reservation->identityDocument->lieu_delivrance ?? $reservation->data['lieu_delivrance'] ?? 'Non renseigné' }}</span>
							</div>
							<div class="col-md-6">
								<strong><i class="bi bi-calendar me-1"></i>Date de délivrance:</strong><br>
								<span class="text-muted">
									{{ isset($reservation->identityDocument->date_delivrance) 
										? $reservation->identityDocument->date_delivrance->format('d/m/Y') 
										: ($reservation->data['date_delivrance'] ?? 'Non renseigné') }}
								</span>
							</div>
						</div>
						
						<div class="row">
							@if($reservation->identityDocument->front_path)
								<div class="col-md-6 mb-3">
									<strong>Recto:</strong><br>
									<img src="{{ asset('storage/' . $reservation->identityDocument->front_path) }}" class="img-fluid rounded border" alt="Recto">
								</div>
							@endif
							@if($reservation->identityDocument->back_path)
								<div class="col-md-6 mb-3">
									<strong>Verso:</strong><br>
									<img src="{{ asset('storage/' . $reservation->identityDocument->back_path) }}" class="img-fluid rounded border" alt="Verso">
								</div>
							@endif
						</div>
					</div>
				</div>
			@endif
			
			<!-- Signature -->
			@if($reservation->signature && $reservation->signature->image_base64)
				<div class="card border-0 shadow-sm">
					<div class="card-header bg-transparent">
						<h5 class="mb-0"><i class="bi bi-pen me-2"></i>Signature du Client</h5>
					</div>
					<div class="card-body">
						@php
							$signatureSrc = $reservation->signature->image_base64;
							// Si la signature ne contient pas déjà le préfixe data:image, l'ajouter
							if (!str_starts_with($signatureSrc, 'data:image')) {
								$signatureSrc = 'data:image/png;base64,' . $signatureSrc;
							}
						@endphp
						<img src="{{ $signatureSrc }}" class="img-fluid border rounded" style="max-height: 150px;" alt="Signature du client">
					</div>
				</div>
			@endif
		</div>
		
		<div class="col-md-4">
			<!-- Statut -->
			<div class="card border-0 shadow-sm mb-4">
				<div class="card-header bg-transparent">
					<h6 class="mb-0">Statut</h6>
				</div>
				<div class="card-body text-center">
					@if($reservation->status === 'pending')
						<span class="badge bg-warning" style="font-size: 1.2rem; padding: 10px 20px;">En attente</span>
					@elseif($reservation->status === 'validated')
						<span class="badge bg-success" style="font-size: 1.2rem; padding: 10px 20px;">Validée</span>
					@else
						<span class="badge bg-danger" style="font-size: 1.2rem; padding: 10px 20px;">Rejetée</span>
					@endif
				</div>
			</div>
			
			<!-- Champs Personnalisés -->
			@php
				$customFields = $reservation->hotel->formFields()->where('active', true)->get();
				$hasCustomFields = false;
				foreach ($customFields as $field) {
					if (isset($reservation->data[$field->key]) && !empty($reservation->data[$field->key])) {
						$hasCustomFields = true;
						break;
					}
				}
			@endphp
			@if($hasCustomFields)
			<div class="card border-0 shadow-sm mb-4">
				<div class="card-header bg-transparent">
					<h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Informations Supplémentaires</h5>
				</div>
				<div class="card-body">
					<div class="row">
						@foreach($customFields as $field)
							@if(isset($reservation->data[$field->key]) && $reservation->data[$field->key] !== null && $reservation->data[$field->key] !== '')
								<div class="col-md-6 mb-3">
									<strong>{{ $field->label }}:</strong><br>
									<span class="text-muted">
										@if($field->type === 'checkbox')
											{{ $reservation->data[$field->key] ? 'Oui' : 'Non' }}
										@elseif($field->type === 'date' && $reservation->data[$field->key])
											{{ \Carbon\Carbon::parse($reservation->data[$field->key])->format('d/m/Y') }}
										@else
											{{ $reservation->data[$field->key] }}
										@endif
									</span>
								</div>
							@endif
						@endforeach
					</div>
				</div>
			</div>
			@endif
			
			<!-- Informations Séjour -->
			<div class="card border-0 shadow-sm mb-4">
				<div class="card-header bg-transparent">
					<h6 class="mb-0">Séjour</h6>
				</div>
				<div class="card-body">
					<div class="mb-2">
						<strong>Arrivée:</strong><br>
						<span class="text-muted">{{ $reservation->data['date_arrivee'] ?? 'Non renseigné' }}</span>
					</div>
					<div class="mb-2">
						<strong>Départ:</strong><br>
						<span class="text-muted">{{ $reservation->data['date_depart'] ?? 'Non renseigné' }}</span>
					</div>
					<div class="mb-2">
						<strong>Adultes:</strong> {{ $reservation->data['nombre_adultes'] ?? 1 }}
					</div>
					<div class="mb-2">
						<strong>Enfants:</strong> {{ $reservation->data['nombre_enfants'] ?? 0 }}
					</div>
					<div class="mb-2">
						<strong>Type de chambre:</strong><br>
						<span class="text-muted">{{ $reservation->data['type_chambre'] ?? 'Non renseigné' }}</span>
					</div>

					@if(isset($reservation->data['accompagnants']) && is_array($reservation->data['accompagnants']) && count($reservation->data['accompagnants']) > 0)
						<hr>
						<div class="mb-2">
							<strong class="mb-0"><i class="bi bi-people me-1"></i> Accompagnants</strong>
							<span class="badge bg-info ms-2">{{ count($reservation->data['accompagnants']) }} personne(s)</span>
						</div>
						<div class="mt-2">
							@foreach($reservation->data['accompagnants'] as $index => $accompagnant)
								<div class="d-flex align-items-center mb-2 p-2 bg-light rounded">
									<i class="bi bi-person me-2 text-primary"></i>
									<div>
										<div class="fw-semibold">
											{{ $accompagnant['prenom'] ?? '' }} {{ $accompagnant['nom'] ?? '' }}
										</div>
										@if(isset($accompagnant['date_naissance']))
											<small class="text-muted">Né(e) le {{ \Carbon\Carbon::parse($accompagnant['date_naissance'])->format('d/m/Y') }}</small>
										@endif
									</div>
								</div>
							@endforeach
						</div>
					@endif
				</div>
			</div>
			
			<!-- Informations système -->
			<div class="card border-0 shadow-sm">
				<div class="card-header bg-transparent">
					<h6 class="mb-0">Informations</h6>
				</div>
				<div class="card-body">
					<div class="mb-2">
						<strong>ID:</strong><br>
						<code>#{{ $reservation->id }}</code>
					</div>
					<div class="mb-2">
						<strong>Type:</strong><br>
						<span class="badge bg-{{ ($reservation->data['type_reservation'] ?? 'Individuel') == 'Groupe' ? 'info' : 'secondary' }}">
							{{ $reservation->data['type_reservation'] ?? 'Individuel' }}
						</span>
					</div>
					<div class="mb-2">
						<strong>Soumis le:</strong><br>
						<span class="text-muted">{{ $reservation->created_at->format('d/m/Y à H:i') }}</span>
					</div>
					<div class="mb-2">
						<strong>Dernière mise à jour:</strong><br>
						<span class="text-muted">{{ $reservation->updated_at->format('d/m/Y à H:i') }}</span>
					</div>
				</div>
			</div>
		</div>
	</div>
</x-app-layout>



