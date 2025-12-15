<x-app-layout>
	<x-slot name="header">Gestion des utilisateurs</x-slot>
	
	<div class="d-flex justify-content-between align-items-center mb-4">
		<h4 class="mb-0">Liste des utilisateurs ({{ $users->count() }})</h4>
		<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
			<i class="bi bi-person-plus me-2"></i>Nouvel utilisateur
		</button>
	</div>

	<!-- Les notifications sont maintenant gérées globalement dans le layout -->

	<!-- Filtres -->
	<div class="card border-0 shadow-sm mb-4">
		<div class="card-body">
			<form method="GET" class="row g-3">
				<div class="col-md-4">
					<label class="form-label">Filtrer par hôtel</label>
					<select name="hotel" class="form-select" onchange="this.form.submit()">
						<option value="">Tous les hôtels</option>
						@foreach($hotels as $hotel)
							<option value="{{ $hotel->id }}" {{ request('hotel') == $hotel->id ? 'selected' : '' }}>
								{{ $hotel->name }}
							</option>
						@endforeach
					</select>
				</div>
				<div class="col-md-4">
					<label class="form-label">Filtrer par rôle</label>
					<select name="role" class="form-select" onchange="this.form.submit()">
						<option value="">Tous les rôles</option>
						@foreach($roles as $role)
							<option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>
								{{ ucfirst(str_replace(['_', '-'], ' ', $role->name)) }}
							</option>
						@endforeach
					</select>
				</div>
				<div class="col-md-4">
					<label class="form-label">&nbsp;</label>
					<a href="{{ route('super.users.index') }}" class="btn btn-outline-secondary d-block">
						<i class="bi bi-arrow-clockwise me-2"></i>Réinitialiser
					</a>
				</div>
			</form>
		</div>
	</div>

	<div class="card border-0 shadow-sm">
		<div class="card-body">
			@if($users->count() > 0)
				<div class="table-responsive">
					<table id="usersTable" class="table table-hover align-middle">
						<thead>
							<tr>
								<th>Utilisateur</th>
								<th>Email</th>
								<th>Hôtel</th>
								<th>Rôle</th>
								<th width="150">Actions</th>
							</tr>
						</thead>
						<tbody>
							@foreach($users as $user)
								<tr>
									<td>
										<div class="d-flex align-items-center">
											<div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3">
												{{ substr($user->name, 0, 1) }}
											</div>
											<div>
												<strong>{{ $user->name }}</strong>
											</div>
										</div>
									</td>
									<td>
										<i class="bi bi-envelope me-1 text-muted"></i>
										<span title="{{ $user->email }}">
											{{ Str::limit($user->email, 20, '...') }}
										</span>
									</td>
									<td>
										@if($user->hotel)
											<span class="badge" style="background-color: {{ $user->hotel->primary_color ?? '#1a4b8c' }}">
												{{ $user->hotel->name }}
											</span>
										@else
											<span class="text-muted">-</span>
										@endif
									</td>
									<td>
										@php
											$role = $user->roles->first();
										@endphp
										@if($role)
											<span class="badge bg-{{ $role->name === 'super-admin' ? 'danger' : ($role->name === 'hotel-admin' ? 'warning' : 'success') }}">
												{{ ucfirst(str_replace(['_', '-'], ' ', $role->name)) }}
											</span>
										@endif
									</td>
									<td>
										<div class="btn-group btn-group-sm">
											<button class="btn btn-outline-info" onclick="viewUserDetails({{ $user->id }})" title="Voir détails">
												<i class="bi bi-eye"></i>
											</button>
											<button class="btn btn-outline-primary" onclick="editUser({{ $user->id }})" title="Modifier">
												<i class="bi bi-pencil"></i>
											</button>
											<button class="btn btn-outline-danger" onclick="deleteUser({{ $user->id }}, '{{ $user->name }}')" title="Supprimer">
												<i class="bi bi-trash"></i>
											</button>
										</div>
									</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			@else
				<div class="text-center py-5">
					<i class="bi bi-people text-muted" style="font-size: 4rem;"></i>
					<h5 class="text-muted mt-3">Aucun utilisateur</h5>
					<p class="text-muted">Aucun utilisateur ne correspond aux filtres appliqués.</p>
				</div>
			@endif
		</div>
	</div>
</x-app-layout>

<!-- Modal Création Utilisateur -->
<div class="modal fade" id="createUserModal" tabindex="-1">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Créer un nouvel utilisateur</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
			</div>
			<form action="{{ route('super.users.store') }}" method="POST">
				@csrf
				<div class="modal-body">
					<div class="row">
						<div class="col-md-6 mb-3">
							<label class="form-label">Nom complet *</label>
							<input type="text" name="name" class="form-control" required>
						</div>
						<div class="col-md-6 mb-3">
							<label class="form-label">Email *</label>
							<input type="email" name="email" class="form-control" required>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6 mb-3">
							<label class="form-label">Mot de passe *</label>
							<input type="password" name="password" class="form-control" required minlength="8">
							<small class="text-muted">Minimum 8 caractères</small>
						</div>
						<div class="col-md-6 mb-3">
							<label class="form-label">Rôle *</label>
							<select name="role" id="create_role" class="form-select" required onchange="toggleHotelField('create')">
								<option value="">Sélectionner un rôle</option>
								@foreach($roles as $role)
									<option value="{{ $role->name }}">{{ ucfirst(str_replace(['_', '-'], ' ', $role->name)) }}</option>
								@endforeach
							</select>
						</div>
					</div>
						<div class="row">
						<div class="col-md-12 mb-3" id="create_hotel_field">
							<label class="form-label">Hôtel *</label>
							<select name="hotel_id" id="create_hotel_id" class="form-select">
								<option value="">Sélectionner un hôtel</option>
								@foreach($hotels as $hotel)
									<option value="{{ $hotel->id }}">{{ $hotel->name }}</option>
							@endforeach
							</select>
							<small class="text-muted">Obligatoire pour les rôles Hotel-Admin et Receptionist</small>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
					<button type="submit" class="btn btn-primary">
						<i class="bi bi-check-lg me-2"></i>Créer l'utilisateur
					</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- Modal Modification Utilisateur -->
<div class="modal fade" id="editUserModal" tabindex="-1">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Modifier l'utilisateur</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
			</div>
			<form id="editUserForm" method="POST">
				@csrf
				@method('PUT')
				<div class="modal-body">
					<div class="row">
						<div class="col-md-6 mb-3">
							<label class="form-label">Nom complet *</label>
							<input type="text" name="name" id="edit_name" class="form-control" required>
						</div>
						<div class="col-md-6 mb-3">
							<label class="form-label">Email</label>
							<input type="email" id="edit_email" class="form-control" disabled readonly>
							<small class="text-muted">L'email ne peut pas être modifié</small>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6 mb-3">
							<label class="form-label">Nouveau mot de passe</label>
							<input type="password" name="password" class="form-control" minlength="8">
							<small class="text-muted">Laisser vide pour ne pas changer</small>
						</div>
						<div class="col-md-6 mb-3">
							<label class="form-label">Rôle *</label>
							<select name="role" id="edit_role" class="form-select" required onchange="toggleHotelField('edit')">
								<option value="">Sélectionner un rôle</option>
								@foreach($roles as $role)
									<option value="{{ $role->name }}">{{ ucfirst(str_replace(['_', '-'], ' ', $role->name)) }}</option>
								@endforeach
							</select>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12 mb-3" id="edit_hotel_field">
							<label class="form-label">Hôtel *</label>
							<select name="hotel_id" id="edit_hotel_id" class="form-select">
								<option value="">Sélectionner un hôtel</option>
								@foreach($hotels as $hotel)
									<option value="{{ $hotel->id }}">{{ $hotel->name }}</option>
								@endforeach
							</select>
							<small class="text-muted">Obligatoire pour les rôles Hotel-Admin et Receptionist</small>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
					<button type="submit" class="btn btn-primary">
						<i class="bi bi-check-lg me-2"></i>Enregistrer
					</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- Modal Détails Utilisateur -->
<div class="modal fade" id="viewUserModal" tabindex="-1">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header bg-info text-white">
				<h5 class="modal-title"><i class="bi bi-person-circle me-2"></i>Détails de l'utilisateur</h5>
				<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
			</div>
			<div class="modal-body">
				<div class="row mb-4">
					<div class="col-md-12 text-center mb-3">
						<div class="avatar-lg bg-info text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 2rem;">
							<span id="view_avatar"></span>
						</div>
					</div>
				</div>
				
				<div class="row">
					<div class="col-md-6 mb-3">
						<label class="form-label fw-bold text-muted small">NOM COMPLET</label>
						<p class="form-control-plaintext border-bottom" id="view_name">-</p>
					</div>
					<div class="col-md-6 mb-3">
						<label class="form-label fw-bold text-muted small">EMAIL</label>
						<p class="form-control-plaintext border-bottom" id="view_email">-</p>
					</div>
				</div>
				
				<div class="row">
					<div class="col-md-6 mb-3">
						<label class="form-label fw-bold text-muted small">RÔLE</label>
						<p class="form-control-plaintext border-bottom">
							<span id="view_role" class="badge">-</span>
						</p>
					</div>
					<div class="col-md-6 mb-3">
						<label class="form-label fw-bold text-muted small">HÔTEL</label>
						<p class="form-control-plaintext border-bottom">
							<span id="view_hotel">-</span>
						</p>
					</div>
				</div>
				
				<div class="row">
					<div class="col-md-6 mb-3">
						<label class="form-label fw-bold text-muted small">DATE DE CRÉATION</label>
						<p class="form-control-plaintext border-bottom" id="view_created">-</p>
					</div>
					<div class="col-md-6 mb-3">
						<label class="form-label fw-bold text-muted small">DERNIÈRE MODIFICATION</label>
						<p class="form-control-plaintext border-bottom" id="view_updated">-</p>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
				<button type="button" class="btn btn-primary" onclick="closeViewAndEdit()">
					<i class="bi bi-pencil me-1"></i>Modifier cet utilisateur
				</button>
			</div>
		</div>
	</div>
</div>

<script>
function toggleHotelField(mode) {
	const role = document.getElementById(mode + '_role').value;
	const hotelField = document.getElementById(mode + '_hotel_field');
	const hotelSelect = document.getElementById(mode + '_hotel_id');
	
	if (role === 'super-admin') {
		hotelField.style.display = 'none';
		hotelSelect.required = false;
		hotelSelect.value = '';
	} else {
		hotelField.style.display = 'block';
		hotelSelect.required = true;
	}
}

// Variable globale pour stocker l'ID de l'utilisateur en cours
let currentUserId = null;

function viewUserDetails(userId) {
	currentUserId = userId;
	
	fetch(`/super/users/${userId}`, {
		headers: {
			'X-Requested-With': 'XMLHttpRequest',
			'Accept': 'application/json',
			'Content-Type': 'application/json'
		}
	})
		.then(response => {
			if (!response.ok) {
				throw new Error(`Erreur ${response.status}: ${response.statusText}`);
			}
			return response.json();
		})
		.then(user => {
			// Avatar
			document.getElementById('view_avatar').textContent = user.name.charAt(0).toUpperCase();
			
			// Informations de base
			document.getElementById('view_name').textContent = user.name || '-';
			
			// Email avec troncature à 36 caractères
			const emailElement = document.getElementById('view_email');
			const userEmail = user.email || '-';
			if (userEmail.length > 36) {
				emailElement.textContent = userEmail.substring(0, 36) + '...';
				emailElement.title = userEmail; // Afficher l'email complet au survol
			} else {
				emailElement.textContent = userEmail;
				emailElement.title = userEmail;
			}
			
			// Rôle avec badge coloré
			const roleElement = document.getElementById('view_role');
			if (user.roles && user.roles.length > 0) {
				const roleName = user.roles[0].name;
				const roleLabel = roleName.replace(/[-_]/g, ' ').split(' ').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
				const roleClass = roleName === 'super-admin' ? 'bg-danger' : (roleName === 'hotel-admin' ? 'bg-warning' : 'bg-success');
				roleElement.textContent = roleLabel;
				roleElement.className = `badge ${roleClass}`;
			} else {
				roleElement.textContent = '-';
				roleElement.className = 'badge bg-secondary';
			}
			
			// Hôtel avec badge coloré
			const hotelElement = document.getElementById('view_hotel');
			if (user.hotel) {
				const hotelBadge = `<span class="badge" style="background-color: ${user.hotel.primary_color || '#1a4b8c'}">${user.hotel.name}</span>`;
				hotelElement.innerHTML = hotelBadge;
			} else {
				hotelElement.innerHTML = '<span class="text-muted">Aucun hôtel assigné</span>';
			}
			
			// Dates
			document.getElementById('view_created').textContent = new Date(user.created_at).toLocaleDateString('fr-FR', {
				year: 'numeric',
				month: 'long',
				day: 'numeric',
				hour: '2-digit',
				minute: '2-digit'
			});
			document.getElementById('view_updated').textContent = new Date(user.updated_at).toLocaleDateString('fr-FR', {
				year: 'numeric',
				month: 'long',
				day: 'numeric',
				hour: '2-digit',
				minute: '2-digit'
			});
			
			// Ouvrir le modal
			const modal = new bootstrap.Modal(document.getElementById('viewUserModal'));
			modal.show();
		})
		.catch(error => {
			console.error('Erreur lors du chargement des détails:', error);
			alert('Erreur: ' + error.message);
		});
}

function closeViewAndEdit() {
	// Fermer le modal de détails
	const viewModal = bootstrap.Modal.getInstance(document.getElementById('viewUserModal'));
	viewModal.hide();
	
	// Attendre que le modal soit fermé avant d'ouvrir celui de modification
	setTimeout(() => {
		if (currentUserId) {
			editUser(currentUserId);
		}
	}, 300);
}

function editUser(userId) {
	currentUserId = userId;
	
	fetch(`/super/users/${userId}`, {
		headers: {
			'X-Requested-With': 'XMLHttpRequest',
			'Accept': 'application/json',
			'Content-Type': 'application/json'
		}
	})
		.then(response => {
			if (!response.ok) {
				throw new Error(`Erreur ${response.status}: ${response.statusText}`);
			}
			return response.json();
		})
		.then(user => {
			console.log('Données utilisateur chargées:', user);
			
			// Remplir le formulaire
			document.getElementById('edit_name').value = user.name || '';
			document.getElementById('edit_email').value = user.email || '';
			
			// Définir le rôle (premier rôle seulement)
			if (user.roles && user.roles.length > 0) {
				document.getElementById('edit_role').value = user.roles[0].name;
			} else {
				document.getElementById('edit_role').value = '';
			}
			
			// Définir l'hôtel et afficher/masquer le champ
			document.getElementById('edit_hotel_id').value = user.hotel_id || '';
			toggleHotelField('edit');
			
			// Définir l'action du formulaire
			document.getElementById('editUserForm').action = `/super/users/${userId}`;
			
			// Ouvrir le modal
			const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
			modal.show();
		})
		.catch(error => {
			console.error('Erreur lors du chargement de l\'utilisateur:', error);
			alert('Erreur: ' + error.message);
		});
}

function deleteUser(userId, userName) {
	if (confirm(`Êtes-vous sûr de vouloir supprimer l'utilisateur "${userName}" ?\n\nCette action est irréversible.`)) {
		const form = document.createElement('form');
		form.method = 'POST';
		form.action = `/super/users/${userId}`;
		form.innerHTML = `
			@csrf
			@method('DELETE')
		`;
		document.body.appendChild(form);
		form.submit();
	}
}

// Initialiser l'affichage au chargement
document.addEventListener('DOMContentLoaded', function() {
	toggleHotelField('create');
});
</script>

<style>
.avatar-sm {
	width: 40px;
	height: 40px;
	font-size: 16px;
}

.avatar-lg {
	width: 80px;
	height: 80px;
	font-size: 2rem;
}

#viewUserModal .form-control-plaintext {
	padding: 0.5rem 0;
	margin-bottom: 0;
	font-size: 1rem;
	color: #212529;
}

#view_email {
	max-width: 100%;
	overflow: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
	cursor: help;
}

#viewUserModal .border-bottom {
	border-bottom: 1px solid #dee2e6 !important;
}

#viewUserModal .modal-body label {
	text-transform: uppercase;
	font-size: 0.75rem;
	letter-spacing: 0.5px;
	margin-bottom: 0.25rem;
}
</style>

<script>
$(document).ready(function() {
	@if($users->count() > 0)
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
	
	// Initialiser DataTable sans aucune option AJAX - utilise uniquement les données DOM
	const usersTableInstance = $('#usersTable').DataTable({
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
					columns: [0, 1, 2, 3],
					orthogonal: 'export' // Utiliser les données locales uniquement
				}
			},
			{
				extend: 'pdf',
				text: '<i class="bi bi-file-earmark-pdf me-1"></i> PDF',
				className: 'btn btn-danger btn-sm',
				exportOptions: {
					columns: [0, 1, 2, 3],
					orthogonal: 'export' // Utiliser les données locales uniquement
				}
			},
			{
				extend: 'print',
				text: '<i class="bi bi-printer me-1"></i> Imprimer',
				className: 'btn btn-info btn-sm',
				exportOptions: {
					columns: [0, 1, 2, 3],
					orthogonal: 'export' // Utiliser les données locales uniquement
				}
			}
		],
		pageLength: 25,
		responsive: true,
		order: [[0, 'asc']],
		columnDefs: [
			{ orderable: false, targets: [4] }
		],
		// Désactiver explicitement le traitement côté serveur
		processing: false,
		serverSide: false
		// Pas d'option ajax - DataTables utilise les données déjà présentes dans le DOM
	});
	
	// Protection complète contre les requêtes AJAX
	if (usersTableInstance) {
		// Surcharger ajax.reload() pour empêcher toute tentative
		if (usersTableInstance.ajax) {
			const originalReload = usersTableInstance.ajax.reload;
			usersTableInstance.ajax.reload = function(callback, resetPaging) {
				console.debug('Tentative de ajax.reload() bloquée - usersTable utilise des données DOM uniquement');
				if (callback && typeof callback === 'function') callback();
				return usersTableInstance;
			};
		}
		
		// S'assurer que l'objet ajax n'a pas d'URL
		if (usersTableInstance.ajax && usersTableInstance.ajax.url) {
			delete usersTableInstance.ajax.url;
		}
	}
	@endif
});
</script>
