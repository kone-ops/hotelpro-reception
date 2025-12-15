<x-app-layout>
	<x-slot name="header">Super Admin - Tableau de bord</x-slot>
	
	<div class="row">
		<div class="col-md-3 mb-4">
			<div class="card border-0 shadow-sm">
				<div class="card-body text-center">
				<i class="bi bi-building text-primary" style="font-size: 2rem;"></i>
				<h5 class="card-title mt-2">Hôtels</h5>
				<h3 class="text-primary">{{ $stats['total_hotels'] }}</h3>
				<a href="{{ route('super.hotels.index') }}" class="btn btn-sm btn-outline-primary mt-2">Gérer</a>
				</div>
			</div>
		</div>
		<div class="col-md-3 mb-4">
			<div class="card border-0 shadow-sm">
				<div class="card-body text-center">
				<i class="bi bi-people text-success" style="font-size: 2rem;"></i>
				<h5 class="card-title mt-2">Utilisateurs</h5>
				<h3 class="text-success">{{ $stats['total_users'] }}</h3>
				<a href="{{ route('super.users.index') }}" class="btn btn-sm btn-outline-success mt-2">Gérer</a>
				</div>
			</div>
		</div>
		<div class="col-md-3 mb-4">
			<div class="card border-0 shadow-sm">
				<div class="card-body text-center">
				<i class="bi bi-calendar-check text-warning" style="font-size: 2rem;"></i>
				<h5 class="card-title mt-2">Réservations</h5>
				<h3 class="text-warning">{{ $stats['total_reservations'] }}</h3>
				<a href="{{ route('super.activity') }}" class="btn btn-sm btn-outline-warning mt-2">Activité</a>
				</div>
			</div>
		</div>
		<div class="col-md-3 mb-4">
			<div class="card border-0 shadow-sm">
				<div class="card-body text-center">
				<i class="bi bi-graph-up text-info" style="font-size: 2rem;"></i>
				<h5 class="card-title mt-2">Aujourd'hui</h5>
				<h3 class="text-info">{{ $stats['reservations_today'] }}</h3>
				<a href="{{ route('super.reports.index') }}" class="btn btn-sm btn-outline-info mt-2">Rapports</a>
				</div>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-md-8">
			<div class="card border-0 shadow-sm">
				<div class="card-header bg-transparent d-flex justify-content-between align-items-center">
					<h5 class="mb-0">
						<i class="bi bi-activity me-2"></i>Activité récente (24h)
					</h5>
					<span class="badge bg-primary">{{ $recentActivities->count() }}</span>
				</div>
				<div class="card-body">
					@if($recentActivities->count() > 0)
						<div class="list-group list-group-flush">
							@foreach($recentActivities as $activity)
								<div class="list-group-item border-0 px-0 py-3">
									<div class="d-flex align-items-start">
										<!-- Icône selon le type d'activité -->
										<div class="activity-icon me-3">
											@php
												$iconClass = 'bi-info-circle';
												$iconColor = 'text-secondary';
												
												if(str_contains(strtolower($activity->description), 'créé') || str_contains(strtolower($activity->description), 'ajout')) {
													$iconClass = 'bi-plus-circle-fill';
													$iconColor = 'text-success';
												} elseif(str_contains(strtolower($activity->description), 'supprimé') || str_contains(strtolower($activity->description), 'retiré')) {
													$iconClass = 'bi-trash-fill';
													$iconColor = 'text-danger';
												} elseif(str_contains(strtolower($activity->description), 'modifié') || str_contains(strtolower($activity->description), 'mis à jour')) {
													$iconClass = 'bi-pencil-fill';
													$iconColor = 'text-warning';
												} elseif(str_contains(strtolower($activity->description), 'réservation')) {
													$iconClass = 'bi-calendar-check-fill';
													$iconColor = 'text-info';
												} elseif(str_contains(strtolower($activity->description), 'hôtel')) {
													$iconClass = 'bi-building';
													$iconColor = 'text-primary';
												} elseif(str_contains(strtolower($activity->description), 'utilisateur') || str_contains(strtolower($activity->description), 'user')) {
													$iconClass = 'bi-person-fill';
													$iconColor = 'text-info';
												}
											@endphp
											<i class="bi {{ $iconClass }} {{ $iconColor }} fs-4"></i>
										</div>
										
										<!-- Contenu de l'activité -->
										<div class="flex-grow-1">
											<div class="d-flex justify-content-between align-items-start mb-1">
												<h6 class="mb-0">{{ $activity->description }}</h6>
												<small class="text-muted ms-3 text-nowrap">
													{{ $activity->created_at->diffForHumans() }}
												</small>
											</div>
											
											<!-- Détails additionnels -->
											<div class="text-muted small">
												@if($activity->subject)
													<span class="me-3">
														<i class="bi bi-box me-1"></i>
														<strong>{{ class_basename($activity->subject_type) }}:</strong>
														@if(method_exists($activity->subject, 'name'))
															{{ $activity->subject->name }}
														@elseif(method_exists($activity->subject, 'room_number'))
															{{ $activity->subject->room_number }}
														@else
															#{{ $activity->subject_id }}
														@endif
													</span>
												@endif
												
												@if($activity->causer)
													<span class="me-3">
														<i class="bi bi-person me-1"></i>
														<strong>Par:</strong> {{ $activity->causer->name }}
													</span>
												@endif
												
												@if($activity->properties && isset($activity->properties['hotel_name']))
													<span>
														<i class="bi bi-building me-1"></i>
														<strong>Hôtel:</strong> {{ $activity->properties['hotel_name'] }}
													</span>
												@endif
											</div>
											
											<!-- Propriétés supplémentaires si disponibles -->
											@if($activity->properties && count($activity->properties) > 1)
												<div class="mt-2">
													@foreach($activity->properties as $key => $value)
														@if($key !== 'hotel_name' && !is_array($value))
															<span class="badge bg-light text-dark me-1">
																{{ ucfirst($key) }}: {{ $value }}
															</span>
														@endif
													@endforeach
												</div>
											@endif
										</div>
									</div>
								</div>
							@endforeach
						</div>
						
						<!-- Lien vers toutes les activités -->
						<div class="text-center mt-3">
							<a href="{{ route('super.activity') }}" class="btn btn-outline-primary btn-sm">
								<i class="bi bi-list-ul me-1"></i>Voir toutes les activités
							</a>
						</div>
					@else
						<div class="text-center py-4">
							<i class="bi bi-inbox fs-1 text-muted"></i>
							<p class="text-muted mt-2 mb-0">Aucune activité récente dans les dernières 24 heures</p>
						</div>
					@endif
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="card border-0 shadow-sm">
				<div class="card-header bg-transparent">
					<h5 class="mb-0">Actions rapides</h5>
				</div>
				<div class="card-body">
					<div class="d-grid gap-2">
						<a href="{{ route('super.hotels.index') }}" class="btn btn-primary">
							<i class="bi bi-building me-2"></i>Gérer les hôtels
						</a>
						<a href="{{ route('super.hotel-data.index') }}" class="btn btn-outline-danger">
							<i class="bi bi-database me-2"></i>Données des hôtels
						</a>
						<a href="{{ route('super.users.index') }}" class="btn btn-outline-primary">
							<i class="bi bi-people me-2"></i>Gérer les utilisateurs
						</a>
						<a href="{{ route('super.activity') }}" class="btn btn-outline-secondary">
							<i class="bi bi-clock-history me-2"></i>Voir l'activité
						</a>
						<a href="{{ route('super.reports.index') }}" class="btn btn-outline-info">
							<i class="bi bi-graph-up me-2"></i>Rapports
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</x-app-layout>

<style>
/* ======================================
   STYLES POUR LES ACTIVITÉS RÉCENTES
   ====================================== */

/* Effet hover sur les items d'activité */
.list-group-item:hover {
	background-color: var(--hover-bg, #f8f9fa);
	transition: background-color 0.2s ease;
}

/* Animation de l'icône d'activité */
.activity-icon i {
	transition: transform 0.2s ease;
}

.list-group-item:hover .activity-icon i {
	transform: scale(1.1);
}

/* Style pour les badges de propriétés */
.badge.bg-light {
	border: 1px solid #dee2e6;
	font-weight: 500;
}

/* Amélioration de la lisibilité du temps écoulé */
.text-nowrap {
	white-space: nowrap;
	font-size: 0.8rem;
}

/* Séparateur visuel entre les activités */
.list-group-item:not(:last-child) {
	border-bottom: 1px solid #f0f0f0 !important;
}

/* Animation de fade-in pour les nouvelles activités */
@keyframes fadeInActivity {
	from {
		opacity: 0;
		transform: translateY(-10px);
	}
	to {
		opacity: 1;
		transform: translateY(0);
	}
}

.list-group-item {
	animation: fadeInActivity 0.3s ease-out;
}

/* Responsive : Ajustement pour petits écrans */
@media (max-width: 768px) {
	.activity-icon {
		display: none;
	}
	
	.list-group-item .d-flex {
		flex-direction: column;
	}
	
	.text-nowrap {
		white-space: normal;
		margin-top: 0.5rem;
	}
}
</style>
