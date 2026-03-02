<x-app-layout>
	<x-slot name="header">Pré-enregistrements - {{ $hotel->name }}</x-slot>
	
	<!-- Les notifications sont maintenant gérées globalement dans le layout -->

	<!-- Statistiques -->
	<div class="row mb-4">
		<div class="col-md-2">
			<div class="card border-0 shadow-sm text-center">
				<div class="card-body">
					<i class="bi bi-calendar-check text-info" style="font-size: 2rem;"></i>
					<h4 class="mt-2">{{ $stats['total'] }}</h4>
					<p class="text-muted mb-0">Total</p>
					<a href="{{ route('hotel.reservations.index') }}" class="btn btn-sm btn-outline-info mt-2">Voir toutes</a>
				</div>
			</div>
		</div>
		<div class="col-md-2">
			<div class="card border-0 shadow-sm text-center">
				<div class="card-body">
					<i class="bi bi-clock text-warning" style="font-size: 2rem;"></i>
					<h4 class="mt-2">{{ $stats['pending'] }}</h4>
					<p class="text-muted mb-0">En attente</p>
					<a href="{{ route('hotel.reservations.index', ['status' => 'pending']) }}" class="btn btn-sm btn-outline-warning mt-2">Filtrer</a>
				</div>
			</div>
		</div>
		<div class="col-md-2">
			<div class="card border-0 shadow-sm text-center">
				<div class="card-body">
					<i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
					<h4 class="mt-2">{{ $stats['validated'] }}</h4>
					<p class="text-muted mb-0">Validées</p>
					<a href="{{ route('hotel.reservations.index', ['status' => 'validated']) }}" class="btn btn-sm btn-outline-success mt-2">Filtrer</a>
				</div>
			</div>
		</div>
		<div class="col-md-2">
			<div class="card border-0 shadow-sm text-center">
				<div class="card-body">
					<i class="bi bi-door-open text-info" style="font-size: 2rem;"></i>
					<h4 class="mt-2">{{ $stats['checked_in'] ?? 0 }}</h4>
					<p class="text-muted mb-0">En séjour</p>
					<a href="{{ route('hotel.reservations.index', ['status' => 'checked_in']) }}" class="btn btn-sm btn-outline-info mt-2">Filtrer</a>
				</div>
			</div>
		</div>
		<div class="col-md-2">
			<div class="card border-0 shadow-sm text-center">
				<div class="card-body">
					<i class="bi bi-door-closed text-secondary" style="font-size: 2rem;"></i>
					<h4 class="mt-2">{{ $stats['checked_out'] ?? 0 }}</h4>
					<p class="text-muted mb-0">Parti</p>
					<a href="{{ route('hotel.reservations.index', ['status' => 'checked_out']) }}" class="btn btn-sm btn-outline-secondary mt-2">Filtrer</a>
				</div>
			</div>
		</div>
		<div class="col-md-2">
			<div class="card border-0 shadow-sm text-center">
				<div class="card-body">
					<i class="bi bi-x-circle text-danger" style="font-size: 2rem;"></i>
					<h4 class="mt-2">{{ $stats['rejected'] }}</h4>
					<p class="text-muted mb-0">Rejetées</p>
					<a href="{{ route('hotel.reservations.index', ['status' => 'rejected']) }}" class="btn btn-sm btn-outline-danger mt-2">Filtrer</a>
				</div>
			</div>
		</div>
	</div>

	<div class="card border-0 shadow-sm">
		<div class="card-header bg-transparent d-flex justify-content-between align-items-center">
			<h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Liste des enregistrements</h5>
			<div>
				@if(request('status'))
					<a href="{{ route('hotel.reservations.index') }}" class="btn btn-sm btn-outline-secondary">
						<i class="bi bi-x-lg me-1"></i>Réinitialiser filtre
					</a>
				@endif
			</div>
		</div>
		<div class="card-body">
			@if($reservations->count() > 0)
				<div class="table-responsive">
					<table id="ReservationsTable" class="table table-sm table-hover table-striped align-middle mb-0 app-table" aria-label="Liste des enregistrements">
						<thead class="table-light">
							<tr>
								<th scope="col"><i class="bi bi-calendar-event me-1 text-muted"></i>Date</th>
								<th scope="col"><i class="bi bi-person me-1 text-muted"></i>Client</th>
								<th scope="col" class="d-none d-lg-table-cell"><i class="bi bi-envelope me-1 text-muted"></i>Email</th>
								<th scope="col" class="d-none d-md-table-cell"><i class="bi bi-telephone me-1 text-muted"></i>Téléphone</th>
								<th scope="col"><i class="bi bi-calendar-range me-1 text-muted"></i>Arrivée</th>
								<th scope="col" class="table-cell-state"><i class="bi bi-tag me-1 text-muted"></i>Statut</th>
								<th scope="col" class="text-end table-actions-cell" style="width: 150px;"><i class="bi bi-gear me-1 text-muted"></i>Actions</th>
							</tr>
						</thead>
						<tbody>
							@foreach($reservations as $reservation)
								<tr>
									<td>
										<small class="text-muted">{{ $reservation->created_at->format('d/m/Y') }}</small><br>
										<small class="text-muted">{{ $reservation->created_at->format('H:i') }}</small>
									</td>
									<td>
										<strong>{{ $reservation->data['nom'] ?? 'N/A' }} {{ $reservation->data['prenom'] ?? '' }}</strong>
									</td>
									<td>
										<i class="bi bi-envelope me-1 text-muted"></i>
										{{ $reservation->data['email'] ?? 'N/A' }}
									</td>
									<td>
										<i class="bi bi-telephone me-1 text-muted"></i>
										{{ $reservation->data['telephone'] ?? 'N/A' }}
									</td>
									<td>
										<i class="bi bi-calendar me-1 text-muted"></i>
										{{ $reservation->data['date_arrivee'] ?? 'N/A' }}
									</td>
									<td class="table-cell-state">
										@if($reservation->status === 'validated')
											<span class="badge bg-success">
												<i class="bi bi-check-lg me-1"></i>Validée
											</span>
										@elseif($reservation->status === 'checked_in')
											<span class="badge bg-info">
												<i class="bi bi-door-open me-1"></i>En séjour
											</span>
										@elseif($reservation->status === 'checked_out')
											<span class="badge bg-secondary">
												<i class="bi bi-door-closed me-1"></i>Parti
											</span>
										@elseif($reservation->status === 'rejected')
											<span class="badge bg-danger">
												<i class="bi bi-x-lg me-1"></i>Rejetée
											</span>
										@else
											<span class="badge bg-warning">
												<i class="bi bi-clock me-1"></i>En attente
											</span>
										@endif
									</td>
									<td class="text-end table-actions-cell">
										<div class="btn-group btn-group-sm">
											<a href="{{ route('hotel.reservations.show', $reservation) }}" class="btn btn-outline-primary" title="Voir détails">
												<i class="bi bi-eye"></i>
											</a>
											@if($reservation->status === 'pending')
												<form action="{{ route('hotel.reservations.validate', $reservation) }}" method="POST" class="d-inline">
													@csrf
													<button type="submit" class="btn btn-outline-success" title="Valider" onclick="return confirm('Valider ce pré-enregistrement ?')">
														<i class="bi bi-check-lg"></i>
													</button>
												</form>
												<form action="{{ route('hotel.reservations.reject', $reservation) }}" method="POST" class="d-inline">
													@csrf
													<button type="submit" class="btn btn-outline-danger" title="Rejeter" onclick="return confirm('Rejeter ce pré-enregistrement ?')">
														<i class="bi bi-x-lg"></i>
													</button>
												</form>
											@endif
										</div>
									</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>
				
				<!-- Pagination -->
				<div class="d-flex justify-content-center mt-3">
					{{ $reservations->links() }}
				</div>
			@else
				<x-super.empty-table
					icon="bi-calendar-x"
					title="Aucun enregistrement"
					:message="request('status') ? 'Aucun enregistrement avec le statut « ' . request('status') . ' ».' : 'Les enregistrements apparaîtront ici une fois que les clients auront rempli le formulaire.'"
				>
					<x-slot:action>
						<a href="{{ route('hotel.qr') }}" class="btn btn-primary">
							<i class="bi bi-qr-code me-2"></i>Voir le QR Code
						</a>
					</x-slot:action>
				</x-super.empty-table>
			@endif
		</div>
	</div>

	<script>
		$(document).ready(function() {
			@if($reservations->count() > 0)
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
			
			const reservationsTableInstance = $('#ReservationsTable').DataTable({
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
						exportOptions: {
							columns: [0, 1, 2, 3, 4, 5],
							orthogonal: 'export'
						}
					},
					{
						extend: 'pdf',
						text: '<i class="bi bi-file-earmark-pdf me-1"></i> PDF',
						className: 'btn btn-danger btn-sm',
						exportOptions: {
							columns: [0, 1, 2, 3, 4, 5],
							orthogonal: 'export'
						}
					},
					{
						extend: 'print',
						text: '<i class="bi bi-printer me-1"></i> Imprimer',
						className: 'btn btn-info btn-sm',
						exportOptions: {
							columns: [0, 1, 2, 3, 4, 5],
							orthogonal: 'export'
						}
					}
				],
				order: [[0, 'desc']],
				pageLength: 25,
				responsive: true,
				columnDefs: [
					{ orderable: false, targets: [6] }
				],
				// Désactiver explicitement le traitement côté serveur et AJAX
				processing: false,
				serverSide: false
				// Pas d'option ajax - les données sont déjà dans le DOM (client-side)
			});
			
			// Protection contre les requêtes AJAX
			if (reservationsTableInstance && reservationsTableInstance.ajax) {
				reservationsTableInstance.ajax.reload = function(callback) {
					if (callback && typeof callback === 'function') callback();
					return reservationsTableInstance;
				};
			}
			@endif
		});
	</script>
</x-app-layout>
