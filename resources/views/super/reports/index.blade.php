<x-app-layout>
	<x-slot name="header">Rapports et statistiques</x-slot>
	
	<!-- Statistiques globales -->
	<div class="row mb-4">
		<div class="col-md-2">
			<div class="card border-0 shadow-sm text-center">
				<div class="card-body">
					<i class="bi bi-building text-primary" style="font-size: 2rem;"></i>
					<h4 class="mt-2">{{ $global_stats['total_hotels'] }}</h4>
					<p class="text-muted mb-0">Hôtels</p>
				</div>
			</div>
		</div>
		<div class="col-md-2">
			<div class="card border-0 shadow-sm text-center">
				<div class="card-body">
					<i class="bi bi-people text-success" style="font-size: 2rem;"></i>
					<h4 class="mt-2">{{ $global_stats['total_users'] }}</h4>
					<p class="text-muted mb-0">Utilisateurs</p>
				</div>
			</div>
		</div>
		<div class="col-md-2">
			<div class="card border-0 shadow-sm text-center">
				<div class="card-body">
					<i class="bi bi-calendar-check text-info" style="font-size: 2rem;"></i>
					<h4 class="mt-2">{{ $global_stats['total_reservations'] }}</h4>
					<p class="text-muted mb-0">Total</p>
				</div>
			</div>
		</div>
		<div class="col-md-2">
			<div class="card border-0 shadow-sm text-center">
				<div class="card-body">
					<i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
					<h4 class="mt-2">{{ $global_stats['validated_reservations'] }}</h4>
					<p class="text-muted mb-0">Validées</p>
				</div>
			</div>
		</div>
		<div class="col-md-2">
			<div class="card border-0 shadow-sm text-center">
				<div class="card-body">
					<i class="bi bi-clock text-warning" style="font-size: 2rem;"></i>
					<h4 class="mt-2">{{ $global_stats['pending_reservations'] }}</h4>
					<p class="text-muted mb-0">En attente</p>
				</div>
			</div>
		</div>
		<div class="col-md-2">
			<div class="card border-0 shadow-sm text-center">
				<div class="card-body">
					<i class="bi bi-percent text-danger" style="font-size: 2rem;"></i>
					<h4 class="mt-2">{{ $global_stats['total_reservations'] > 0 ? round(($global_stats['validated_reservations'] / $global_stats['total_reservations']) * 100, 1) : 0 }}%</h4>
					<p class="text-muted mb-0">Taux validation</p>
				</div>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-md-8">
			<!-- Statistiques par hôtel -->
			<div class="card border-0 shadow-sm">
				<div class="card-header bg-transparent">
					<h5 class="mb-0">Statistiques par hôtel</h5>
				</div>
				<div class="card-body">
					@if($hotels_stats->count() > 0)
						<div class="table-responsive">
							<table id="hotelsStatsTable" class="table table-hover">
								<thead>
									<tr>
										<th>Hôtel</th>
										<th>Utilisateurs</th>
										<th>Total réservations</th>
										<th>Validées</th>
										<th>En attente</th>
										<th>Taux</th>
									</tr>
								</thead>
								<tbody>
									@foreach($hotels_stats as $hotel)
										<tr>
											<td>
												<strong>{{ $hotel->name }}</strong>
											</td>
											<td>
												<span class="badge bg-info">{{ $hotel->users_count }}</span>
											</td>
											<td>
												<span class="badge bg-primary">{{ $hotel->reservations_count }}</span>
											</td>
											<td>
												<span class="badge bg-success">{{ $hotel->validated_count }}</span>
											</td>
											<td>
												<span class="badge bg-warning">{{ $hotel->pending_count }}</span>
											</td>
											<td>
												@if($hotel->reservations_count > 0)
													{{ round(($hotel->validated_count / $hotel->reservations_count) * 100, 1) }}%
												@else
													-
												@endif
											</td>
										</tr>
									@endforeach
								</tbody>
							</table>
						</div>
					@else
						<div class="text-center py-4">
							<i class="bi bi-building text-muted" style="font-size: 3rem;"></i>
							<h5 class="text-muted mt-3">Aucun hôtel</h5>
							<p class="text-muted">Créez votre premier hôtel pour voir les statistiques.</p>
						</div>
					@endif
				</div>
			</div>
		</div>
		
		<div class="col-md-4">
			<!-- Actions rapides -->
			<div class="card border-0 shadow-sm mb-4">
				<div class="card-header bg-transparent">
					<h6 class="mb-0">Actions rapides</h6>
				</div>
				<div class="card-body">
					<div class="d-grid gap-2">
						<a href="{{ route('super.activity') }}" class="btn btn-outline-primary">
							<i class="bi bi-activity me-2"></i>Voir l'activité
						</a>
						<a href="{{ route('super.hotels.index') }}" class="btn btn-outline-secondary">
							<i class="bi bi-building me-2"></i>Gérer les hôtels
						</a>
						<a href="{{ route('super.users.index') }}" class="btn btn-outline-info">
							<i class="bi bi-people me-2"></i>Gérer les utilisateurs
						</a>
					</div>
				</div>
			</div>
			
			<!-- Résumé -->
			<div class="card border-0 shadow-sm">
				<div class="card-header bg-transparent">
					<h6 class="mb-0">Résumé</h6>
				</div>
				<div class="card-body">
					<div class="mb-3">
						<strong>Performance globale:</strong><br>
						@if($global_stats['total_reservations'] > 0)
							<div class="progress mt-2">
								<div class="progress-bar" role="progressbar" style="width: {{ ($global_stats['validated_reservations'] / $global_stats['total_reservations']) * 100 }}%">
									{{ round(($global_stats['validated_reservations'] / $global_stats['total_reservations']) * 100, 1) }}%
								</div>
							</div>
						@else
							<span class="text-muted">Aucune donnée</span>
						@endif
					</div>
					<div class="mb-3">
						<strong>Hôtels actifs:</strong><br>
						<span class="text-muted">{{ $global_stats['total_hotels'] }} hôtels configurés</span>
					</div>
					<div class="mb-3">
						<strong>Utilisateurs actifs:</strong><br>
						<span class="text-muted">{{ $global_stats['total_users'] }} utilisateurs</span>
					</div>
				</div>
			</div>
		</div>
	</div>
</x-app-layout>

<script>
$(document).ready(function() {
	@if($hotels_stats->count() > 0)
	// Configuration de langue française inline pour éviter les erreurs CSP
	const frenchLanguage = {
		"decimal": ",",
		"emptyTable": "Aucune donnée disponible dans le tableau",
		"info": "Affichage de _START_ à _END_ sur _TOTAL_ entrées",
		"infoEmpty": "Affichage de 0 à 0 sur 0 entrées",
		"infoFiltered": "(filtré à partir de _MAX_ entrées au total)",
		"infoPostFix": "",
		"thousands": " ",
		"lengthMenu": "Afficher _MENU_ entrées",
		"loadingRecords": "Chargement...",
		"processing": "Traitement en cours...",
		"search": "Rechercher:",
		"zeroRecords": "Aucun enregistrement correspondant trouvé",
		"paginate": {
			"first": "Premier",
			"last": "Dernier",
			"next": "Suivant",
			"previous": "Précédent"
		},
		"aria": {
			"sortAscending": ": activer pour trier la colonne par ordre croissant",
			"sortDescending": ": activer pour trier la colonne par ordre décroissant"
		}
	};
	
	const hotelsStatsTableInstance = $('#hotelsStatsTable').DataTable({
		language: frenchLanguage,
		dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
			 '<"row"<"col-sm-12"B>>' +
			 '<"row"<"col-sm-12"tr>>' +
			 '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
		buttons: [
			{
				extend: 'excel',
				text: '<i class="bi bi-file-earmark-excel me-1"></i> Excel',
				className: 'btn btn-success btn-sm',
				title: 'Statistiques par hôtel',
				exportOptions: {
					orthogonal: 'export'
				}
			},
			{
				extend: 'pdf',
				text: '<i class="bi bi-file-earmark-pdf me-1"></i> PDF',
				className: 'btn btn-danger btn-sm',
				title: 'Statistiques par hôtel',
				exportOptions: {
					orthogonal: 'export'
				}
			},
			{
				extend: 'print',
				text: '<i class="bi bi-printer me-1"></i> Imprimer',
				className: 'btn btn-info btn-sm',
				title: 'Statistiques par hôtel',
				exportOptions: {
					orthogonal: 'export'
				}
			}
		],
		pageLength: 10,
		responsive: true,
		order: [[2, 'desc']],
		// Désactiver explicitement le traitement côté serveur et AJAX
		processing: false,
		serverSide: false
		// Pas d'option ajax - les données sont déjà dans le DOM (client-side)
	});
	
	// Protection contre les requêtes AJAX
	if (hotelsStatsTableInstance && hotelsStatsTableInstance.ajax) {
		hotelsStatsTableInstance.ajax.reload = function(callback) {
			if (callback && typeof callback === 'function') callback();
			return hotelsStatsTableInstance;
		};
	}
	@endif
});
</script>
