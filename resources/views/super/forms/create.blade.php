<x-app-layout>
	<x-slot name="header">Créer un nouveau champ</x-slot>
	
	<div class="row">
		<div class="col-md-8">
			<div class="card border-0 shadow-sm">
				<div class="card-header bg-transparent">
					<h5 class="mb-0">Informations du champ</h5>
				</div>
				<div class="card-body">
					<form method="post" action="{{ route('super.forms.store') }}">
						@csrf
						
						<div class="row">
							<div class="col-md-6 mb-3">
								<label class="form-label">Hôtel *</label>
								<select name="hotel_id" class="form-select" required>
									<option value="">Sélectionner un hôtel</option>
									@foreach($hotels as $hotel)
										<option value="{{ $hotel->id }}" {{ old('hotel_id') == $hotel->id ? 'selected' : '' }}>
											{{ $hotel->name }}
										</option>
									@endforeach
								</select>
								@error('hotel_id')<div class="text-danger small">{{ $message }}</div>@enderror
							</div>
							<div class="col-md-6 mb-3">
								<label class="form-label">Nom du champ *</label>
								<input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
								@error('name')<div class="text-danger small">{{ $message }}</div>@enderror
							</div>
						</div>
						
						<div class="row">
							<div class="col-md-6 mb-3">
								<label class="form-label">Libellé *</label>
								<input type="text" name="label" class="form-control" value="{{ old('label') }}" required>
								@error('label')<div class="text-danger small">{{ $message }}</div>@enderror
							</div>
							<div class="col-md-6 mb-3">
								<label class="form-label">Type de champ *</label>
								<select name="type" class="form-select" required>
									<option value="text" {{ old('type') == 'text' ? 'selected' : '' }}>Texte</option>
									<option value="email" {{ old('type') == 'email' ? 'selected' : '' }}>Email</option>
									<option value="number" {{ old('type') == 'number' ? 'selected' : '' }}>Nombre</option>
									<option value="date" {{ old('type') == 'date' ? 'selected' : '' }}>Date</option>
									<option value="file" {{ old('type') == 'file' ? 'selected' : '' }}>Fichier</option>
									<option value="signature" {{ old('type') == 'signature' ? 'selected' : '' }}>Signature</option>
									<option value="checkbox" {{ old('type') == 'checkbox' ? 'selected' : '' }}>Case à cocher</option>
									<option value="textarea" {{ old('type') == 'textarea' ? 'selected' : '' }}>Zone de texte</option>
									<option value="select" {{ old('type') == 'select' ? 'selected' : '' }}>Liste déroulante</option>
								</select>
								@error('type')<div class="text-danger small">{{ $message }}</div>@enderror
							</div>
						</div>
						
						<div class="row">
							<div class="col-md-6 mb-3">
								<label class="form-label">Ordre d'affichage</label>
								<input type="number" name="order" class="form-control" value="{{ old('order', 0) }}" min="0">
								@error('order')<div class="text-danger small">{{ $message }}</div>@enderror
							</div>
							<div class="col-md-6 mb-3">
								<label class="form-label">Options (JSON)</label>
								<input type="text" name="options" class="form-control" value="{{ old('options') }}" placeholder='["Option 1", "Option 2"]'>
								@error('options')<div class="text-danger small">{{ $message }}</div>@enderror
							</div>
						</div>
						
						<div class="mb-3">
							<div class="form-check">
								<input type="checkbox" name="is_required" value="1" class="form-check-input" id="is_required" {{ old('is_required') ? 'checked' : '' }}>
								<label class="form-check-label" for="is_required">
									Champ obligatoire
								</label>
							</div>
						</div>
						
						<div class="d-flex gap-2">
							<button type="submit" class="btn btn-primary">
								<i class="bi bi-check-lg me-2"></i>Créer le champ
							</button>
							<a href="{{ route('super.forms.index') }}" class="btn btn-outline-secondary">
								<i class="bi bi-arrow-left me-2"></i>Annuler
							</a>
						</div>
					</form>
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="card border-0 shadow-sm">
				<div class="card-header bg-transparent">
					<h6 class="mb-0">Types de champs</h6>
				</div>
				<div class="card-body">
					<div class="mb-2">
						<strong>Texte</strong><br>
						<small class="text-muted">Champ de saisie libre</small>
					</div>
					<div class="mb-2">
						<strong>Email</strong><br>
						<small class="text-muted">Validation email automatique</small>
					</div>
					<div class="mb-2">
						<strong>Nombre</strong><br>
						<small class="text-muted">Valeurs numériques uniquement</small>
					</div>
					<div class="mb-2">
						<strong>Date</strong><br>
						<small class="text-muted">Sélecteur de date</small>
					</div>
					<div class="mb-2">
						<strong>Fichier</strong><br>
						<small class="text-muted">Upload de documents</small>
					</div>
					<div class="mb-2">
						<strong>Signature</strong><br>
						<small class="text-muted">Signature électronique</small>
					</div>
					<div class="mb-2">
						<strong>Zone de texte</strong><br>
						<small class="text-muted">Texte long multilignes</small>
					</div>
					<div class="mb-2">
						<strong>Liste déroulante</strong><br>
						<small class="text-muted">Sélection parmi options</small>
					</div>
				</div>
			</div>
		</div>
	</div>
</x-app-layout>
