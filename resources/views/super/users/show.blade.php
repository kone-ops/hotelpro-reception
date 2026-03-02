<x-app-layout>
	<x-slot name="header">{{ $user->name }}</x-slot>
	
	<div class="row">
		<div class="col-md-8">
			<div class="card border-0 shadow-sm mb-4">
				<div class="card-header bg-transparent">
					<div class="d-flex justify-content-between align-items-center">
						<h5 class="mb-0">Informations personnelles</h5>
						<a href="{{ route('super.users.edit', $user) }}" class="btn btn-sm btn-outline-primary">
							<i class="bi bi-pencil me-1"></i>Modifier
						</a>
					</div>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col-md-6">
							<div class="mb-3">
								<strong>Nom:</strong><br>
								<span class="text-muted">{{ $user->name }}</span>
							</div>
							<div class="mb-3">
								<strong>Email:</strong><br>
								<span class="text-muted">{{ $user->email }}</span>
							</div>
						</div>
						<div class="col-md-6">
							<div class="mb-3">
								<strong>Hôtel:</strong><br>
								@if($user->hotel)
									<span class="badge bg-info">{{ $user->hotel->name }}</span>
								@else
									<span class="text-muted">Aucun hôtel assigné</span>
								@endif
							</div>
							<div class="mb-3">
								<strong>Rôles:</strong><br>
								@php
									$roleLabels = [
										'super-admin' => 'Super Admin',
										'hotel-admin' => 'Gérant d\'hôtel',
										'receptionist' => 'Réceptionniste',
										'housekeeping' => 'Service des étages',
										'laundry' => 'Buanderie',
									];
									$displayRole = $user->roles->first();
								@endphp
								@if($displayRole)
									<span class="badge bg-{{ $displayRole->name === 'super-admin' ? 'danger' : ($displayRole->name === 'hotel-admin' ? 'warning' : ($displayRole->name === 'laundry' ? 'primary' : 'success')) }}">
										{{ $roleLabels[$displayRole->name] ?? ucfirst(str_replace(['_', '-'], ' ', $displayRole->name)) }}
									</span>
								@else
									<span class="text-muted">Aucun rôle</span>
								@endif
							</div>
						</div>
					</div>
				</div>
			</div>
			
			<div class="card border-0 shadow-sm">
				<div class="card-header bg-transparent">
					<h5 class="mb-0">Activité récente</h5>
				</div>
				<div class="card-body">
					<div class="text-center py-4">
						<i class="bi bi-clock-history text-muted" style="font-size: 3rem;"></i>
						<h5 class="text-muted mt-3">Aucune activité</h5>
						<p class="text-muted">Les logs d'activité seront disponibles prochainement.</p>
					</div>
				</div>
			</div>
		</div>
		
		<div class="col-md-4">
			<div class="card border-0 shadow-sm mb-4">
				<div class="card-header bg-transparent">
					<h6 class="mb-0">Statistiques</h6>
				</div>
				<div class="card-body">
					<div class="row text-center">
						<div class="col-6">
							<div class="text-primary">
								<i class="bi bi-calendar-check" style="font-size: 1.5rem;"></i>
								<div class="small">0</div>
								<div class="small text-muted">Actions</div>
							</div>
						</div>
						<div class="col-6">
							<div class="text-success">
								<i class="bi bi-check-circle" style="font-size: 1.5rem;"></i>
								<div class="small">0</div>
								<div class="small text-muted">Validations</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			
			<div class="card border-0 shadow-sm">
				<div class="card-header bg-transparent">
					<h6 class="mb-0">Actions</h6>
				</div>
				<div class="card-body">
					<div class="d-grid gap-2">
						<a href="{{ route('super.users.edit', $user) }}" class="btn btn-outline-primary">
							<i class="bi bi-pencil me-2"></i>Modifier
						</a>
						@if($user->hotel)
							<a href="{{ route('super.hotels.show', $user->hotel) }}" class="btn btn-outline-info">
								<i class="bi bi-building me-2"></i>Voir l'hôtel
							</a>
						@endif
						<form method="post" action="{{ route('super.users.destroy', $user) }}" class="d-inline">
							@csrf @method('DELETE')
							<button type="submit" class="btn btn-outline-danger w-100" onclick="return confirm('Supprimer cet utilisateur ?')">
								<i class="bi bi-trash me-2"></i>Supprimer
							</button>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</x-app-layout>
