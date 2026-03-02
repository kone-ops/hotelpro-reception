<x-app-layout>
    <x-slot name="header">Modifier le pré-enregistrement #{{ $reservation->id }}</x-slot>
    
    <div class="row">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Modifier les informations</h5>
                        <a href="{{ route('hotel.reservations.show', $reservation) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Retour
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('hotel.reservations.update', $reservation) }}" method="POST" id="editForm">
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
                            
                            <div class="mb-3">
                                <label class="form-label">Type de Chambre <span class="text-danger">*</span></label>
                                <select name="type_chambre" class="form-select" required>
                                    <option value="">-- Sélectionner --</option>
                                    @foreach($roomTypes as $roomType)
                                        <option value="{{ $roomType->name }}" {{ old('type_chambre', $reservation->data['type_chambre'] ?? '') === $roomType->name ? 'selected' : '' }}>
                                            {{ $roomType->name }} - {{ number_format($roomType->price, 0, ',', ' ') }} FCFA/nuit
                                        </option>
                                    @endforeach
                                </select>
                                @error('type_chambre')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
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
                        
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('hotel.reservations.show', $reservation) }}" class="btn btn-outline-secondary">
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

