<x-app-layout>
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="mb-0">
                <i class="bi bi-palette me-2"></i>Configuration Design & Formulaire - {{ $hotel->name }}
            </h2>
            <a href="{{ route('super.hotels.show', $hotel) }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Retour
            </a>
        </div>

    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        <form action="{{ route('super.hotels.design.update', $hotel) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <!-- Onglets -->
            <ul class="nav nav-tabs mb-4" id="configTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="design-tab" data-bs-toggle="tab" data-bs-target="#design" type="button" role="tab">
                        <i class="bi bi-palette me-2"></i>Design
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="form-fields-tab" data-bs-toggle="tab" data-bs-target="#form-fields" type="button" role="tab">
                        <i class="bi bi-list-check me-2"></i>Configuration Formulaire
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="custom-fields-tab" data-bs-toggle="tab" data-bs-target="#custom-fields" type="button" role="tab">
                        <i class="bi bi-plus-circle me-2"></i>Champs Personnalisés
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="configTabsContent">
                <!-- Onglet Design -->
                <div class="tab-pane fade show active" id="design" role="tabpanel">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent">
                            <h5 class="mb-0"><i class="bi bi-palette me-2"></i>Personnalisation du Design</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Logo -->
                                <div class="col-md-6 mb-4">
                                    <label class="form-label fw-bold">Logo de l'hôtel</label>
                                    <div class="mb-3">
                                        @if($hotel->logo_url)
                                            <img src="{{ $hotel->logo_url }}" alt="Logo actuel" 
                                                 class="img-thumbnail" style="max-height: 150px; max-width: 200px;">
                                        @else
                                            <div class="border rounded p-3 text-center text-muted" style="height: 150px; display: flex; align-items: center; justify-content: center;">
                                                <div>Aucun logo</div>
                                            </div>
                                        @endif
                                    </div>
                                    <input type="file" name="logo" class="form-control" accept="image/jpeg,image/jpg,image/png,image/svg+xml">
                                    <small class="text-muted">Formats acceptés : JPG, PNG, SVG - Max 2MB</small>
                                </div>

                                <!-- Couleurs -->
                                <div class="col-md-6 mb-4">
                                    <label class="form-label fw-bold">Couleur Primaire</label>
                                    <div class="input-group">
                                        <input type="color" name="primary_color" class="form-control form-control-color" 
                                               value="{{ $hotel->primary_color ?? '#1a4b8c' }}" 
                                               title="Couleur primaire">
                                        <input type="text" class="form-control" 
                                               value="{{ $hotel->primary_color ?? '#1a4b8c' }}" 
                                               pattern="^#[0-9A-Fa-f]{6}$" 
                                               placeholder="#1a4b8c"
                                               onchange="this.previousElementSibling.value = this.value">
                                    </div>
                                    <small class="text-muted">Couleur principale du thème</small>
                                </div>

                                <div class="col-md-6 mb-4">
                                    <label class="form-label fw-bold">Couleur Secondaire</label>
                                    <div class="input-group">
                                        <input type="color" name="secondary_color" class="form-control form-control-color" 
                                               value="{{ $hotel->secondary_color ?? '#2563a8' }}" 
                                               title="Couleur secondaire">
                                        <input type="text" class="form-control" 
                                               value="{{ $hotel->secondary_color ?? '#2563a8' }}" 
                                               pattern="^#[0-9A-Fa-f]{6}$" 
                                               placeholder="#2563a8"
                                               onchange="this.previousElementSibling.value = this.value">
                                    </div>
                                    <small class="text-muted">Couleur secondaire du thème</small>
                                </div>

                                <!-- Aperçu -->
                                <div class="col-12">
                                    <label class="form-label fw-bold">Aperçu du design</label>
                                    <div class="border rounded p-4" style="background: linear-gradient(135deg, {{ $hotel->primary_color ?? '#1a4b8c' }} 0%, {{ $hotel->secondary_color ?? '#2563a8' }} 100%); color: white; min-height: 150px;">
                                        <div class="text-center">
                                            @if($hotel->logo_url)
                                                <img src="{{ $hotel->logo_url }}" alt="Logo" 
                                                     style="max-height: 60px; max-width: 120px; background: white; padding: 10px; border-radius: 8px; margin-bottom: 15px;">
                                            @endif
                                            <h4 class="mb-0">{{ $hotel->name }}</h4>
                                            <p class="mb-0 mt-2">Formulaire d'enregistrement</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Onglet Configuration Formulaire -->
                <div class="tab-pane fade" id="form-fields" role="tabpanel">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent">
                            <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>Configuration des Champs du Formulaire</h5>
                            <small class="text-muted">Activez/désactivez les champs et définissez s'ils sont obligatoires</small>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover table-striped align-middle mb-0 super-admin-table" aria-label="Configuration des champs du formulaire">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col" style="width: 40%;"><i class="bi bi-input-cursor me-1 text-primary"></i>Champ</th>
                                            <th scope="col" class="text-center" style="width: 30%;"><i class="bi bi-eye me-1 text-primary"></i>Visible</th>
                                            <th scope="col" class="text-center" style="width: 30%;"><i class="bi bi-asterisk me-1 text-primary"></i>Obligatoire</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($availableFields as $fieldKey => $fieldInfo)
                                            @php
                                                $config = $formFieldConfig[$fieldKey] ?? [];
                                                $visible = $config['visible'] ?? $fieldInfo['default_visible'];
                                                $required = $config['required'] ?? $fieldInfo['default_required'];
                                            @endphp
                                            <tr>
                                                <td>
                                                    <strong>{{ $fieldInfo['label'] }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $fieldKey }}</small>
                                                </td>
                                                <td class="text-center">
                                                    <div class="form-check form-switch d-inline-block">
                                                        <input type="hidden" name="form_fields[{{ $fieldKey }}][visible]" id="input_visible_{{ $fieldKey }}" value="{{ $visible ? '1' : '0' }}">
                                                        <input class="form-check-input design-config-checkbox" type="checkbox" data-field="{{ $fieldKey }}" data-type="visible" id="visible_{{ $fieldKey }}" {{ $visible ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="visible_{{ $fieldKey }}"></label>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <div class="form-check form-switch d-inline-block">
                                                        <input type="hidden" name="form_fields[{{ $fieldKey }}][required]" id="input_required_{{ $fieldKey }}" value="{{ $required ? '1' : '0' }}">
                                                        <input class="form-check-input design-config-checkbox" type="checkbox" data-field="{{ $fieldKey }}" data-type="required" id="required_{{ $fieldKey }}" {{ $required ? 'checked' : '' }} onchange="toggleRequired(this, '{{ $fieldKey }}')">
                                                        <label class="form-check-label" for="required_{{ $fieldKey }}"></label>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="alert alert-info mt-3">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>Note :</strong> Un champ doit être visible pour pouvoir être obligatoire. 
                                Si un champ est masqué, il ne peut pas être obligatoire.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Onglet Champs Personnalisés -->
                <div class="tab-pane fade" id="custom-fields" role="tabpanel">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Gestion des Champs Personnalisés</h5>
                                <small class="text-muted">Créez, modifiez ou supprimez des champs personnalisés pour le formulaire</small>
                            </div>
                            <div class="btn-group">
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="selectAllFieldsBtn" onclick="toggleSelectAllFields()">
                                    <i class="bi bi-check-square me-2"></i><span id="selectAllFieldsText">Tout sélectionner</span>
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" id="deleteSelectedBtn" onclick="deleteSelectedFields()" style="display: none;">
                                    <i class="bi bi-trash me-1"></i>Supprimer la sélection (<span id="selectedCount">0</span>)
                                </button>
                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#fieldModal" onclick="openFieldModal()">
                                    <i class="bi bi-plus-circle me-1"></i>Nouveau Champ
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            @if($customFields->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover table-striped align-middle mb-0 super-admin-table" aria-label="Champs personnalisés du formulaire">
                                        <thead class="table-light">
                                            <tr>
                                                <th scope="col" width="40" class="ps-3">
                                                    <label class="visually-hidden" for="selectAllFields">Tout sélectionner</label>
                                                    <input type="checkbox" id="selectAllFields" onchange="toggleSelectAll(this)" aria-label="Tout sélectionner">
                                                </th>
                                                <th scope="col"><i class="bi bi-folder me-1 text-primary"></i>Section</th>
                                                <th scope="col"><i class="bi bi-sort-numeric-down me-1 text-primary"></i>Position</th>
                                                <th scope="col"><i class="bi bi-key me-1 text-primary"></i>Clé</th>
                                                <th scope="col"><i class="bi bi-tag me-1 text-primary"></i>Libellé</th>
                                                <th scope="col"><i class="bi bi-input-cursor me-1 text-primary"></i>Type</th>
                                                <th scope="col" class="text-center"><i class="bi bi-eye me-1 text-primary"></i>Visible</th>
                                                <th scope="col" class="text-center"><i class="bi bi-asterisk me-1 text-primary"></i>Obligatoire</th>
                                                <th scope="col" class="text-center" width="120">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($customFields as $field)
                                                @php
                                                    $sections = [
                                                        0 => 'Recherche Client',
                                                        1 => 'Type d\'enregistrement',
                                                        2 => 'Informations Personnelles',
                                                        3 => 'Coordonnées',
                                                        4 => 'Informations Séjour',
                                                        5 => 'Validation',
                                                        6 => 'Signature'
                                                    ];
                                                @endphp
                                                <tr data-field-id="{{ $field->id }}" data-field-data="{{ json_encode([
                                                    'id' => $field->id,
                                                    'key' => $field->key,
                                                    'label' => $field->label,
                                                    'type' => $field->type,
                                                    'position' => $field->position,
                                                    'section' => $field->section ?? 2,
                                                    'required' => $field->required,
                                                    'active' => $field->active,
                                                    'options' => $field->options
                                                ]) }}">
                                                    <td>
                                                        <input type="checkbox" class="field-checkbox" value="{{ $field->id }}" data-field-label="{{ $field->label }}" onchange="updateDeleteButton()">
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info">{{ $sections[$field->section ?? 2] ?? 'Section ' . ($field->section ?? 2) }}</span>
                                                    </td>
                                                    <td>{{ $field->position }}</td>
                                                    <td><code>{{ $field->key }}</code></td>
                                                    <td><strong>{{ $field->label }}</strong></td>
                                                    <td>
                                                        <span class="badge bg-secondary">{{ ucfirst($field->type) }}</span>
                                                        @if($field->options)
                                                            <br><small class="text-muted">{{ is_array($field->options) ? implode(', ', $field->options) : $field->options }}</small>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        @if($field->active)
                                                            <span class="badge bg-success"><i class="bi bi-eye"></i> Oui</span>
                                                        @else
                                                            <span class="badge bg-secondary"><i class="bi bi-eye-slash"></i> Non</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        @if($field->required)
                                                            <span class="badge bg-danger"><i class="bi bi-asterisk"></i> Oui</span>
                                                        @else
                                                            <span class="badge bg-secondary">Non</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="btn-group btn-group-sm">
                                                            <button type="button" class="btn btn-outline-primary" 
                                                                    onclick="editField({{ $field->id }})" 
                                                                    title="Modifier">
                                                                <i class="bi bi-pencil"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-outline-danger" 
                                                                    onclick="deleteField({{ $field->id }}, '{{ $field->label }}')" 
                                                                    title="Supprimer">
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
                                    <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                                    <h5 class="text-muted mt-3">Aucun champ personnalisé</h5>
                                    <p class="text-muted">Créez votre premier champ personnalisé pour le formulaire</p>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#fieldModal" onclick="openFieldModal()">
                                        <i class="bi bi-plus-circle me-1"></i>Créer un champ
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Boutons d'action -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('super.hotels.show', $hotel) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i>Annuler
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>Enregistrer les modifications
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Modal pour créer/modifier un champ personnalisé -->
    <div class="modal fade" id="fieldModal" tabindex="-1" aria-labelledby="fieldModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="fieldForm" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="field_method" value="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="fieldModalLabel">
                            <i class="bi bi-plus-circle me-2"></i>Nouveau Champ Personnalisé
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Clé du champ <span class="text-danger">*</span></label>
                                <input type="text" name="key" id="field_key" class="form-control" 
                                       pattern="[a-z0-9_]+" 
                                       placeholder="ex: numero_passeport" 
                                       required>
                                <small class="text-muted">Utilisez uniquement des lettres minuscules, chiffres et underscores</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Libellé <span class="text-danger">*</span></label>
                                <input type="text" name="label" id="field_label" class="form-control" 
                                       placeholder="ex: Numéro de passeport" 
                                       required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Type de champ <span class="text-danger">*</span></label>
                                <select name="type" id="field_type" class="form-select" required onchange="toggleOptionsField()">
                                    <option value="text">Texte</option>
                                    <option value="email">Email</option>
                                    <option value="tel">Téléphone</option>
                                    <option value="number">Nombre</option>
                                    <option value="date">Date</option>
                                    <option value="textarea">Zone de texte</option>
                                    <option value="select">Liste déroulante</option>
                                    <option value="radio">Boutons radio</option>
                                    <option value="checkbox">Case à cocher</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Section <span class="text-danger">*</span></label>
                                <select name="section" id="field_section" class="form-select" required onchange="updateInsertionPoints()">
                                    <option value="0">Section 0: Recherche Client</option>
                                    <option value="1">Section 1: Type d'enregistrement</option>
                                    <option value="2" selected>Section 2: Informations Personnelles</option>
                                    <option value="3">Section 3: Coordonnées</option>
                                    <option value="4">Section 4: Informations du Séjour</option>
                                    <option value="5">Section 5: Validation</option>
                                    <option value="6">Section 6: Signature</option>
                                </select>
                                <small class="text-muted">Choisissez dans quelle section afficher ce champ</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Position d'insertion <span class="text-danger">*</span></label>
                                <select name="position" id="field_position" class="form-select" required>
                                    <!-- Les options seront générées dynamiquement par JavaScript -->
                                </select>
                                <small class="text-muted">Choisissez après quel champ afficher ce champ personnalisé</small>
                            </div>
                        </div>
                        <div class="row" id="optionsRow" style="display: none;">
                            <div class="col-12 mb-3">
                                <label class="form-label fw-bold">Options (séparées par des virgules)</label>
                                <input type="text" name="options" id="field_options" class="form-control" 
                                       placeholder="ex: Option 1, Option 2, Option 3">
                                <small class="text-muted">Uniquement pour les types "Liste déroulante" et "Boutons radio"</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="required" id="field_required" value="1">
                                    <label class="form-check-label" for="field_required">
                                        <strong>Champ obligatoire</strong>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="active" id="field_active" value="1" checked>
                                    <label class="form-check-label" for="field_active">
                                        <strong>Champ visible</strong>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i>Annuler
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Points d'insertion pour chaque section
        const insertionPoints = @json($insertionPoints);
        
        // Fonction pour mettre à jour les points d'insertion selon la section sélectionnée
        function updateInsertionPoints() {
            const section = parseInt(document.getElementById('field_section').value);
            const positionSelect = document.getElementById('field_position');
            
            // Vider les options existantes
            positionSelect.innerHTML = '';
            
            // Ajouter les points d'insertion pour cette section
            if (insertionPoints[section]) {
                insertionPoints[section].forEach(point => {
                    const option = document.createElement('option');
                    option.value = point.position;
                    option.textContent = point.label;
                    positionSelect.appendChild(option);
                });
            }
        }
        
        // Gérer l'ouverture du modal pour créer ou modifier
        function openFieldModal(fieldId = null) {
            const modal = document.getElementById('fieldModal');
            const form = document.getElementById('fieldForm');
            const modalTitle = document.getElementById('fieldModalLabel');
            
            // Réinitialiser le formulaire
            form.reset();
            form.action = '{{ route("super.hotels.design.fields.store", $hotel) }}';
            form.method = 'POST';
            document.getElementById('field_active').checked = true;
            document.getElementById('field_section').value = 2; // Section par défaut
            
            // Mettre à jour les points d'insertion pour la section par défaut
            updateInsertionPoints();
            
            if (fieldId) {
                // Mode modification - charger les données du champ
                modalTitle.innerHTML = '<i class="bi bi-pencil me-2"></i>Modifier le Champ';
                
                // Récupérer les données du champ depuis le tableau
                const row = document.querySelector(`tr[data-field-id="${fieldId}"]`);
                if (!row) {
                    // Si on ne trouve pas dans le DOM, faire une requête AJAX
                    fetch(`/super/hotels/{{ $hotel->id }}/design/fields/${fieldId}`)
                        .then(response => response.json())
                        .then(data => {
                            populateFieldForm(data);
                        })
                        .catch(error => {
                            console.error('Erreur:', error);
                            alert('Erreur lors du chargement du champ');
                        });
                } else {
                    // Utiliser les données du DOM (on pourrait aussi faire une requête AJAX)
                    // Pour l'instant, on va utiliser une approche simple avec les données du tableau
                }
            } else {
                // Mode création
                modalTitle.innerHTML = '<i class="bi bi-plus-circle me-2"></i>Nouveau Champ Personnalisé';
            }
            
            toggleOptionsField();
        }
        
        // Fonction pour remplir le formulaire avec les données d'un champ
        function populateFieldForm(field) {
            document.getElementById('field_key').value = field.key || '';
            document.getElementById('field_label').value = field.label || '';
            document.getElementById('field_type').value = field.type || 'text';
            
            const section = field.section !== undefined ? field.section : 2;
            document.getElementById('field_section').value = section;
            
            // Mettre à jour les points d'insertion pour la section
            updateInsertionPoints();
            
            // Sélectionner la position (chercher la plus proche si exacte n'existe pas)
            const positionSelect = document.getElementById('field_position');
            const fieldPosition = parseFloat(field.position || 0);
            
            // Chercher l'option la plus proche
            let closestOption = null;
            let closestDiff = Infinity;
            for (let i = 0; i < positionSelect.options.length; i++) {
                const optionValue = parseFloat(positionSelect.options[i].value);
                const diff = Math.abs(optionValue - fieldPosition);
                if (diff < closestDiff) {
                    closestDiff = diff;
                    closestOption = positionSelect.options[i];
                }
            }
            
            if (closestOption) {
                positionSelect.value = closestOption.value;
            } else if (fieldPosition > 0) {
                // Si aucune option ne correspond, créer une option temporaire
                const option = document.createElement('option');
                option.value = fieldPosition;
                option.textContent = `Position ${fieldPosition} (personnalisée)`;
                option.selected = true;
                positionSelect.appendChild(option);
            }
            
            document.getElementById('field_required').checked = field.required || false;
            document.getElementById('field_active').checked = field.active !== false;
            
            if (field.options && Array.isArray(field.options)) {
                document.getElementById('field_options').value = field.options.join(', ');
            }
            
            toggleOptionsField();
        }
        
        // Afficher/masquer le champ options selon le type
        function toggleOptionsField() {
            const type = document.getElementById('field_type').value;
            const optionsRow = document.getElementById('optionsRow');
            const optionsInput = document.getElementById('field_options');
            
            if (type === 'select' || type === 'radio') {
                optionsRow.style.display = 'block';
                optionsInput.required = true;
            } else {
                optionsRow.style.display = 'none';
                optionsInput.required = false;
                optionsInput.value = '';
            }
        }
        
        // Gérer la soumission du formulaire pour la modification
        @if($customFields->count() > 0)
            @foreach($customFields as $field)
                // Ajouter un attribut data-field-id aux lignes du tableau
                document.addEventListener('DOMContentLoaded', function() {
                    const rows = document.querySelectorAll('tbody tr');
                    rows.forEach((row, index) => {
                        if (index < {{ $customFields->count() }}) {
                            const field = @json($field);
                            row.setAttribute('data-field-id', field.id);
                            row.setAttribute('data-field-data', JSON.stringify({
                                id: field.id,
                                key: field.key,
                                label: field.label,
                                type: field.type,
                                position: field.position,
                                required: field.required,
                                active: field.active,
                                options: field.options
                            }));
                        }
                    });
                });
            @endforeach
        @endif
        
        // Fonction pour modifier un champ existant
        function editField(fieldId) {
            const row = document.querySelector(`tr[data-field-id="${fieldId}"]`);
            if (!row) {
                alert('Champ introuvable');
                return;
            }
            
            const fieldData = JSON.parse(row.getAttribute('data-field-data'));
            const modal = document.getElementById('fieldModal');
            const form = document.getElementById('fieldForm');
            const modalTitle = document.getElementById('fieldModalLabel');
            const methodInput = document.getElementById('field_method');
            
            modalTitle.innerHTML = '<i class="bi bi-pencil me-2"></i>Modifier le Champ';
            form.action = `{{ route('super.hotels.design.fields.update', [$hotel, ':id']) }}`.replace(':id', fieldId);
            methodInput.value = 'PUT';
            
            populateFieldForm(fieldData);
            if (document.activeElement && document.activeElement.blur) document.activeElement.blur();
            modal.addEventListener('shown.bs.modal', function onShown() {
                modal.removeEventListener('shown.bs.modal', onShown);
                const focusTarget = modal.querySelector('button[data-bs-dismiss="modal"]') || modal.querySelector('.btn-primary') || modal;
                if (focusTarget && typeof focusTarget.focus === 'function') focusTarget.focus();
            });
            new bootstrap.Modal(modal).show();
        }
        
        // Réinitialiser le formulaire quand le modal est fermé
        document.getElementById('fieldModal').addEventListener('hidden.bs.modal', function () {
            const form = document.getElementById('fieldForm');
            const methodInput = document.getElementById('field_method');
            form.reset();
            form.action = '{{ route("super.hotels.design.fields.store", $hotel) }}';
            methodInput.value = 'POST';
            document.getElementById('field_active').checked = true;
            document.getElementById('field_section').value = 2; // Section par défaut
            updateInsertionPoints(); // Mettre à jour les points d'insertion
            document.getElementById('fieldModalLabel').innerHTML = '<i class="bi bi-plus-circle me-2"></i>Nouveau Champ Personnalisé';
        });
        
        // Fonction pour supprimer un champ
        function deleteField(fieldId, fieldLabel) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer le champ "' + fieldLabel + '" ?\n\nCette action est irréversible.')) {
                return;
            }
            
            // Créer un formulaire de suppression dynamique
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("super.hotels.design.fields.destroy", [$hotel, ":id"]) }}'.replace(':id', fieldId);
            
            // Ajouter le token CSRF
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            form.appendChild(csrfInput);
            
            // Ajouter la méthode DELETE
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);
            
            // Ajouter le formulaire au body et le soumettre
            document.body.appendChild(form);
            form.submit();
        }
        
        // Fonction pour sélectionner/désélectionner tous les champs
        function toggleSelectAll(checkbox) {
            const checkboxes = document.querySelectorAll('.field-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = checkbox.checked;
            });
            updateDeleteButton();
        }
        
        // Fonction alternative pour le bouton "Tout sélectionner"
        window.toggleSelectAllFields = function() {
            const checkbox = document.getElementById('selectAllFields');
            if (checkbox) {
                checkbox.checked = !checkbox.checked;
                toggleSelectAll(checkbox);
            }
        }
        
        // Fonction pour mettre à jour le bouton de suppression multiple
        function updateDeleteButton() {
            const selectedCheckboxes = document.querySelectorAll('.field-checkbox:checked');
            const deleteBtn = document.getElementById('deleteSelectedBtn');
            const countSpan = document.getElementById('selectedCount');
            
            if (selectedCheckboxes.length > 0) {
                deleteBtn.style.display = 'inline-block';
                countSpan.textContent = selectedCheckboxes.length;
            } else {
                deleteBtn.style.display = 'none';
                countSpan.textContent = '0';
            }
            
            // Mettre à jour le checkbox "Tout sélectionner"
            const selectAllCheckbox = document.getElementById('selectAllFields');
            const allCheckboxes = document.querySelectorAll('.field-checkbox');
            if (allCheckboxes.length > 0) {
                selectAllCheckbox.checked = selectedCheckboxes.length === allCheckboxes.length;
            }
        }
        
        // Fonction pour supprimer les champs sélectionnés
        function deleteSelectedFields() {
            const selectedCheckboxes = document.querySelectorAll('.field-checkbox:checked');
            
            if (selectedCheckboxes.length === 0) {
                alert('Veuillez sélectionner au moins un champ à supprimer.');
                return;
            }
            
            // Récupérer les IDs et labels des champs sélectionnés
            const fieldIds = Array.from(selectedCheckboxes).map(cb => cb.value);
            const fieldLabels = Array.from(selectedCheckboxes).map(cb => {
                return cb.getAttribute('data-field-label');
            });
            
            const message = 'Êtes-vous sûr de vouloir supprimer ' + selectedCheckboxes.length + ' champ(s) ?\n\n' +
                          'Champs à supprimer :\n' + fieldLabels.join('\n') + '\n\n' +
                          'Cette action est irréversible.';
            
            if (!confirm(message)) {
                return;
            }
            
            // Créer un formulaire pour la suppression multiple
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("super.hotels.design.fields.destroy-multiple", $hotel) }}';
            form.style.display = 'none';
            
            // Ajouter le token CSRF
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            form.appendChild(csrfInput);
            
            // Ajouter les IDs des champs à supprimer
            fieldIds.forEach((id, index) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'field_ids[' + index + ']';
                input.value = id;
                form.appendChild(input);
            });
            
            // Ajouter le formulaire au body et le soumettre
            document.body.appendChild(form);
            form.submit();
        }
        // Synchroniser les inputs de couleur
        document.querySelectorAll('input[type="color"]').forEach(colorInput => {
            const textInput = colorInput.nextElementSibling;
            colorInput.addEventListener('input', function() {
                textInput.value = this.value;
            });
            textInput.addEventListener('input', function() {
                if (/^#[0-9A-Fa-f]{6}$/i.test(this.value)) {
                    colorInput.value = this.value;
                }
            });
        });

        // Désactiver "obligatoire" si "visible" est désactivé
        // Synchroniser les checkboxes de config avec les champs cachés (une seule valeur envoyée = pas de tableau)
        document.querySelectorAll('.design-config-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const fieldKey = this.getAttribute('data-field');
                const type = this.getAttribute('data-type');
                const hidden = document.getElementById('input_' + type + '_' + fieldKey);
                if (hidden) hidden.value = this.checked ? '1' : '0';
            });
        });

        function toggleRequired(checkbox, fieldKey) {
            const visibleCheckbox = document.getElementById('visible_' + fieldKey);
            if (!visibleCheckbox.checked && checkbox.checked) {
                checkbox.checked = false;
                const hiddenRequired = document.getElementById('input_required_' + fieldKey);
                if (hiddenRequired) hiddenRequired.value = '0';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Attention',
                        text: 'Un champ doit être visible pour pouvoir être obligatoire.',
                        confirmButtonText: 'OK'
                    });
                } else {
                    alert('Un champ doit être visible pour pouvoir être obligatoire.');
                }
            }
        }

        // Désactiver "obligatoire" quand "visible" est désactivé
        document.querySelectorAll('.design-config-checkbox[data-type="visible"]').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                if (!this.checked) {
                    const fieldKey = this.getAttribute('data-field');
                    const requiredCheckbox = document.getElementById('required_' + fieldKey);
                    if (requiredCheckbox) {
                        requiredCheckbox.checked = false;
                        const hiddenRequired = document.getElementById('input_required_' + fieldKey);
                        if (hiddenRequired) hiddenRequired.value = '0';
                    }
                }
            });
        });

        // Mettre à jour l'aperçu en temps réel
        document.querySelectorAll('input[name="primary_color"], input[name="secondary_color"]').forEach(input => {
            input.addEventListener('input', function() {
                const primaryColor = document.querySelector('input[name="primary_color"]').value;
                const secondaryColor = document.querySelector('input[name="secondary_color"]').value;
                const preview = document.querySelector('.border.rounded.p-4');
                if (preview) {
                    preview.style.background = `linear-gradient(135deg, ${primaryColor} 0%, ${secondaryColor} 100%)`;
                }
            });
        });

        // Confirmation avant création d'un champ personnalisé
        document.getElementById('fieldForm').addEventListener('submit', function(e) {
            const method = document.getElementById('field_method').value;
            const key = document.getElementById('field_key').value;
            const label = document.getElementById('field_label').value;
            
            // Si c'est une création (POST), demander confirmation
            if (method === 'POST') {
                if (!key || !label) {
                    e.preventDefault();
                    alert('Veuillez remplir au moins la clé et le libellé du champ.');
                    return false;
                }
                
                const confirmed = confirm(
                    'Êtes-vous sûr de vouloir créer ce champ personnalisé ?\n\n' +
                    'Clé: ' + key + '\n' +
                    'Libellé: ' + label + '\n\n' +
                    'Le champ sera ajouté au formulaire d\'enregistrement.'
                );
                
                if (!confirmed) {
                    e.preventDefault();
                    return false;
                }
            }
        });
    </script>
</x-app-layout>

