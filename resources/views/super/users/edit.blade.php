<x-app-layout>
	<x-slot name="header">Modifier {{ $user->name }}</x-slot>
	
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
							<label class="form-label">Rôles *</label>
							<div class="row">
								@foreach($roles as $role)
									<div class="col-md-4">
										<div class="form-check">
											<input type="checkbox" name="roles[]" value="{{ $role->name }}" class="form-check-input" id="role_{{ $role->id }}"
												{{ in_array($role->name, old('roles', $user->roles->pluck('name')->toArray())) ? 'checked' : '' }}>
											<label class="form-check-label" for="role_{{ $role->id }}">
												{{ ucfirst(str_replace('_', ' ', $role->name)) }}
											</label>
										</div>
									</div>
								@endforeach
							</div>
							@error('roles')<div class="text-danger small">{{ $message }}</div>@enderror
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
						@foreach($user->roles as $role)
							<span class="badge bg-{{ $role->name === 'super_admin' ? 'danger' : ($role->name === 'hotel_admin' ? 'warning' : 'success') }}">
								{{ ucfirst(str_replace('_', ' ', $role->name)) }}
							</span>
						@endforeach
					</div>
				</div>
			</div>
		</div>
	</div>
</x-app-layout>
