<x-app-layout>
	<x-slot name="header">Gestion des hôtels</x-slot>
	
	<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
		<h4 class="mb-0">Liste des hôtels</h4>
		<div class="d-flex gap-2">
			<button class="btn btn-outline-secondary" id="selectAllBtn" onclick="toggleSelectAll()">
				<i class="bi bi-check-square me-2"></i><span id="selectAllText">Tout sélectionner</span>
			</button>
			<button class="btn btn-danger" id="deleteMultipleBtn" style="display: none;">
				<i class="bi bi-trash me-2"></i>Supprimer sélectionnés
			</button>
			<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createHotelModal">
				<i class="bi bi-plus-lg me-2"></i>Nouvel hôtel
			</button>
		</div>
	</div>

	<!-- Barre de recherche et filtres -->
	<div class="card mb-4 border-0 shadow-sm">
		<div class="card-body">
			<form method="GET" action="{{ route('super.hotels.index') }}" id="searchForm">
				<div class="row g-3">
					<div class="col-md-6">
						<label for="search" class="form-label">Rechercher</label>
						<input type="text" class="form-control" id="search" name="search" 
						       value="{{ request('search') }}" 
						       placeholder="Nom, ville, pays, adresse...">
					</div>
					<div class="col-md-3">
						<label for="sort_by" class="form-label">Trier par</label>
						<select class="form-select" id="sort_by" name="sort_by">
							<option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>Nom</option>
							<option value="users_count" {{ request('sort_by') == 'users_count' ? 'selected' : '' }}>Nombre d'utilisateurs</option>
							<option value="reservations_count" {{ request('sort_by') == 'reservations_count' ? 'selected' : '' }}>Nombre d'enregistrements</option>
						</select>
					</div>
					<div class="col-md-3">
						<label for="sort_order" class="form-label">Ordre</label>
						<select class="form-select" id="sort_order" name="sort_order">
							<option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>Croissant</option>
							<option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>Décroissant</option>
						</select>
					</div>
				</div>
				<div class="row mt-3">
					<div class="col-12">
						<button type="submit" class="btn btn-primary">
							<i class="bi bi-search me-2"></i>Rechercher
						</button>
						@if(request('search') || request('sort_by') || request('sort_order'))
						<a href="{{ route('super.hotels.index') }}" class="btn btn-outline-secondary">
							<i class="bi bi-x-circle me-2"></i>Réinitialiser
						</a>
						@endif
					</div>
				</div>
			</form>
		</div>
	</div>

	<!-- Les notifications sont maintenant gérées globalement dans le layout -->

	<div class="row">
		@forelse($hotels as $hotel)
			<div class="col-md-6 col-lg-4 mb-4">
				<div class="card border-0 shadow-sm h-100 hotel-card" data-hotel-id="{{ $hotel->id }}">
					<div class="card-header bg-transparent d-flex justify-content-between align-items-center position-relative" 
						 style="background: linear-gradient(135deg, {{ $hotel->primary_color ?? '#1a4b8c' }} 0%, {{ $hotel->secondary_color ?? '#2563a8' }} 100%) !important; color: white;">
						<div class="form-check position-absolute" style="left: 10px; top: 50%; transform: translateY(-50%); z-index: 10;">
							<input class="form-check-input hotel-checkbox" type="checkbox" value="{{ $hotel->id }}" id="hotel-{{ $hotel->id }}" style="background-color: white; border-color: white;">
							<label class="form-check-label" for="hotel-{{ $hotel->id }}" style="display: none;"></label>
						</div>
						<div class="d-flex align-items-center gap-2" style="margin-left: 35px;">
							@if($hotel->logo_url)
								<img src="{{ $hotel->logo_url }}" alt="Logo {{ $hotel->name }}" style="max-height: 30px; max-width: 50px; object-fit: contain; background: white; padding: 3px; border-radius: 5px;" loading="lazy">
							@endif
							<h6 class="mb-0 text-white">{{ $hotel->name }}</h6>
						</div>
						<div class="dropdown">
							<button class="btn btn-sm text-white" data-bs-toggle="dropdown">
								<i class="bi bi-three-dots-vertical"></i>
							</button>
							<ul class="dropdown-menu dropdown-menu-end">
								<li><a class="dropdown-item" href="{{ route('super.hotels.show', $hotel) }}">
									<i class="bi bi-eye me-2"></i>Voir détails
								</a></li>
								<li><a class="dropdown-item" href="{{ route('super.hotels.design', $hotel) }}">
									<i class="bi bi-palette me-2"></i>Design & Formulaire
								</a></li>
								<li><a class="dropdown-item" href="{{ route('super.hotels.notifications.show', $hotel) }}">
									<i class="bi bi-envelope-check me-2"></i>Notifications client
								</a></li>
								<li><a class="dropdown-item" href="{{ route('super.hotels.show', $hotel) }}#modules">
									<i class="bi bi-puzzle me-2"></i>Modules
								</a></li>
								<li><button class="dropdown-item" onclick="editHotel({{ $hotel->id }});">
									<i class="bi bi-pencil me-2"></i>Modifier
								</button></li>
								<li><hr class="dropdown-divider"></li>
								<li><button class="dropdown-item text-danger" onclick="deleteHotel({{ $hotel->id }}, '{{ $hotel->name }}')">
									<i class="bi bi-trash me-2"></i>Supprimer
								</button></li>
							</ul>
						</div>
					</div>
					<div class="card-body">
						<div class="mb-3">
							<div class="row text-center">
								<div class="col-4">
									<div class="text-primary">
										<i class="bi bi-people" style="font-size: 1.5rem;"></i>
										<div class="small fw-bold">{{ $hotel->users_count }}</div>
										<div class="small text-muted">Utilisateurs</div>
									</div>
								</div>
								<div class="col-4">
									<div class="text-success">
										<i class="bi bi-calendar-check" style="font-size: 1.5rem;"></i>
										<div class="small fw-bold">{{ $hotel->reservations_count }}</div>
										<div class="small text-muted">Enregistrements</div>
									</div>
								</div>
								<div class="col-4">
									<div class="text-info">
										<i class="bi bi-qr-code" style="font-size: 1.5rem;"></i>
										<div class="small fw-bold">1</div>
										<div class="small text-muted">QR Code</div>
									</div>
								</div>
							</div>
						</div>
						
						@if($hotel->address)
							<p class="text-muted small mb-2">
								<i class="bi bi-geo-alt me-1"></i>{{ $hotel->address }}
							</p>
						@endif
						
						@if($hotel->city)
							<p class="text-muted small mb-3">
								<i class="bi bi-building me-1"></i>{{ $hotel->city }}, {{ $hotel->country }}
							</p>
						@endif
						
						<div class="d-grid gap-2">
							<a href="{{ route('super.hotels.show', $hotel) }}" class="btn btn-sm btn-outline-primary">
								<i class="bi bi-eye me-1"></i>Détails complets
							</a>
						</div>
					</div>
				</div>
			</div>
		@empty
			<div class="col-12">
				<div class="text-center py-5">
					<i class="bi bi-building text-muted" style="font-size: 4rem;"></i>
					<h5 class="text-muted mt-3">Aucun hôtel</h5>
					<p class="text-muted">Commencez par créer votre premier hôtel.</p>
					<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createHotelModal">
						<i class="bi bi-plus-lg me-2"></i>Créer le premier hôtel
					</button>
				</div>
			</div>
		@endforelse
	</div>
</x-app-layout>

<!-- Modal Création Hôtel -->
<div class="modal fade" id="createHotelModal" tabindex="-1">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title"><i class="bi bi-building me-2"></i>Créer un nouvel hôtel</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
			</div>
			<form action="{{ route('super.hotels.store') }}" method="POST" enctype="multipart/form-data">
				@csrf
				<div class="modal-body">
					<!-- Logo - Section mise en évidence -->
					<div class="mb-4 p-3 bg-light border-start border-primary border-4 rounded">
						<label class="form-label fw-bold text-primary mb-2">
							<i class="bi bi-image-fill me-2"></i>Logo de l'hôtel
						</label>
						<div class="d-flex align-items-start gap-3">
							<div class="flex-grow-1">
								<input type="file" name="logo" class="form-control form-control-lg" id="createLogoInput" accept="image/jpeg,image/jpg,image/png,image/svg+xml">
								<div class="form-text mt-2">
									<i class="bi bi-info-circle-fill text-primary me-1"></i>
									<strong>Formats acceptés :</strong> JPG, PNG ou SVG - <strong>Taille max :</strong> 2 Mo
								</div>
							</div>
							<div id="createLogoPreview" class="d-none">
								<img src="" alt="Aperçu" style="max-height: 80px; max-width: 120px; border-radius: 10px; border: 3px solid #0d6efd; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
							</div>
						</div>
					</div>
					
					<hr class="my-4">
					
					<div class="row">
						<div class="col-md-6 mb-3">
							<label class="form-label">Nom de l'hôtel *</label>
							<input type="text" name="name" class="form-control" required>
						</div>
						<div class="col-md-6 mb-3">
							<label class="form-label">Ville</label>
							<input type="text" name="city" class="form-control">
						</div>
					</div>
					<div class="row">
						<div class="col-md-8 mb-3">
							<label class="form-label">Adresse</label>
							<input type="text" name="address" class="form-control" placeholder="Ex: BP 5448">
						</div>
						<div class="col-md-4 mb-3">
							<label class="form-label">Pays</label>
							<select name="country" class="form-select">
								<option value="">-- Sélectionner --</option>
								<option value="Cameroun" selected>Cameroun</option>
								<option value="France">France</option>
								<option value="Belgique">Belgique</option>
								<option value="Suisse">Suisse</option>
								<option value="Canada">Canada</option>
								<option value="Sénégal">Sénégal</option>
								<option value="Côte d'Ivoire">Côte d'Ivoire</option>
								<option value="Mali">Mali</option>
								<option value="Burkina Faso">Burkina Faso</option>
								<option value="Bénin">Bénin</option>
								<option value="Togo">Togo</option>
								<option value="Gabon">Gabon</option>
								<option value="Congo">Congo</option>
								<option value="RDC">RDC</option>
								<option value="Maroc">Maroc</option>
								<option value="Tunisie">Tunisie</option>
								<option value="Algérie">Algérie</option>
								<option value="Madagascar">Madagascar</option>
								<option value="Autre">Autre</option>
							</select>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6 mb-3">
							<label class="form-label">Téléphone</label>
							<input type="text" name="phone" class="form-control" placeholder="+237 222 ...">
						</div>
						<div class="col-md-6 mb-3">
							<label class="form-label">Email</label>
							<input type="email" name="email" class="form-control" placeholder="contact@hotel.com">
						</div>
					</div>
					<div class="row">
						<div class="col-md-6 mb-3">
							<label class="form-label">Couleur primaire</label>
							<input type="color" name="primary_color" class="form-control form-control-color" value="#1a4b8c">
						</div>
						<div class="col-md-6 mb-3">
							<label class="form-label">Couleur secondaire</label>
							<input type="color" name="secondary_color" class="form-control form-control-color" value="#e19f32">
						</div>
					</div>
					<hr>
					<h6>Configuration Oracle (optionnel)</h6>
					<div class="row">
						<div class="col-md-6 mb-3">
							<label class="form-label">DSN Oracle</label>
							<input type="text" name="oracle_dsn" class="form-control" placeholder="oracle://host:port/sid">
						</div>
						<div class="col-md-6 mb-3">
							<label class="form-label">Utilisateur Oracle</label>
							<input type="text" name="oracle_username" class="form-control">
						</div>
					</div>
					<div class="mb-3">
						<label class="form-label">Mot de passe Oracle</label>
						<input type="password" name="oracle_password" class="form-control">
					</div>
					
					<hr>
					
					<!-- Section Types de Chambre -->
					<div class="mb-3">
						<div class="d-flex justify-content-between align-items-center mb-3">
							<h6 class="mb-0"><i class="bi bi-door-open me-2"></i>Types de Chambre & Configuration</h6>
							<button type="button" class="btn btn-sm btn-outline-primary" onclick="addRoomType()">
								<i class="bi bi-plus-circle me-1"></i>Ajouter un type
							</button>
						</div>
						<div id="roomTypesContainer" class="border rounded p-3 bg-light">
							<small class="text-muted">Aucun type de chambre ajouté. Cliquez sur "Ajouter un type" pour commencer.</small>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
					<button type="submit" class="btn btn-primary">
						<i class="bi bi-check-lg me-2"></i>Créer l'hôtel
					</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- Modal Modification Hôtel -->
<div class="modal fade" id="editHotelModal" tabindex="-1">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Modifier l'hôtel</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
			</div>
			<form id="editHotelForm" method="POST" enctype="multipart/form-data">
				@csrf
				@method('PUT')
				<div class="modal-body">
					<!-- Logo - Section mise en évidence -->
					<div class="mb-4 p-3 bg-light border-start border-warning border-4 rounded">
						<label class="form-label fw-bold text-warning mb-2">
							<i class="bi bi-image-fill me-2"></i>Logo de l'hôtel
						</label>
						<div class="d-flex align-items-start gap-3">
							<div class="flex-grow-1">
								<input type="file" name="logo" class="form-control form-control-lg" id="editLogoInput" accept="image/jpeg,image/jpg,image/png,image/svg+xml">
								<div class="form-text mt-2">
									<i class="bi bi-info-circle-fill text-warning me-1"></i>
									<strong>Formats acceptés :</strong> JPG, PNG ou SVG - <strong>Taille max :</strong> 2 Mo<br>
									<small class="text-muted">💡 Laisser vide pour conserver le logo actuel</small>
								</div>
							</div>
							<div id="editLogoPreview">
								<img id="editCurrentLogo" src="" alt="Logo actuel" class="d-none" style="max-height: 80px; max-width: 120px; border-radius: 10px; border: 3px solid #ffc107; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
							</div>
						</div>
					</div>
					
					<hr class="my-4">
					
					<div class="row">
						<div class="col-md-6 mb-3">
							<label class="form-label">Nom de l'hôtel *</label>
							<input type="text" name="name" id="edit_name" class="form-control" required>
						</div>
						<div class="col-md-6 mb-3">
							<label class="form-label">Ville</label>
							<input type="text" name="city" id="edit_city" class="form-control">
						</div>
					</div>
					<div class="row">
						<div class="col-md-8 mb-3">
							<label class="form-label">Adresse</label>
							<input type="text" name="address" id="edit_address" class="form-control">
						</div>
						<div class="col-md-4 mb-3">
							<label class="form-label">Pays</label>
							<select name="country" id="edit_country" class="form-select">
								<option value="">-- Sélectionner --</option>
								<option value="Cameroun">Cameroun</option>
								<option value="France">France</option>
								<option value="Belgique">Belgique</option>
								<option value="Suisse">Suisse</option>
								<option value="Canada">Canada</option>
								<option value="Sénégal">Sénégal</option>
								<option value="Côte d'Ivoire">Côte d'Ivoire</option>
								<option value="Mali">Mali</option>
								<option value="Burkina Faso">Burkina Faso</option>
								<option value="Bénin">Bénin</option>
								<option value="Togo">Togo</option>
								<option value="Gabon">Gabon</option>
								<option value="Congo">Congo</option>
								<option value="RDC">RDC</option>
								<option value="Maroc">Maroc</option>
								<option value="Tunisie">Tunisie</option>
								<option value="Algérie">Algérie</option>
								<option value="Madagascar">Madagascar</option>
								<option value="Autre">Autre</option>
							</select>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6 mb-3">
							<label class="form-label">Téléphone</label>
							<input type="text" name="phone" id="edit_phone" class="form-control" placeholder="+237 222 ...">
						</div>
						<div class="col-md-6 mb-3">
							<label class="form-label">Email</label>
							<input type="email" name="email" id="edit_email" class="form-control" placeholder="contact@hotel.com">
						</div>
					</div>
					<div class="row">
						<div class="col-md-6 mb-3">
							<label class="form-label">Couleur primaire</label>
							<input type="color" name="primary_color" id="edit_primary_color" class="form-control form-control-color">
						</div>
						<div class="col-md-6 mb-3">
							<label class="form-label">Couleur secondaire</label>
							<input type="color" name="secondary_color" id="edit_secondary_color" class="form-control form-control-color">
						</div>
					</div>
					
					<hr>
					
					<!-- Section Types de Chambre -->
					<div class="mb-3">
						<div class="d-flex justify-content-between align-items-center mb-3">
							<h6 class="mb-0"><i class="bi bi-door-open me-2"></i>Types de Chambre & Prix</h6>
							<button type="button" class="btn btn-sm btn-outline-primary" onclick="addEditRoomType()">
								<i class="bi bi-plus-circle me-1"></i>Ajouter un type
							</button>
						</div>
						<div id="editRoomTypesContainer" class="border rounded p-3 bg-light">
							<small class="text-muted">Chargement...</small>
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

<script>
// Compteur pour les types de chambre
let roomTypeCounter = 0;
let editRoomTypeCounter = 0;

// Auto-ouvrir le modal de modification si demandé via sessionStorage
document.addEventListener('DOMContentLoaded', function() {
	const editHotelId = sessionStorage.getItem('editHotelId');
	if (editHotelId) {
		sessionStorage.removeItem('editHotelId');
		// Petit délai pour s'assurer que la page est complètement chargée
		setTimeout(() => {
			console.log('Auto-ouverture du modal pour l\'hôtel ID:', editHotelId);
			editHotel(editHotelId);
		}, 150);
	}
});

// Ajouter un type de chambre (Modal création)
function addRoomType() {
	const container = document.getElementById('roomTypesContainer');
	
	// Retirer le message "Aucun type" si présent
	if (container.querySelector('small')) {
		container.innerHTML = '';
	}
	
	const roomTypeHtml = `
		<div class="room-type-item mb-3 p-3 bg-white rounded border" data-index="${roomTypeCounter}">
			<div class="d-flex justify-content-between align-items-center mb-3">
				<strong class="text-primary"><i class="bi bi-door-closed me-2"></i>Type de chambre ${roomTypeCounter + 1}</strong>
				<button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRoomType(${roomTypeCounter})">
					<i class="bi bi-trash"></i> Supprimer
				</button>
			</div>
			
			<!-- Info du type -->
			<div class="row">
				<div class="col-md-4 mb-2">
					<label class="form-label small fw-bold">Nom du type *</label>
					<input type="text" name="room_types[${roomTypeCounter}][name]" class="form-control" placeholder="Ex: Suite Deluxe" required>
				</div>
				<div class="col-md-3 mb-2">
					<label class="form-label small fw-bold">Prix/nuit *</label>
					<div class="input-group">
						<input type="number" name="room_types[${roomTypeCounter}][price]" class="form-control" placeholder="0.00" step="0.01" min="0" required>
						<span class="input-group-text">€</span>
					</div>
				</div>
				<div class="col-md-2 mb-2">
					<label class="form-label small fw-bold">Capacité</label>
					<input type="number" name="room_types[${roomTypeCounter}][capacity]" class="form-control" placeholder="2" min="1">
				</div>
				<div class="col-md-3 mb-2">
					<label class="form-label small fw-bold">Disponible</label>
					<select name="room_types[${roomTypeCounter}][is_available]" class="form-select">
						<option value="1">Oui</option>
						<option value="0">Non</option>
					</select>
				</div>
			</div>
			<div class="mb-3">
				<label class="form-label small fw-bold">Description</label>
				<textarea name="room_types[${roomTypeCounter}][description]" class="form-control" rows="2" placeholder="Description du type de chambre..."></textarea>
			</div>
			
			<hr class="my-3">
			
			<!-- Gestion des chambres -->
			<div class="rooms-section">
				<div class="d-flex justify-content-between align-items-center mb-2">
					<span class="fw-bold text-secondary">
						<i class="bi bi-key me-1"></i>Chambres de ce type
						<span class="badge bg-secondary ms-2" id="roomCount_${roomTypeCounter}">0</span>
					</span>
					<div class="btn-group btn-group-sm">
						<button type="button" class="btn btn-outline-success" onclick="showRoomGenerator(${roomTypeCounter})">
							<i class="bi bi-magic me-1"></i>Générer en lot
						</button>
						<button type="button" class="btn btn-outline-primary" onclick="addSingleRoom(${roomTypeCounter})">
							<i class="bi bi-plus-circle me-1"></i>Ajouter une chambre
						</button>
					</div>
				</div>
				<div id="roomsList_${roomTypeCounter}" class="border rounded p-2 bg-light" style="max-height: 200px; overflow-y: auto;">
					<small class="text-muted">Aucune chambre ajoutée</small>
				</div>
			</div>
		</div>
	`;
	
	container.insertAdjacentHTML('beforeend', roomTypeHtml);
	roomTypeCounter++;
}

// Supprimer un type de chambre
function removeRoomType(index) {
	const item = document.querySelector(`[data-index="${index}"]`);
	if (item) {
		item.remove();
		
		// Si plus aucun type, remettre le message
		const container = document.getElementById('roomTypesContainer');
		if (container.children.length === 0) {
			container.innerHTML = '<small class="text-muted">Aucun type de chambre ajouté. Cliquez sur "Ajouter un type" pour commencer.</small>';
		}
	}
}

// Variables globales pour les compteurs de chambres
const roomCounters = {};

// Ajouter une chambre unique
function addSingleRoom(typeIndex) {
	if (!roomCounters[typeIndex]) {
		roomCounters[typeIndex] = 0;
	}
	
	const roomIndex = roomCounters[typeIndex];
	const container = document.getElementById(`roomsList_${typeIndex}`);
	
	// Supprimer le message si c'est la première chambre
	if (container.querySelector('small')) {
		container.innerHTML = '';
	}
	
	const roomHtml = `
		<div class="room-item mb-2 p-2 bg-white rounded border" data-room-index="${typeIndex}_${roomIndex}">
			<div class="row align-items-center">
				<div class="col-md-4">
					<input type="text" name="room_types[${typeIndex}][rooms][${roomIndex}][number]" 
						   class="form-control form-control-sm" placeholder="Numéro (ex: 101)" required>
				</div>
				<div class="col-md-3">
					<input type="text" name="room_types[${typeIndex}][rooms][${roomIndex}][floor]" 
						   class="form-control form-control-sm" placeholder="Étage">
				</div>
				<div class="col-md-3">
					<select name="room_types[${typeIndex}][rooms][${roomIndex}][status]" class="form-select form-select-sm">
						<option value="available">Disponible</option>
						<option value="maintenance">Maintenance</option>
						<option value="reserved">Réservée</option>
					</select>
				</div>
				<div class="col-md-2 text-end">
					<button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRoom('${typeIndex}_${roomIndex}', ${typeIndex})">
						<i class="bi bi-x"></i>
					</button>
				</div>
			</div>
		</div>
	`;
	
	container.insertAdjacentHTML('beforeend', roomHtml);
	roomCounters[typeIndex]++;
	updateRoomCount(typeIndex);
}

// Afficher le générateur en lot
function showRoomGenerator(typeIndex) {
	const roomsList = document.getElementById(`roomsList_${typeIndex}`);
	
	// Vérifier si le générateur existe déjà
	if (document.getElementById(`generator_${typeIndex}`)) {
		document.getElementById(`generator_${typeIndex}`).remove();
		return;
	}
	
	const generatorHtml = `
		<div id="generator_${typeIndex}" class="p-3 bg-info bg-opacity-10 border border-info rounded mb-3">
			<div class="d-flex justify-content-between align-items-center mb-2">
				<strong class="text-info"><i class="bi bi-magic me-2"></i>Générateur en lot</strong>
				<button type="button" class="btn-close btn-sm" onclick="document.getElementById('generator_${typeIndex}').remove()"></button>
			</div>
			<div class="row g-2">
				<div class="col-md-3">
					<label class="form-label small">Quantité *</label>
					<input type="number" id="gen_quantity_${typeIndex}" class="form-control form-control-sm" 
						   placeholder="Ex: 10" min="1" max="100" value="5" onchange="previewGeneration(${typeIndex})">
				</div>
				<div class="col-md-3">
					<label class="form-label small">Préfixe</label>
					<input type="text" id="gen_prefix_${typeIndex}" class="form-control form-control-sm" 
						   placeholder="Ex: S, 1" maxlength="3" onchange="previewGeneration(${typeIndex})">
				</div>
				<div class="col-md-2">
					<label class="form-label small">Commence à</label>
					<input type="number" id="gen_start_${typeIndex}" class="form-control form-control-sm" 
						   placeholder="1" min="1" value="101" onchange="previewGeneration(${typeIndex})">
				</div>
				<div class="col-md-2">
					<label class="form-label small">Étage</label>
					<input type="text" id="gen_floor_${typeIndex}" class="form-control form-control-sm" 
						   placeholder="Ex: 1">
				</div>
				<div class="col-md-2">
					<label class="form-label small">&nbsp;</label>
					<button type="button" class="btn btn-success btn-sm w-100" onclick="generateRooms(${typeIndex})">
						<i class="bi bi-check me-1"></i>Générer
					</button>
				</div>
			</div>
			<div id="gen_preview_${typeIndex}" class="mt-2 p-2 bg-white rounded small">
				<i class="text-muted">Aperçu : Modifiez les paramètres ci-dessus</i>
			</div>
		</div>
	`;
	
	roomsList.insertAdjacentHTML('beforebegin', generatorHtml);
	previewGeneration(typeIndex);
}

// Prévisualiser la génération
function previewGeneration(typeIndex) {
	const quantity = parseInt(document.getElementById(`gen_quantity_${typeIndex}`).value) || 0;
	const prefix = document.getElementById(`gen_prefix_${typeIndex}`).value || '';
	const start = parseInt(document.getElementById(`gen_start_${typeIndex}`).value) || 1;
	
	const preview = document.getElementById(`gen_preview_${typeIndex}`);
	
	if (quantity === 0) {
		preview.innerHTML = '<i class="text-muted">Entrez une quantité pour voir l\'aperçu</i>';
		return;
	}
	
	if (quantity > 50) {
		preview.innerHTML = `<i class="text-success"><strong>${quantity} chambres</strong> : de ${prefix}${start} à ${prefix}${start + quantity - 1}</i>`;
		return;
	}
	
	const examples = [];
	for (let i = 0; i < Math.min(quantity, 10); i++) {
		examples.push(`<span class="badge bg-success me-1">${prefix}${start + i}</span>`);
	}
	
	let html = '<i class="text-success fw-bold">Aperçu : </i>' + examples.join('');
	if (quantity > 10) {
		html += ` <span class="text-muted">... +${quantity - 10} autres</span>`;
	}
	
	preview.innerHTML = html;
}

// Générer les chambres en lot
function generateRooms(typeIndex) {
	const quantity = parseInt(document.getElementById(`gen_quantity_${typeIndex}`).value) || 0;
	const prefix = document.getElementById(`gen_prefix_${typeIndex}`).value || '';
	const start = parseInt(document.getElementById(`gen_start_${typeIndex}`).value) || 1;
	const floor = document.getElementById(`gen_floor_${typeIndex}`).value || '';
	
	if (quantity === 0 || quantity > 100) {
		alert('Veuillez entrer une quantité valide (1 à 100 chambres)');
		return;
	}
	
	if (!roomCounters[typeIndex]) {
		roomCounters[typeIndex] = 0;
	}
	
	const container = document.getElementById(`roomsList_${typeIndex}`);
	
	// Supprimer le message si présent
	if (container.querySelector('small')) {
		container.innerHTML = '';
	}
	
	// Générer toutes les chambres
	for (let i = 0; i < quantity; i++) {
		const roomNumber = `${prefix}${start + i}`;
		const roomIndex = roomCounters[typeIndex];
		
		const roomHtml = `
			<div class="room-item mb-2 p-2 bg-white rounded border" data-room-index="${typeIndex}_${roomIndex}">
				<div class="row align-items-center">
					<div class="col-md-4">
						<input type="text" name="room_types[${typeIndex}][rooms][${roomIndex}][number]" 
							   class="form-control form-control-sm" value="${roomNumber}" required>
					</div>
					<div class="col-md-3">
						<input type="text" name="room_types[${typeIndex}][rooms][${roomIndex}][floor]" 
							   class="form-control form-control-sm" value="${floor}">
					</div>
					<div class="col-md-3">
						<select name="room_types[${typeIndex}][rooms][${roomIndex}][status]" class="form-select form-select-sm">
							<option value="available" selected>Disponible</option>
							<option value="maintenance">Maintenance</option>
							<option value="reserved">Réservée</option>
						</select>
					</div>
					<div class="col-md-2 text-end">
						<button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRoom('${typeIndex}_${roomIndex}', ${typeIndex})">
							<i class="bi bi-x"></i>
						</button>
					</div>
				</div>
			</div>
		`;
		
		container.insertAdjacentHTML('beforeend', roomHtml);
		roomCounters[typeIndex]++;
	}
	
	updateRoomCount(typeIndex);
	
	// Fermer le générateur
	document.getElementById(`generator_${typeIndex}`).remove();
	
	// Message de succès
	const successMsg = document.createElement('div');
	successMsg.className = 'alert alert-success alert-dismissible fade show';
	successMsg.innerHTML = `
		<i class="bi bi-check-circle me-2"></i><strong>${quantity} chambres</strong> générées avec succès !
		<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
	`;
	container.insertAdjacentElement('beforebegin', successMsg);
	setTimeout(() => successMsg.remove(), 3000);
}

// Supprimer une chambre
function removeRoom(roomIndex, typeIndex) {
	const room = document.querySelector(`[data-room-index="${roomIndex}"]`);
	if (room) {
		room.remove();
		updateRoomCount(typeIndex);
		
		const container = document.getElementById(`roomsList_${typeIndex}`);
		if (container.children.length === 0) {
			container.innerHTML = '<small class="text-muted">Aucune chambre ajoutée</small>';
		}
	}
}

// Mettre à jour le compteur de chambres
function updateRoomCount(typeIndex) {
	const container = document.getElementById(`roomsList_${typeIndex}`);
	const count = container.querySelectorAll('.room-item').length;
	const badge = document.getElementById(`roomCount_${typeIndex}`);
	if (badge) {
		badge.textContent = count;
		badge.className = count > 0 ? 'badge bg-success ms-2' : 'badge bg-secondary ms-2';
	}
}

// Ajouter un type de chambre (Modal édition)
function addEditRoomType() {
	const container = document.getElementById('editRoomTypesContainer');
	
	// Retirer le message si présent
	if (container.querySelector('small')) {
		container.innerHTML = '';
	}
	
	const typeIndex = editRoomTypeCounter;
	editRoomCounters[typeIndex] = 0;
	
	const roomTypeHtml = `
		<div class="room-type-item mb-3 p-3 bg-white rounded border" data-edit-index="${typeIndex}">
			<div class="d-flex justify-content-between align-items-center mb-3">
				<strong class="text-primary"><i class="bi bi-door-closed me-2"></i>Nouveau Type de chambre</strong>
				<button type="button" class="btn btn-sm btn-outline-danger" onclick="removeEditRoomType(${typeIndex})">
					<i class="bi bi-trash"></i> Supprimer
				</button>
			</div>
			
			<!-- Info du type -->
			<div class="row">
				<div class="col-md-4 mb-2">
					<label class="form-label small fw-bold">Nom du type *</label>
					<input type="text" name="room_types[${typeIndex}][name]" class="form-control form-control-sm" placeholder="Ex: Suite Deluxe" required>
				</div>
				<div class="col-md-3 mb-2">
					<label class="form-label small fw-bold">Prix/nuit *</label>
					<div class="input-group input-group-sm">
						<input type="number" name="room_types[${typeIndex}][price]" class="form-control" placeholder="0.00" step="0.01" min="0" required>
						<span class="input-group-text">€</span>
					</div>
				</div>
				<div class="col-md-2 mb-2">
					<label class="form-label small fw-bold">Capacité</label>
					<input type="number" name="room_types[${typeIndex}][capacity]" class="form-control form-control-sm" placeholder="2" min="1">
				</div>
				<div class="col-md-3 mb-2">
					<label class="form-label small fw-bold">Disponible</label>
					<select name="room_types[${typeIndex}][is_available]" class="form-select form-select-sm">
						<option value="1">Oui</option>
						<option value="0">Non</option>
					</select>
				</div>
			</div>
			<div class="mb-3">
				<label class="form-label small fw-bold">Description</label>
				<textarea name="room_types[${typeIndex}][description]" class="form-control form-control-sm" rows="2" placeholder="Description du type de chambre..."></textarea>
			</div>
			
			<hr class="my-3">
			
			<!-- Gestion des chambres -->
			<div class="rooms-section">
				<div class="d-flex justify-content-between align-items-center mb-2">
					<span class="fw-bold text-secondary">
						<i class="bi bi-key me-1"></i>Chambres de ce type
						<span class="badge bg-secondary ms-2" id="editRoomCount_${typeIndex}">0</span>
					</span>
					<button type="button" class="btn btn-sm btn-outline-primary" onclick="addEditSingleRoom(${typeIndex})">
						<i class="bi bi-plus-circle me-1"></i>Ajouter une chambre
					</button>
				</div>
				<div id="editRoomsList_${typeIndex}" class="border rounded p-2 bg-light" style="max-height: 300px; overflow-y: auto;">
					<small class="text-muted">Aucune chambre ajoutée</small>
				</div>
			</div>
		</div>
	`;
	
	container.insertAdjacentHTML('beforeend', roomTypeHtml);
	editRoomTypeCounter++;
}

// Supprimer un type de chambre (Modal édition)
function removeEditRoomType(index) {
	const item = document.querySelector(`[data-edit-index="${index}"]`);
	if (item) {
		item.remove();
		
		const container = document.getElementById('editRoomTypesContainer');
		if (container.children.length === 0) {
			container.innerHTML = '<small class="text-muted">Aucun type de chambre. Cliquez sur "Ajouter un type".</small>';
		}
	}
}

// Aperçu du logo - Modal création
document.getElementById('createLogoInput').addEventListener('change', function(e) {
	const file = e.target.files[0];
	if (file) {
		const reader = new FileReader();
		reader.onload = function(e) {
			const preview = document.getElementById('createLogoPreview');
			const img = preview.querySelector('img');
			img.src = e.target.result;
			preview.classList.remove('d-none');
		}
		reader.readAsDataURL(file);
	}
});

// Aperçu du logo - Modal modification
document.getElementById('editLogoInput').addEventListener('change', function(e) {
	const file = e.target.files[0];
	if (file) {
		const reader = new FileReader();
		reader.onload = function(e) {
			const img = document.getElementById('editCurrentLogo');
			img.src = e.target.result;
			img.classList.remove('d-none');
		}
		reader.readAsDataURL(file);
	}
});

function editHotel(hotelId) {
	fetch(`/super/hotels/${hotelId}`, {
		headers: {
			'X-Requested-With': 'XMLHttpRequest',
			'Accept': 'application/json',
			'Content-Type': 'application/json'
		}
	})
		.then(response => {
			if (!response.ok) {
				return response.text().then(text => {
					console.error('Erreur serveur:', text);
					throw new Error(`Erreur ${response.status}: ${response.statusText}`);
				});
			}
			return response.json();
		})
		.then(hotel => {
			document.getElementById('edit_name').value = hotel.name || '';
			document.getElementById('edit_city').value = hotel.city || '';
			document.getElementById('edit_address').value = hotel.address || '';
			document.getElementById('edit_phone').value = hotel.phone || '';
			document.getElementById('edit_email').value = hotel.email || '';
			
			// Sélectionner le pays dans le select
			const countrySelect = document.getElementById('edit_country');
			const countryValue = hotel.country || '';
			for (let i = 0; i < countrySelect.options.length; i++) {
				if (countrySelect.options[i].value === countryValue) {
					countrySelect.selectedIndex = i;
					break;
				}
			}
			
			document.getElementById('edit_primary_color').value = hotel.primary_color || '#1a4b8c';
			document.getElementById('edit_secondary_color').value = hotel.secondary_color || '#e19f32';
			
			// Afficher le logo actuel
			const logoImg = document.getElementById('editCurrentLogo');
			if (hotel.logo_url) {
				logoImg.src = hotel.logo_url;
				logoImg.classList.remove('d-none');
			} else {
				logoImg.classList.add('d-none');
			}
			
			// Charger les types de chambre
			loadRoomTypes(hotelId);
			
			document.getElementById('editHotelForm').action = `/super/hotels/${hotelId}`;
			const modalEl = document.getElementById('editHotelModal');
			if (document.activeElement && document.activeElement.blur) document.activeElement.blur();
			modalEl.addEventListener('shown.bs.modal', function onShown() {
				modalEl.removeEventListener('shown.bs.modal', onShown);
				const t = modalEl.querySelector('button[data-bs-dismiss="modal"]') || modalEl.querySelector('.btn-primary') || modalEl;
				if (t && typeof t.focus === 'function') t.focus();
			});
			new bootstrap.Modal(modalEl).show();
		})
		.catch(error => {
			console.error('Erreur détaillée:', error);
			alert('Erreur: ' + error.message);
		});
}

// Variables globales pour les compteurs de chambres en édition
const editRoomCounters = {};

// Charger les types de chambre existants avec leurs chambres
function loadRoomTypes(hotelId) {
	const container = document.getElementById('editRoomTypesContainer');
	container.innerHTML = '<small class="text-muted">Chargement...</small>';
	
	fetch(`/super/hotels/${hotelId}/room-types`, {
		headers: {
			'X-Requested-With': 'XMLHttpRequest',
			'Accept': 'application/json',
			'Content-Type': 'application/json'
		}
	})
		.then(response => {
			if (!response.ok) {
				throw new Error('Erreur lors du chargement des types de chambre');
			}
			return response.json();
		})
		.then(roomTypes => {
			container.innerHTML = '';
			editRoomTypeCounter = 0;
			
			if (roomTypes.length === 0) {
				container.innerHTML = '<small class="text-muted">Aucun type de chambre. Cliquez sur "Ajouter un type".</small>';
			} else {
				roomTypes.forEach(roomType => {
					const typeIndex = editRoomTypeCounter;
					editRoomCounters[typeIndex] = 0;
					
					// Construire la liste des chambres existantes
					let roomsHtml = '';
					if (roomType.rooms && roomType.rooms.length > 0) {
						roomType.rooms.forEach(room => {
							const roomIndex = editRoomCounters[typeIndex];
							roomsHtml += `
								<div class="room-item mb-2 p-2 bg-white rounded border" data-edit-room-index="${typeIndex}_${roomIndex}">
									<input type="hidden" name="room_types[${typeIndex}][rooms][${roomIndex}][id]" value="${room.id}">
									<div class="row align-items-center">
										<div class="col-md-4">
											<input type="text" name="room_types[${typeIndex}][rooms][${roomIndex}][room_number]" 
												   class="form-control form-control-sm" value="${room.room_number}" required>
										</div>
										<div class="col-md-3">
											<input type="text" name="room_types[${typeIndex}][rooms][${roomIndex}][floor]" 
												   class="form-control form-control-sm" value="${room.floor || ''}" placeholder="Étage">
										</div>
										<div class="col-md-3">
											<select name="room_types[${typeIndex}][rooms][${roomIndex}][status]" class="form-select form-select-sm">
												<option value="available" ${room.status === 'available' ? 'selected' : ''}>Disponible</option>
												<option value="maintenance" ${room.status === 'maintenance' ? 'selected' : ''}>Maintenance</option>
												<option value="reserved" ${room.status === 'reserved' ? 'selected' : ''}>Réservée</option>
											</select>
										</div>
										<div class="col-md-2 text-end">
											<button type="button" class="btn btn-sm btn-outline-danger" onclick="removeEditRoom('${typeIndex}_${roomIndex}', ${typeIndex})">
												<i class="bi bi-x"></i>
											</button>
										</div>
									</div>
								</div>
							`;
							editRoomCounters[typeIndex]++;
						});
					} else {
						roomsHtml = '<small class="text-muted">Aucune chambre ajoutée</small>';
					}
					
					const roomTypeHtml = `
						<div class="room-type-item mb-3 p-3 bg-white rounded border" data-edit-index="${typeIndex}">
							<input type="hidden" name="room_types[${typeIndex}][id]" value="${roomType.id}">
							<div class="d-flex justify-content-between align-items-center mb-3">
								<strong class="text-primary"><i class="bi bi-door-closed me-2"></i>${roomType.name}</strong>
								<button type="button" class="btn btn-sm btn-outline-danger" onclick="removeEditRoomType(${typeIndex})">
									<i class="bi bi-trash"></i> Supprimer
								</button>
							</div>
							
							<!-- Info du type -->
							<div class="row">
								<div class="col-md-4 mb-2">
									<label class="form-label small fw-bold">Nom du type</label>
									<input type="text" name="room_types[${typeIndex}][name]" class="form-control form-control-sm" value="${roomType.name}" required>
								</div>
								<div class="col-md-3 mb-2">
									<label class="form-label small fw-bold">Prix/nuit</label>
									<div class="input-group input-group-sm">
										<input type="number" name="room_types[${typeIndex}][price]" class="form-control" value="${roomType.price}" step="0.01" min="0" required>
										<span class="input-group-text">€</span>
									</div>
								</div>
								<div class="col-md-2 mb-2">
									<label class="form-label small fw-bold">Capacité</label>
									<input type="number" name="room_types[${typeIndex}][capacity]" class="form-control form-control-sm" value="${roomType.capacity || ''}" min="1" placeholder="2">
								</div>
								<div class="col-md-3 mb-2">
									<label class="form-label small fw-bold">Disponible</label>
									<select name="room_types[${typeIndex}][is_available]" class="form-select form-select-sm">
										<option value="1" ${roomType.is_available ? 'selected' : ''}>Oui</option>
										<option value="0" ${!roomType.is_available ? 'selected' : ''}>Non</option>
									</select>
								</div>
							</div>
							<div class="mb-3">
								<label class="form-label small fw-bold">Description</label>
								<textarea name="room_types[${typeIndex}][description]" class="form-control form-control-sm" rows="2" placeholder="Description du type de chambre...">${roomType.description || ''}</textarea>
							</div>
							
							<hr class="my-3">
							
							<!-- Gestion des chambres -->
							<div class="rooms-section">
								<div class="d-flex justify-content-between align-items-center mb-2">
									<span class="fw-bold text-secondary">
										<i class="bi bi-key me-1"></i>Chambres de ce type
										<span class="badge bg-secondary ms-2" id="editRoomCount_${typeIndex}">${roomType.rooms ? roomType.rooms.length : 0}</span>
									</span>
									<button type="button" class="btn btn-sm btn-outline-primary" onclick="addEditSingleRoom(${typeIndex})">
										<i class="bi bi-plus-circle me-1"></i>Ajouter une chambre
									</button>
								</div>
								<div id="editRoomsList_${typeIndex}" class="border rounded p-2 bg-light" style="max-height: 300px; overflow-y: auto;">
									${roomsHtml}
								</div>
							</div>
						</div>
					`;
					
					container.insertAdjacentHTML('beforeend', roomTypeHtml);
					editRoomTypeCounter++;
				});
			}
		})
		.catch(error => {
			container.innerHTML = '<small class="text-danger">Erreur lors du chargement des types de chambre</small>';
			console.error('Error:', error);
		});
}

// Ajouter une chambre unique en mode édition
function addEditSingleRoom(typeIndex) {
	if (!editRoomCounters[typeIndex]) {
		editRoomCounters[typeIndex] = 0;
	}
	
	const roomIndex = editRoomCounters[typeIndex];
	const container = document.getElementById(`editRoomsList_${typeIndex}`);
	
	// Supprimer le message si c'est la première chambre
	if (container.querySelector('small')) {
		container.innerHTML = '';
	}
	
	const roomHtml = `
		<div class="room-item mb-2 p-2 bg-white rounded border" data-edit-room-index="${typeIndex}_${roomIndex}">
			<div class="row align-items-center">
				<div class="col-md-4">
					<input type="text" name="room_types[${typeIndex}][rooms][${roomIndex}][room_number]" 
						   class="form-control form-control-sm" placeholder="Numéro (ex: 101)" required>
				</div>
				<div class="col-md-3">
					<input type="text" name="room_types[${typeIndex}][rooms][${roomIndex}][floor]" 
						   class="form-control form-control-sm" placeholder="Étage">
				</div>
				<div class="col-md-3">
					<select name="room_types[${typeIndex}][rooms][${roomIndex}][status]" class="form-select form-select-sm">
						<option value="available" selected>Disponible</option>
						<option value="maintenance">Maintenance</option>
						<option value="reserved">Réservée</option>
					</select>
				</div>
				<div class="col-md-2 text-end">
					<button type="button" class="btn btn-sm btn-outline-danger" onclick="removeEditRoom('${typeIndex}_${roomIndex}', ${typeIndex})">
						<i class="bi bi-x"></i>
					</button>
				</div>
			</div>
		</div>
	`;
	
	container.insertAdjacentHTML('beforeend', roomHtml);
	editRoomCounters[typeIndex]++;
	updateEditRoomCount(typeIndex);
}

// Supprimer une chambre en mode édition
function removeEditRoom(roomIndex, typeIndex) {
	const room = document.querySelector(`[data-edit-room-index="${roomIndex}"]`);
	if (room) {
		room.remove();
		updateEditRoomCount(typeIndex);
		
		const container = document.getElementById(`editRoomsList_${typeIndex}`);
		if (container.children.length === 0) {
			container.innerHTML = '<small class="text-muted">Aucune chambre ajoutée</small>';
		}
	}
}

// Mettre à jour le compteur de chambres en mode édition
function updateEditRoomCount(typeIndex) {
	const container = document.getElementById(`editRoomsList_${typeIndex}`);
	const count = container.querySelectorAll('.room-item').length;
	const badge = document.getElementById(`editRoomCount_${typeIndex}`);
	if (badge) {
		badge.textContent = count;
		badge.className = count > 0 ? 'badge bg-success ms-2' : 'badge bg-secondary ms-2';
	}
}

function deleteHotel(hotelId, hotelName) {
	if (confirm(`Êtes-vous sûr de vouloir supprimer l'hôtel "${hotelName}" ?\n\nCette action est irréversible et supprimera tous les utilisateurs, formulaires et pré-enregistrements associés.`)) {
		const form = document.createElement('form');
		form.method = 'POST';
		form.action = `/super/hotels/${hotelId}`;
		form.innerHTML = `
			@csrf
			@method('DELETE')
		`;
		document.body.appendChild(form);
		form.submit();
	}
}

// Gestion de la sélection multiple - Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
	const checkboxes = document.querySelectorAll('.hotel-checkbox');
	const deleteMultipleBtn = document.getElementById('deleteMultipleBtn');
	const selectAllBtn = document.getElementById('selectAllBtn');
	const selectAllText = document.getElementById('selectAllText');

	function updateDeleteButton() {
		const checked = document.querySelectorAll('.hotel-checkbox:checked');
		if (checked.length > 0 && deleteMultipleBtn) {
			deleteMultipleBtn.style.display = 'block';
			deleteMultipleBtn.innerHTML = `<i class="bi bi-trash me-2"></i>Supprimer ${checked.length} sélectionné(s)`;
		} else if (deleteMultipleBtn) {
			deleteMultipleBtn.style.display = 'none';
		}
		
		// Mettre à jour le texte du bouton "Tout sélectionner"
		if (selectAllText && checkboxes.length > 0) {
			const allChecked = checked.length === checkboxes.length;
			selectAllText.textContent = allChecked ? 'Tout désélectionner' : 'Tout sélectionner';
			if (selectAllBtn) {
				selectAllBtn.innerHTML = allChecked 
					? `<i class="bi bi-square me-2"></i><span id="selectAllText">Tout désélectionner</span>`
					: `<i class="bi bi-check-square me-2"></i><span id="selectAllText">Tout sélectionner</span>`;
				selectAllText = document.getElementById('selectAllText'); // Référencer à nouveau après innerHTML
			}
		}
	}
	
	// Fonction pour tout sélectionner/désélectionner
	window.toggleSelectAll = function() {
		const checkboxes = document.querySelectorAll('.hotel-checkbox');
		const checked = document.querySelectorAll('.hotel-checkbox:checked');
		const allChecked = checked.length === checkboxes.length;
		
		checkboxes.forEach(checkbox => {
			checkbox.checked = !allChecked;
		});
		
		updateDeleteButton();
	}

	// Attacher les événements aux checkboxes
	if (checkboxes.length > 0) {
		checkboxes.forEach(checkbox => {
			checkbox.addEventListener('change', updateDeleteButton);
		});
	}

	// Suppression multiple
	if (deleteMultipleBtn) {
		deleteMultipleBtn.addEventListener('click', function() {
			const checked = Array.from(document.querySelectorAll('.hotel-checkbox:checked'))
				.map(cb => cb.value);
			
			if (checked.length === 0) {
				alert('Aucun hôtel sélectionné');
				return;
			}

			if (confirm(`Êtes-vous sûr de vouloir supprimer ${checked.length} hôtel(s) ? Cette action est irréversible et supprimera toutes les données associées.`)) {
				const form = document.createElement('form');
				form.method = 'POST';
				form.action = '{{ route("super.hotels.destroy-multiple") }}';
				
				// Token CSRF
				const csrfInput = document.createElement('input');
				csrfInput.type = 'hidden';
				csrfInput.name = '_token';
				csrfInput.value = '{{ csrf_token() }}';
				form.appendChild(csrfInput);

				// IDs des hôtels
				checked.forEach(id => {
					const input = document.createElement('input');
					input.type = 'hidden';
					input.name = 'hotel_ids[]';
					input.value = id;
					form.appendChild(input);
				});

				document.body.appendChild(form);
				form.submit();
			}
		});
	}
});
</script>

<style>
/* Design System - Variables */
:root {
	--icon-size-sm: 1rem;
	--icon-size-md: 1.25rem;
	--icon-size-lg: 1.5rem;
	--icon-size-xl: 2rem;
	--spacing-xs: 0.5rem;
	--spacing-sm: 0.75rem;
	--spacing-md: 1rem;
	--spacing-lg: 1.5rem;
	--spacing-xl: 2rem;
	--border-radius-sm: 0.375rem;
	--border-radius-md: 0.5rem;
	--border-radius-lg: 0.75rem;
	--transition-fast: 0.15s ease;
	--transition-normal: 0.3s ease;
	--shadow-sm: 0 2px 4px rgba(0,0,0,0.08);
	--shadow-md: 0 4px 12px rgba(0,0,0,0.1);
	--shadow-lg: 0 10px 30px rgba(0,0,0,0.15);
}

/* Modern Hotel Card */
.hotel-card {
	transition: all var(--transition-normal);
	border-radius: var(--border-radius-lg);
	overflow: hidden;
}

.hotel-card:hover {
	transform: translateY(-8px);
	box-shadow: var(--shadow-lg) !important;
}

.hotel-card .bi {
	font-size: var(--icon-size-lg);
}

/* Modal Headers */
.modal-header {
	padding: var(--spacing-lg);
	border-bottom: 2px solid #f0f0f0;
}

.modal-header .modal-title {
	font-size: 1.25rem;
	font-weight: 600;
	display: flex;
	align-items: center;
	gap: var(--spacing-sm);
}

.modal-header .modal-title .bi {
	font-size: var(--icon-size-xl);
	color: #0d6efd;
}

/* Form Labels */
.form-label {
	font-size: 0.875rem;
	font-weight: 500;
	margin-bottom: var(--spacing-xs);
	color: #495057;
	display: flex;
	align-items: center;
	gap: var(--spacing-xs);
}

.form-label .bi {
	font-size: var(--icon-size-sm);
	color: #6c757d;
}

/* Buttons Standardization */
.btn {
	display: inline-flex;
	align-items: center;
	gap: var(--spacing-xs);
	padding: 0.5rem 1rem;
	font-weight: 500;
	transition: all var(--transition-fast);
	border-radius: var(--border-radius-md);
}

.btn .bi {
	font-size: var(--icon-size-md);
}

.btn-sm {
	padding: 0.375rem 0.75rem;
	font-size: 0.875rem;
}

.btn-sm .bi {
	font-size: var(--icon-size-sm);
}

.btn-lg {
	padding: 0.75rem 1.5rem;
	font-size: 1.125rem;
}

.btn-lg .bi {
	font-size: var(--icon-size-lg);
}

/* Room Type Item */
.room-type-item {
	transition: all var(--transition-normal);
	border-left: 4px solid #0d6efd !important;
	border-radius: var(--border-radius-md);
	background: var(--card-bg, #ffffff);
	padding: var(--spacing-lg);
	margin-bottom: var(--spacing-md);
}

.room-type-item:hover {
	box-shadow: var(--shadow-md) !important;
	transform: translateX(4px);
}

.room-type-item .bi {
	font-size: var(--icon-size-lg);
}

/* Room Item */
.room-item {
	transition: all var(--transition-fast);
	border-left: 3px solid #198754 !important;
	border-radius: var(--border-radius-sm);
	background: var(--card-bg, #ffffff);
	padding: var(--spacing-sm);
}

.room-item:hover {
	background-color: #f8f9fa !important;
	transform: translateX(3px);
	box-shadow: var(--shadow-sm);
}

.room-item .bi {
	font-size: var(--icon-size-sm);
}

/* Rooms Section */
.rooms-section {
	background: linear-gradient(to bottom, #f8f9fa 0%, #ffffff 100%);
	border-radius: var(--border-radius-md);
	padding: var(--spacing-md);
}

.rooms-section .bi {
	font-size: var(--icon-size-md);
}

/* Generator Section */
#gen_preview_{} {
	font-family: 'Courier New', monospace;
	font-size: 0.875rem;
}

.bg-info.bg-opacity-10 {
	border-radius: var(--border-radius-md);
}

/* Badges Modern */
.badge {
	display: inline-flex;
	align-items: center;
	gap: 0.25rem;
	padding: 0.35em 0.65em;
	font-size: 0.875em;
	font-weight: 500;
	border-radius: var(--border-radius-sm);
}

.badge .bi {
	font-size: 0.875em;
}

.badge-animate {
	animation: pulse 0.5s ease-in-out;
}

/* Input Groups */
.input-group-text {
	display: flex;
	align-items: center;
	gap: var(--spacing-xs);
	font-weight: 500;
}

.input-group-text .bi {
	font-size: var(--icon-size-sm);
}

/* Dropdown Items */
.dropdown-item {
	display: flex;
	align-items: center;
	gap: var(--spacing-sm);
	padding: 0.5rem 1rem;
	transition: all var(--transition-fast);
}

.dropdown-item .bi {
	font-size: var(--icon-size-md);
	width: 1.25rem;
}

.dropdown-item:hover {
	background-color: #f8f9fa;
	transform: translateX(4px);
}

/* Card Stats */
.card-body .bi {
	font-size: var(--icon-size-xl);
}

/* Section Headers */
h6 .bi, h5 .bi, h4 .bi {
	font-size: 1.25em;
	vertical-align: middle;
}

/* Alert Messages */
.alert {
	display: flex;
	align-items: center;
	gap: var(--spacing-sm);
	border-radius: var(--border-radius-md);
	padding: var(--spacing-md);
}

.alert .bi {
	font-size: var(--icon-size-lg);
	flex-shrink: 0;
}

/* Animations */
@keyframes pulse {
	0%, 100% {
		transform: scale(1);
	}
	50% {
		transform: scale(1.1);
	}
}

@keyframes slideIn {
	from {
		opacity: 0;
		transform: translateY(-10px);
	}
	to {
		opacity: 1;
		transform: translateY(0);
	}
}

.room-type-item, .room-item {
	animation: slideIn 0.3s ease-out;
}

/* Scrollbar Modern */
.rooms-section [style*="overflow-y: auto"]::-webkit-scrollbar {
	width: 8px;
}

.rooms-section [style*="overflow-y: auto"]::-webkit-scrollbar-track {
	background: #f1f1f1;
	border-radius: 10px;
	margin: 4px;
}

.rooms-section [style*="overflow-y: auto"]::-webkit-scrollbar-thumb {
	background: linear-gradient(180deg, #0d6efd 0%, #0a58ca 100%);
	border-radius: 10px;
	transition: background var(--transition-fast);
}

.rooms-section [style*="overflow-y: auto"]::-webkit-scrollbar-thumb:hover {
	background: linear-gradient(180deg, #0a58ca 0%, #084298 100%);
}

/* Form Controls Modern */
.form-control:focus,
.form-select:focus {
	border-color: #0d6efd;
	box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
}

/* Card Header with Gradient */
.card-header {
	border-top-left-radius: var(--border-radius-lg);
	border-top-right-radius: var(--border-radius-lg);
}

/* Text with Icons */
.text-primary .bi,
.text-success .bi,
.text-danger .bi,
.text-warning .bi,
.text-info .bi {
	font-size: inherit;
}

/* Modern Spacing */
.mb-2 { margin-bottom: var(--spacing-xs) !important; }
.mb-3 { margin-bottom: var(--spacing-md) !important; }
.mb-4 { margin-bottom: var(--spacing-lg) !important; }
.p-2 { padding: var(--spacing-xs) !important; }
.p-3 { padding: var(--spacing-md) !important; }
.p-4 { padding: var(--spacing-lg) !important; }

/* Responsive Icon Sizes */
@media (max-width: 768px) {
	:root {
		--icon-size-xl: 1.75rem;
		--icon-size-lg: 1.25rem;
		--icon-size-md: 1.125rem;
	}
}
</style>