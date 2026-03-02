<x-app-layout>
	<x-slot name="header">
		<i class="bi bi-activity me-2"></i>Journal d'Activité
	</x-slot>
	
	<!-- Statistiques en haut -->
	<div class="row g-3 mb-4">
		<div class="col-md-2">
			<div class="stat-card">
				<i class="bi bi-list-check stat-icon icon-primary"></i>
				<div class="stat-value">{{ $stats['total_activities'] }}</div>
				<div class="stat-label">Total activités</div>
			</div>
				</div>
		<div class="col-md-2">
			<div class="stat-card">
				<i class="bi bi-calendar-day stat-icon icon-info"></i>
				<div class="stat-value">{{ $stats['activities_today'] }}</div>
				<div class="stat-label">Aujourd'hui</div>
			</div>
		</div>
		<div class="col-md-2">
			<div class="stat-card">
				<i class="bi bi-calendar-week stat-icon icon-success"></i>
				<div class="stat-value">{{ $stats['activities_week'] }}</div>
				<div class="stat-label">Cette semaine</div>
			</div>
				</div>
		<div class="col-md-2">
			<div class="stat-card">
				<i class="bi bi-calendar-month stat-icon icon-warning"></i>
				<div class="stat-value">{{ $stats['activities_month'] }}</div>
				<div class="stat-label">Ce mois</div>
			</div>
		</div>
		<div class="col-md-2">
			<div class="stat-card">
				<i class="bi bi-people stat-icon icon-secondary"></i>
				<div class="stat-value">{{ $stats['unique_users'] }}</div>
				<div class="stat-label">Utilisateurs actifs</div>
			</div>
		</div>
		<div class="col-md-2">
			<div class="stat-card">
				<i class="bi bi-bar-chart-line stat-icon icon-danger"></i>
				<div class="stat-value">{{ $activities_chart->sum('count') }}</div>
				<div class="stat-label">7 derniers jours</div>
			</div>
		</div>
	</div>

	<!-- Filtres -->
	<div class="modern-card mb-4">
		<div class="card-header">
			<h6 class="mb-0 text-white">
				<i class="bi bi-funnel me-2"></i>Filtres
			</h6>
		</div>
		<div class="card-body">
			<form method="GET" class="row g-3">
				<div class="col-md-3">
					<label class="form-label small fw-bold">
						<i class="bi bi-search me-1"></i>Rechercher
					</label>
					<input type="text" name="search" class="form-control" placeholder="Rechercher dans la description..." value="{{ request('search') }}">
				</div>
				<div class="col-md-2">
					<label class="form-label small fw-bold">
						<i class="bi bi-building me-1"></i>Hôtel
					</label>
					<select name="hotel_id" class="form-select">
						<option value="">Tous les hôtels</option>
						@foreach($hotels as $hotel)
							<option value="{{ $hotel->id }}" {{ request('hotel_id') == $hotel->id ? 'selected' : '' }}>
								{{ $hotel->name }}
							</option>
						@endforeach
					</select>
				</div>
				<div class="col-md-2">
					<label class="form-label small fw-bold">
						<i class="bi bi-person me-1"></i>Utilisateur
					</label>
					<select name="user_id" class="form-select">
						<option value="">Tous</option>
						@foreach($users as $user)
							<option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
								{{ $user->name }}
							</option>
						@endforeach
					</select>
				</div>
				<div class="col-md-2">
					<label class="form-label small fw-bold">
						<i class="bi bi-tag me-1"></i>Type
					</label>
					<select name="event" class="form-select">
						<option value="">Tous</option>
						@foreach($events as $key => $label)
							<option value="{{ $key }}" {{ request('event') == $key ? 'selected' : '' }}>
								{{ $label }}
							</option>
						@endforeach
					</select>
				</div>
				<div class="col-md-2">
					<label class="form-label small fw-bold">
						<i class="bi bi-shield-exclamation me-1"></i>Catégorie
					</label>
					<select name="action_category" class="form-select">
						<option value="">Toutes</option>
						@foreach($actionCategories ?? [] as $key => $label)
							<option value="{{ $key }}" {{ request('action_category') == $key ? 'selected' : '' }}>
								{{ $label }}
							</option>
						@endforeach
					</select>
				</div>
				<div class="col-md-2">
					<label class="form-label small fw-bold">
						<i class="bi bi-list-check me-1"></i>Action
					</label>
					<select name="action_type" class="form-select">
						<option value="">Toutes</option>
						@foreach($actionTypes ?? [] as $key => $label)
							<option value="{{ $key }}" {{ request('action_type') == $key ? 'selected' : '' }}>
								{{ $label }}
							</option>
						@endforeach
					</select>
				</div>
				<div class="col-md-2">
					<label class="form-label small fw-bold">
						<i class="bi bi-person-badge me-1"></i>Rôle
					</label>
					<select name="user_role" class="form-select">
						<option value="">Tous</option>
						<option value="super-admin" {{ request('user_role') == 'super-admin' ? 'selected' : '' }}>Super Admin</option>
						<option value="hotel-admin" {{ request('user_role') == 'hotel-admin' ? 'selected' : '' }}>Admin Hôtel</option>
						<option value="receptionist" {{ request('user_role') == 'receptionist' ? 'selected' : '' }}>Réceptionniste</option>
					</select>
				</div>
				<div class="col-md-2">
					<label class="form-label small fw-bold">
						<i class="bi bi-calendar-range me-1"></i>Période
					</label>
					<div class="input-group">
						<input type="date" name="date_debut" class="form-control" value="{{ request('date_debut') }}">
						<span class="input-group-text">→</span>
						<input type="date" name="date_fin" class="form-control" value="{{ request('date_fin') }}">
					</div>
				</div>
				<div class="col-md-12">
					<button type="submit" class="btn btn-primary">
						<i class="bi bi-search me-1"></i>Rechercher
					</button>
					<a href="{{ route('super.activity') }}" class="btn btn-outline-secondary">
						<i class="bi bi-arrow-clockwise me-1"></i>Réinitialiser
					</a>
				</div>
			</form>
		</div>
	</div>

	<div class="row">
		<!-- Liste des activités -->
		<div class="col-md-9">
			<div class="modern-card">
				<div class="card-header d-flex justify-content-between align-items-center">
					<h6 class="mb-0 text-white">
						<i class="bi bi-clock-history me-2"></i>Activités récentes
					</h6>
					<span class="badge bg-primary">{{ $totalActivities ?? $activities->count() }}</span>
				</div>
				<div class="card-body p-0">
					@if($activities->count() > 0)
						<div class="activity-timeline" id="activities-timeline">
							@foreach($activities as $activity)
								<div class="activity-item">
									<!-- Icône -->
									<div class="activity-icon-wrapper">
										@php
											$iconClass = 'bi-info-circle';
											$iconColor = 'secondary';
											$actionType = $activity->properties['action_type'] ?? null;
											
											// Déterminer l'icône selon le type d'action
											if ($actionType === 'reservation_validated') {
												$iconClass = 'bi-check-circle-fill';
												$iconColor = 'success';
											} elseif ($actionType === 'reservation_rejected') {
												$iconClass = 'bi-x-circle-fill';
												$iconColor = 'danger';
											} elseif ($actionType === 'reservation_checkin') {
												$iconClass = 'bi-box-arrow-in-right';
												$iconColor = 'info';
											} elseif ($actionType === 'reservation_checkout') {
												$iconClass = 'bi-box-arrow-right';
												$iconColor = 'primary';
											} elseif ($actionType === 'price_modified' || $actionType === 'payment_received') {
												$iconClass = 'bi-currency-dollar';
												$iconColor = 'warning';
											} elseif ($actionType === 'user_deleted' || $actionType === 'data_deleted') {
												$iconClass = 'bi-trash-fill';
												$iconColor = 'danger';
											} elseif ($activity->event === 'created') {
												$iconClass = 'bi-plus-circle-fill';
												$iconColor = 'success';
											} elseif ($activity->event === 'deleted') {
												$iconClass = 'bi-trash-fill';
												$iconColor = 'danger';
											} elseif ($activity->event === 'updated') {
												$iconClass = 'bi-pencil-fill';
												$iconColor = 'warning';
											}
										@endphp
										<div class="activity-icon bg-{{ $iconColor }}-soft">
											<i class="bi {{ $iconClass }} text-{{ $iconColor }}"></i>
										</div>
									</div>
									
									<!-- Contenu -->
									<div class="activity-content">
										<div class="activity-header">
											<h6 class="activity-title">{{ $activity->description }}</h6>
											<span class="activity-time">
												<i class="bi bi-clock me-1"></i>
												{{ $activity->created_at->diffForHumans() }}
											</span>
										</div>
										
										<div class="activity-details">
											@if($activity->causer)
												<span class="detail-item">
													<i class="bi bi-person-circle me-1"></i>
													<strong>{{ $activity->causer->name }}</strong>
												</span>
											@endif
											
											@if($activity->subject)
												<span class="detail-item">
													<i class="bi bi-box me-1"></i>
													{{ class_basename($activity->subject_type) }}
													@if(method_exists($activity->subject, 'name'))
														: {{ $activity->subject->name }}
													@elseif(method_exists($activity->subject, 'room_number'))
														: {{ $activity->subject->room_number }}
												@else
														#{{ $activity->subject_id }}
													@endif
												</span>
											@endif
											
											@if($activity->properties && isset($activity->properties['hotel_name']))
												<span class="detail-item">
													<i class="bi bi-building me-1"></i>
													{{ $activity->properties['hotel_name'] }}
												</span>
											@endif
											
											@if($activity->ip_address)
												<span class="detail-item">
													<i class="bi bi-geo-alt me-1"></i>
													{{ $activity->ip_address }}
												</span>
											@endif
											
											@if($activity->causer && $activity->causer->roles)
												<span class="detail-item">
													<i class="bi bi-person-badge me-1"></i>
													@foreach($activity->causer->roles as $role)
														<span class="badge bg-info">{{ ucfirst($role->name) }}</span>
													@endforeach
												</span>
											@endif
											
											@if($activity->properties && isset($activity->properties['action_type']))
												@php
													$actionType = $activity->properties['action_type'];
													$actionLabel = $actionTypes[$actionType] ?? $actionType;
													$isCritical = in_array($actionType, ['reservation_validated', 'reservation_rejected', 'reservation_checkin', 'reservation_checkout', 'price_modified', 'payment_received', 'user_deleted', 'data_deleted']);
													$isSensitive = in_array($actionType, ['reservation_updated', 'reservation_pending', 'room_status_changed', 'user_created', 'user_updated', 'hotel_updated', 'settings_changed', 'data_exported', 'data_imported']);
												@endphp
												<span class="detail-item">
													<i class="bi bi-{{ $isCritical ? 'shield-exclamation' : ($isSensitive ? 'shield-check' : 'info-circle') }} me-1"></i>
													<span class="badge bg-{{ $isCritical ? 'danger' : ($isSensitive ? 'warning' : 'secondary') }}">
														{{ $actionLabel }}
													</span>
												</span>
											@endif
										</div>
										
										@if($activity->properties && (isset($activity->properties['changes']) || isset($activity->properties['old_values']) || isset($activity->properties['new_values'])))
										<div class="activity-changes mt-2 p-2 bg-light rounded" style="font-size: 0.8rem;">
											<strong class="text-muted">Détails des modifications :</strong>
											@if(isset($activity->properties['changes']))
												@foreach($activity->properties['changes'] as $field => $value)
													<div class="mt-1">
														<code class="text-primary">{{ $field }}</code>: 
														<span class="text-success">{{ is_array($value) ? json_encode($value) : $value }}</span>
													</div>
												@endforeach
											@elseif(isset($activity->properties['old_values']) && isset($activity->properties['new_values']))
												@foreach($activity->properties['old_values'] as $field => $oldValue)
													@if(isset($activity->properties['new_values'][$field]) && $activity->properties['new_values'][$field] != $oldValue)
														<div class="mt-1">
															<code class="text-primary">{{ $field }}</code>: 
															<span class="text-danger">{{ is_array($oldValue) ? json_encode($oldValue) : $oldValue }}</span>
															→ 
															<span class="text-success">{{ is_array($activity->properties['new_values'][$field]) ? json_encode($activity->properties['new_values'][$field]) : $activity->properties['new_values'][$field] }}</span>
														</div>
													@endif
												@endforeach
											@endif
										</div>
										@endif
										
										@if($activity->user_agent)
										<div class="activity-user-agent mt-1" style="font-size: 0.75rem; color: #6c757d;">
											<i class="bi bi-laptop me-1"></i>
											<small>{{ Str::limit($activity->user_agent, 80) }}</small>
										</div>
										@endif
										
										<!-- Badge du type d'événement -->
										<div class="activity-badge mt-2">
											@php
												$actionType = $activity->properties['action_type'] ?? null;
												$isCritical = $actionType && in_array($actionType, ['reservation_validated', 'reservation_rejected', 'reservation_checkin', 'reservation_checkout', 'price_modified', 'payment_received', 'user_deleted', 'data_deleted']);
												$isSensitive = $actionType && in_array($actionType, ['reservation_updated', 'reservation_pending', 'room_status_changed', 'user_created', 'user_updated', 'hotel_updated', 'settings_changed', 'data_exported', 'data_imported']);
											@endphp
											
											@if($actionType)
												@php
													$actionLabel = $actionTypes[$actionType] ?? $actionType;
												@endphp
												<span class="badge bg-{{ $isCritical ? 'danger' : ($isSensitive ? 'warning' : 'info') }}-soft text-{{ $isCritical ? 'danger' : ($isSensitive ? 'warning' : 'info') }}">
													<i class="bi bi-{{ $isCritical ? 'shield-exclamation' : ($isSensitive ? 'shield-check' : 'info-circle') }} me-1"></i>
													{{ $actionLabel }}
												</span>
											@elseif($activity->event === 'created')
												<span class="badge bg-success-soft text-success">
													<i class="bi bi-plus-circle me-1"></i>Créé
												</span>
											@elseif($activity->event === 'updated')
												<span class="badge bg-warning-soft text-warning">
													<i class="bi bi-pencil me-1"></i>Modifié
												</span>
											@elseif($activity->event === 'deleted')
												<span class="badge bg-danger-soft text-danger">
													<i class="bi bi-trash me-1"></i>Supprimé
												</span>
											@else
												<span class="badge bg-secondary-soft text-secondary">
													{{ ucfirst($activity->event) }}
												</span>
											@endif
											
											<small class="text-muted ms-2">
												{{ $activity->created_at->format('d/m/Y à H:i:s') }}
											</small>
										</div>
									</div>
								</div>
									@endforeach
						</div>
						
						<!-- Bouton Voir plus -->
						@if(($totalActivities ?? $activities->count()) > $activities->count())
							<div class="text-center p-3 border-top" id="load-more-container">
								<button type="button" class="btn btn-outline-primary btn-sm" id="load-more-activities" 
										data-offset="{{ $activities->count() }}" 
										data-limit="10"
										data-hotel-id="{{ request('hotel_id') }}"
										data-user-id="{{ request('user_id') }}"
										data-event="{{ request('event') }}"
										data-action-type="{{ request('action_type') }}"
										data-action-category="{{ request('action_category') }}"
										data-user-role="{{ request('user_role') }}"
										data-date-debut="{{ request('date_debut') }}"
										data-date-fin="{{ request('date_fin') }}"
										data-search="{{ request('search') }}">
									<i class="bi bi-arrow-down me-1"></i>Voir plus
								</button>
							</div>
						@endif
					@else
						<div class="text-center py-5">
							<i class="bi bi-inbox fs-1 text-muted"></i>
							<h5 class="text-muted mt-3">Aucune activité</h5>
							<p class="text-muted">Les activités apparaîtront ici lorsque des actions seront effectuées.</p>
						</div>
					@endif
			</div>
		</div>
	</div>
	
		<!-- Sidebar avec statistiques -->
		<div class="col-md-3">
			<!-- Top utilisateurs -->
			<div class="modern-card mb-4">
				<div class="card-header">
					<h6 class="mb-0 text-white">
						<i class="bi bi-trophy me-2"></i>Top Utilisateurs
					</h6>
				</div>
				<div class="card-body">
					@if($top_users->count() > 0)
						@foreach($top_users as $index => $user)
							<div class="top-user-item {{ !$loop->last ? 'mb-3 pb-3 border-bottom' : '' }}">
								<div class="d-flex align-items-center">
									<div class="rank-badge rank-{{ $index + 1 }} me-2">
										{{ $index + 1 }}
						</div>
									<div class="flex-grow-1">
										<div class="fw-bold">{{ $user->name }}</div>
										<small class="text-muted">{{ $user->email }}</small>
						</div>
									<span class="badge bg-primary">{{ $user->activities_count }}</span>
						</div>
					</div>
						@endforeach
					@else
						<p class="text-muted mb-0 small">Aucune donnée disponible</p>
					@endif
			</div>
		</div>
		
			<!-- Graphique 7 derniers jours -->
			<div class="modern-card">
				<div class="card-header">
					<h6 class="mb-0 text-white">
						<i class="bi bi-graph-up me-2"></i>7 derniers jours
					</h6>
				</div>
				<div class="card-body">
					@if($activities_chart->count() > 0)
						<canvas id="activityChart" height="200"></canvas>
					@else
						<p class="text-muted mb-0 small">Aucune donnée disponible</p>
					@endif
				</div>
			</div>
		</div>
	</div>
</x-app-layout>

<style>
/* ======================================
   STYLES POUR LA PAGE D'ACTIVITÉ
   ====================================== */

/* Timeline des activités */
.activity-timeline {
	position: relative;
}

.activity-item {
	display: flex;
	padding: 1.25rem;
	border-bottom: 1px solid #e9ecef;
	transition: background-color 0.2s ease;
}

.activity-item:hover {
	background-color: #f8f9fa;
}

.activity-item:last-child {
	border-bottom: none;
}

/* Icône de l'activité */
.activity-icon-wrapper {
	margin-right: 1rem;
	flex-shrink: 0;
}

.activity-icon {
	width: 40px;
	height: 40px;
	border-radius: 10px;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 1.25rem;
}

/* Contenu de l'activité */
.activity-content {
	flex-grow: 1;
	min-width: 0;
}

.activity-header {
	display: flex;
	justify-content: space-between;
	align-items: start;
	margin-bottom: 0.5rem;
}

.activity-title {
	margin: 0;
	font-size: 0.95rem;
	font-weight: 600;
	color: #2c3e50;
}

.activity-time {
	font-size: 0.75rem;
	color: #6c757d;
	white-space: nowrap;
	margin-left: 1rem;
}

.activity-details {
	display: flex;
	flex-wrap: wrap;
	gap: 0.75rem;
	margin-bottom: 0.5rem;
}

.detail-item {
	font-size: 0.85rem;
	color: #6c757d;
	display: inline-flex;
	align-items: center;
}

.detail-item i {
	font-size: 0.9rem;
}

.activity-badge {
	display: flex;
	align-items: center;
	flex-wrap: wrap;
	gap: 0.5rem;
}

/* Badges personnalisés */
.bg-success-soft {
	background-color: rgba(25, 135, 84, 0.1) !important;
}

.bg-warning-soft {
	background-color: rgba(255, 193, 7, 0.1) !important;
}

.bg-danger-soft {
	background-color: rgba(220, 53, 69, 0.1) !important;
}

.bg-secondary-soft {
	background-color: rgba(108, 117, 125, 0.1) !important;
}

.bg-info-soft {
	background-color: rgba(13, 202, 240, 0.1) !important;
}

.bg-primary-soft {
	background-color: rgba(13, 110, 253, 0.1) !important;
}

/* Top utilisateurs */
.top-user-item {
	font-size: 0.9rem;
}

.rank-badge {
	width: 28px;
	height: 28px;
	border-radius: 50%;
	display: flex;
	align-items: center;
	justify-content: center;
	font-weight: 700;
	font-size: 0.8rem;
}

.rank-1 {
	background: linear-gradient(135deg, #FFD700, #FFA500);
	color: #fff;
	box-shadow: 0 2px 8px rgba(255, 215, 0, 0.3);
}

.rank-2 {
	background: linear-gradient(135deg, #C0C0C0, #A9A9A9);
	color: #fff;
	box-shadow: 0 2px 8px rgba(192, 192, 192, 0.3);
}

.rank-3 {
	background: linear-gradient(135deg, #CD7F32, #B87333);
	color: #fff;
	box-shadow: 0 2px 8px rgba(205, 127, 50, 0.3);
}

.rank-4, .rank-5 {
	background: #e9ecef;
	color: #495057;
}

/* Responsive */
@media (max-width: 768px) {
	.activity-header {
		flex-direction: column;
		align-items: start;
	}
	
	.activity-time {
		margin-left: 0;
		margin-top: 0.25rem;
}

	.activity-details {
		flex-direction: column;
		gap: 0.25rem;
	}
	
	.activity-icon-wrapper {
		display: none;
	}
}

/* Pagination des activités */
.pagination svg,
nav[role="navigation"] svg {
	display: none !important;
}

.pagination .page-link {
	padding: 0.375rem 0.75rem !important;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Graphique des 7 derniers jours
@if($activities_chart->count() > 0)
document.addEventListener('DOMContentLoaded', function() {
	const ctx = document.getElementById('activityChart');
	if (ctx) {
		new Chart(ctx, {
			type: 'line',
			data: {
				labels: {!! json_encode($activities_chart->pluck('date')->map(function($date) {
					return \Carbon\Carbon::parse($date)->format('d/m');
				})) !!},
				datasets: [{
					label: 'Activités',
					data: {!! json_encode($activities_chart->pluck('count')) !!},
					borderColor: '#0d6efd',
					backgroundColor: 'rgba(13, 110, 253, 0.1)',
					tension: 0.4,
					fill: true
				}]
			},
			options: {
		responsive: true,
				maintainAspectRatio: false,
				plugins: {
					legend: {
						display: false
					}
				},
				scales: {
					y: {
						beginAtZero: true,
						ticks: {
							precision: 0
				}
			}
				}
			}
		});
		}
	});
	@endif

// Charger plus d'activités
document.addEventListener('DOMContentLoaded', function() {
	const loadMoreBtn = document.getElementById('load-more-activities');
	const timeline = document.getElementById('activities-timeline');
	const loadMoreContainer = document.getElementById('load-more-container');
	
	if (loadMoreBtn && timeline) {
		loadMoreBtn.addEventListener('click', function() {
			const btn = this;
			const offset = parseInt(btn.dataset.offset);
			const limit = parseInt(btn.dataset.limit);
			
			// Désactiver le bouton et afficher le loader
			btn.disabled = true;
			const originalHtml = btn.innerHTML;
			btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Chargement...';
			
			// Construire les paramètres de requête
			const params = new URLSearchParams({
				offset: offset,
				limit: limit,
			});
			
			// Ajouter les filtres s'ils existent
			if (btn.dataset.hotelId) params.append('hotel_id', btn.dataset.hotelId);
			if (btn.dataset.userId) params.append('user_id', btn.dataset.userId);
			if (btn.dataset.event) params.append('event', btn.dataset.event);
			if (btn.dataset.actionType) params.append('action_type', btn.dataset.actionType);
			if (btn.dataset.actionCategory) params.append('action_category', btn.dataset.actionCategory);
			if (btn.dataset.userRole) params.append('user_role', btn.dataset.userRole);
			if (btn.dataset.dateDebut) params.append('date_debut', btn.dataset.dateDebut);
			if (btn.dataset.dateFin) params.append('date_fin', btn.dataset.dateFin);
			if (btn.dataset.search) params.append('search', btn.dataset.search);
			
			fetch(`{{ route('super.activity.load-more') }}?${params.toString()}`, {
				headers: {
					'Accept': 'application/json',
					'X-Requested-With': 'XMLHttpRequest',
					'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
				},
				credentials: 'same-origin'
			})
			.then(async response => {
				if (!response.ok) {
					const errorText = await response.text();
					console.error('Erreur HTTP:', response.status, errorText);
					throw new Error(`Erreur HTTP ${response.status}: ${errorText.substring(0, 100)}`);
				}
				
				let data;
				try {
					data = await response.json();
				} catch (e) {
					const text = await response.text();
					console.error('Erreur parsing JSON:', text);
					throw new Error('Réponse invalide du serveur (pas de JSON)');
				}
				
				if (!data || !data.activities) {
					console.error('Données invalides:', data);
					throw new Error('Réponse invalide du serveur (pas de données)');
				}
				
				if (data.activities && data.activities.length > 0) {
					// Ajouter un séparateur
					const separator = document.createElement('hr');
					separator.className = 'my-0';
					timeline.appendChild(separator);
					
					// Ajouter les nouvelles activités
					data.activities.forEach(activity => {
						const activityItem = createActivityItem(activity);
						timeline.appendChild(activityItem);
						const hr = document.createElement('hr');
						hr.className = 'my-0';
						timeline.appendChild(hr);
					});
					
					// Mettre à jour l'offset
					btn.dataset.offset = data.next_offset;
					
					// Si plus d'activités, réactiver le bouton, sinon le masquer
					if (data.has_more) {
						btn.disabled = false;
						btn.innerHTML = originalHtml;
					} else {
						if (loadMoreContainer) {
							loadMoreContainer.remove();
						}
					}
				} else {
					// Plus d'activités
					if (loadMoreContainer) {
						loadMoreContainer.remove();
					}
				}
			})
			.catch(error => {
				console.error('Erreur lors du chargement:', error);
				console.error('Détails:', {
					message: error.message,
					stack: error.stack,
					offset: offset,
					limit: limit
				});
				btn.disabled = false;
				btn.innerHTML = originalHtml;
				
				// Afficher un message d'erreur plus détaillé
				let errorMessage = 'Erreur lors du chargement des activités.';
				if (error.message) {
					errorMessage += '\n\n' + error.message;
				}
				errorMessage += '\n\nVeuillez vérifier la console pour plus de détails.';
				
				// Utiliser SweetAlert2 si disponible, sinon alert
				if (typeof Swal !== 'undefined') {
					Swal.fire({
						icon: 'error',
						title: 'Erreur',
						text: errorMessage,
						confirmButtonText: 'OK'
					});
				} else {
					alert(errorMessage);
				}
			});
		});
	}
});

function createActivityItem(activity) {
	const div = document.createElement('div');
	div.className = 'activity-item';
	
	// Déterminer l'icône et la couleur selon le type d'action
	let iconClass = 'bi-info-circle';
	let iconColor = 'secondary';
	const actionType = activity.action_type || activity.properties?.action_type;
	
	if (actionType === 'reservation_validated') {
		iconClass = 'bi-check-circle-fill';
		iconColor = 'success';
	} else if (actionType === 'reservation_rejected') {
		iconClass = 'bi-x-circle-fill';
		iconColor = 'danger';
	} else if (actionType === 'reservation_checkin') {
		iconClass = 'bi-box-arrow-in-right';
		iconColor = 'info';
	} else if (actionType === 'reservation_checkout') {
		iconClass = 'bi-box-arrow-right';
		iconColor = 'primary';
	} else if (actionType === 'price_modified' || actionType === 'payment_received') {
		iconClass = 'bi-currency-dollar';
		iconColor = 'warning';
	} else if (actionType === 'user_deleted' || actionType === 'data_deleted') {
		iconClass = 'bi-trash-fill';
		iconColor = 'danger';
	} else if (activity.event === 'created') {
		iconClass = 'bi-plus-circle-fill';
		iconColor = 'success';
	} else if (activity.event === 'deleted') {
		iconClass = 'bi-trash-fill';
		iconColor = 'danger';
	} else if (activity.event === 'updated') {
		iconClass = 'bi-pencil-fill';
		iconColor = 'warning';
	}
	
	// Badge de l'événement
	const isCritical = actionType && ['reservation_validated', 'reservation_rejected', 'reservation_checkin', 'reservation_checkout', 'price_modified', 'payment_received', 'user_deleted', 'data_deleted'].includes(actionType);
	const isSensitive = actionType && ['reservation_updated', 'reservation_pending', 'room_status_changed', 'user_created', 'user_updated', 'hotel_updated', 'settings_changed', 'data_exported', 'data_imported'].includes(actionType);
	
	let eventBadge = '';
	if (actionType) {
		const actionLabels = {
			'reservation_validated': 'Validation d\'enregistrement',
			'reservation_rejected': 'Rejet d\'enregistrement',
			'reservation_checkin': 'Check-in',
			'reservation_checkout': 'Check-out',
			'reservation_pending': 'Remise en attente',
			'reservation_updated': 'Modification d\'enregistrement',
			'room_status_changed': 'Changement statut chambre',
			'price_modified': 'Modification de prix',
			'payment_received': 'Paiement reçu',
			'user_created': 'Création utilisateur',
			'user_updated': 'Modification utilisateur',
			'user_deleted': 'Suppression utilisateur',
			'hotel_updated': 'Modification hôtel',
			'settings_changed': 'Changement de paramètres',
			'data_exported': 'Export de données',
			'data_imported': 'Import de données',
			'data_deleted': 'Suppression de données',
		};
		const actionLabel = actionLabels[actionType] || actionType;
		eventBadge = `<span class="badge bg-${isCritical ? 'danger' : (isSensitive ? 'warning' : 'info')}-soft text-${isCritical ? 'danger' : (isSensitive ? 'warning' : 'info')}"><i class="bi bi-${isCritical ? 'shield-exclamation' : (isSensitive ? 'shield-check' : 'info-circle')} me-1"></i>${escapeHtml(actionLabel)}</span>`;
	} else if (activity.event === 'created') {
		eventBadge = '<span class="badge bg-success-soft text-success"><i class="bi bi-plus-circle me-1"></i>Créé</span>';
	} else if (activity.event === 'updated') {
		eventBadge = '<span class="badge bg-warning-soft text-warning"><i class="bi bi-pencil me-1"></i>Modifié</span>';
	} else if (activity.event === 'deleted') {
		eventBadge = '<span class="badge bg-danger-soft text-danger"><i class="bi bi-trash me-1"></i>Supprimé</span>';
	} else {
		eventBadge = `<span class="badge bg-secondary-soft text-secondary">${escapeHtml(activity.event.charAt(0).toUpperCase() + activity.event.slice(1))}</span>`;
	}
	
	// Détails
	let detailsHtml = '';
	if (activity.causer) {
		detailsHtml += `<span class="detail-item"><i class="bi bi-person-circle me-1"></i><strong>${escapeHtml(activity.causer.name)}</strong></span>`;
		if (activity.causer.roles && activity.causer.roles.length > 0) {
			const rolesHtml = activity.causer.roles.map(role => `<span class="badge bg-info">${escapeHtml(role.charAt(0).toUpperCase() + role.slice(1))}</span>`).join(' ');
			detailsHtml += `<span class="detail-item"><i class="bi bi-person-badge me-1"></i>${rolesHtml}</span>`;
		}
	}
	if (activity.subject) {
		detailsHtml += `<span class="detail-item"><i class="bi bi-box me-1"></i>${escapeHtml(activity.subject.type)}: ${escapeHtml(activity.subject.name)}</span>`;
	}
	if (activity.hotel_name) {
		detailsHtml += `<span class="detail-item"><i class="bi bi-building me-1"></i>${escapeHtml(activity.hotel_name)}</span>`;
	}
	if (activity.ip_address) {
		detailsHtml += `<span class="detail-item"><i class="bi bi-geo-alt me-1"></i>${escapeHtml(activity.ip_address)}</span>`;
	}
	
	// Afficher les changements si disponibles
	let changesHtml = '';
	if (activity.properties) {
		if (activity.properties.changes) {
			changesHtml = '<div class="activity-changes mt-2 p-2 bg-light rounded" style="font-size: 0.8rem;"><strong class="text-muted">Détails des modifications :</strong>';
			for (const [field, value] of Object.entries(activity.properties.changes)) {
				const valueStr = typeof value === 'object' ? JSON.stringify(value) : value;
				changesHtml += `<div class="mt-1"><code class="text-primary">${escapeHtml(field)}</code>: <span class="text-success">${escapeHtml(valueStr)}</span></div>`;
			}
			changesHtml += '</div>';
		} else if (activity.properties.old_values && activity.properties.new_values) {
			changesHtml = '<div class="activity-changes mt-2 p-2 bg-light rounded" style="font-size: 0.8rem;"><strong class="text-muted">Détails des modifications :</strong>';
			for (const [field, oldValue] of Object.entries(activity.properties.old_values)) {
				if (activity.properties.new_values[field] && activity.properties.new_values[field] != oldValue) {
					const oldStr = typeof oldValue === 'object' ? JSON.stringify(oldValue) : oldValue;
					const newStr = typeof activity.properties.new_values[field] === 'object' ? JSON.stringify(activity.properties.new_values[field]) : activity.properties.new_values[field];
					changesHtml += `<div class="mt-1"><code class="text-primary">${escapeHtml(field)}</code>: <span class="text-danger">${escapeHtml(oldStr)}</span> → <span class="text-success">${escapeHtml(newStr)}</span></div>`;
				}
			}
			changesHtml += '</div>';
		}
	}
	
	// User agent
	let userAgentHtml = '';
	if (activity.user_agent) {
		const userAgentShort = activity.user_agent.length > 80 ? activity.user_agent.substring(0, 80) + '...' : activity.user_agent;
		userAgentHtml = `<div class="activity-user-agent mt-1" style="font-size: 0.75rem; color: #6c757d;"><i class="bi bi-laptop me-1"></i><small>${escapeHtml(userAgentShort)}</small></div>`;
	}
	
	div.innerHTML = `
		<div class="activity-icon-wrapper">
			<div class="activity-icon bg-${iconColor}-soft">
				<i class="bi ${iconClass} text-${iconColor}"></i>
			</div>
		</div>
		<div class="activity-content">
			<div class="activity-header">
				<h6 class="activity-title">${escapeHtml(activity.description)}</h6>
				<span class="activity-time">
					<i class="bi bi-clock me-1"></i>
					${activity.created_at_human}
				</span>
			</div>
			<div class="activity-details">
				${detailsHtml}
			</div>
			${changesHtml}
			${userAgentHtml}
			<div class="activity-badge mt-2">
				${eventBadge}
				<small class="text-muted ms-2">${activity.created_at_formatted}</small>
			</div>
		</div>
	`;
	
	return div;
}

function escapeHtml(text) {
	const div = document.createElement('div');
	div.textContent = text;
	return div.innerHTML;
}
</script>
