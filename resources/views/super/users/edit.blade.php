<x-app-layout>
	<x-slot name="header">Modifier {{ $user->name }}</x-slot>
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
					<h5 class="mb-0">Modifier les informations</h5>
				</div>
				<div class="card-body">
					<form method="post" action="{{ route('super.users.update', $user) }}">
						@csrf @method('PUT')
						
						<div class="row">
							<div class="col-md-6 mb-3">
								<label class="form-label">Nom complet *</label>
								<input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
								@error('name')<div class="text-danger small">{{ $message }}</div>@enderror
							</div>
							<div class="col-md-6 mb-3">
								<label class="form-label">Email *</label>
								<input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
								@error('email')<div class="text-danger small">{{ $message }}</div>@enderror
							</div>
						</div>
						
						<div class="row">
							<div class="col-md-6 mb-3">
								<label class="form-label">Nouveau mot de passe</label>
								<input type="password" name="password" class="form-control" placeholder="Laisser vide pour ne pas changer">
								@error('password')<div class="text-danger small">{{ $message }}</div>@enderror
							</div>
							<div class="col-md-6 mb-3">
								<label class="form-label">Hôtel</label>
								<select name="hotel_id" class="form-select">
									<option value="">Sélectionner un hôtel</option>
									@foreach($hotels as $hotel)
										<option value="{{ $hotel->id }}" {{ old('hotel_id', $user->hotel_id) == $hotel->id ? 'selected' : '' }}>
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
									<option value="{{ $role->name }}" {{ old('role', $user->roles->first()?->name) === $role->name ? 'selected' : '' }}>
										{{ $roleLabels[$role->name] ?? ucfirst(str_replace(['_', '-'], ' ', $role->name)) }}
									</option>
								@endforeach
							</select>
							@error('role')<div class="text-danger small">{{ $message }}</div>@enderror
						</div>
						
						<div class="d-flex gap-2">
							<button type="submit" class="btn btn-primary">
								<i class="bi bi-check-lg me-2"></i>Mettre à jour
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
					<h6 class="mb-0">Informations actuelles</h6>
				</div>
				<div class="card-body">
					<div class="mb-3">
						<strong>Email:</strong><br>
						<span class="text-muted">{{ $user->email }}</span>
					</div>
					<div class="mb-3">
						<strong>Hôtel:</strong><br>
						@if($user->hotel)
							<span class="badge bg-info">{{ $user->hotel->name }}</span>
						@else
							<span class="text-muted">Aucun hôtel</span>
						@endif
					</div>
					<div class="mb-3">
						<strong>Rôles actuels:</strong><br>
						@php
							$currentRole = $user->roles->first();
						@endphp
						@if($currentRole)
							<span class="badge bg-{{ $currentRole->name === 'super-admin' ? 'danger' : ($currentRole->name === 'hotel-admin' ? 'warning' : ($currentRole->name === 'laundry' ? 'primary' : 'success')) }}">
								{{ $roleLabels[$currentRole->name] ?? ucfirst(str_replace(['_', '-'], ' ', $currentRole->name)) }}
							</span>
						@endif
					</div>
				</div>
			</div>
		</div>
	</div>
</x-app-layout>
