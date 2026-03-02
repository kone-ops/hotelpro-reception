<x-app-layout>
	<x-slot name="header">Gestion des formulaires</x-slot>
	
	<div class="d-flex justify-content-between align-items-center mb-4">
		<h4 class="mb-0">Champs de formulaires (Prédéfinis)</h4>
		<div class="alert alert-info mb-0">
			<i class="bi bi-info-circle me-2"></i>Champs selon cahier de charge
		</div>
	</div>

	<!-- Les notifications sont maintenant gérées globalement dans le layout -->

	<!-- Filtres -->
	<div class="card border-0 shadow-sm mb-4">
		<div class="card-body">
			<form method="GET" class="row g-3">
				<div class="col-md-4">
					<label class="form-label">Filtrer par hôtel</label>
					<select name="hotel" class="form-select">
						<option value="">Tous les hôtels</option>
						@foreach($hotels as $hotel)
							<option value="{{ $hotel->id }}" {{ request('hotel') == $hotel->id ? 'selected' : '' }}>
								{{ $hotel->name }}
							</option>
						@endforeach
					</select>
				</div>
				<div class="col-md-2">
					<label class="form-label">&nbsp;</label>
					<button type="submit" class="btn btn-outline-primary d-block">Filtrer</button>
				</div>
			</form>
		</div>
	</div>

	<div class="card border-0 shadow-sm">
		<div class="card-body p-0">
			@if($formFields->count() > 0)
				<div class="table-responsive">
					<table id="formsTable" class="table table-sm table-hover table-striped align-middle mb-0 super-admin-table" aria-label="Champs de formulaire prédéfinis par hôtel">
						<thead class="table-light">
							<tr>
								<th scope="col"><i class="bi bi-building me-1 text-primary"></i>Hôtel</th>
								<th scope="col"><i class="bi bi-tag me-1 text-primary"></i>Nom</th>
								<th scope="col"><i class="bi bi-card-text me-1 text-primary"></i>Label</th>
								<th scope="col"><i class="bi bi-input-cursor me-1 text-primary"></i>Type</th>
								<th scope="col"><i class="bi bi-asterisk me-1 text-primary"></i>Requis</th>
								<th scope="col"><i class="bi bi-sort-numeric-down me-1 text-primary"></i>Ordre</th>
								<th scope="col" class="text-end" width="120">Actions</th>
							</tr>
						</thead>
						<tbody>
							@foreach($formFields as $field)
								<tr>
									<td>
										<span class="badge bg-info">{{ $field->hotel->name }}</span>
									</td>
									<td><code>{{ $field->name }}</code></td>
									<td>{{ $field->label }}</td>
									<td>
										<span class="badge bg-{{ $field->type === 'email' ? 'primary' : ($field->type === 'date' ? 'success' : 'secondary') }}">
											{{ ucfirst($field->type) }}
										</span>
									</td>
									<td>
										@if($field->is_required)
											<span class="badge bg-danger">Oui</span>
										@else
											<span class="badge bg-secondary">Non</span>
										@endif
									</td>
									<td>{{ $field->order }}</td>
									<td>
										<span class="badge bg-light text-dark">Prédéfini</span>
									</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			@else
				<div class="card-body">
					<x-super.empty-table
						icon="bi-input-cursor-text"
						title="Aucun champ prédéfini"
						message="Lancez la commande pour initialiser les champs : php artisan fields:init"
					/>
				</div>
			@endif
		</div>
	</div>
</x-app-layout>

<script>
$(document).ready(function() {
	@if($formFields->count() > 0)
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
	
	const formsTableInstance = $('#formsTable').DataTable({
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
		pageLength: 25,
		responsive: true,
		order: [[5, 'asc']],
		columnDefs: [
			{ orderable: false, targets: [6] }
		],
		// Désactiver explicitement le traitement côté serveur et AJAX
		processing: false,
		serverSide: false
		// Pas d'option ajax - les données sont déjà dans le DOM (client-side)
	});
	
	// Protection contre les requêtes AJAX
	if (formsTableInstance && formsTableInstance.ajax) {
		formsTableInstance.ajax.reload = function(callback) {
			if (callback && typeof callback === 'function') callback();
			return formsTableInstance;
		};
	}
	@endif
});
</script>
