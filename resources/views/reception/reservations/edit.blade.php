<x-app-layout>
    <x-slot name="header">Modifier l'enregistrement #{{ $reservation->id }}</x-slot>
    
    <div class="row">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Modifier les informations</h5>
                        <a href="{{ route('reception.reservations.show', $reservation->id) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Retour
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('reception.reservations.update', $reservation->id) }}" method="POST" id="editForm">
                        @csrf
                        @method('PUT')
                        
                        <!-- Section 1: Type d'enregistrement -->
                        <div class="border rounded p-4 mb-4">
                            <h5 class="mb-3"><i class="bi bi-bookmark-check me-2"></i>Type d'enregistrement</h5>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="type_reservation" id="typeIndividuel" value="individuel" 
                                            {{ (old('type_reservation', $reservation->data['type_reservation'] ?? 'individuel') === 'individuel') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="typeIndividuel">
                                            <strong>Individuel</strong>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="type_reservation" id="typeGroupe" value="groupe"
                                            {{ (old('type_reservation', $reservation->data['type_reservation'] ?? 'individuel') === 'groupe') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="typeGroupe">
                                            <strong>Groupe</strong>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="groupFields" class="row" style="display: {{ (old('type_reservation', $reservation->data['type_reservation'] ?? 'individuel') === 'groupe') ? 'flex' : 'none' }};">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nom du Groupe</label>
                                    <input type="text" name="nom_groupe" class="form-control" value="{{ old('nom_groupe', $reservation->data['nom_groupe'] ?? '') }}" placeholder="Ex: Entreprise ABC">
                                    @error('nom_groupe')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Code Groupe</label>
                                    <input type="text" name="code_groupe" class="form-control" value="{{ old('code_groupe', $reservation->data['code_groupe'] ?? '') }}" placeholder="Demander à la réception">
                                    @error('code_groupe')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Section 2: Informations Personnelles -->
                        <div class="border rounded p-4 mb-4">
                            <h5 class="mb-3"><i class="bi bi-person me-2"></i>Informations Personnelles</h5>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Type de Pièce d'Identité <span class="text-danger">*</span></label>
                                    <select name="type_piece_identite" class="form-select" required>
                                        <option value="">-- Sélectionner --</option>
                                        <option value="CNI" {{ old('type_piece_identite', $reservation->data['type_piece_identite'] ?? '') === 'CNI' ? 'selected' : '' }}>CNI</option>
                                        <option value="Passeport" {{ old('type_piece_identite', $reservation->data['type_piece_identite'] ?? '') === 'Passeport' ? 'selected' : '' }}>Passeport</option>
                                        <option value="Permis de conduire" {{ old('type_piece_identite', $reservation->data['type_piece_identite'] ?? '') === 'Permis de conduire' ? 'selected' : '' }}>Permis de conduire</option>
                                        <option value="Autre" {{ old('type_piece_identite', $reservation->data['type_piece_identite'] ?? '') === 'Autre' ? 'selected' : '' }}>Autre</option>
                                    </select>
                                    @error('type_piece_identite')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Numéro de Pièce d'Identité <span class="text-danger">*</span></label>
                                    <input type="text" name="numero_piece_identite" class="form-control" value="{{ old('numero_piece_identite', $reservation->data['numero_piece_identite'] ?? '') }}" required>
                                    @error('numero_piece_identite')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nom <span class="text-danger">*</span></label>
                                    <input type="text" name="nom" class="form-control" value="{{ old('nom', $reservation->data['nom'] ?? '') }}" required>
                                    @error('nom')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Prénom(s) <span class="text-danger">*</span></label>
                                    <input type="text" name="prenom" class="form-control" value="{{ old('prenom', $reservation->data['prenom'] ?? '') }}" required>
                                    @error('prenom')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Sexe <span class="text-danger">*</span></label>
                                    <select name="sexe" class="form-select" required>
                                        <option value="">-- Sélectionner --</option>
                                        <option value="Masculin" {{ old('sexe', $reservation->data['sexe'] ?? '') === 'Masculin' ? 'selected' : '' }}>Masculin</option>
                                        <option value="Féminin" {{ old('sexe', $reservation->data['sexe'] ?? '') === 'Féminin' ? 'selected' : '' }}>Féminin</option>
                                    </select>
                                    @error('sexe')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Date de Naissance <span class="text-danger">*</span></label>
                                    <input type="date" name="date_naissance" class="form-control" value="{{ old('date_naissance', $reservation->data['date_naissance'] ?? '') }}" required max="2008-01-01">
                                    @error('date_naissance')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Lieu de Naissance <span class="text-danger">*</span></label>
                                    <input type="text" name="lieu_naissance" class="form-control" value="{{ old('lieu_naissance', $reservation->data['lieu_naissance'] ?? '') }}" required>
                                    @error('lieu_naissance')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nationalité <span class="text-danger">*</span></label>
                                    <input type="text" name="nationalite" class="form-control" value="{{ old('nationalite', $reservation->data['nationalite'] ?? '') }}" required>
                                    @error('nationalite')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Section 3: Coordonnées -->
                        <div class="border rounded p-4 mb-4">
                            <h5 class="mb-3"><i class="bi bi-envelope me-2"></i>Coordonnées</h5>
                            
                            <div class="mb-3">
                                <label class="form-label">Adresse Complète</label>
                                <textarea name="adresse" class="form-control" rows="2">{{ old('adresse', $reservation->data['adresse'] ?? '') }}</textarea>
                                @error('adresse')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Téléphone <span class="text-danger">*</span></label>
                                    <input type="tel" name="telephone" class="form-control" value="{{ old('telephone', $reservation->data['telephone'] ?? '') }}" required>
                                    @error('telephone')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control" value="{{ old('email', $reservation->data['email'] ?? '') }}" required>
                                    @error('email')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Profession</label>
                                    <input type="text" name="profession" class="form-control" value="{{ old('profession', $reservation->data['profession'] ?? '') }}">
                                    @error('profession')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Section 4: Informations du Séjour -->
                        <div class="border rounded p-4 mb-4">
                            <h5 class="mb-3"><i class="bi bi-calendar-check me-2"></i>Informations du Séjour</h5>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Venant de</label>
                                    <input type="text" name="venant_de" class="form-control" value="{{ old('venant_de', $reservation->data['venant_de'] ?? '') }}" placeholder="Ex: Paris, Dakar, New York...">
                                    @error('venant_de')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Heure d'Arrivée</label>
                                    <input type="time" name="heure_arrivee" class="form-control" value="{{ old('heure_arrivee', $reservation->data['heure_arrivee'] ?? '') }}">
                                    @error('heure_arrivee')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Date d'Arrivée <span class="text-danger">*</span></label>
                                    <input type="date" name="date_arrivee" id="dateArrivee" class="form-control" value="{{ old('date_arrivee', $reservation->data['date_arrivee'] ?? '') }}" required>
                                    @error('date_arrivee')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Date de Départ <span class="text-danger">*</span></label>
                                    <input type="date" name="date_depart" id="dateDepart" class="form-control" value="{{ old('date_depart', $reservation->data['date_depart'] ?? '') }}" required>
                                    @error('date_depart')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Nombre de Nuits</label>
                                    <input type="number" name="nombre_nuits" id="nombreNuits" class="form-control" value="{{ old('nombre_nuits', $reservation->data['nombre_nuits'] ?? '') }}" readonly>
                                    @error('nombre_nuits')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Nombre d'Adultes <span class="text-danger">*</span></label>
                                    <input type="number" name="nombre_adultes" id="nombreAdultes" class="form-control" value="{{ old('nombre_adultes', $reservation->data['nombre_adultes'] ?? 1) }}" min="1" max="20" required>
                                    @error('nombre_adultes')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Nombre d'Enfants</label>
                                    <input type="number" name="nombre_enfants" class="form-control" value="{{ old('nombre_enfants', $reservation->data['nombre_enfants'] ?? 0) }}" min="0" max="20">
                                    @error('nombre_enfants')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <!-- Affichage de la chambre actuelle -->
                            @if($reservation->room)
                                <div class="alert alert-info d-flex align-items-center mb-3">
                                    <i class="bi bi-info-circle-fill me-2" style="font-size: 1.2rem;"></i>
                                    <div>
                                        <strong>Chambre actuellement assignée :</strong>
                                        <span class="badge bg-primary fs-6 ms-2">
                                            <i class="bi bi-door-closed me-1"></i>Chambre {{ $reservation->room->room_number }}
                                        </span>
                                    </div>
                                </div>
                            @endif
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bi bi-house-door me-1"></i>Type de Chambre <span class="text-danger">*</span>
                                    </label>
                                    <select name="room_type_id" id="roomTypeSelect" class="form-select" required>
                                        <option value="">-- Sélectionner un type de chambre --</option>
                                    @foreach($roomTypes as $roomType)
                                            <option value="{{ $roomType->id }}" 
                                                    data-name="{{ $roomType->name }}"
                                                    data-price="{{ $roomType->price }}"
                                                    data-capacity="{{ $roomType->capacity ?? '' }}"
                                                    data-rooms-count="{{ $roomType->rooms->count() }}"
                                                    {{ old('room_type_id', $reservation->room_type_id ?? '') == $roomType->id ? 'selected' : '' }}>
                                            {{ $roomType->name }} - {{ number_format($roomType->price, 0, ',', ' ') }} FCFA/nuit
                                                @if($roomType->capacity)
                                                    ({{ $roomType->capacity }} pers.)
                                                @endif
                                                - {{ $roomType->rooms->count() }} chambre(s) disponible(s)
                                        </option>
                                    @endforeach
                                </select>
                                    <!-- Champ caché pour compatibilité -->
                                    <input type="hidden" name="type_chambre" id="typeChambreHidden" value="{{ old('type_chambre', $reservation->data['type_chambre'] ?? '') }}">
                                @error('type_chambre')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                                    @error('room_type_id')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bi bi-door-closed me-1"></i>Numéro de Chambre
                                        <span class="badge bg-secondary badge-sm">Optionnel</span>
                                    </label>
                                    <select name="room_id" id="roomSelect" class="form-select">
                                        <option value="">-- Aucune chambre spécifique --</option>
                                    </select>
                                    <small class="text-muted">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Laissez vide pour qu'une chambre soit attribuée automatiquement
                                    </small>
                                    @error('room_id')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Préférences</label>
                                <textarea name="preferences" class="form-control" rows="3">{{ old('preferences', $reservation->data['preferences'] ?? '') }}</textarea>
                                <small class="text-muted">Lit supplémentaire, étage, vue, etc.</small>
                                @error('preferences')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- Accompagnants -->
                            <div id="accompagnantsSection" style="display: {{ (old('nombre_adultes', $reservation->data['nombre_adultes'] ?? 1) >= 2) ? 'block' : 'none' }};">
                                <hr class="my-4">
                                <h6 class="mb-3"><i class="bi bi-people me-2"></i>Accompagnants supplémentaires</h6>
                                <div id="accompagnantsContainer"></div>
                            </div>
                        </div>
                        
                        {{-- Afficher les champs personnalisés --}}
                        @php
                            $customFields = $formConfig->getCustomFields();
                        @endphp
                        @if($customFields->count() > 0)
                        <div class="border rounded p-4 mb-4">
                            <h5 class="mb-3"><i class="bi bi-list-ul me-2"></i>Champs personnalisés</h5>
                            <div class="row">
                                @foreach($customFields as $field)
                                    @if($field->active)
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">{{ $field->label }} {!! $formConfig->getRequiredStar($field->key) !!}</label>
                                            @if($field->type === 'textarea')
                                                <textarea name="{{ $field->key }}" class="form-control" rows="3" {{ $formConfig->getRequiredAttribute($field->key) }}>{{ old($field->key, $reservation->data[$field->key] ?? '') }}</textarea>
                                            @elseif($field->type === 'select')
                                                <select name="{{ $field->key }}" class="form-select" {{ $formConfig->getRequiredAttribute($field->key) }}>
                                                    <option value="">-- Sélectionner --</option>
                                                    @if($field->options)
                                                        @foreach($field->options as $option)
                                                            <option value="{{ $option }}" {{ old($field->key, $reservation->data[$field->key] ?? '') == $option ? 'selected' : '' }}>{{ $option }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            @elseif($field->type === 'radio')
                                                <div>
                                                    @if($field->options)
                                                        @foreach($field->options as $index => $option)
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio" name="{{ $field->key }}" id="{{ $field->key }}_{{ $index }}" value="{{ $option }}" {{ old($field->key, $reservation->data[$field->key] ?? '') == $option ? 'checked' : '' }} {{ $formConfig->getRequiredAttribute($field->key) }}>
                                                                <label class="form-check-label" for="{{ $field->key }}_{{ $index }}">{{ $option }}</label>
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            @elseif($field->type === 'checkbox')
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="{{ $field->key }}" id="{{ $field->key }}" value="1" {{ old($field->key, $reservation->data[$field->key] ?? false) ? 'checked' : '' }} {{ $formConfig->getRequiredAttribute($field->key) }}>
                                                    <label class="form-check-label" for="{{ $field->key }}">{{ $field->label }}</label>
                                                </div>
                                            @else
                                                <input type="{{ $field->type }}" name="{{ $field->key }}" class="form-control" value="{{ old($field->key, $reservation->data[$field->key] ?? '') }}" {{ $formConfig->getRequiredAttribute($field->key) }}>
                                            @endif
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        @endif
                        
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('reception.reservations.show', $reservation->id) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-1"></i>Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i>Enregistrer les modifications
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const typeIndividuel = document.getElementById('typeIndividuel');
            const typeGroupe = document.getElementById('typeGroupe');
            const groupFields = document.getElementById('groupFields');
            const dateArrivee = document.getElementById('dateArrivee');
            const dateDepart = document.getElementById('dateDepart');
            const nombreNuits = document.getElementById('nombreNuits');
            const nombreAdultes = document.getElementById('nombreAdultes');
            const accompagnantsSection = document.getElementById('accompagnantsSection');
            const accompagnantsContainer = document.getElementById('accompagnantsContainer');
            const roomTypeSelect = document.getElementById('roomTypeSelect');
            const roomSelect = document.getElementById('roomSelect');
            const typeChambreHidden = document.getElementById('typeChambreHidden');
            
            // Données des chambres disponibles (passées depuis le contrôleur)
            const availableRooms = @json($rooms ?? []);
            
            // Données des types de chambres avec leurs chambres
            const roomTypesData = @json($roomTypes);
            
            // ID de la chambre actuellement assignée (s'il y en a une)
            const currentRoomId = {{ $reservation->room_id ?? 'null' }};
            
            console.log('Chambres disponibles:', availableRooms);
            console.log('Types de chambres:', roomTypesData);
            console.log('Chambre actuelle:', currentRoomId);
            
            // Fonction pour charger les chambres disponibles selon le type
            function loadAvailableRooms(roomTypeId) {
                roomSelect.innerHTML = '<option value="">🏨 Aucune chambre spécifique (attribution automatique)</option>';
                
                if (!roomTypeId) {
                    console.log('Aucun type de chambre sélectionné');
                    return;
                }
                
                console.log('Chargement des chambres pour le type:', roomTypeId);
                
                // Filtrer les chambres pour ce type
                const filteredRooms = availableRooms.filter(room => 
                    room.room_type_id == roomTypeId
                );
                
                console.log('Chambres filtrées:', filteredRooms);
                
                if (filteredRooms.length > 0) {
                    // Ajouter un séparateur
                    const separator = document.createElement('option');
                    separator.disabled = true;
                    separator.textContent = `──────── ${filteredRooms.length} chambre(s) disponible(s) ────────`;
                    roomSelect.appendChild(separator);
                    
                    // Trier les chambres par numéro
                    filteredRooms.sort((a, b) => {
                        const numA = a.room_number || a.number || 0;
                        const numB = b.room_number || b.number || 0;
                        return numA - numB;
                    });
                    
                    filteredRooms.forEach(room => {
                        const option = document.createElement('option');
                        option.value = room.id;
                        
                        // Récupérer le numéro de chambre (priorité à room_number)
                        const roomNumber = room.room_number || room.number || 'N/A';
                        
                        // Icône et texte
                        let roomText = `🚪 Chambre ${roomNumber}`;
                        if (room.floor) {
                            roomText += ` - 📍 Étage ${room.floor}`;
                        }
                        
                        // Marquer la chambre actuelle
                        if (room.id === currentRoomId) {
                            roomText += ' ✓ (Actuelle)';
                            option.selected = true;
                        }
                        
                        option.textContent = roomText;
                        roomSelect.appendChild(option);
                    });
                    
                    console.log(`${filteredRooms.length} chambre(s) ajoutée(s) au sélecteur`);
                } else {
                    const noRoomsOption = document.createElement('option');
                    noRoomsOption.value = '';
                    noRoomsOption.textContent = '❌ Aucune chambre disponible pour ce type';
                    noRoomsOption.disabled = true;
                    roomSelect.appendChild(noRoomsOption);
                    console.log('Aucune chambre disponible pour ce type');
                }
            }
            
            // Gérer le changement de type de chambre
            roomTypeSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const roomTypeName = selectedOption.getAttribute('data-name');
                
                // Mettre à jour le champ caché pour compatibilité
                if (typeChambreHidden) {
                    typeChambreHidden.value = roomTypeName || '';
                }
                
                // Charger les chambres disponibles
                loadAvailableRooms(this.value);
            });
            
            // Initialiser au chargement si un type est déjà sélectionné
            if (roomTypeSelect.value) {
                loadAvailableRooms(roomTypeSelect.value);
            }
            
            // Gérer l'affichage des champs groupe
            function toggleGroupFields() {
                if (typeGroupe.checked) {
                    groupFields.style.display = 'flex';
                } else {
                    groupFields.style.display = 'none';
                }
            }
            
            typeIndividuel.addEventListener('change', toggleGroupFields);
            typeGroupe.addEventListener('change', toggleGroupFields);
            
            // Calculer le nombre de nuits
            function calculateNights() {
                if (dateArrivee.value && dateDepart.value) {
                    const arrival = new Date(dateArrivee.value);
                    const departure = new Date(dateDepart.value);
                    const diffTime = Math.abs(departure - arrival);
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                    nombreNuits.value = diffDays;
                }
            }
            
            dateArrivee.addEventListener('change', calculateNights);
            dateDepart.addEventListener('change', calculateNights);
            
            // Gérer les accompagnants
            function updateAccompagnants() {
                const nbAdultes = parseInt(nombreAdultes.value) || 1;
                
                if (nbAdultes >= 2) {
                    accompagnantsSection.style.display = 'block';
                    const nbAccompagnants = nbAdultes - 1;
                    
                    accompagnantsContainer.innerHTML = '';
                    
                    @if(isset($reservation->data['accompagnants']) && is_array($reservation->data['accompagnants']))
                        const existingAccompagnants = @json($reservation->data['accompagnants']);
                    @else
                        const existingAccompagnants = [];
                    @endif
                    
                    for (let i = 1; i <= nbAccompagnants; i++) {
                        const accompagnantDiv = document.createElement('div');
                        accompagnantDiv.className = 'row mb-2';
                        
                        const existingNom = existingAccompagnants[i-1]?.nom || '';
                        const existingPrenom = existingAccompagnants[i-1]?.prenom || '';
                        
                        accompagnantDiv.innerHTML = `
                            <div class="col-md-6">
                                <input type="text" name="accompagnant_nom_${i}" class="form-control" 
                                    placeholder="Nom de l'accompagnant ${i}" value="${existingNom}">
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="accompagnant_prenom_${i}" class="form-control" 
                                    placeholder="Prénom de l'accompagnant ${i}" value="${existingPrenom}">
                            </div>
                        `;
                        accompagnantsContainer.appendChild(accompagnantDiv);
                    }
                } else {
                    accompagnantsSection.style.display = 'none';
                }
            }
            
            nombreAdultes.addEventListener('change', updateAccompagnants);
            
            // Initialiser
            calculateNights();
            updateAccompagnants();
        });
    </script>
</x-app-layout>

