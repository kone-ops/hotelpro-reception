<x-app-layout>
	<x-slot name="header">Créer un nouvel utilisateur</x-slot>
	@php
		$roleLabels = [
			'super-admin' => 'Super Admin',
			'hotel-admin' => 'Gérant d\'hôtel',
			'receptionist' => 'Réceptionniste',
			'housekeeping' => 'Service des étages',
			'laundry' => 'Buanderie',
		];
	@endphp
	<div class="row">
		<div class="col-md-8">
			<div class="card border-0 shadow-sm">
				<div class="card-header bg-transparent">
					<h5 class="mb-0">Informations de l'utilisateur</h5>
				</div>
				<div class="card-body">
					<form method="post" action="{{ route('super.users.store') }}">
						@csrf
						
						<div class="row">
							<div class="col-md-6 mb-3">
								<label class="form-label">Nom complet *</label>
								<input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
								@error('name')<div class="text-danger small">{{ $message }}</div>@enderror
							</div>
							<div class="col-md-6 mb-3">
								<label class="form-label">Email *</label>
								<input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
								@error('email')<div class="text-danger small">{{ $message }}</div>@enderror
							</div>
						</div>
						
						<div class="row">
							<div class="col-md-6 mb-3">
								<label class="form-label">Mot de passe *</label>
								<input type="password" name="password" class="form-control" required>
								@error('password')<div class="text-danger small">{{ $message }}</div>@enderror
							</div>
							<div class="col-md-6 mb-3">
								<label class="form-label">Hôtel</label>
								<select name="hotel_id" class="form-select">
									<option value="">Sélectionner un hôtel</option>
									@foreach($hotels as $hotel)
										<option value="{{ $hotel->id }}" {{ old('hotel_id') == $hotel->id ? 'selected' : '' }}>
											{{ $hotel->name }}
										</option>
									@endforeach
								</select>
								@error('hotel_id')<div class="text-danger small">{{ $message }}</div>@enderror
							</div>
						</div>
						
						<div class="mb-3">
							<label class="form-label">Rôle *</label>
							<select name="role" class="form-select" required>
								<option value="">Sélectionner un rôle</option>
								@foreach($roles as $role)
									<option value="{{ $role->name }}" {{ old('role') === $role->name ? 'selected' : '' }}>
										{{ $roleLabels[$role->name] ?? ucfirst(str_replace(['_', '-'], ' ', $role->name)) }}
									</option>
								@endforeach
							</select>
							<small class="text-muted">L'hôtel est obligatoire pour tous les rôles sauf Super Admin.</small>
							@error('role')<div class="text-danger small">{{ $message }}</div>@enderror
						</div>
						
						<div class="d-flex gap-2">
							<button type="submit" class="btn btn-primary">
								<i class="bi bi-check-lg me-2"></i>Créer l'utilisateur
							</button>
							<a href="{{ route('super.users.index') }}" class="btn btn-outline-secondary">
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
					<h6 class="mb-0">Types de rôles</h6>
				</div>
				<div class="card-body">
					<div class="mb-3">
						<strong class="text-danger">Super Admin</strong><br>
						<small class="text-muted">Accès complet à tous les hôtels et fonctionnalités. Aucun hôtel assigné.</small>
					</div>
					<div class="mb-3">
						<strong class="text-warning">Hotel Admin</strong><br>
						<small class="text-muted">Gestion d'un hôtel (utilisateurs, paramètres, formulaires).</small>
					</div>
					<div class="mb-3">
						<strong class="text-success">Receptionist</strong><br>
						<small class="text-muted">Réception : enregistrements, check-in / check-out, validation des arrivées.</small>
					</div>
					<div class="mb-3">
						<strong class="text-info">Service des étages</strong><br>
						<small class="text-muted">Chambres à nettoyer, début/fin de nettoyage, historique de ses activités (filtrable par période).</small>
					</div>
					<div class="mb-3">
						<strong class="text-primary">Buanderie</strong><br>
						<small class="text-muted">Collectes de linge (linge d'étage), types de linge, statuts (en attente, en lavage, terminé), historique de ses activités (filtrable par période).</small>
					</div>
				</div>
			</div>
		</div>
	</div>
</x-app-layout>
