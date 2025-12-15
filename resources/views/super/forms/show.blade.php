<x-app-layout>
	<x-slot name="header">Détails du champ "{{ $formField->label }}"</x-slot>
	
	<div class="row">
		<div class="col-md-8">
			<div class="card border-0 shadow-sm mb-4">
				<div class="card-header bg-transparent">
					<div class="d-flex justify-content-between align-items-center">
						<h5 class="mb-0">Informations du champ</h5>
						<a href="{{ route('super.forms.edit', $formField) }}" class="btn btn-sm btn-outline-primary">
							<i class="bi bi-pencil me-1"></i>Modifier
						</a>
					</div>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col-md-6">
							<div class="mb-3">
								<strong>Nom du champ:</strong><br>
								<code>{{ $formField->name }}</code>
							</div>
							<div class="mb-3">
								<strong>Libellé:</strong><br>
								<span class="text-muted">{{ $formField->label }}</span>
							</div>
							<div class="mb-3">
								<strong>Type:</strong><br>
								<span class="badge bg-{{ $formField->type === 'email' ? 'primary' : ($formField->type === 'date' ? 'success' : 'secondary') }}">
									{{ ucfirst($formField->type) }}
								</span>
							</div>
						</div>
						<div class="col-md-6">
							<div class="mb-3">
								<strong>Hôtel:</strong><br>
								<span class="badge bg-info">{{ $formField->hotel->name }}</span>
							</div>
							<div class="mb-3">
								<strong>Obligatoire:</strong><br>
								@if($formField->is_required)
									<span class="badge bg-danger">Oui</span>
								@else
									<span class="badge bg-secondary">Non</span>
								@endif
							</div>
							<div class="mb-3">
								<strong>Ordre d'affichage:</strong><br>
								<span class="text-muted">{{ $formField->order }}</span>
							</div>
						</div>
					</div>
					
					@if($formField->options)
						<div class="mb-3">
							<strong>Options:</strong><br>
							<pre class="bg-light p-2 rounded"><code>{{ json_encode($formField->options, JSON_PRETTY_PRINT) }}</code></pre>
						</div>
					@endif
				</div>
			</div>
		</div>
		
		<div class="col-md-4">
			<div class="card border-0 shadow-sm mb-4">
				<div class="card-header bg-transparent">
					<h6 class="mb-0">Actions</h6>
				</div>
				<div class="card-body">
					<div class="d-grid gap-2">
						<a href="{{ route('super.forms.edit', $formField) }}" class="btn btn-outline-primary">
							<i class="bi bi-pencil me-2"></i>Modifier
						</a>
						<a href="{{ route('super.hotels.show', $formField->hotel) }}" class="btn btn-outline-info">
							<i class="bi bi-building me-2"></i>Voir l'hôtel
						</a>
						<a href="{{ route('super.forms.index') }}" class="btn btn-outline-secondary">
							<i class="bi bi-arrow-left me-2"></i>Retour à la liste
						</a>
					</div>
				</div>
			</div>
			
			<div class="card border-0 shadow-sm">
				<div class="card-header bg-transparent">
					<h6 class="mb-0">Statistiques</h6>
				</div>
				<div class="card-body">
					<div class="text-center">
						<i class="bi bi-calendar-check text-muted" style="font-size: 2rem;"></i>
						<h5 class="mt-2">0</h5>
						<p class="text-muted mb-0">Utilisations</p>
					</div>
					<small class="text-muted">Les statistiques d'utilisation seront disponibles prochainement.</small>
				</div>
			</div>
		</div>
	</div>
</x-app-layout>
