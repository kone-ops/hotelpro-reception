<x-app-layout>
	<x-slot name="header">Gestion des champs du formulaire</x-slot>

	<div class="d-flex justify-content-between align-items-center mb-4">
		<h4 class="mb-0">Formulaire de {{ $hotel->name }}</h4>
		<div class="alert alert-info mb-0">
			<i class="bi bi-info-circle me-2"></i>Champs prédéfinis selon le cahier de charge
		</div>
	</div>

	<!-- Les notifications sont maintenant gérées globalement dans le layout -->

	<div class="row">
		<div class="col-md-8">
			<div class="card border-0 shadow-sm">
				<div class="card-header bg-transparent">
					<h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Champs du formulaire</h5>
					<small class="text-muted">Champs prédéfinis (lecture seule)</small>
				</div>
				<div class="card-body">
					@if($formFields->count() > 0)
						<div class="list-group">
							@foreach($formFields as $field)
								<div class="list-group-item d-flex justify-content-between align-items-center">
									<div class="d-flex align-items-center flex-grow-1">
										<i class="bi bi-check-circle text-success me-3"></i>
										<div>
											<div class="d-flex align-items-center">
												<strong>{{ $field->label }}</strong>
												@if($field->required)
													<span class="badge bg-danger ms-2">Requis</span>
												@endif
												<span class="badge bg-secondary ms-2">{{ ucfirst($field->type) }}</span>
											</div>
											<small class="text-muted">Clé: <code>{{ $field->key }}</code> | Position: {{ $field->position }}</small>
										</div>
									</div>
									<span class="badge bg-light text-dark">Prédéfini</span>
								</div>
							@endforeach
						</div>
					@else
					<div class="text-center py-5">
						<i class="bi bi-exclamation-circle text-warning" style="font-size: 4rem;"></i>
						<h5 class="text-muted mt-3">Aucun champ prédéfini</h5>
						<p class="text-muted">Contactez l'administrateur pour initialiser les champs du formulaire.</p>
						<p class="small text-muted">Commande: <code>php artisan fields:init</code></p>
					</div>
					@endif
				</div>
			</div>
		</div>

		<div class="col-md-4">
			<div class="card border-0 shadow-sm mb-3">
				<div class="card-header bg-transparent">
					<h6 class="mb-0"><i class="bi bi-eye me-2"></i>Aperçu du formulaire</h6>
				</div>
				<div class="card-body">
					<a href="{{ route('public.form', $hotel) }}" target="_blank" class="btn btn-outline-primary w-100 mb-2">
						<i class="bi bi-box-arrow-up-right me-2"></i>Voir le formulaire public
					</a>
					<a href="{{ route('hotel.qr') }}" class="btn btn-outline-secondary w-100">
						<i class="bi bi-qr-code me-2"></i>Voir le QR Code
					</a>
				</div>
			</div>

			<div class="card border-0 shadow-sm">
				<div class="card-header bg-transparent">
					<h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Types de champs disponibles</h6>
				</div>
				<div class="card-body">
					<div class="mb-2">
						<i class="bi bi-input-cursor me-2 text-primary"></i><strong>Text</strong><br>
						<small class="text-muted">Champ de saisie libre</small>
					</div>
					<div class="mb-2">
						<i class="bi bi-envelope me-2 text-primary"></i><strong>Email</strong><br>
						<small class="text-muted">Validation email automatique</small>
					</div>
					<div class="mb-2">
						<i class="bi bi-telephone me-2 text-primary"></i><strong>Tel</strong><br>
						<small class="text-muted">Numéro de téléphone</small>
					</div>
					<div class="mb-2">
						<i class="bi bi-calendar me-2 text-primary"></i><strong>Date</strong><br>
						<small class="text-muted">Sélecteur de date</small>
					</div>
					<div class="mb-2">
						<i class="bi bi-123 me-2 text-primary"></i><strong>Number</strong><br>
						<small class="text-muted">Valeurs numériques</small>
					</div>
					<div class="mb-2">
						<i class="bi bi-file-earmark me-2 text-primary"></i><strong>File</strong><br>
						<small class="text-muted">Upload de fichiers</small>
					</div>
					<div class="mb-2">
						<i class="bi bi-textarea me-2 text-primary"></i><strong>Textarea</strong><br>
						<small class="text-muted">Zone de texte multilignes</small>
					</div>
					<div class="mb-2">
						<i class="bi bi-list me-2 text-primary"></i><strong>Select</strong><br>
						<small class="text-muted">Liste déroulante</small>
					</div>
					<div class="mb-2">
						<i class="bi bi-check-square me-2 text-primary"></i><strong>Checkbox</strong><br>
						<small class="text-muted">Case à cocher</small>
					</div>
				</div>
			</div>
		</div>
	</div>
</x-app-layout>

<!-- Modals de gestion désactivés - champs prédéfinis uniquement -->

<!-- Scripts de gestion désactivés - champs prédéfinis seulement -->
</style>