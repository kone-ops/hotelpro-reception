<x-app-layout>
	<x-slot name="header">Gestion des utilisateurs</x-slot>
	
	<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
		<h4 class="mb-0 h5">Liste des utilisateurs ({{ $users->count() }})</h4>
		<div class="d-flex gap-2">
			<button class="btn btn-outline-secondary" id="selectAllUsersBtn" onclick="toggleSelectAllUsers()">
				<i class="bi bi-check-square me-2"></i><span id="selectAllUsersText">Tout sélectionner</span>
			</button>
			<button class="btn btn-danger" id="deleteMultipleUsersBtn" style="display: none;">
				<i class="bi bi-trash me-2"></i>Supprimer sélectionnés
			</button>
			<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
				<i class="bi bi-person-plus me-2"></i>Nouvel utilisateur
			</button>
		</div>
	</div>

	<!-- Les notifications sont maintenant gérées globalement dans le layout -->

	<!-- Filtres -->
	<div class="card border-0 shadow-sm mb-3">
		<div class="card-body py-2">
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
				@php
					$roleLabels = [
						'super-admin' => 'Super Admin',
						'hotel-admin' => 'Gérant d\'hôtel',
						'receptionist' => 'Réceptionniste',
						'housekeeping' => 'Service des étages',
						'laundry' => 'Buanderie',
						'maintenance' => 'Service technique',
					];
				@endphp
				<div class="col-md-4">
					<label class="form-label">Filtrer par rôle</label>
					<select name="role" class="form-select" onchange="this.form.submit()">
						<option value="">Tous les rôles</option>
						@foreach($roles as $role)
							<option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>
								{{ $roleLabels[$role->name] ?? ucfirst(str_replace(['_', '-'], ' ', $role->name)) }}
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
		<div class="card-body p-0">
			@if($users->count() > 0)
				<div class="table-responsive">
					<table id="usersTable" class="table table-sm table-hover table-striped align-middle mb-0 super-admin-table table-compact" aria-label="Liste des utilisateurs avec filtre par hôtel et rôle">
						<thead class="table-light">
							<tr>
								<th scope="col" width="50" class="ps-3">
									<label class="visually-hidden" for="selectAllUsersCheckbox">Tout sélectionner</label>
									<input type="checkbox" class="form-check-input" id="selectAllUsersCheckbox" onchange="toggleSelectAllUsers()" aria-label="Tout sélectionner">
								</th>
								<th scope="col"><i class="bi bi-person me-1 text-primary"></i>Utilisateur</th>
								<th scope="col"><i class="bi bi-envelope me-1 text-primary"></i>Email</th>
								<th scope="col"><i class="bi bi-building me-1 text-primary"></i>Hôtel</th>
								<th scope="col"><i class="bi bi-person-badge me-1 text-primary"></i>Rôle</th>
								<th scope="col" width="140" class="text-end pe-3 table-actions-cell"><i class="bi bi-gear me-1 text-muted"></i>Actions</th>
							</tr>
						</thead>
						<tbody>
							@foreach($users as $user)
								<tr>
									<td class="ps-3">
										<label class="visually-hidden" for="user-{{ $user->id }}">Sélectionner {{ $user->name }}</label>
										<input type="checkbox" class="form-check-input user-checkbox" value="{{ $user->id }}" id="user-{{ $user->id }}" aria-label="Sélectionner {{ $user->name }}">
									</td>
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
											<span class="badge bg-{{ $role->name === 'super-admin' ? 'danger' : ($role->name === 'hotel-admin' ? 'warning' : ($role->name === 'laundry' ? 'primary' : 'success')) }}">
												{{ $roleLabels[$role->name] ?? ucfirst(str_replace(['_', '-'], ' ', $role->name)) }}
											</span>
										@endif
									</td>
									<td class="text-end pe-3 table-actions-cell">
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
				<div class="card-body">
					<x-super.empty-table
						icon="bi-people"
						title="Aucun utilisateur"
						message="Aucun utilisateur ne correspond aux filtres appliqués."
					>
						<x-slot name="action">
							<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
								<i class="bi bi-person-plus me-1"></i>Nouvel utilisateur
							</button>
						</x-slot>
					</x-super.empty-table>
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
									<option value="{{ $role->name }}">{{ $roleLabels[$role->name] ?? ucfirst(str_replace(['_', '-'], ' ', $role->name)) }}</option>
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
							<small class="text-muted">Obligatoire pour tous les rôles sauf Super Admin</small>
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
									<option value="{{ $role->name }}">{{ $roleLabels[$role->name] ?? ucfirst(str_replace(['_', '-'], ' ', $role->name)) }}</option>
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
							<small class="text-muted">Obligatoire pour tous les rôles sauf Super Admin</small>
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
			
			// Rôle avec badge coloré (libellés français)
			const roleLabelsJs = {
				'super-admin': 'Super Admin',
				'hotel-admin': 'Gérant d\'hôtel',
				'receptionist': 'Réceptionniste',
				'housekeeping': 'Service des étages',
				'laundry': 'Buanderie'
			};
			const roleElement = document.getElementById('view_role');
			if (user.roles && user.roles.length > 0) {
				const roleName = user.roles[0].name;
				const roleLabel = roleLabelsJs[roleName] || roleName.replace(/[-_]/g, ' ').split(' ').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
				const roleClass = roleName === 'super-admin' ? 'bg-danger' : (roleName === 'hotel-admin' ? 'bg-warning' : (roleName === 'laundry' ? 'bg-primary' : 'bg-success'));
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
			const modalEl = document.getElementById('viewUserModal');
			if (document.activeElement && document.activeElement.blur) document.activeElement.blur();
			modalEl.addEventListener('shown.bs.modal', function onShown() {
				modalEl.removeEventListener('shown.bs.modal', onShown);
				const t = modalEl.querySelector('button[data-bs-dismiss="modal"]') || modalEl.querySelector('.btn') || modalEl;
				if (t && typeof t.focus === 'function') t.focus();
			});
			const modal = new bootstrap.Modal(modalEl);
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
			const modalEl = document.getElementById('editUserModal');
			if (document.activeElement && document.activeElement.blur) document.activeElement.blur();
			modalEl.addEventListener('shown.bs.modal', function onShown() {
				modalEl.removeEventListener('shown.bs.modal', onShown);
				const t = modalEl.querySelector('button[data-bs-dismiss="modal"]') || modalEl.querySelector('.btn-primary') || modalEl;
				if (t && typeof t.focus === 'function') t.focus();
			});
			const modal = new bootstrap.Modal(modalEl);
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
					columns: [1, 2, 3, 4],
					orthogonal: 'export' // Utiliser les données locales uniquement
				}
			},
			{
				extend: 'print',
				text: '<i class="bi bi-printer me-1"></i> Imprimer',
				className: 'btn btn-info btn-sm',
				exportOptions: {
					columns: [1, 2, 3, 4],
					orthogonal: 'export' // Utiliser les données locales uniquement
				}
			}
		],
		pageLength: 25,
		responsive: true,
		order: [[1, 'asc']],
		columnDefs: [
			{ orderable: false, targets: [0, 5] } // Colonnes checkbox et actions non triables
		],
		// Désactiver explicitement le traitement côté serveur
		processing: false,
		serverSide: false
		// Pas d'option ajax - DataTables utilise les données déjà présentes dans le DOM
	});
	
	// Gestion de la sélection multiple pour les utilisateurs
	const userCheckboxes = document.querySelectorAll('.user-checkbox');
	const deleteMultipleUsersBtn = document.getElementById('deleteMultipleUsersBtn');
	const selectAllUsersCheckbox = document.getElementById('selectAllUsersCheckbox');
	const selectAllUsersBtn = document.getElementById('selectAllUsersBtn');

	function updateDeleteUsersButton() {
		const checked = document.querySelectorAll('.user-checkbox:checked');
		if (checked.length > 0 && deleteMultipleUsersBtn) {
			deleteMultipleUsersBtn.style.display = 'block';
			deleteMultipleUsersBtn.innerHTML = `<i class="bi bi-trash me-2"></i>Supprimer ${checked.length} sélectionné(s)`;
		} else if (deleteMultipleUsersBtn) {
			deleteMultipleUsersBtn.style.display = 'none';
		}
		
		// Mettre à jour le checkbox "Tout sélectionner"
		if (selectAllUsersCheckbox && userCheckboxes.length > 0) {
			const allChecked = checked.length === userCheckboxes.length && userCheckboxes.length > 0;
			selectAllUsersCheckbox.checked = allChecked;
		}
		
		// Mettre à jour le texte du bouton
		const selectAllUsersText = document.getElementById('selectAllUsersText');
		if (selectAllUsersText && userCheckboxes.length > 0) {
			const allChecked = checked.length === userCheckboxes.length;
			selectAllUsersText.textContent = allChecked ? 'Tout désélectionner' : 'Tout sélectionner';
			if (selectAllUsersBtn) {
				selectAllUsersBtn.innerHTML = allChecked 
					? `<i class="bi bi-square me-2"></i><span id="selectAllUsersText">Tout désélectionner</span>`
					: `<i class="bi bi-check-square me-2"></i><span id="selectAllUsersText">Tout sélectionner</span>`;
			}
		}
	}
	
	// Fonction pour tout sélectionner/désélectionner
	window.toggleSelectAllUsers = function() {
		const checkboxes = document.querySelectorAll('.user-checkbox');
		const checked = document.querySelectorAll('.user-checkbox:checked');
		const allChecked = checked.length === checkboxes.length && checkboxes.length > 0;
		
		checkboxes.forEach(checkbox => {
			checkbox.checked = !allChecked;
		});
		
		updateDeleteUsersButton();
	}
	
	// Attacher les événements aux checkboxes
	if (userCheckboxes.length > 0) {
		userCheckboxes.forEach(checkbox => {
			checkbox.addEventListener('change', updateDeleteUsersButton);
		});
	}
	
	// Suppression multiple
	if (deleteMultipleUsersBtn) {
		deleteMultipleUsersBtn.addEventListener('click', function() {
			const checked = Array.from(document.querySelectorAll('.user-checkbox:checked'))
				.map(cb => cb.value);
			
			if (checked.length === 0) {
				alert('Aucun utilisateur sélectionné');
				return;
			}

			if (confirm(`Êtes-vous sûr de vouloir supprimer ${checked.length} utilisateur(s) ? Cette action est irréversible.`)) {
				const form = document.createElement('form');
				form.method = 'POST';
				form.action = '{{ route('super.users.destroy-multiple') }}';
				form.style.display = 'none';
				// Token CSRF et IDs (même pattern que deleteUser pour éviter "Page Expired")
				form.innerHTML = `
					@csrf
					${checked.map(id => `<input type="hidden" name="user_ids[]" value="${id}">`).join('')}
				`;
				document.body.appendChild(form);
				form.submit();
			}
		});
	}
	
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
