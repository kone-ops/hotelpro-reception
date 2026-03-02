<x-app-layout>
	<x-slot name="header">Enregistrements - {{ $hotel->name }}</x-slot>
	
	<!-- Les notifications sont maintenant gérées globalement dans le layout -->

	<!-- Filtres et statistiques -->
	<div class="row mb-4">
		<div class="col-md-2">
			<div class="card border-0 shadow-sm text-center">
				<div class="card-body">
					<i class="bi bi-clock text-warning" style="font-size: 2rem;"></i>
					<h4 class="mt-2">{{ $stats['pending'] }}</h4>
					<p class="text-muted mb-0">En attente</p>
					<a href="{{ route('reception.reservations.index', ['status' => 'pending']) }}" class="btn btn-sm btn-outline-warning mt-2">Filtrer</a>
				</div>
			</div>
		</div>
		<div class="col-md-2">
			<div class="card border-0 shadow-sm text-center">
				<div class="card-body">
					<i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
					<h4 class="mt-2">{{ $stats['validated'] }}</h4>
					<p class="text-muted mb-0">Validées</p>
					<a href="{{ route('reception.reservations.index', ['status' => 'validated']) }}" class="btn btn-sm btn-outline-success mt-2">Filtrer</a>
				</div>
			</div>
		</div>
		<div class="col-md-2">
			<div class="card border-0 shadow-sm text-center">
				<div class="card-body">
					<i class="bi bi-door-open text-info" style="font-size: 2rem;"></i>
					<h4 class="mt-2">{{ $stats['checked_in'] }}</h4>
					<p class="text-muted mb-0">En séjour</p>
					<a href="{{ route('reception.reservations.index', ['status' => 'checked_in']) }}" class="btn btn-sm btn-outline-info mt-2">Filtrer</a>
				</div>
			</div>
		</div>
		<div class="col-md-2">
			<div class="card border-0 shadow-sm text-center">
				<div class="card-body">
					<i class="bi bi-door-closed text-secondary" style="font-size: 2rem;"></i>
					<h4 class="mt-2">{{ $stats['checked_out'] }}</h4>
					<p class="text-muted mb-0">Parti</p>
					<a href="{{ route('reception.reservations.index', ['status' => 'checked_out']) }}" class="btn btn-sm btn-outline-secondary mt-2">Filtrer</a>
				</div>
			</div>
		</div>
		<div class="col-md-2">
			<div class="card border-0 shadow-sm text-center">
				<div class="card-body">
					<i class="bi bi-x-circle text-danger" style="font-size: 2rem;"></i>
					<h4 class="mt-2">{{ $stats['rejected'] }}</h4>
					<p class="text-muted mb-0">Rejetées</p>
					<a href="{{ route('reception.reservations.index', ['status' => 'rejected']) }}" class="btn btn-sm btn-outline-danger mt-2">Filtrer</a>
				</div>
			</div>
		</div>
		<div class="col-md-2">
			<div class="card border-0 shadow-sm text-center">
				<div class="card-body">
					<i class="bi bi-calendar-check text-info" style="font-size: 2rem;"></i>
					<h4 class="mt-2">{{ $stats['total'] }}</h4>
					<p class="text-muted mb-0">Total</p>
					<a href="{{ route('reception.reservations.index') }}" class="btn btn-sm btn-outline-info mt-2">Toutes</a>
				</div>
			</div>
		</div>
	</div>

	<div class="card border-0 shadow-sm">
		<div class="card-header bg-transparent">
			<h5 class="mb-0">Liste des enregistrements</h5>
		</div>
		<div class="card-body">
			@if($reservations->count() > 0)
				<div class="table-responsive">
					<table id="receptionReservationsTable" class="table table-sm table-hover table-striped align-middle mb-0 app-table" aria-label="Liste des enregistrements">
						<thead class="table-light">
							<tr>
								<th scope="col" class="text-black"><i class="bi bi-calendar-event me-1 text-muted"></i>Date</th>
								<th scope="col" class="text-black"><i class="bi bi-person me-1 text-muted"></i>Client</th>
								<th scope="col" class="text-black d-none d-lg-table-cell"><i class="bi bi-envelope me-1 text-muted"></i>Email</th>
								<th scope="col" class="text-black d-none d-md-table-cell"><i class="bi bi-telephone me-1 text-muted"></i>Téléphone</th>
								<th scope="col" class="text-black table-cell-state"><i class="bi bi-tag me-1 text-muted"></i>Statut</th>
								<th scope="col" class="text-black text-end table-actions-cell" style="width: 120px;"><i class="bi bi-gear me-1 text-muted"></i>Actions</th>
							</tr>
						</thead>
						<tbody>
							@foreach($reservations as $reservation)
								<tr class="text-black">
									<td class="text-black">{{ $reservation->created_at->format('d/m/Y H:i') }}</td>
									<td class="text-black">{{ $reservation->data['nom'] ?? 'N/A' }}</td>
									<td class="text-black d-none d-lg-table-cell">{{ $reservation->data['email'] ?? 'N/A' }}</td>
									<td class="text-black d-none d-md-table-cell">{{ $reservation->data['telephone'] ?? 'N/A' }}</td>
									<td class="text-black table-cell-state">
										@if($reservation->status === 'validated')
											<span class="badge bg-success text-black">Validée</span>
										@elseif($reservation->status === 'checked_in')
											<span class="badge bg-info text-black">En séjour</span>
										@elseif($reservation->status === 'checked_out')
											<span class="badge bg-secondary text-black">Parti</span>
										@elseif($reservation->status === 'rejected')
											<span class="badge bg-danger text-black">Rejetée</span>
										@else
											<span class="badge bg-warning text-black">En attente</span>
										@endif
									</td>
									<td class="text-end table-actions-cell">
										<div class="dropdown">
											<button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown" aria-label="Menu actions" title="Menu actions">
												<i class="bi bi-three-dots"></i>
											</button>
											<ul class="dropdown-menu">
												<li><a class="dropdown-item" href="{{ route('reception.reservations.show', $reservation->id) }}">Voir détails</a></li>
												@if($reservation->status === 'pending')
													<li>
														<form action="{{ route('reception.reservations.validate', $reservation->id) }}" method="POST" style="display: inline;">
															@csrf
															<button type="submit" class="dropdown-item text-success" onclick="return confirm('Valider ce pré-enregistrement ?')" style="border: none; background: none; width: 100%; text-align: left; cursor: pointer;">
																Valider
															</button>
														</form>
													</li>
													<li>
														<form action="{{ route('reception.reservations.reject', $reservation->id) }}" method="POST" style="display: inline;">
															@csrf
															<button type="submit" class="dropdown-item text-danger" onclick="return confirm('Rejeter ce pré-enregistrement ?')" style="border: none; background: none; width: 100%; text-align: left; cursor: pointer;">
																Rejeter
															</button>
														</form>
													</li>
												@elseif($reservation->status === 'validated')
													<li><a class="dropdown-item" href="{{ route('reception.police-sheet.preview', $reservation) }}">Aperçu feuille police</a></li>
													<li><a class="dropdown-item" href="{{ route('reception.police-sheet.generate', $reservation) }}" target="_blank">Imprimer feuille police</a></li>
												@endif
											</ul>
										</div>
									</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			@else
				<x-super.empty-table icon="bi-calendar-x" title="Aucun enregistrement" message="Les enregistrements de l'hôtel apparaîtront ici." />
			@endif
		</div>
	</div>
</x-app-layout>

<script>
// Wait for jQuery, DataTables and all dependencies to be loaded
function initDataTable() {
	// Vérifier que toutes les dépendances sont chargées
	if (typeof jQuery !== 'undefined' && typeof jQuery.fn.DataTable !== 'undefined') {
		
		@if($reservations->count() > 0)
		try {
			// Configuration de langue française inline pour éviter les erreurs de chargement
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
			
			// Vérifier si les boutons sont disponibles
			const hasButtons = typeof jQuery.fn.DataTable.Buttons !== 'undefined';
			const hasJSZip = typeof JSZip !== 'undefined' || typeof window.JSZip !== 'undefined';
			const hasPdfMake = typeof pdfMake !== 'undefined' || typeof window.pdfMake !== 'undefined';
			const canUseButtons = hasButtons && hasJSZip && hasPdfMake;
			
			const dtConfig = {
				language: frenchLanguage,
				order: [[0, 'desc']],
				pageLength: 25,
				responsive: true,
				columnDefs: [
					{ orderable: false, targets: [5] }
				],
				// Désactiver explicitement le traitement côté serveur
				processing: false,
				serverSide: false
				// Pas d'option ajax - les données sont déjà dans le DOM (client-side)
			};
			
			// Ajouter les boutons seulement si toutes les dépendances sont disponibles
			if (canUseButtons) {
				dtConfig.dom = '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
							 '<"row"<"col-sm-12"B>>' +
							 '<"row"<"col-sm-12"tr>>' +
							 '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>';
				dtConfig.buttons = [
					{
						extend: 'excel',
						text: '<i class="bi bi-file-earmark-excel me-1"></i> Excel',
						className: 'btn btn-success btn-sm',
						exportOptions: {
							columns: [0, 1, 2, 3, 4]
						}
					},
					{
						extend: 'pdf',
						text: '<i class="bi bi-file-earmark-pdf me-1"></i> PDF',
						className: 'btn btn-danger btn-sm',
						exportOptions: {
							columns: [0, 1, 2, 3, 4]
						}
					},
					{
						extend: 'print',
						text: '<i class="bi bi-printer me-1"></i> Imprimer',
						className: 'btn btn-info btn-sm',
						exportOptions: {
							columns: [0, 1, 2, 3, 4]
						}
					}
				];
			} else {
				dtConfig.dom = '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
							 '<"row"<"col-sm-12"tr>>' +
							 '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>';
			}
			
			jQuery('#receptionReservationsTable').DataTable(dtConfig);
			
		} catch (error) {
			console.error('Erreur lors de l\'initialisation de DataTable:', error);
			// Fallback : initialiser avec configuration minimale
			try {
				jQuery('#receptionReservationsTable').DataTable({
					order: [[0, 'desc']],
					pageLength: 25,
					responsive: true,
					columnDefs: [
						{ orderable: false, targets: [5] }
					],
					processing: false,
					serverSide: false
					// Pas d'option ajax - les données sont déjà dans le DOM (client-side)
				});
			} catch (fallbackError) {
				console.error('Erreur lors de l\'initialisation de fallback:', fallbackError);
			}
		}
		@endif
	} else {
		// Retry after a short delay if libraries aren't loaded yet
		setTimeout(initDataTable, 100);
	}
}

// Start initialization when DOM is ready and all scripts are loaded
function startInit() {
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function() {
			// Attendre que les scripts defer soient chargés
			setTimeout(initDataTable, 1000);
		});
	} else {
		// Attendre que les scripts defer soient chargés
		setTimeout(initDataTable, 1000);
	}
}

startInit();
</script>
