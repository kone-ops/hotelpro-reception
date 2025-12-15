<x-app-layout>
	<x-slot name="header">Modifier le champ "{{ $formField->label }}"</x-slot>
	
	<div class="row">
		<div class="col-md-8">
			<div class="card border-0 shadow-sm">
				<div class="card-header bg-transparent">
					<h5 class="mb-0">Modifier les informations</h5>
				</div>
				<div class="card-body">
					<form method="post" action="{{ route('super.forms.update', $formField) }}">
						@csrf @method('PUT')
						
						<div class="row">
							<div class="col-md-6 mb-3">
								<label class="form-label">Hôtel *</label>
								<select name="hotel_id" class="form-select" required>
									<option value="">Sélectionner un hôtel</option>
									@foreach($hotels as $hotel)
										<option value="{{ $hotel->id }}" {{ old('hotel_id', $formField->hotel_id) == $hotel->id ? 'selected' : '' }}>
											{{ $hotel->name }}
										</option>
									@endforeach
								</select>
								@error('hotel_id')<div class="text-danger small">{{ $message }}</div>@enderror
							</div>
							<div class="col-md-6 mb-3">
								<label class="form-label">Nom du champ *</label>
								<input type="text" name="name" class="form-control" value="{{ old('name', $formField->name) }}" required>
								@error('name')<div class="text-danger small">{{ $message }}</div>@enderror
							</div>
						</div>
						
						<div class="row">
							<div class="col-md-6 mb-3">
								<label class="form-label">Libellé *</label>
								<input type="text" name="label" class="form-control" value="{{ old('label', $formField->label) }}" required>
								@error('label')<div class="text-danger small">{{ $message }}</div>@enderror
							</div>
							<div class="col-md-6 mb-3">
								<label class="form-label">Type de champ *</label>
								<select name="type" class="form-select" required>
									<option value="text" {{ old('type', $formField->type) == 'text' ? 'selected' : '' }}>Texte</option>
									<option value="email" {{ old('type', $formField->type) == 'email' ? 'selected' : '' }}>Email</option>
									<option value="number" {{ old('type', $formField->type) == 'number' ? 'selected' : '' }}>Nombre</option>
									<option value="date" {{ old('type', $formField->type) == 'date' ? 'selected' : '' }}>Date</option>
									<option value="file" {{ old('type', $formField->type) == 'file' ? 'selected' : '' }}>Fichier</option>
									<option value="signature" {{ old('type', $formField->type) == 'signature' ? 'selected' : '' }}>Signature</option>
									<option value="checkbox" {{ old('type', $formField->type) == 'checkbox' ? 'selected' : '' }}>Case à cocher</option>
									<option value="textarea" {{ old('type', $formField->type) == 'textarea' ? 'selected' : '' }}>Zone de texte</option>
									<option value="select" {{ old('type', $formField->type) == 'select' ? 'selected' : '' }}>Liste déroulante</option>
								</select>
								@error('type')<div class="text-danger small">{{ $message }}</div>@enderror
							</div>
						</div>
						
						<div class="row">
							<div class="col-md-6 mb-3">
								<label class="form-label">Ordre d'affichage</label>
								<input type="number" name="order" class="form-control" value="{{ old('order', $formField->order) }}" min="0">
								@error('order')<div class="text-danger small">{{ $message }}</div>@enderror
							</div>
							<div class="col-md-6 mb-3">
								<label class="form-label">Options (JSON)</label>
								<input type="text" name="options" class="form-control" value="{{ old('options', json_encode($formField->options)) }}" placeholder='["Option 1", "Option 2"]'>
								@error('options')<div class="text-danger small">{{ $message }}</div>@enderror
							</div>
						</div>
						
						<div class="mb-3">
							<div class="form-check">
								<input type="checkbox" name="is_required" value="1" class="form-check-input" id="is_required" {{ old('is_required', $formField->is_required) ? 'checked' : '' }}>
								<label class="form-check-label" for="is_required">
									Champ obligatoire
								</label>
							</div>
						</div>
						
						<div class="d-flex gap-2">
							<button type="submit" class="btn btn-primary">
								<i class="bi bi-check-lg me-2"></i>Mettre à jour
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
					<h6 class="mb-0">Informations actuelles</h6>
				</div>
				<div class="card-body">
					<div class="mb-3">
						<strong>Hôtel:</strong><br>
						<span class="badge bg-info">{{ $formField->hotel->name }}</span>
					</div>
					<div class="mb-3">
						<strong>Nom:</strong><br>
						<code>{{ $formField->name }}</code>
					</div>
					<div class="mb-3">
						<strong>Type:</strong><br>
						<span class="badge bg-secondary">{{ ucfirst($formField->type) }}</span>
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
						<strong>Ordre:</strong><br>
						<span class="text-muted">{{ $formField->order }}</span>
					</div>
				</div>
			</div>
		</div>
	</div>
</x-app-layout>
