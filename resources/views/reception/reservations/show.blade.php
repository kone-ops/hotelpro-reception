<x-app-layout>
	<x-slot name="header">Détails de la Réservation #{{ $reservation->id }}</x-slot>
	
	<div class="row">
		<div class="col-md-8">
			<!-- Informations client -->
			<div class="card border-0 shadow-sm mb-4">
				<div class="card-header bg-transparent">
					<div class="d-flex justify-content-between align-items-center">
						<h5 class="mb-0">Informations client</h5>
						<span class="badge bg-{{ $reservation->status === 'validated' ? 'success' : ($reservation->status === 'rejected' ? 'danger' : 'warning') }}">
							{{ ucfirst($reservation->status) }}
						</span>
					</div>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col-md-6">
							<div class="mb-3">
								<strong>Nom complet:</strong><br>
								<span class="text-muted">{{ $reservation->data['nom'] ?? 'Non renseigné' }}</span>
							</div>
							<div class="mb-3">
								<strong>Email:</strong><br>
								<span class="text-muted">{{ $reservation->data['email'] ?? 'Non renseigné' }}</span>
							</div>
							<div class="mb-3">
								<strong>Téléphone:</strong><br>
								<span class="text-muted">{{ $reservation->data['telephone'] ?? 'Non renseigné' }}</span>
							</div>
						</div>
						<div class="col-md-6">
							<div class="mb-3">
								<strong>Date de naissance:</strong><br>
								<span class="text-muted">{{ $reservation->data['date_naissance'] ?? 'Non renseigné' }}</span>
							</div>
							<div class="mb-3">
								<strong>Nationalité:</strong><br>
								<span class="text-muted">{{ $reservation->data['nationalite'] ?? 'Non renseigné' }}</span>
							</div>
							<div class="mb-3">
								<strong>Date d'arrivée:</strong><br>
								<span class="text-muted">{{ $reservation->data['date_arrivee'] ?? 'Non renseigné' }}</span>
							</div>
							<div class="mb-3">
								<strong>Type de chambre:</strong><br>
								<span class="text-muted">{{ $reservation->data['type_chambre'] ?? 'Non renseigné' }}</span>
							</div>
							<div class="mb-3">
								<strong>Numéro de chambre:</strong><br>
								@if($reservation->room)
									<span class="badge bg-primary fs-6">
										<i class="bi bi-door-closed me-1"></i>Chambre {{ $reservation->room->room_number }}
									</span>
								@else
									<span class="badge bg-secondary">
										<i class="bi bi-clock me-1"></i>Non assignée
									</span>
								@endif
							</div>
						</div>
					</div>
					
					@if(isset($reservation->data['commentaires']) && $reservation->data['commentaires'])
						<div class="mb-3">
							<strong>Commentaires:</strong><br>
							<div class="bg-light p-3 rounded">
								{{ $reservation->data['commentaires'] }}
							</div>
						</div>
					@endif
					
					{{-- Afficher les champs personnalisés --}}
					@php
						$customFieldsWithValues = $formConfig->getCustomFieldsWithValues($reservation->data);
					@endphp
					@if(count($customFieldsWithValues) > 0)
						<div class="mt-4 pt-3 border-top">
							<strong><i class="bi bi-list-ul me-2"></i>Informations supplémentaires:</strong>
							<div class="row mt-3">
								@foreach($customFieldsWithValues as $item)
									<div class="col-md-6 mb-3">
										<strong>{{ $item['field']->label }}:</strong><br>
										<span class="text-muted">{!! $item['formatted_value'] !!}</span>
									</div>
								@endforeach
							</div>
						</div>
					@endif
					
					@if(isset($reservation->data['accompagnants']) && is_array($reservation->data['accompagnants']) && count($reservation->data['accompagnants']) > 0)
						<div class="mt-3">
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
			
			<!-- Documents d'identité -->
			@if($reservation->identityDocument)
				<div class="card border-0 shadow-sm mb-4">
					<div class="card-header bg-transparent">
						<h5 class="mb-0">Document d'identité</h5>
					</div>
					<div class="card-body">
						<div class="row">
							<div class="col-md-12 mb-3">
								<div class="border rounded p-3">
									<h6>{{ ucfirst($reservation->identityDocument->type ?? 'Document') }}</h6>
									@if($reservation->identityDocument->front_path)
										<div class="mb-2">
											<strong>Recto:</strong><br>
											<img src="{{ asset('storage/' . $reservation->identityDocument->front_path) }}" class="img-fluid rounded" style="max-height: 200px;" loading="lazy" alt="Recto de la pièce d'identité">
										</div>
									@endif
									@if($reservation->identityDocument->back_path)
										<div class="mb-2">
											<strong>Verso:</strong><br>
											<img src="{{ asset('storage/' . $reservation->identityDocument->back_path) }}" class="img-fluid rounded" style="max-height: 200px;" loading="lazy" alt="Verso de la pièce d'identité">
										</div>
									@endif
								</div>
							</div>
						</div>
					</div>
				</div>
			@endif
			
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
			
			<!-- Signature -->
			@if($reservation->signature && $reservation->signature->image_base64)
				<div class="card border-0 shadow-sm">
					<div class="card-header bg-transparent">
						<h5 class="mb-0">Signature du Client</h5>
					</div>
					<div class="card-body">
						<div class="mb-3">
							@php
								$signatureSrc = $reservation->signature->image_base64;
								// Si la signature ne contient pas déjà le préfixe data:image, l'ajouter
								if (!str_starts_with($signatureSrc, 'data:image')) {
									$signatureSrc = 'data:image/png;base64,' . $signatureSrc;
								}
							@endphp
							<img src="{{ $signatureSrc }}" class="img-fluid border rounded" style="max-height: 150px;" alt="Signature du client" loading="lazy">
						</div>
					</div>
				</div>
			@endif
		</div>
		
		<div class="col-md-4">
			<!-- Actions -->
			<div class="card border-0 shadow-sm mb-4">
				<div class="card-header bg-transparent">
					<h6 class="mb-0">Actions</h6>
				</div>
				<div class="card-body">
					<!-- Bouton Modifier (désactivé après check-in) -->
					@php
						$statusService = new \App\Services\ReservationStatusService();
						$canModify = $statusService->canBeModified($reservation);
					@endphp
					
					@if($canModify)
						<div class="d-grid mb-2">
							<a href="{{ route('reception.reservations.edit', $reservation->id) }}" class="btn btn-outline-primary">
								<i class="bi bi-pencil-square me-2"></i>Modifier les informations
							</a>
						</div>
					@else
						<div class="alert alert-info mb-2">
							<small><i class="bi bi-lock me-1"></i>Les modifications sont verrouillées après le check-in pour des raisons de sécurité et de traçabilité.</small>
						</div>
					@endif
					
					@if($reservation->status === 'pending')
						<div class="d-grid gap-2">
							<form action="{{ route('reception.reservations.validate', $reservation->id) }}" method="POST">
								@csrf
								<button type="submit" class="btn btn-success w-100" onclick="return confirm('Valider cette pré-réservation ?')">
									<i class="bi bi-check-lg me-2"></i>Valider
								</button>
							</form>
							<form action="{{ route('reception.reservations.reject', $reservation->id) }}" method="POST">
								@csrf
								<button type="submit" class="btn btn-danger w-100" onclick="return confirm('Rejeter cette pré-réservation ?')">
									<i class="bi bi-x-lg me-2"></i>Rejeter
								</button>
							</form>
						</div>
					@elseif($reservation->status === 'validated')
						<div class="d-grid gap-2">
							{{-- Bouton Check-in : uniquement si réservation validée ET chambre assignée --}}
							@if($reservation->room_id)
								<form action="{{ route('reception.reservations.check-in', $reservation->id) }}" method="POST">
									@csrf
									<button type="submit" class="btn btn-success w-100" onclick="return confirm('⚠️ ATTENTION: Le check-in est IRRÉVERSIBLE. Après cette action, aucune modification ne sera possible.\n\nEffectuer le check-in du client dans la chambre {{ $reservation->room->room_number }} ?')">
										<i class="bi bi-box-arrow-in-right me-2"></i>Check-in (Irréversible)
									</button>
								</form>
							@else
								<div class="alert alert-warning mb-2">
									<small><i class="bi bi-exclamation-triangle me-1"></i>Aucune chambre assignée - Assignez une chambre pour effectuer le check-in</small>
								</div>
							@endif
							<a href="{{ route('reception.police-sheet.preview', $reservation) }}" class="btn btn-outline-primary">
								<i class="bi bi-eye me-2"></i>Aperçu feuille police
							</a>
							<a href="{{ route('reception.police-sheet.generate', $reservation) }}" class="btn btn-primary" target="_blank">
								<i class="bi bi-printer me-2"></i>Imprimer feuille police
							</a>
						</div>
					@elseif($reservation->status === 'checked_in')
						<div class="d-grid gap-2">
							<div class="alert alert-success mb-2">
								<small><i class="bi bi-check-circle me-1"></i>Client en séjour</small>
								@if($reservation->checked_in_at)
									<br><small class="text-muted">Check-in: {{ $reservation->checked_in_at->format('d/m/Y H:i') }}</small>
								@endif
							</div>
							@if($reservation->room)
								<div class="mb-2">
									<span class="badge bg-primary fs-6 w-100 p-2">
										<i class="bi bi-door-open me-1"></i>Chambre {{ $reservation->room->room_number }}
									</span>
								</div>
								{{-- Bouton Check-out : uniquement si client en séjour ET chambre assignée --}}
								<form action="{{ route('reception.reservations.check-out', $reservation->id) }}" method="POST">
									@csrf
									<button type="submit" class="btn btn-warning w-100" onclick="return confirm('Effectuer le check-out du client ? La chambre sera libérée.')">
										<i class="bi bi-box-arrow-right me-2"></i>Check-out
									</button>
								</form>
							@else
								<div class="alert alert-warning mb-2">
									<small><i class="bi bi-exclamation-triangle me-1"></i>Aucune chambre assignée - Impossible d'effectuer le check-out</small>
								</div>
							@endif
							<a href="{{ route('reception.police-sheet.preview', $reservation) }}" class="btn btn-outline-primary">
								<i class="bi bi-eye me-2"></i>Aperçu feuille police
							</a>
							<a href="{{ route('reception.police-sheet.generate', $reservation) }}" class="btn btn-primary" target="_blank">
								<i class="bi bi-printer me-2"></i>Imprimer feuille police
							</a>
						</div>
					@elseif($reservation->status === 'checked_out')
						<div class="d-grid gap-2">
							<div class="alert alert-info mb-2">
								<small><i class="bi bi-check-circle me-1"></i>Client parti</small>
								@if($reservation->checked_out_at)
									<br><small class="text-muted">Check-out: {{ $reservation->checked_out_at->format('d/m/Y H:i') }}</small>
								@endif
							</div>
							<a href="{{ route('reception.police-sheet.preview', $reservation) }}" class="btn btn-outline-primary">
								<i class="bi bi-eye me-2"></i>Aperçu feuille police
							</a>
							<a href="{{ route('reception.police-sheet.generate', $reservation) }}" class="btn btn-primary" target="_blank">
								<i class="bi bi-printer me-2"></i>Imprimer feuille police
							</a>
						</div>
					@elseif($reservation->status === 'rejected')
						<div class="alert alert-danger">
							<i class="bi bi-x-circle me-2"></i>Réservation rejetée (irréversible)
						</div>
					@endif
					
					<hr>
					<div class="d-grid gap-2">
						<a href="{{ route('reception.reservations.index') }}" class="btn btn-outline-secondary">
							<i class="bi bi-arrow-left me-2"></i>Retour à la liste
						</a>
					</div>
				</div>
			</div>
			
			<!-- Informations -->
			<div class="card border-0 shadow-sm">
				<div class="card-header bg-transparent">
					<h6 class="mb-0">Informations</h6>
				</div>
				<div class="card-body">
					<div class="mb-3">
						<strong>ID:</strong><br>
						<code>{{ $reservation->id }}</code>
					</div>
					<div class="mb-3">
						<strong>Hôtel:</strong><br>
						<span class="text-muted">{{ $reservation->hotel->name }}</span>
					</div>
					<div class="mb-3">
						<strong>Date de soumission:</strong><br>
						<span class="text-muted">{{ $reservation->created_at->format('d/m/Y H:i') }}</span>
					</div>
					<div class="mb-3">
						<strong>Dernière modification:</strong><br>
						<span class="text-muted">{{ $reservation->updated_at->format('d/m/Y H:i') }}</span>
					</div>
				</div>
			</div>
		</div>
	</div>
</x-app-layout>
