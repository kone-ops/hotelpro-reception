<x-app-layout>
	<x-slot name="header">{{ $hotel->name }}</x-slot>
	
	<div class="row">
		<div class="col-md-8">
			<div class="card border-0 shadow-sm mb-4">
				<div class="card-header bg-transparent">
					<div class="d-flex justify-content-between align-items-center">
						<h5 class="mb-0">Informations générales</h5>
						<div class="btn-group">
							<a href="{{ route('super.hotels.design', $hotel) }}" class="btn btn-sm btn-outline-info">
								<i class="bi bi-palette me-1"></i>Design & Formulaire
							</a>
							<button class="btn btn-sm btn-outline-primary" onclick="editHotel({{ $hotel->id }})">
								<i class="bi bi-pencil me-1"></i>Modifier
							</button>
						</div>
					</div>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col-md-6">
							@if($hotel->logo_url)
								<div class="mb-3">
									<strong>Logo:</strong><br>
									<img src="{{ $hotel->logo_url }}" alt="Logo {{ $hotel->name }}" 
									     style="max-height: 80px; max-width: 150px; border-radius: 8px; border: 2px solid #e9ecef;">
								</div>
							@endif
							<div class="mb-3">
								<strong>Nom:</strong><br>
								<span class="text-muted">{{ $hotel->name }}</span>
							</div>
							<div class="mb-3">
								<strong>Adresse:</strong><br>
								<span class="text-muted">{{ $hotel->address ?? 'Non renseignée' }}</span>
							</div>
							<div class="mb-3">
								<strong>Ville:</strong><br>
								<span class="text-muted">{{ $hotel->city ?? 'Non renseignée' }}</span>
							</div>
						</div>
						<div class="col-md-6">
							<div class="mb-3">
								<strong>Pays:</strong><br>
								<span class="text-muted">{{ $hotel->country ?? 'Non renseigné' }}</span>
							</div>
							<div class="mb-3">
								<strong>Couleurs:</strong><br>
								<div class="d-flex gap-2">
									@if($hotel->primary_color)
										<span class="badge" style="background-color: {{ $hotel->primary_color }}">Primaire</span>
									@endif
									@if($hotel->secondary_color)
										<span class="badge" style="background-color: {{ $hotel->secondary_color }}">Secondaire</span>
									@endif
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			
			<div class="card border-0 shadow-sm mb-4">
				<div class="card-header bg-transparent">
					<h5 class="mb-0">Configuration Oracle</h5>
				</div>
				<div class="card-body">
					@if($hotel->oracle_dsn)
						<div class="row">
							<div class="col-md-6">
								<strong>DSN:</strong><br>
								<code>{{ $hotel->oracle_dsn }}</code>
							</div>
							<div class="col-md-6">
								<strong>Utilisateur:</strong><br>
								<code>{{ $hotel->oracle_username }}</code>
							</div>
						</div>
					@else
						<p class="text-muted">Aucune configuration Oracle</p>
					@endif
				</div>
			</div>
			
			<div class="card border-0 shadow-sm">
				<div class="card-header bg-transparent">
					<h5 class="mb-0">Réservations récentes</h5>
				</div>
				<div class="card-body">
					@if($hotel->reservations->count() > 0)
						<div class="table-responsive">
							<table class="table table-sm">
								<thead>
									<tr>
										<th>Date</th>
										<th>Client</th>
										<th>Statut</th>
									</tr>
								</thead>
								<tbody>
									@foreach($hotel->reservations as $reservation)
										<tr>
											<td>{{ $reservation->created_at->format('d/m/Y H:i') }}</td>
											<td>{{ $reservation->data['nom'] ?? 'N/A' }}</td>
											<td>
												<span class="badge bg-{{ $reservation->status === 'validated' ? 'success' : 'warning' }}">
													{{ ucfirst($reservation->status) }}
												</span>
											</td>
										</tr>
									@endforeach
								</tbody>
							</table>
						</div>
					@else
						<p class="text-muted">Aucune réservation</p>
					@endif
				</div>
			</div>
		</div>
		
		<div class="col-md-4">
			<div class="card border-0 shadow-sm mb-4">
				<div class="card-header bg-transparent">
					<h6 class="mb-0">QR Code</h6>
				</div>
				<div class="card-body text-center">
					<div class="mb-3">{!! $qrSvg !!}</div>
					<p class="small text-muted">Formulaire public</p>
					<button class="btn btn-sm btn-outline-primary" onclick="window.print()">
						<i class="bi bi-printer me-1"></i>Imprimer
					</button>
				</div>
			</div>
			
			<div class="card border-0 shadow-sm mb-4">
				<div class="card-header bg-transparent">
					<h6 class="mb-0">Statistiques</h6>
				</div>
				<div class="card-body">
					<div class="row text-center">
						<div class="col-6">
							<div class="text-primary">
								<i class="bi bi-people" style="font-size: 1.5rem;"></i>
								<div class="small">{{ $hotel->users->count() }}</div>
								<div class="small text-muted">Utilisateurs</div>
							</div>
						</div>
						<div class="col-6">
							<div class="text-success">
								<i class="bi bi-calendar-check" style="font-size: 1.5rem;"></i>
								<div class="small">{{ $hotel->reservations->count() }}</div>
								<div class="small text-muted">Réservations</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			
			<div class="card border-0 shadow-sm">
				<div class="card-header bg-transparent">
					<h6 class="mb-0">Actions rapides</h6>
				</div>
				<div class="card-body">
					<div class="d-grid gap-2">
						<a href="{{ route('super.users.index', ['hotel' => $hotel->id]) }}" class="btn btn-outline-primary btn-sm">
							<i class="bi bi-people me-1"></i>Gérer les utilisateurs
						</a>
						<a href="{{ route('super.forms.index', ['hotel' => $hotel->id]) }}" class="btn btn-outline-secondary btn-sm">
							<i class="bi bi-gear me-1"></i>Configurer le formulaire
						</a>
						<a href="{{ route('super.reports.hotel', $hotel) }}" class="btn btn-outline-info btn-sm">
							<i class="bi bi-graph-up me-1"></i>Rapports
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</x-app-layout>

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
let editRoomTypeCounter = 0;
const editRoomCounters = {};

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
			new bootstrap.Modal(document.getElementById('editHotelModal')).show();
		})
		.catch(error => {
			console.error('Erreur détaillée:', error);
			alert('Erreur: ' + error.message);
		});
}

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
</script>
