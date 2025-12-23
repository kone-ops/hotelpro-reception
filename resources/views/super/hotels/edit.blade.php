<x-app-layout>
	<x-slot name="header">Modifier {{ $hotel->name }}</x-slot>
	
	<div class="row">
		<div class="col-md-8">
			<div class="card border-0 shadow-sm">
				<div class="card-header bg-transparent">
					<h5 class="mb-0">Modifier les informations</h5>
				</div>
				<div class="card-body">
					<form method="post" action="{{ route('super.hotels.update', $hotel) }}" enctype="multipart/form-data">
						@csrf @method('PUT')
						
						<div class="mb-4">
							<label class="form-label">Logo de l'hôtel</label>
							<div class="d-flex align-items-start gap-3">
								<div class="flex-grow-1">
									<input type="file" name="logo" class="form-control" id="logoInput" accept="image/jpeg,image/jpg,image/png,image/svg+xml">
									<div class="form-text">Format: JPG, PNG ou SVG. Taille max: 2 Mo. Laisser vide pour conserver le logo actuel.</div>
									@error('logo')<div class="text-danger small">{{ $message }}</div>@enderror
								</div>
								<div id="logoPreview">
									@if($hotel->logo_url)
										<img src="{{ $hotel->logo_url }}" alt="Logo actuel" style="max-height: 80px; max-width: 150px; border-radius: 8px; border: 2px solid #e9ecef;">
									@else
										<img src="" alt="Aperçu logo" class="d-none" style="max-height: 80px; max-width: 150px; border-radius: 8px; border: 2px solid #e9ecef;">
									@endif
								</div>
							</div>
						</div>
						
						<div class="row">
							<div class="col-md-6 mb-3">
								<label class="form-label">Nom de l'hôtel *</label>
								<input type="text" name="name" class="form-control" value="{{ old('name', $hotel->name) }}" required>
								@error('name')<div class="text-danger small">{{ $message }}</div>@enderror
							</div>
							<div class="col-md-6 mb-3">
								<label class="form-label">Ville</label>
								<input type="text" name="city" class="form-control" value="{{ old('city', $hotel->city) }}">
								@error('city')<div class="text-danger small">{{ $message }}</div>@enderror
							</div>
						</div>
						
						<div class="row">
							<div class="col-md-8 mb-3">
								<label class="form-label">Adresse</label>
								<input type="text" name="address" class="form-control" value="{{ old('address', $hotel->address) }}">
								@error('address')<div class="text-danger small">{{ $message }}</div>@enderror
							</div>
							<div class="col-md-4 mb-3">
								<label class="form-label">Pays</label>
								<select name="country" class="form-select">
									<option value="">-- Sélectionner --</option>
									<option value="Cameroun" {{ old('country', $hotel->country) == 'Cameroun' ? 'selected' : '' }}>Cameroun</option>
									<option value="France" {{ old('country', $hotel->country) == 'France' ? 'selected' : '' }}>France</option>
									<option value="Belgique" {{ old('country', $hotel->country) == 'Belgique' ? 'selected' : '' }}>Belgique</option>
									<option value="Suisse" {{ old('country', $hotel->country) == 'Suisse' ? 'selected' : '' }}>Suisse</option>
									<option value="Canada" {{ old('country', $hotel->country) == 'Canada' ? 'selected' : '' }}>Canada</option>
									<option value="Sénégal" {{ old('country', $hotel->country) == 'Sénégal' ? 'selected' : '' }}>Sénégal</option>
									<option value="Côte d'Ivoire" {{ old('country', $hotel->country) == "Côte d'Ivoire" ? 'selected' : '' }}>Côte d'Ivoire</option>
									<option value="Mali" {{ old('country', $hotel->country) == 'Mali' ? 'selected' : '' }}>Mali</option>
									<option value="Burkina Faso" {{ old('country', $hotel->country) == 'Burkina Faso' ? 'selected' : '' }}>Burkina Faso</option>
									<option value="Bénin" {{ old('country', $hotel->country) == 'Bénin' ? 'selected' : '' }}>Bénin</option>
									<option value="Togo" {{ old('country', $hotel->country) == 'Togo' ? 'selected' : '' }}>Togo</option>
									<option value="Gabon" {{ old('country', $hotel->country) == 'Gabon' ? 'selected' : '' }}>Gabon</option>
									<option value="Congo" {{ old('country', $hotel->country) == 'Congo' ? 'selected' : '' }}>Congo</option>
									<option value="RDC" {{ old('country', $hotel->country) == 'RDC' ? 'selected' : '' }}>RDC</option>
									<option value="Maroc" {{ old('country', $hotel->country) == 'Maroc' ? 'selected' : '' }}>Maroc</option>
									<option value="Tunisie" {{ old('country', $hotel->country) == 'Tunisie' ? 'selected' : '' }}>Tunisie</option>
									<option value="Algérie" {{ old('country', $hotel->country) == 'Algérie' ? 'selected' : '' }}>Algérie</option>
									<option value="Madagascar" {{ old('country', $hotel->country) == 'Madagascar' ? 'selected' : '' }}>Madagascar</option>
									<option value="Autre" {{ old('country', $hotel->country) == 'Autre' ? 'selected' : '' }}>Autre</option>
								</select>
								@error('country')<div class="text-danger small">{{ $message }}</div>@enderror
							</div>
						</div>
						
						<div class="row">
							<div class="col-md-6 mb-3">
								<label class="form-label">Couleur primaire</label>
								<div class="input-group">
									<input type="color" name="primary_color" class="form-control form-control-color" value="{{ old('primary_color', $hotel->primary_color ?: '#1a4b8c') }}">
									<input type="text" class="form-control" value="{{ old('primary_color', $hotel->primary_color ?: '#1a4b8c') }}" readonly>
								</div>
								@error('primary_color')<div class="text-danger small">{{ $message }}</div>@enderror
							</div>
							<div class="col-md-6 mb-3">
								<label class="form-label">Couleur secondaire</label>
								<div class="input-group">
									<input type="color" name="secondary_color" class="form-control form-control-color" value="{{ old('secondary_color', $hotel->secondary_color ?: '#e19f32') }}">
									<input type="text" class="form-control" value="{{ old('secondary_color', $hotel->secondary_color ?: '#e19f32') }}" readonly>
								</div>
								@error('secondary_color')<div class="text-danger small">{{ $message }}</div>@enderror
							</div>
						</div>
						
						<hr class="my-4">
						<h6 class="mb-3">Configuration Oracle</h6>
						
						<div class="row">
							<div class="col-md-6 mb-3">
								<label class="form-label">DSN Oracle</label>
								<input type="text" name="oracle_dsn" class="form-control" value="{{ old('oracle_dsn', $hotel->oracle_dsn) }}" placeholder="oracle://host:port/sid">
								@error('oracle_dsn')<div class="text-danger small">{{ $message }}</div>@enderror
							</div>
							<div class="col-md-6 mb-3">
								<label class="form-label">Nom d'utilisateur Oracle</label>
								<input type="text" name="oracle_username" class="form-control" value="{{ old('oracle_username', $hotel->oracle_username) }}">
								@error('oracle_username')<div class="text-danger small">{{ $message }}</div>@enderror
							</div>
						</div>
						
						<div class="mb-3">
							<label class="form-label">Mot de passe Oracle</label>
							<input type="password" name="oracle_password" class="form-control" value="{{ old('oracle_password') }}" placeholder="Laisser vide pour ne pas changer">
							@error('oracle_password')<div class="text-danger small">{{ $message }}</div>@enderror
						</div>
						
						<div class="d-flex gap-2">
							<button type="submit" class="btn btn-primary">
								<i class="bi bi-check-lg me-2"></i>Mettre à jour
							</button>
							<a href="{{ route('super.hotels.show', $hotel) }}" class="btn btn-outline-secondary">
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
					<h6 class="mb-0">Actions</h6>
				</div>
				<div class="card-body">
					<div class="d-grid gap-2">
						<a href="{{ route('super.hotels.show', $hotel) }}" class="btn btn-outline-primary">
							<i class="bi bi-eye me-2"></i>Voir les détails
						</a>
						<a href="{{ route('super.users.index', ['hotel' => $hotel->id]) }}" class="btn btn-outline-secondary">
							<i class="bi bi-people me-2"></i>Gérer les utilisateurs
						</a>
						<a href="{{ route('super.forms.index', ['hotel' => $hotel->id]) }}" class="btn btn-outline-info">
							<i class="bi bi-gear me-2"></i>Configurer le formulaire
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<script>
		// Aperçu du logo
		document.getElementById('logoInput').addEventListener('change', function(e) {
			const file = e.target.files[0];
			if (file) {
				const reader = new FileReader();
				reader.onload = function(e) {
					const preview = document.getElementById('logoPreview');
					const img = preview.querySelector('img');
					img.src = e.target.result;
					img.classList.remove('d-none');
				}
				reader.readAsDataURL(file);
			}
		});
	</script>
</x-app-layout>
