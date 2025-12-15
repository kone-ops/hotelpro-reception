<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Réservation - {{ $hotel->name }}</title>
    <link href="{{ asset('assets/vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/intl-tel-input/css/intlTelInput.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/select2/css/select2-bootstrap-5-theme.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/sweetalert2/sweetalert2.min.css') }}">
    
    <style>
        :root {
            --primary-color: {{ $hotel->primary_color ?? '#1a4b8c' }};
            --secondary-color: {{ $hotel->secondary_color ?? '#2563a8' }};
        }
        body {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 100vh;
            padding: 40px 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .form-container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .form-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            margin-bottom: 30px;
        }
        
        .form-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        
        .logo-container {
            margin-bottom: 20px;
            display: inline-block;
        }
        
        .hotel-logo {
            max-height: 100px;
            max-width: 200px;
            height: auto;
            width: auto;
            object-fit: contain;
            filter: drop-shadow(0 5px 15px rgba(0,0,0,0.3));
            background: white;
            padding: 15px;
            border-radius: 15px;
        }
        
        .logo-icon {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }
        
        .logo-icon i {
            font-size: 40px;
            color: var(--primary-color);
        }
        
        .form-header h1 {
            font-size: 32px;
            font-weight: 700;
            margin: 0;
        }
        
        .form-header p {
            margin: 10px 0 0;
            opacity: 0.95;
            font-size: 16px;
        }
        
        .form-body {
            padding: 40px;
        }
        
        .section-card {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 25px;
            border: 2px solid #e9ecef;
        }
        
        .section-header {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid var(--primary-color);
        }
        
        .section-number {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 20px;
            margin-right: 15px;
            flex-shrink: 0;
        }
        
        .section-title {
            font-size: 20px;
            font-weight: 700;
            color: #333;
            margin: 0;
        }
        
        .section-subtitle {
            font-size: 14px;
            color: #666;
            margin: 5px 0 0;
        }
        
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .form-label .required {
            color: #dc3545;
            margin-left: 3px;
        }
        
        .form-control, .form-select {
            border: 2px solid #dee2e6;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 15px;
            transition: all 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(26, 75, 140, 0.15);
        }
        
        .input-group-text {
            border: 2px solid #dee2e6;
            border-radius: 10px 0 0 10px;
            background: #f8f9fa;
            font-weight: 600;
        }
        
        .input-group .form-control {
            border-left: 0;
            border-radius: 0 10px 10px 0;
        }
        
        /* Radio buttons pour type de réservation */
        .reservation-type-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .type-card {
            border: 3px solid #dee2e6;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: white;
        }
        
        .type-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .type-card.active {
            border-color: var(--primary-color);
            background: #f0f7ff;
        }
        
        .type-card input[type="radio"] {
            display: none;
        }
        
        .type-card-icon {
            font-size: 50px;
            margin-bottom: 15px;
            display: block;
            color: #666;
            transition: color 0.3s;
        }
        
        .type-card.active .type-card-icon {
            color: var(--primary-color);
        }
        
        .type-card-title {
            font-weight: 700;
            font-size: 18px;
            margin: 0;
            color: #333;
        }
        
        /* Upload/Photo buttons */
        .upload-method-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .upload-method-btn {
            padding: 15px;
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            background: white;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }
        
        .upload-method-btn:hover {
            border-color: var(--primary-color);
            background: #f0f7ff;
        }
        
        .upload-method-btn.active {
            border-color: var(--primary-color);
            border-style: solid;
            background: #f0f7ff;
        }
        
        .upload-method-btn i {
            font-size: 30px;
            display: block;
            margin-bottom: 8px;
            color: var(--primary-color);
        }
        
        /* Masquer caméra sur mobile et tablette */
        @media (max-width: 991px) {
            .camera-option {
                display: none !important;
            }
            
            .upload-method-buttons {
                grid-template-columns: 1fr;
            }
        }
        
        /* Signature Canvas */
        .signature-pad {
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            background: white;
            cursor: crosshair;
            touch-action: none;
            width: 100%;
        }
        
        .signature-controls {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        
        /* Camera preview */
        .camera-preview {
            position: relative;
            background: #000;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 10px;
        }
        
        .camera-preview video,
        .camera-preview canvas {
            width: 100%;
            display: block;
        }
        
        .capture-button {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: white;
            border: 5px solid var(--primary-color);
            cursor: pointer;
            transition: all 0.2s;
            z-index: 10;
        }
        
        .capture-button:hover {
            transform: translateX(-50%) scale(1.1);
        }
        
        /* Buttons */
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 10px;
            padding: 14px 30px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        
        .btn-outline-primary {
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            border-radius: 10px;
            padding: 12px 25px;
            font-weight: 600;
        }
        
        .btn-outline-primary:hover {
            background: var(--primary-color);
            color: white;
        }
        
        /* Preview Modal */
        .preview-modal .modal-content {
            border-radius: 20px;
        }
        
        .preview-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 15px;
        }
        
        .preview-section h6 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 10px;
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 5px;
        }
        
        .preview-row {
            display: grid;
            grid-template-columns: 150px 1fr;
            padding: 8px 0;
            border-bottom: 1px solid #dee2e6;
        }
        
        .preview-row:last-child {
            border-bottom: none;
        }
        
        .preview-label {
            font-weight: 600;
            color: #666;
        }
        
        .preview-value {
            color: #333;
        }
        
        /* Conditional fields */
        .conditional-field {
            display: none;
        }
        
        .conditional-field.show {
            display: block;
        }
        
        /* Loading */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
        
        .loading-overlay.show {
            display: flex;
        }
        
        .spinner-border {
            width: 60px;
            height: 60px;
            border-width: 5px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .form-body {
                padding: 20px;
            }
            
            .section-card {
                padding: 20px;
            }
            
            .reservation-type-cards {
                grid-template-columns: 1fr;
            }
        }
        
        /* Select2 customization */
        .select2-container--bootstrap-5 .select2-selection {
            border: 2px solid #dee2e6;
            border-radius: 10px;
            min-height: 46px;
        }
        
        .select2-container--bootstrap-5.select2-container--focus .select2-selection {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(26, 75, 140, 0.15);
        }
        
        /* Int Tel Input */
        .iti {
            width: 100%;
        }
        
        .iti__flag-container {
            border-radius: 10px 0 0 10px;
        }
    </style>
</head>
<body data-hotel-id="{{ $hotel->id }}">
    <div class="form-container">
        <div class="form-card">
            <div class="form-header">
                @if($hotel->logo)
                    <div class="logo-container">
                        <img src="{{ asset('storage/' . $hotel->logo) }}" alt="Logo {{ $hotel->name }}" class="hotel-logo" loading="lazy">
                    </div>
                @else
                    <div class="logo-icon">
                        <i class="bi bi-building"></i>
                    </div>
                @endif
                <h1>{{ $hotel->name }}</h1>
                <p><i class="bi bi-geo-alt"></i> Formulaire de Réservation</p>
            </div>
            
            <div class="form-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Erreurs détectées :</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @php
                    // Fonction helper pour afficher un champ personnalisé
                    function renderCustomField($field, $formConfig) {
                        $colClass = $field->type === 'textarea' ? 'col-md-12' : 'col-md-6';
                        $html = '<div class="' . $colClass . ' mb-3">';
                        $html .= '<label class="form-label">';
                        $html .= htmlspecialchars($field->label);
                        $html .= $formConfig->getRequiredStar($field->key);
                        $html .= '</label>';
                        
                        if ($field->type === 'textarea') {
                            $html .= '<textarea name="' . htmlspecialchars($field->key) . '" class="form-control" rows="3" ' . $formConfig->getRequiredAttribute($field->key) . '></textarea>';
                        } elseif ($field->type === 'select') {
                            $html .= '<select name="' . htmlspecialchars($field->key) . '" class="form-select" ' . $formConfig->getRequiredAttribute($field->key) . '>';
                            $html .= '<option value="">-- Sélectionner --</option>';
                            if ($field->options) {
                                foreach ($field->options as $option) {
                                    $html .= '<option value="' . htmlspecialchars($option) . '">' . htmlspecialchars($option) . '</option>';
                                }
                            }
                            $html .= '</select>';
                        } elseif ($field->type === 'radio') {
                            $html .= '<div>';
                            if ($field->options) {
                                foreach ($field->options as $index => $option) {
                                    $html .= '<div class="form-check">';
                                    $html .= '<input class="form-check-input" type="radio" name="' . htmlspecialchars($field->key) . '" id="' . htmlspecialchars($field->key) . '_' . $index . '" value="' . htmlspecialchars($option) . '" ' . $formConfig->getRequiredAttribute($field->key) . '>';
                                    $html .= '<label class="form-check-label" for="' . htmlspecialchars($field->key) . '_' . $index . '">' . htmlspecialchars($option) . '</label>';
                                    $html .= '</div>';
                                }
                            }
                            $html .= '</div>';
                        } elseif ($field->type === 'checkbox') {
                            $html .= '<div class="form-check form-switch">';
                            $html .= '<input class="form-check-input" type="checkbox" name="' . htmlspecialchars($field->key) . '" id="' . htmlspecialchars($field->key) . '" value="1" ' . $formConfig->getRequiredAttribute($field->key) . '>';
                            $html .= '<label class="form-check-label" for="' . htmlspecialchars($field->key) . '">' . htmlspecialchars($field->label) . '</label>';
                            $html .= '</div>';
                        } else {
                            $html .= '<input type="' . htmlspecialchars($field->type) . '" name="' . htmlspecialchars($field->key) . '" class="form-control" ' . $formConfig->getRequiredAttribute($field->key) . '>';
                        }
                        
                        $html .= '</div>';
                        return $html;
                    }
                    
                    // Fonction pour obtenir les champs personnalisés à une position spécifique
                    function getCustomFieldsAtPosition($customFields, $section, $position) {
                        return $customFields->where('section', $section)
                            ->where('active', true)
                            ->filter(function($field) use ($position) {
                                $fieldPos = (float) ($field->position ?? 0);
                                // Accepter les champs à cette position exacte ou dans une plage proche (pour flexibilité)
                                return abs($fieldPos - $position) < 0.1;
                            })
                            ->sortBy('position')
                            ->values();
                    }
                    
                    // Fonction pour afficher les champs personnalisés à une position spécifique
                    function renderCustomFieldsAtPosition($customFields, $section, $position, $formConfig) {
                        $fields = getCustomFieldsAtPosition($customFields, $section, $position);
                        if ($fields->count() === 0) return '';
                        
                        $html = '<div class="row">';
                        foreach ($fields as $field) {
                            $html .= renderCustomField($field, $formConfig);
                        }
                        $html .= '</div>';
                        return $html;
                    }
                    
                    // Fonction helper pour afficher les champs personnalisés d'une section (pour les sections simples)
                    function renderCustomFields($customFields, $section, $formConfig) {
                        // Filtrer les champs par section et actifs, puis trier numériquement par position
                        $fields = $customFields->where('section', $section)
                            ->where('active', true)
                            ->sortBy(function($field) {
                                // Trier numériquement par position (0 si null)
                                return (float) ($field->position ?? 0);
                            })
                            ->values(); // Réindexer la collection
                        if ($fields->count() === 0) return '';
                        
                        $html = '<div class="row">';
                        foreach ($fields as $field) {
                            $html .= renderCustomField($field, $formConfig);
                        }
                        $html .= '</div>';
                        return $html;
                    }
                    
                    $customFields = $formConfig->getCustomFields();
                @endphp

                <form id="reservationForm" method="POST" action="{{ route('public.form.store', $hotel) }}" enctype="multipart/form-data">
                    @csrf
                    
                    <!-- SECTION 0: Recherche Client (Optionnel) -->
                    <div class="section-card" style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); border: 2px solid #2196f3;">
                        <div class="section-header">
                            <div class="section-number" style="background: #2196f3;">🔍</div>
                            <div>
                                <h3 class="section-title">Client récurrent ?</h3>
                                <p class="section-subtitle">Recherchez vos informations pour pré-remplir le formulaire</p>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">
                                    <i class="bi bi-search me-2"></i>Rechercher par email, téléphone ou numéro de pièce d'identité
                                </label>
                                <div class="input-group">
                                    <input type="text" 
                                           id="clientSearchInput" 
                                           class="form-control" 
                                           placeholder="Ex: client@example.com ou +33 6 12 34 56 78 ou 123456789"
                                           autocomplete="off">
                                    <button type="button" 
                                            class="btn btn-primary" 
                                            id="clientSearchBtn"
                                            style="background: var(--primary-color); border-color: var(--primary-color);">
                                        <i class="bi bi-search me-1"></i>Rechercher
                                    </button>
                                </div>
                                <small class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Si vous avez déjà réservé dans cet hôtel, vos informations seront pré-remplies automatiquement.
                                </small>
                            </div>
                            <div class="col-md-4 mb-3 d-flex align-items-end">
                                <div id="clientSearchResult" class="w-100"></div>
                            </div>
                        </div>
                        
                        <!-- Champs personnalisés de la section 0 -->
                        {!! renderCustomFields($customFields, 0, $formConfig) !!}
                    </div>
                    
                    <!-- SECTION 1: Type de Réservation -->
                    <div class="section-card">
                        <div class="section-header">
                            <div class="section-number">1</div>
                            <div>
                                <h3 class="section-title">Type de Réservation</h3>
                                <p class="section-subtitle">Choisissez votre type de réservation</p>
                            </div>
                        </div>
                        
                        <div class="reservation-type-cards">
                            <label class="type-card" id="typeIndividuel">
                                <input type="radio" name="type_reservation" value="Individuel" required>
                                <i class="bi bi-person type-card-icon"></i>
                                <h4 class="type-card-title">Individuel</h4>
                            </label>
                            
                            <label class="type-card" id="typeGroupe">
                                <input type="radio" name="type_reservation" value="Groupe">
                                <i class="bi bi-people type-card-icon"></i>
                                <h4 class="type-card-title">Groupe</h4>
                            </label>
                        </div>
                        
                        <!-- Champs Groupe (conditionnels) -->
                        @if($formConfig->isVisible('nom_groupe') || $formConfig->isVisible('code_groupe'))
                        <div id="groupeFields" class="conditional-field">
                            <div class="alert alert-info mb-3">
                                <i class="bi bi-info-circle me-2"></i>
                                Vous avez choisi une réservation de <strong>groupe</strong>. Veuillez renseigner les informations ci-dessous.
                            </div>
                            <div class="row">
                                @if($formConfig->isVisible('nom_groupe'))
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nom du Groupe {!! $formConfig->getRequiredStar('nom_groupe') !!}</label>
                                    <input type="text" name="nom_groupe" id="nomGroupe" class="form-control" placeholder="Ex: Entreprise ABC" {{ $formConfig->getRequiredAttribute('nom_groupe') }}>
                                </div>
                                @endif
                                @if($formConfig->isVisible('code_groupe'))
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Code Groupe {!! $formConfig->getRequiredStar('code_groupe') !!}</label>
                                    <input type="text" name="code_groupe" id="codeGroupe" class="form-control" placeholder="Demander à la réception" {{ $formConfig->getRequiredAttribute('code_groupe') }}>
                                    <small class="text-muted">Code fourni par l'hôtel</small>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif
                        
                        <!-- Champs personnalisés de la section 1 -->
                        {!! renderCustomFields($customFields, 1, $formConfig) !!}
                    </div>

                    <!-- SECTION 2: Informations Personnelles -->
                    <div class="section-card">
                        <div class="section-header">
                            <div class="section-number">2</div>
                            <div>
                                <h3 class="section-title">Informations Personnelles</h3>
                                <p class="section-subtitle">Vos informations d'identité</p>
                            </div>
                        </div>
                        
                        <div class="row">
                            @if($formConfig->isVisible('type_piece_identite'))
                            <div class="col-md-6 mb-4">
                                <label class="form-label">Type de Pièce d'Identité {!! $formConfig->getRequiredStar('type_piece_identite') !!}</label>
                                <select name="type_piece_identite" id="typePieceSelect" class="form-select" {{ $formConfig->getRequiredAttribute('type_piece_identite') }}>
                                    <option value="">-- Sélectionner --</option>
                                    <option value="CNI">Carte Nationale d'Identité (CNI)</option>
                                    <option value="Passeport">Passeport</option>
                                    <option value="Permis">Permis de Conduire</option>
                                    <option value="Autre">Autre</option>
                                </select>
                            </div>
                            @endif
                            @if($formConfig->isVisible('numero_piece_identite'))
                            <div class="col-md-6 mb-4">
                                <label class="form-label">Numéro de Pièce d'Identité {!! $formConfig->getRequiredStar('numero_piece_identite') !!}</label>
                                <input type="text" name="numero_piece_identite" id="numeroPiece" class="form-control" placeholder="Ex: CI123456789" {{ $formConfig->getRequiredAttribute('numero_piece_identite') }}>
                            </div>
                            @endif
                        </div>
                        
                        <!-- Champs personnalisés à la position 2.5 (après Numéro de Pièce d'Identité) -->
                        {!! renderCustomFieldsAtPosition($customFields, 2, 2.5, $formConfig) !!}
                        
                        <!-- Méthode d'upload (PC: Upload OU Caméra, Mobile: Upload seulement) -->
                        @if($formConfig->isVisible('piece_identite_recto') || $formConfig->isVisible('piece_identite_verso'))
                        <div id="uploadMethodSection" class="conditional-field mb-4">
                            <label class="form-label">Comment souhaitez-vous fournir votre pièce d'identité ?</label>
                            <div class="upload-method-buttons">
                                <div class="upload-method-btn" id="uploadMethodBtn">
                                    <i class="bi bi-cloud-upload"></i>
                                    <div><strong>Télécharger</strong></div>
                                    <small class="text-muted">Depuis votre appareil</small>
                                </div>
                                <div class="upload-method-btn camera-option" id="cameraMethodBtn">
                                    <i class="bi bi-camera"></i>
                                    <div><strong>Prendre une photo</strong></div>
                                    <small class="text-muted">Avec votre caméra</small>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        <!-- Upload Files -->
                        @if($formConfig->isVisible('piece_identite_recto') || $formConfig->isVisible('piece_identite_verso'))
                        <div id="uploadFilesSection" class="conditional-field">
                            <div class="row">
                                @if($formConfig->isVisible('piece_identite_recto'))
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Pièce d'Identité (Recto) {!! $formConfig->getRequiredStar('piece_identite_recto') !!}</label>
                                    <input type="file" name="piece_identite_recto" id="fileRecto" class="form-control" accept="image/jpeg,image/png,image/webp,application/pdf,.jpg,.jpeg" {{ $formConfig->getRequiredAttribute('piece_identite_recto') }}>
                                    <small class="text-muted">JPG, PNG, WEBP ou PDF - Max 5MB</small>
                                    <div id="photoRectoPreview" class="mt-2"></div>
                                </div>
                                @endif
                                @if($formConfig->isVisible('piece_identite_verso'))
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Pièce d'Identité (Verso) {!! $formConfig->getRequiredStar('piece_identite_verso') !!}</label>
                                    <input type="file" name="piece_identite_verso" id="fileVerso" class="form-control" accept="image/jpeg,image/png,image/webp,application/pdf,.jpg,.jpeg" {{ $formConfig->getRequiredAttribute('piece_identite_verso') }}>
                                    <small class="text-muted">JPG, PNG, WEBP ou PDF - Max 5MB</small>
                                    <div id="photoVersoPreview" class="mt-2"></div>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif
                        
                        <!-- Camera Capture (Seulement sur PC) -->
                        @if($formConfig->isVisible('piece_identite_recto') || $formConfig->isVisible('piece_identite_verso'))
                        <div id="cameraSection" class="conditional-field">
                            <div class="row mb-3">
                                @if($formConfig->isVisible('piece_identite_recto'))
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Photo Recto {!! $formConfig->getRequiredStar('piece_identite_recto') !!}</label>
                                    <div class="camera-preview" id="cameraPreviewRecto" style="display:none;">
                                        <video id="videoRecto" autoplay playsinline></video>
                                        <button type="button" class="capture-button" id="captureRectoBtn"></button>
                                    </div>
                                    <button type="button" class="btn btn-outline-primary w-100 mb-2" id="startCameraRectoBtn">
                                        <i class="bi bi-camera me-2"></i>Activer la caméra
                                    </button>
                                    <canvas id="canvasRecto" style="display:none;"></canvas>
                                    <input type="hidden" name="photo_recto" id="photoRectoData">
                                    <div id="photoRectoPreview"></div>
                                </div>
                                @endif
                                @if($formConfig->isVisible('piece_identite_verso'))
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Photo Verso {!! $formConfig->getRequiredStar('piece_identite_verso') !!}</label>
                                    <div class="camera-preview" id="cameraPreviewVerso" style="display:none;">
                                        <video id="videoVerso" autoplay playsinline></video>
                                        <button type="button" class="capture-button" id="captureVersoBtn"></button>
                                    </div>
                                    <button type="button" class="btn btn-outline-primary w-100 mb-2" id="startCameraVersoBtn">
                                        <i class="bi bi-camera me-2"></i>Activer la caméra
                                    </button>
                                    <canvas id="canvasVerso" style="display:none;"></canvas>
                                    <input type="hidden" name="photo_verso" id="photoVersoData" {{ $formConfig->getRequiredAttribute('piece_identite_verso') }}>
                                    <div id="photoVersoPreview"></div>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif
                        
                        <!-- Champs personnalisés à la position 3 (après les sections upload/camera) -->
                        {!! renderCustomFieldsAtPosition($customFields, 2, 3, $formConfig) !!}
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                @if($formConfig->isVisible('nom'))
                                <label class="form-label">Nom de Famille {!! $formConfig->getRequiredStar('nom') !!}</label>
                                <input type="text" name="nom" class="form-control" {{ $formConfig->getRequiredAttribute('nom') }}>
                                @endif
                            </div>
                            <div class="col-md-6 mb-3">
                                @if($formConfig->isVisible('prenom'))
                                <label class="form-label">Prénom(s) {!! $formConfig->getRequiredStar('prenom') !!}</label>
                                <input type="text" name="prenom" class="form-control" {{ $formConfig->getRequiredAttribute('prenom') }}>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Champs personnalisés à la position 4 (après Nom/Prénom) -->
                        {!! renderCustomFieldsAtPosition($customFields, 2, 4, $formConfig) !!}
                        
                        <div class="row">
                            @if($formConfig->isVisible('sexe'))
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Sexe {!! $formConfig->getRequiredStar('sexe') !!}</label>
                                <select name="sexe" class="form-select" {{ $formConfig->getRequiredAttribute('sexe') }}>
                                    <option value="">-- Sélectionner --</option>
                                    <option value="Masculin">Masculin</option>
                                    <option value="Féminin">Féminin</option>
                                </select>
                            </div>
                            @endif
                            @if($formConfig->isVisible('date_naissance'))
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Date de Naissance {!! $formConfig->getRequiredStar('date_naissance') !!}</label>
                                <input type="date" name="date_naissance" id="dateNaissance" class="form-control" 
                                       max="{{ now()->subYears(18)->format('Y-m-d') }}" 
                                       min="{{ now()->subYears(120)->format('Y-m-d') }}" 
                                       {{ $formConfig->getRequiredAttribute('date_naissance') }}>
                                <small class="text-muted">Vous devez avoir au moins 18 ans (né(e) avant le {{ now()->subYears(18)->format('d/m/Y') }})</small>
                            </div>
                            @endif
                            @if($formConfig->isVisible('lieu_naissance'))
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Lieu de Naissance {!! $formConfig->getRequiredStar('lieu_naissance') !!}</label>
                                <input type="text" name="lieu_naissance" class="form-control" placeholder="Ville de naissance" {{ $formConfig->getRequiredAttribute('lieu_naissance') }}>
                            </div>
                            @endif
                        </div>
                        
                        <!-- Champs personnalisés à la position 6 (après Sexe/Date/Lieu de naissance) -->
                        {!! renderCustomFieldsAtPosition($customFields, 2, 6, $formConfig) !!}
                        
                        @if($formConfig->isVisible('nationalite'))
                        <div class="mb-3">
                            <label class="form-label">Nationalité {!! $formConfig->getRequiredStar('nationalite') !!}</label>
                            <select name="nationalite" id="nationalite" class="form-select" {{ $formConfig->getRequiredAttribute('nationalite') }}>
                                <option value="">-- Rechercher un pays --</option>
                            </select>
                        </div>
                        @endif
                        
                        <!-- Champs personnalisés à la position 7 (après Nationalité) -->
                        {!! renderCustomFieldsAtPosition($customFields, 2, 7, $formConfig) !!}
                        
                        <!-- Champs personnalisés à la position 8 (fin de section) -->
                        {!! renderCustomFieldsAtPosition($customFields, 2, 8, $formConfig) !!}
                    </div>

                    <!-- SECTION 3: Coordonnées -->
                    <div class="section-card">
                        <div class="section-header">
                            <div class="section-number">3</div>
                            <div>
                                <h3 class="section-title">Coordonnées</h3>
                                <p class="section-subtitle">Comment vous contacter</p>
                            </div>
                        </div>
                        
                        @if($formConfig->isVisible('adresse'))
                        <div class="mb-3">
                            <label class="form-label">Adresse Complète {!! $formConfig->getRequiredStar('adresse') !!}</label>
                            <textarea name="adresse" class="form-control" rows="2" placeholder="Rue, Ville, Pays" {{ $formConfig->getRequiredAttribute('adresse') }}></textarea>
                        </div>
                        @endif
                        
                        <div class="row">
                            @if($formConfig->isVisible('telephone'))
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Téléphone {!! $formConfig->getRequiredStar('telephone') !!}</label>
                                <input type="tel" name="telephone" id="telephone" class="form-control" {{ $formConfig->getRequiredAttribute('telephone') }}>
                            </div>
                            @endif
                            @if($formConfig->isVisible('email'))
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email {!! $formConfig->getRequiredStar('email') !!}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="email" id="emailInput" class="form-control" {{ $formConfig->getRequiredAttribute('email') }}>
                                </div>
                                <div id="emailError" class="text-danger mt-1" style="display: none; font-size: 13px;">
                                    <i class="bi bi-exclamation-circle me-1"></i>
                                    <span id="emailErrorMessage"></span>
                                </div>
                            </div>
                            @endif
                        </div>
                        
                        @if($formConfig->isVisible('profession'))
                        <div class="mb-3">
                            <label class="form-label">Profession {!! $formConfig->getRequiredStar('profession') !!}</label>
                            <input type="text" name="profession" class="form-control" placeholder="Ex: Médecin, Enseignant, Commerçant..." {{ $formConfig->getRequiredAttribute('profession') }}>
                            <small class="text-muted">Votre profession ou activité</small>
                        </div>
                        @endif
                        
                        <!-- Champs personnalisés de la section 3 -->
                        {!! renderCustomFields($customFields, 3, $formConfig) !!}
                    </div>

                    <!-- SECTION 4: Informations du Séjour -->
                    <div class="section-card">
                        <div class="section-header">
                            <div class="section-number">4</div>
                            <div>
                                <h3 class="section-title">Informations du Séjour</h3>
                                <p class="section-subtitle">Détails de votre réservation</p>
                            </div>
                        </div>
                        
                        <div class="row">
                            @if($formConfig->isVisible('date_arrivee'))
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Date d'Arrivée {!! $formConfig->getRequiredStar('date_arrivee') !!}</label>
                                <input type="date" name="date_arrivee" id="dateArrivee" class="form-control" {{ $formConfig->getRequiredAttribute('date_arrivee') }}>
                                <small class="text-muted">À partir d'aujourd'hui</small>
                            </div>
                            @endif
                            @if($formConfig->isVisible('heure_arrivee'))
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Heure d'Arrivée {!! $formConfig->getRequiredStar('heure_arrivee') !!}</label>
                                <input type="time" name="heure_arrivee" class="form-control" {{ $formConfig->getRequiredAttribute('heure_arrivee') }}>
                            </div>
                            @endif
                            @if($formConfig->isVisible('date_depart'))
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Date de Départ {!! $formConfig->getRequiredStar('date_depart') !!}</label>
                                <input type="date" name="date_depart" id="dateDepart" class="form-control" {{ $formConfig->getRequiredAttribute('date_depart') }}>
                                <small class="text-muted">Après la date d'arrivée</small>
                            </div>
                            @endif
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre de Nuits</label>
                                <input type="number" name="nombre_nuits" id="nombreNuits" class="form-control" readonly style="background-color: #e9ecef;">
                                <small class="text-muted">Calculé automatiquement</small>
                            </div>
                            @if($formConfig->isVisible('venant_de'))
                            <div class="col-md-6">
                                <label class="form-label">Venant de {!! $formConfig->getRequiredStar('venant_de') !!}</label>
                                <input type="text" name="venant_de" class="form-control" placeholder="Ex: Paris, Dakar, New York..." {{ $formConfig->getRequiredAttribute('venant_de') }}>
                                <small class="text-muted">Ville ou pays de provenance</small>
                            </div>
                            @endif
                        </div>
                        
                        <div class="row">
                            @if($formConfig->isVisible('nombre_adultes'))
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Nombre d'Adultes {!! $formConfig->getRequiredStar('nombre_adultes') !!}</label>
                                <input type="number" name="nombre_adultes" id="nombreAdultes" class="form-control" min="1" value="1" {{ $formConfig->getRequiredAttribute('nombre_adultes') }}>
                            </div>
                            @endif
                            @if($formConfig->isVisible('nombre_enfants'))
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Nombre d'Enfants {!! $formConfig->getRequiredStar('nombre_enfants') !!}</label>
                                <input type="number" name="nombre_enfants" class="form-control" min="0" value="0" {{ $formConfig->getRequiredAttribute('nombre_enfants') }}>
                            </div>
                            @endif
                            
                            @if($formConfig->isVisible('type_chambre'))
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Type de Chambre {!! $formConfig->getRequiredStar('type_chambre') !!}</label>
                                <select name="type_chambre" id="typeChambre" class="form-select" style="display: none;">
                                    <option value="">-- Sélectionner --</option>
                                    @if($roomTypes && $roomTypes->count() > 0)
                                        @foreach($roomTypes as $roomType)
                                            <option value="{{ $roomType->name }}" data-price="{{ $roomType->price }}" data-capacity="{{ $roomType->capacity }}">
                                                {{ $roomType->name }} - {{ number_format($roomType->price, 0, ',', ' ') }} FCFA/nuit
                                                @if($roomType->capacity)
                                                    ({{ $roomType->capacity }} pers.)
                                                @endif
                                            </option>
                                        @endforeach
                                    @else
                                        <option value="Single">Single</option>
                                        <option value="Double">Double</option>
                                        <option value="Suite">Suite</option>
                                        <option value="Triple">Triple</option>
                                        <option value="Familiale">Familiale</option>
                                    @endif
                                </select>
                                
                                <!-- Nouveau sélecteur avec disponibilité -->
                                <select name="room_type_id" id="roomTypeSelect" class="form-select" {{ $formConfig->getRequiredAttribute('type_chambre') }}>
                                    <option value="">-- Sélectionnez un type de chambre --</option>
                                    @if($roomTypes && $roomTypes->count() > 0)
                                        @foreach($roomTypes as $roomType)
                                            <option value="{{ $roomType->id }}" 
                                                    data-name="{{ $roomType->name }}"
                                                    data-price="{{ $roomType->price }}" 
                                                    data-capacity="{{ $roomType->capacity }}"
                                                    data-rooms-count="{{ $roomType->rooms->count() }}">
                                                {{ $roomType->name }} - {{ number_format($roomType->price, 0, ',', ' ') }} FCFA/nuit
                                                @if($roomType->capacity)
                                                    ({{ $roomType->capacity }} pers.)
                                                @endif
                                                - {{ $roomType->rooms->count() }} chambre(s) disponible(s)
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                <small class="text-muted" id="prixChambreInfo" style="display: none;">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Prix total: <strong id="prixTotal">0</strong> FCFA
                                </small>
                            </div>
                            @endif
                        </div>
                        
                        <!-- Sélection de la chambre spécifique -->
                        @if($formConfig->isVisible('room_id'))
                        <div class="row mb-3" id="roomSelectContainer" style="display: none;">
                            <div class="col-12">
                                <label class="form-label">Numéro de Chambre {!! !$formConfig->isRequired('room_id') ? '<span class="text-muted">(optionnel)</span>' : $formConfig->getRequiredStar('room_id') !!}</label>
                                <select name="room_id" id="roomSelect" class="form-select" {{ $formConfig->getRequiredAttribute('room_id') }}>
                                    <option value="">-- Sélectionnez d'abord un type de chambre --</option>
                                </select>
                                <small class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Vous pouvez choisir une chambre spécifique ou laisser l'hôtel vous en attribuer une
                                </small>
                            </div>
                        </div>
                        @endif
                        
                        <!-- Champs accompagnants (si adultes >= 2) -->
                        <div id="accompagnantsFields" class="conditional-field mt-3">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>Accompagnants supplémentaires</strong><br>
                                <small>Vous pouvez renseigner les noms des autres adultes (optionnel)</small>
                            </div>
                            <div id="accompagnantsContainer"></div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Préférences Particulières</label>
                            <textarea name="preferences" class="form-control" rows="3" placeholder="Lit supplémentaire, étage préféré, vue, allergies, etc."></textarea>
                        </div>
                        
                        <!-- Champs personnalisés de la section 4 -->
                        {!! renderCustomFields($customFields, 4, $formConfig) !!}
                    </div>

                    <!-- SECTION 5: Validation -->
                    <div class="section-card">
                        <div class="section-header">
                            <div class="section-number">5</div>
                            <div>
                                <h3 class="section-title">Validation</h3>
                                <p class="section-subtitle">Confirmation et acceptation</p>
                            </div>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input type="checkbox" name="confirmation_exactitude" class="form-check-input" id="confirmExact" required>
                            <label class="form-check-label" for="confirmExact">
                                <strong>Je confirme l'exactitude des informations <span class="required">*</span></strong>
                            </label>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input type="checkbox" name="acceptation_conditions" class="form-check-input" id="acceptCGU" required>
                            <label class="form-check-label" for="acceptCGU">
                                <strong>J'accepte les conditions de réservation et la politique de confidentialité <span class="required">*</span></strong>
                            </label>
                        </div>
                        
                        <!-- Champs personnalisés de la section 5 -->
                        {!! renderCustomFields($customFields, 5, $formConfig) !!}
                    </div>

                    <!-- SECTION 6: Signature -->
                    @if($formConfig->isVisible('signature'))
                    <div class="section-card">
                        <div class="section-header">
                            <div class="section-number">6</div>
                            <div>
                                <h3 class="section-title">Signature Électronique {!! $formConfig->getRequiredStar('signature') !!}</h3>
                                <p class="section-subtitle">Signez avec votre doigt, stylet ou souris</p>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <canvas id="signaturePad" class="signature-pad" width="800" height="200"></canvas>
                            <input type="hidden" name="signature" id="signatureData" {{ $formConfig->getRequiredAttribute('signature') }}>
                        </div>
                        
                        <div class="signature-controls">
                            <button type="button" class="btn btn-outline-danger" id="clearSignatureBtn">
                                <i class="bi bi-eraser me-2"></i>Effacer
                            </button>
                            <small class="text-muted ms-auto align-self-center">
                                <i class="bi bi-info-circle me-1"></i>Signez dans le cadre ci-dessus
                            </small>
                        </div>
                        
                        <!-- Champs personnalisés de la section 6 -->
                        {!! renderCustomFields($customFields, 6, $formConfig) !!}
                    </div>
                    @endif

                    <!-- Boutons de soumission -->
                    <div class="text-center mt-4">
                        <div class="d-flex gap-3 justify-content-center flex-wrap">
                            <button type="button" class="btn btn-outline-danger btn-lg" id="resetFormBtn">
                                <i class="bi bi-arrow-counterclockwise me-2"></i>Effacer Tout
                            </button>
                            <button type="button" class="btn btn-primary btn-lg" id="showPreviewBtn">
                                <i class="bi bi-eye me-2"></i>Aperçu et Confirmation
                            </button>
                        </div>
                    </div>
                    
                    <div class="text-center mt-3">
                        <small class="text-muted">
                            <i class="bi bi-shield-check me-1"></i>
                            Vos données sont sécurisées et protégées
                        </small>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Prévisualisation -->
    <div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white;">
                    <h5 class="modal-title"><i class="bi bi-eye me-2"></i>Vérifiez vos Informations</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="previewContent">
                    <!-- Contenu généré dynamiquement -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-pencil me-2"></i>Modifier
                    </button>
                    <button type="button" class="btn btn-primary" id="confirmSubmitBtn">
                        <i class="bi bi-check-circle me-2"></i>Confirmer et Envoyer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="text-center text-white">
            <div class="spinner-border mb-3" role="status"></div>
            <h5>Envoi en cours...</h5>
            <p>Veuillez patienter</p>
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('assets/vendor/jquery/jquery-3.7.0.min.js') }}"></script>
    <script>
        // Vérification immédiate que jQuery est chargé
        if (typeof jQuery === 'undefined') {
            console.error('❌ jQuery n\'a pas été chargé correctement!');
        } else {
            console.log('✓ jQuery chargé, version:', jQuery.fn.jquery);
        }
    </script>
    <script src="{{ asset('assets/vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/select2/js/select2.min.js') }}"></script>
    <script>
        // Vérification que Select2 est chargé
        if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
            console.log('✓ Select2 chargé');
        } else {
            console.error('❌ Select2 n\'a pas été chargé correctement!');
        }
    </script>
    <script src="{{ asset('assets/vendor/intl-tel-input/js/intlTelInput.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/signature-pad/signature_pad.umd.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/sweetalert2/sweetalert2.min.js') }}"></script>
    
    <script>
        // Vérifier que jQuery est disponible
        function waitForJQuery(callback, maxAttempts = 100) {
            let attempts = 0;
            function check() {
                attempts++;
                if (typeof jQuery !== 'undefined' && typeof jQuery.fn !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
                    console.log('✓ jQuery et Select2 sont disponibles');
                    callback();
                } else if (attempts < maxAttempts) {
                    setTimeout(check, 50);
                } else {
                    console.error('✗ jQuery ou Select2 ne sont pas disponibles après', maxAttempts, 'tentatives');
                }
            }
            check();
        }
        
        // Helper pour remplacer alert() par SweetAlert
        function showAlert(message, type = 'info', title = null) {
            const titles = {
                'error': 'Erreur',
                'warning': 'Attention',
                'success': 'Succès',
                'info': 'Information'
            };
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: type,
                    title: title || titles[type] || 'Information',
                    text: message,
                    confirmButtonText: 'OK',
                    customClass: {
                        popup: 'swal2-popup-custom'
                    }
                });
            } else {
                // Fallback vers alert standard si SweetAlert n'est pas disponible
                alert((title || titles[type] || 'Information') + ': ' + message);
            }
        }
        
        // DONNÉES: Liste des pays
        const countries = ["Afghanistan","Afrique du Sud","Albanie","Algérie","Allemagne","Andorre","Angola","Arabie Saoudite","Argentine","Arménie","Australie","Autriche","Azerbaïdjan","Bahamas","Bahreïn","Bangladesh","Barbade","Belgique","Belize","Bénin","Bhoutan","Biélorussie","Bolivie","Bosnie-Herzégovine","Botswana","Brésil","Brunei","Bulgarie","Burkina Faso","Burundi","Cambodge","Cameroun","Canada","Cap-Vert","Chili","Chine","Chypre","Colombie","Comores","Congo","Corée du Nord","Corée du Sud","Costa Rica","Côte d'Ivoire","Croatie","Cuba","Danemark","Djibouti","Dominique","Égypte","Émirats Arabes Unis","Équateur","Érythrée","Espagne","Estonie","Eswatini","États-Unis","Éthiopie","Fidji","Finlande","France","Gabon","Gambie","Géorgie","Ghana","Grèce","Grenade","Guatemala","Guinée","Guinée-Bissau","Guinée équatoriale","Guyana","Haïti","Honduras","Hongrie","Îles Marshall","Îles Salomon","Inde","Indonésie","Irak","Iran","Irlande","Islande","Israël","Italie","Jamaïque","Japon","Jordanie","Kazakhstan","Kenya","Kirghizistan","Kiribati","Koweït","Laos","Lesotho","Lettonie","Liban","Libéria","Libye","Liechtenstein","Lituanie","Luxembourg","Macédoine du Nord","Madagascar","Malaisie","Malawi","Maldives","Mali","Malte","Maroc","Maurice","Mauritanie","Mexique","Micronésie","Moldavie","Monaco","Mongolie","Monténégro","Mozambique","Myanmar","Namibie","Nauru","Népal","Nicaragua","Niger","Nigéria","Norvège","Nouvelle-Zélande","Oman","Ouganda","Ouzbékistan","Pakistan","Palaos","Palestine","Panama","Papouasie-Nouvelle-Guinée","Paraguay","Pays-Bas","Pérou","Philippines","Pologne","Portugal","Qatar","République centrafricaine","République démocratique du Congo","République dominicaine","République tchèque","Roumanie","Royaume-Uni","Russie","Rwanda","Saint-Kitts-et-Nevis","Saint-Vincent-et-les-Grenadines","Sainte-Lucie","Salvador","Samoa","Sao Tomé-et-Principe","Sénégal","Serbie","Seychelles","Sierra Leone","Singapour","Slovaquie","Slovénie","Somalie","Soudan","Soudan du Sud","Sri Lanka","Suède","Suisse","Suriname","Syrie","Tadjikistan","Tanzanie","Tchad","Thaïlande","Timor oriental","Togo","Tonga","Trinité-et-Tobago","Tunisie","Turkménistan","Turquie","Tuvalu","Ukraine","Uruguay","Vanuatu","Vatican","Venezuela","Vietnam","Yémen","Zambie","Zimbabwe"];
        
        // ========== RECHERCHE ET PRÉ-REMPLISSAGE CLIENT ==========
        const hotelId = {{ $hotel->id }};
        let searchTimeout = null;
        
        // Fonction pour rechercher un client
        function searchClient(query) {
            if (!query || query.trim().length < 3) {
                return;
            }
            
            const searchBtn = document.getElementById('clientSearchBtn');
            const resultDiv = document.getElementById('clientSearchResult');
            
            // Afficher le loading
            searchBtn.disabled = true;
            searchBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Recherche...';
            resultDiv.innerHTML = '';
            
            // Faire la requête API
            fetch(`/api/hotels/${hotelId}/clients/search?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    searchBtn.disabled = false;
                    searchBtn.innerHTML = '<i class="bi bi-search me-1"></i>Rechercher';
                    
                    if (data.found) {
                        // Afficher le résultat
                        resultDiv.innerHTML = `
                            <div class="alert alert-success mb-0">
                                <i class="bi bi-check-circle me-2"></i>
                                <strong>Client trouvé :</strong> ${data.client.full_name}<br>
                                <small>${data.client.reservations_count} réservation(s) précédente(s)</small>
                            </div>
                        `;
                        
                        // Pré-remplir le formulaire
                        fillFormWithClientData(data.form_data);
                        
                        // Afficher un message de succès
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Client trouvé !',
                                text: 'Vos informations ont été pré-remplies. Veuillez vérifier et compléter les champs manquants.',
                                timer: 3000,
                                showConfirmButton: false,
                                toast: true,
                                position: 'top-end'
                            });
                        }
                    } else {
                        resultDiv.innerHTML = `
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-info-circle me-2"></i>
                                ${data.message || 'Aucun client trouvé'}
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Erreur lors de la recherche:', error);
                    searchBtn.disabled = false;
                    searchBtn.innerHTML = '<i class="bi bi-search me-1"></i>Rechercher';
                    resultDiv.innerHTML = `
                        <div class="alert alert-danger mb-0">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Erreur lors de la recherche
                        </div>
                    `;
                });
        }
        
        // Fonction pour pré-remplir le formulaire avec les données du client
        function fillFormWithClientData(formData) {
            // Champs standards à pré-remplir (exclure les champs de séjour, type de réservation, etc.)
            const fieldsToFill = [
                'type_piece_identite',
                'numero_piece_identite',
                'nom',
                'prenom',
                'sexe',
                'date_naissance',
                'lieu_naissance',
                'nationalite',
                'adresse',
                'telephone',
                'email',
                'profession',
                'document_number',
                'document_delivery_date',
                'document_delivery_place',
                'piece_identite_delivery_date',
                'piece_identite_delivery_place'
            ];
            
            fieldsToFill.forEach(fieldName => {
                if (formData[fieldName]) {
                    fillField(fieldName, formData[fieldName]);
                }
            });
            
            // Pré-remplir aussi tous les champs personnalisés si présents dans formData
            // Les champs personnalisés peuvent avoir n'importe quel nom (key)
            // On itère sur toutes les clés de formData et on essaie de remplir les champs correspondants
            Object.keys(formData).forEach(fieldName => {
                // Si ce n'est pas un champ standard déjà traité, c'est peut-être un champ personnalisé
                if (!fieldsToFill.includes(fieldName) && !['id', 'created_at', 'updated_at', 'piece_identite_recto_url', 'piece_identite_verso_url', 'piece_identite_recto_path', 'piece_identite_verso_path'].includes(fieldName)) {
                    fillField(fieldName, formData[fieldName]);
                }
            });
            
            // Fonction helper pour remplir un champ
            function fillField(fieldName, value) {
                    const field = document.querySelector(`[name="${fieldName}"]`);
                    if (field) {
                        if (field.type === 'select-one') {
                            // Pour les selects, trouver l'option correspondante
                        const option = Array.from(field.options).find(opt => opt.value === value);
                            if (option) {
                            field.value = value;
                                // Déclencher l'événement change pour Select2 si présent
                                if (typeof jQuery !== 'undefined' && jQuery(field).hasClass('select2-hidden-accessible')) {
                                jQuery(field).val(value).trigger('change');
                                } else {
                                    field.dispatchEvent(new Event('change', { bubbles: true }));
                                }
                            }
                    } else if (field.type === 'checkbox') {
                        // Pour les checkboxes, vérifier si la valeur est truthy
                        field.checked = !!value;
                        field.dispatchEvent(new Event('change', { bubbles: true }));
                    } else if (field.type === 'radio') {
                        // Pour les radio, sélectionner celui avec la bonne valeur
                        const radio = document.querySelector(`[name="${fieldName}"][value="${value}"]`);
                        if (radio) {
                            radio.checked = true;
                            radio.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                        } else {
                        field.value = value;
                            field.dispatchEvent(new Event('input', { bubbles: true }));
                            field.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    }
                }
            
            // Gérer le téléphone international si présent
            if (formData['telephone'] && window.itiInstance) {
                try {
                    window.itiInstance.setNumber(formData['telephone']);
                } catch (e) {
                    console.warn('Impossible de définir le numéro de téléphone international:', e);
                }
            }
            
            // Pré-remplir les images des pièces d'identité si disponibles
            if (formData['piece_identite_recto_url']) {
                loadIdentityDocumentImage('recto', formData['piece_identite_recto_url'], formData['piece_identite_recto_path']);
            }
            
            if (formData['piece_identite_verso_url']) {
                loadIdentityDocumentImage('verso', formData['piece_identite_verso_url'], formData['piece_identite_verso_path']);
            }
            
            // Mapper les champs de livraison de document si nécessaire (compatibilité entre différents noms de champs)
            if (formData['piece_identite_delivery_date'] && !formData['document_delivery_date']) {
                fillField('document_delivery_date', formData['piece_identite_delivery_date']);
            }
            if (formData['piece_identite_delivery_place'] && !formData['document_delivery_place']) {
                fillField('document_delivery_place', formData['piece_identite_delivery_place']);
            }
            
            console.log('Formulaire pré-rempli avec les données du client');
        }
        
        // Fonction pour charger une image de pièce d'identité depuis une URL
        function loadIdentityDocumentImage(side, imageUrl, imagePath) {
            const previewId = side === 'recto' ? 'photoRectoPreview' : 'photoVersoPreview';
            const fileInputId = side === 'recto' ? 'fileRecto' : 'fileVerso';
            const hiddenInputId = side === 'recto' ? 'photoRectoData' : 'photoVersoData';
            
            const previewDiv = document.getElementById(previewId);
            const fileInput = document.getElementById(fileInputId);
            const hiddenInput = document.getElementById(hiddenInputId);
            
            if (!previewDiv || !hiddenInput) {
                console.warn(`Éléments manquants pour charger l'image ${side}`);
                return;
            }
            
            // Afficher l'image de prévisualisation immédiatement
            const img = document.createElement('img');
            img.src = imageUrl;
            img.style.maxWidth = '100%';
            img.style.maxHeight = '200px';
            img.style.borderRadius = '8px';
            img.style.marginTop = '10px';
            img.style.border = '2px solid #28a745';
            img.style.display = 'block';
            
            // Ajouter un badge "Image chargée depuis votre compte"
            const badge = document.createElement('div');
            badge.className = 'badge bg-success mt-2';
            badge.innerHTML = '<i class="bi bi-check-circle me-1"></i>Image pré-remplie depuis votre compte précédent';
            
            // Nettoyer le preview existant
            previewDiv.innerHTML = '';
            previewDiv.appendChild(img);
            previewDiv.appendChild(badge);
            
            // Charger l'image depuis l'URL et créer un File object
            fetch(imageUrl)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Erreur HTTP: ${response.status}`);
                    }
                    return response.blob();
                })
                .then(blob => {
                    // Déterminer le type MIME et l'extension
                    const mimeType = blob.type || 'image/jpeg';
                    const extension = mimeType.split('/')[1] || 'jpg';
                    const fileName = `piece_identite_${side}_${Date.now()}.${extension}`;
                    
                    // Créer un File object à partir du blob
                    const file = new File([blob], fileName, { type: mimeType });
                    
                    // Créer un DataTransfer pour pouvoir assigner le fichier à l'input
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    
                    // Assigner le fichier à l'input file si disponible
                    if (fileInput) {
                        fileInput.files = dataTransfer.files;
                        // Déclencher l'événement change pour que les autres handlers soient notifiés
                        const changeEvent = new Event('change', { bubbles: true });
                        fileInput.dispatchEvent(changeEvent);
                        console.log(`✓ Fichier ${side} assigné à l'input file:`, fileName);
                    }
                    
                    // Convertir en base64 pour le champ caché (pour compatibilité avec photo_recto/photo_verso)
                    const reader = new FileReader();
                    reader.onloadend = function() {
                        const base64data = reader.result;
                        if (hiddenInput) {
                            hiddenInput.value = base64data;
                            console.log(`✓ Image ${side} convertie en base64 et placée dans le champ caché`);
                        }
                    };
                    reader.readAsDataURL(blob);
                })
                .catch(error => {
                    console.error(`Erreur lors du chargement de l'image ${side}:`, error);
                    
                    // En cas d'erreur, essayer de charger directement en base64 si c'est déjà une URL data:
                    if (imageUrl.startsWith('data:')) {
                        hiddenInput.value = imageUrl;
                        console.log(`✓ Image ${side} chargée directement depuis data URL`);
                    } else {
                        // Afficher un message d'avertissement mais garder l'aperçu
                        const warning = document.createElement('div');
                        warning.className = 'alert alert-warning mt-2';
                        warning.innerHTML = '<small><i class="bi bi-exclamation-triangle me-1"></i>L\'image est visible mais vous devrez peut-être la recharger pour la soumettre</small>';
                        previewDiv.appendChild(warning);
                    }
                });
            
            console.log(`Image ${side} en cours de chargement depuis:`, imageUrl);
        }
        
        // Initialisation au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            console.log('═══════════════════════════════════════');
            console.log('🚀 DOM LOADED - Initialisation du formulaire');
            console.log('═══════════════════════════════════════');
            console.log('jQuery disponible:', typeof jQuery !== 'undefined');
            console.log('Select2 disponible:', typeof jQuery !== 'undefined' && typeof jQuery.fn !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined');
            console.log('Document ready state:', document.readyState);
            
            // Populer d'abord le select avec les options
            const nationaliteSelect = document.getElementById('nationalite');
            if (nationaliteSelect) {
                countries.forEach(country => {
                    const option = document.createElement('option');
                    option.value = country;
                    option.text = country;
                    nationaliteSelect.appendChild(option);
                });
                
                console.log('Nationalité select populated with', nationaliteSelect.options.length, 'options');
                
                // Attendre que jQuery et Select2 soient disponibles avant d'initialiser
                waitForJQuery(function() {
                    try {
                        jQuery('#nationalite').select2({
                            theme: 'bootstrap-5',
                            placeholder: 'Rechercher un pays...',
                            allowClear: true
                        });
                        console.log('✓ Select2 initialized for nationality');
                    } catch (error) {
                        console.error('Erreur lors de l\'initialisation de Select2:', error);
                    }
                });
            } else {
                console.warn('Nationalité select element not found');
            }
            
            // Initialisation du téléphone international
            const phoneInput = document.querySelector("#telephone");
            const iti = window.intlTelInput(phoneInput, {
                initialCountry: "fr", // Pays par défaut (France) - geoIpLookup désactivé pour éviter les violations CSP
                utilsScript: "{{ asset('assets/vendor/intl-tel-input/js/utils.js') }}",
                separateDialCode: true,
                preferredCountries: ['fr', 'be', 'ch', 'sn', 'ci', 'ma']
            });
            
            // Stocker l'instance iti globalement pour pouvoir y accéder dans les callbacks
            window.itiInstance = iti;
            
            // Configurer le chemin des drapeaux via CSS (si nécessaire)
            // Le CSS devrait déjà pointer vers le bon chemin
            
            // ========== RECHERCHE CLIENT ==========
            const clientSearchInput = document.getElementById('clientSearchInput');
            const clientSearchBtn = document.getElementById('clientSearchBtn');
            
            if (clientSearchInput && clientSearchBtn) {
                // Recherche au clic sur le bouton
                clientSearchBtn.addEventListener('click', function() {
                    const query = clientSearchInput.value.trim();
                    if (query.length >= 3) {
                        searchClient(query);
                    } else {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Recherche trop courte',
                                text: 'Veuillez saisir au moins 3 caractères (email, téléphone ou numéro de pièce d\'identité)',
                                timer: 2000,
                                showConfirmButton: false,
                                toast: true,
                                position: 'top-end'
                            });
                        }
                    }
                });
                
                // Recherche avec Enter
                clientSearchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        clientSearchBtn.click();
                    }
                });
                
                // Recherche automatique avec debounce (après 1 seconde d'inactivité)
                let searchTimeout = null;
                clientSearchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    const query = this.value.trim();
                    
                    if (query.length >= 3) {
                        searchTimeout = setTimeout(function() {
                            searchClient(query);
                        }, 1000);
                    } else {
                        // Effacer le résultat si la recherche est trop courte
                        const resultDiv = document.getElementById('clientSearchResult');
                        if (resultDiv) {
                            resultDiv.innerHTML = '';
                        }
                    }
                });
                
                console.log('✅ Event listeners pour la recherche client ajoutés');
            }
            
            // ========== SAUVEGARDE AUTOMATIQUE DES DONNÉES DU FORMULAIRE ==========
            const formId = 'reservationForm';
            const storageKey = 'hotelReservationData_{{ $hotel->id }}';
            
            // Fonction pour sauvegarder les données
            function saveFormData() {
                const form = document.getElementById(formId);
                const formData = new FormData(form);
                const data = {};
                
                // Sauvegarder tous les champs sauf les fichiers et la signature
                for (let [key, value] of formData.entries()) {
                    if (key !== 'piece_identite_recto' && key !== 'piece_identite_verso' && key !== 'signature') {
                        data[key] = value;
                    }
                }
                
                // Sauvegarder le type de réservation sélectionné
                const typeReservation = document.querySelector('input[name="type_reservation"]:checked');
                if (typeReservation) {
                    data['type_reservation'] = typeReservation.value;
                }
                
                localStorage.setItem(storageKey, JSON.stringify(data));
                console.log('Form data saved to localStorage');
            }
            
            // Fonction pour restaurer les données
            function loadFormData() {
                const savedData = localStorage.getItem(storageKey);
                if (!savedData) return;
                
                try {
                    const data = JSON.parse(savedData);
                    console.log('Loading saved form data:', data);
                    
                    // Restaurer tous les champs
                    for (let [key, value] of Object.entries(data)) {
                        const field = document.querySelector(`[name="${key}"]`);
                        
                        if (field) {
                            if (field.type === 'radio') {
                                const radio = document.querySelector(`[name="${key}"][value="${value}"]`);
                                if (radio) {
                                    radio.checked = true;
                                    radio.click(); // Déclencher l'événement pour afficher les champs conditionnels
                                }
                            } else if (field.type === 'checkbox') {
                                field.checked = value === 'on' || value === '1' || value === true;
                            } else if (field.tagName === 'SELECT') {
                                field.value = value;
                                // Déclencher le changement pour Select2
                                if (typeof jQuery !== 'undefined') {
                                    const $field = jQuery(field);
                                    if ($field.hasClass('select2-countries') || $field.hasClass('select2-cities')) {
                                        $field.val(value).trigger('change');
                                    }
                                }
                            } else {
                                field.value = value;
                            }
                        }
                    }
                    
                    console.log('Form data restored successfully');
                } catch (e) {
                    console.error('Error loading form data:', e);
                }
            }
            
            // Charger les données au chargement de la page
            loadFormData();
            
            // Sauvegarder automatiquement à chaque changement
            document.getElementById(formId).addEventListener('input', saveFormData);
            document.getElementById(formId).addEventListener('change', saveFormData);
            
            // Effacer les données après envoi réussi
            @if(session('success'))
                localStorage.removeItem(storageKey);
                console.log('Form data cleared after successful submission');
            @endif
            // ========== FIN SAUVEGARDE AUTOMATIQUE ==========
            
            // Configuration des champs groupe depuis PHP
            const fieldConfig = {
                nom_groupe: {
                    visible: {{ $formConfig->isVisible('nom_groupe') ? 'true' : 'false' }},
                    required: {{ $formConfig->isRequired('nom_groupe') ? 'true' : 'false' }}
                },
                code_groupe: {
                    visible: {{ $formConfig->isVisible('code_groupe') ? 'true' : 'false' }},
                    required: {{ $formConfig->isRequired('code_groupe') ? 'true' : 'false' }}
                }
            };
            
            // Gestion du type de réservation
            console.log('🔍 Initialisation de la gestion du type de réservation...');
            const typeIndividuel = document.getElementById('typeIndividuel');
            const typeGroupe = document.getElementById('typeGroupe');
            const groupeFields = document.getElementById('groupeFields');
            const nomGroupe = document.getElementById('nomGroupe');
            const codeGroupe = document.getElementById('codeGroupe');
            const radioIndividuel = document.querySelector('input[name="type_reservation"][value="Individuel"]');
            const radioGroupe = document.querySelector('input[name="type_reservation"][value="Groupe"]');
            
            console.log('📋 Éléments trouvés:', {
                typeIndividuel: !!typeIndividuel,
                typeGroupe: !!typeGroupe,
                groupeFields: !!groupeFields,
                nomGroupe: !!nomGroupe,
                codeGroupe: !!codeGroupe,
                radioIndividuel: !!radioIndividuel,
                radioGroupe: !!radioGroupe
            });
            
            // Vérifier si les champs groupe existent (peuvent être masqués par configuration)
            // Les champs nomGroupe et codeGroupe peuvent ne pas exister si masqués
            if (typeIndividuel && typeGroupe && groupeFields) {
                console.log('✅ Tous les éléments sont trouvés, initialisation des event listeners...');
                
                // Fonction pour afficher/masquer les champs groupe
                function toggleGroupeFields(show) {
                    console.log('🔄 toggleGroupeFields appelé avec show=', show);
                    if (show) {
                        groupeFields.classList.add('show');
                        // Utiliser les configurations au lieu de forcer required = true
                        if (nomGroupe) {
                            nomGroupe.required = fieldConfig.nom_groupe.required;
                        }
                        if (codeGroupe) {
                            codeGroupe.required = fieldConfig.code_groupe.required;
                        }
                        console.log('✅ Champs groupe affichés');
                    } else {
                        groupeFields.classList.remove('show');
                        if (nomGroupe) {
                            nomGroupe.required = false;
                        }
                        if (codeGroupe) {
                            codeGroupe.required = false;
                        }
                        console.log('✅ Champs groupe masqués');
                    }
                }
                
                // Fonction pour gérer la sélection Individuel
                function handleIndividuelSelection() {
                    console.log('👤 Type Individuel sélectionné');
                    if (radioIndividuel) {
                        radioIndividuel.checked = true;
                        console.log('✅ Radio Individuel coché');
                    }
                    typeIndividuel.classList.add('active');
                    if (typeGroupe) typeGroupe.classList.remove('active');
                    toggleGroupeFields(false);
                    if (typeof saveFormData === 'function') {
                        saveFormData();
                    }
                }
                
                // Fonction pour gérer la sélection Groupe
                function handleGroupeSelection() {
                    console.log('👥 Type Groupe sélectionné');
                    if (radioGroupe) {
                        radioGroupe.checked = true;
                        console.log('✅ Radio Groupe coché');
                    }
                    typeGroupe.classList.add('active');
                    if (typeIndividuel) typeIndividuel.classList.remove('active');
                    toggleGroupeFields(true);
                    if (typeof saveFormData === 'function') {
                        saveFormData();
                    }
                }
                
                // Fonction unifiée pour gérer les clics (labels et radios)
                function handleReservationTypeClick(type) {
                    console.log('🖱️ Clic détecté sur type:', type);
                    
                    // Utiliser setTimeout pour s'assurer que le radio est coché après le clic
                    setTimeout(function() {
                        const checkedRadio = document.querySelector('input[name="type_reservation"]:checked');
                        console.log('🔍 Radio coché après clic:', checkedRadio ? checkedRadio.value : 'aucun');
                        
                        if (checkedRadio) {
                            if (checkedRadio.value === 'Individuel') {
                                handleIndividuelSelection();
                            } else if (checkedRadio.value === 'Groupe') {
                                handleGroupeSelection();
                            }
                        }
                    }, 10);
                }
                
                // Event listeners sur les inputs radio
                if (radioIndividuel) {
                    radioIndividuel.addEventListener('change', function() {
                        console.log('🔄 Radio Individuel change event déclenché, checked:', this.checked);
                        if (this.checked) {
                            handleIndividuelSelection();
                        }
                    });
                    
                    radioIndividuel.addEventListener('click', function() {
                        console.log('🖱️ Radio Individuel cliqué directement');
                        handleReservationTypeClick('Individuel');
                    });
                    
                    console.log('✅ Event listeners ajoutés sur radio Individuel');
                } else {
                    console.warn('⚠️ Radio Individuel non trouvé');
                }
                
                if (radioGroupe) {
                    radioGroupe.addEventListener('change', function() {
                        console.log('🔄 Radio Groupe change event déclenché, checked:', this.checked);
                        if (this.checked) {
                            handleGroupeSelection();
                        }
                    });
                    
                    radioGroupe.addEventListener('click', function() {
                        console.log('🖱️ Radio Groupe cliqué directement');
                        handleReservationTypeClick('Groupe');
                    });
                    
                    console.log('✅ Event listeners ajoutés sur radio Groupe');
                } else {
                    console.warn('⚠️ Radio Groupe non trouvé');
                }
                
                // Event listeners sur les labels
                typeIndividuel.addEventListener('click', function(e) {
                    console.log('🖱️ Label Individuel cliqué');
                    // Ne pas utiliser preventDefault - laisser le label cocher le radio naturellement
                    handleReservationTypeClick('Individuel');
                });
                
                typeGroupe.addEventListener('click', function(e) {
                    console.log('🖱️ Label Groupe cliqué');
                    // Ne pas utiliser preventDefault - laisser le label cocher le radio naturellement
                    handleReservationTypeClick('Groupe');
                });
                
                console.log('✅ Event listeners ajoutés sur les labels');
                
                // Initialiser l'état selon la sélection par défaut
                const defaultType = document.querySelector('input[name="type_reservation"]:checked');
                console.log('🔍 Type par défaut trouvé:', defaultType ? defaultType.value : 'aucun');
                if (defaultType) {
                    if (defaultType.value === 'Groupe') {
                        toggleGroupeFields(true);
                        if (typeGroupe) typeGroupe.classList.add('active');
                        if (typeIndividuel) typeIndividuel.classList.remove('active');
                    } else {
                        toggleGroupeFields(false);
                        if (typeIndividuel) typeIndividuel.classList.add('active');
                        if (typeGroupe) typeGroupe.classList.remove('active');
                    }
                } else {
                    // Aucune sélection par défaut, masquer les champs groupe
                    toggleGroupeFields(false);
                }
                console.log('✅ Initialisation du type de réservation terminée');
            } else {
                console.error('❌ Type reservation elements not found:', {
                    typeIndividuel: !typeIndividuel,
                    typeGroupe: !typeGroupe,
                    groupeFields: !groupeFields,
                    nomGroupe: !nomGroupe,
                    codeGroupe: !codeGroupe
                });
            }
            
            // Gestion du type de pièce d'identité
            console.log('🔍 Initialisation de la gestion du type de pièce d\'identité...');
            const typePieceSelect = document.getElementById('typePieceSelect');
            const uploadMethodSection = document.getElementById('uploadMethodSection');
            
            console.log('📋 Éléments type pièce trouvés:', {
                typePieceSelect: !!typePieceSelect,
                uploadMethodSection: !!uploadMethodSection
            });
            
            if (typePieceSelect) {
                function toggleUploadMethod(show) {
                    console.log('🔄 toggleUploadMethod appelé avec show=', show);
                    if (uploadMethodSection) {
                        if (show) {
                            uploadMethodSection.classList.add('show');
                            console.log('✅ Section méthode upload affichée');
                        } else {
                            uploadMethodSection.classList.remove('show');
                            console.log('✅ Section méthode upload masquée');
                        }
                    }
                    
                    // Cacher les sections de fichiers si on cache la méthode d'upload
                    if (!show) {
                        const uploadFilesSection = document.getElementById('uploadFilesSection');
                        const cameraSection = document.getElementById('cameraSection');
                        if (uploadFilesSection) uploadFilesSection.classList.remove('show');
                        if (cameraSection) cameraSection.classList.remove('show');
                    }
                }
                
                // Vérifier si un listener existe déjà pour éviter les doublons
                if (!typePieceSelect.hasAttribute('data-listener-attached')) {
                    typePieceSelect.setAttribute('data-listener-attached', 'true');
                    
                    typePieceSelect.addEventListener('change', function() {
                        console.log('🔄 Type pièce changé:', this.value);
                        toggleUploadMethod(!!this.value);
                    });
                    
                    console.log('✅ Event listener ajouté sur type piece select');
                }
                
                // Initialiser l'état si une valeur est déjà sélectionnée
                if (typePieceSelect.value) {
                    toggleUploadMethod(true);
                } else {
                    toggleUploadMethod(false);
                }
                console.log('✅ Initialisation du type de pièce d\'identité terminée');
            } else {
                console.warn('⚠️ Type piece select element (typePieceSelect) not found in DOM');
            }
            
            // Gestion de la méthode d'upload
            const uploadMethodBtn = document.getElementById('uploadMethodBtn');
            const cameraMethodBtn = document.getElementById('cameraMethodBtn');
            const uploadFilesSection = document.getElementById('uploadFilesSection');
            const cameraSection = document.getElementById('cameraSection');
            const fileRecto = document.getElementById('fileRecto');
            const fileVerso = document.getElementById('fileVerso');
            const photoRectoData = document.getElementById('photoRectoData');
            const photoVersoData = document.getElementById('photoVersoData');
            
            // Fonction pour désactiver/activer les champs requis selon la section visible
            function toggleRequiredFields() {
                const isUploadVisible = uploadFilesSection && uploadFilesSection.classList.contains('show');
                const isCameraVisible = cameraSection && cameraSection.classList.contains('show');
                
                // Désactiver les champs requis dans les sections cachées
                if (fileRecto) {
                    if (isUploadVisible) {
                        fileRecto.removeAttribute('disabled');
                        if (fileRecto.hasAttribute('required')) {
                            // Le champ reste requis seulement si visible
                        }
                    } else {
                        fileRecto.setAttribute('disabled', 'disabled');
                        fileRecto.removeAttribute('required');
                    }
                }
                
                if (fileVerso) {
                    if (isUploadVisible) {
                        fileVerso.removeAttribute('disabled');
                    } else {
                        fileVerso.setAttribute('disabled', 'disabled');
                        fileVerso.removeAttribute('required');
                    }
                }
            }
            
            if (uploadMethodBtn && uploadFilesSection) {
                uploadMethodBtn.addEventListener('click', function() {
                    console.log('Upload method selected');
                    uploadMethodBtn.classList.add('active');
                    if (cameraMethodBtn) cameraMethodBtn.classList.remove('active');
                    uploadFilesSection.classList.add('show');
                    if (cameraSection) cameraSection.classList.remove('show');
                    toggleRequiredFields();
                });
            }
            
            if (cameraMethodBtn && cameraSection) {
                cameraMethodBtn.addEventListener('click', function() {
                    console.log('Camera method selected');
                    cameraMethodBtn.classList.add('active');
                    if (uploadMethodBtn) uploadMethodBtn.classList.remove('active');
                    cameraSection.classList.add('show');
                    if (uploadFilesSection) uploadFilesSection.classList.remove('show');
                    toggleRequiredFields();
                });
            }
            
            // Initialiser l'état des champs requis
            toggleRequiredFields();
            
            // Gestion de la caméra
            let streamRecto = null;
            let streamVerso = null;
            
            // Bouton caméra recto
            const startCameraRectoBtn = document.getElementById('startCameraRectoBtn');
            if (startCameraRectoBtn) {
                startCameraRectoBtn.addEventListener('click', function() {
                console.log('Starting camera recto...');
                const video = document.getElementById('videoRecto');
                const preview = document.getElementById('cameraPreviewRecto');
                
                navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
                    .then(function(stream) {
                        streamRecto = stream;
                        video.srcObject = stream;
                        preview.style.display = 'block';
                    })
                    .catch(function(err) {
                        console.error('Camera error:', err);
                        showAlert('Erreur d\'accès à la caméra : ' + err.message, 'error');
                    });
                });
            }
            
            // Bouton caméra verso
            const startCameraVersoBtn = document.getElementById('startCameraVersoBtn');
            if (startCameraVersoBtn) {
                startCameraVersoBtn.addEventListener('click', function() {
                console.log('Starting camera verso...');
                const video = document.getElementById('videoVerso');
                const preview = document.getElementById('cameraPreviewVerso');
                
                navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
                    .then(function(stream) {
                        streamVerso = stream;
                        video.srcObject = stream;
                        preview.style.display = 'block';
                    })
                    .catch(function(err) {
                        console.error('Camera error:', err);
                        showAlert('Erreur d\'accès à la caméra : ' + err.message, 'error');
                    });
                });
            }
            
            // Capture photo recto
            const captureRectoBtn = document.getElementById('captureRectoBtn');
            if (captureRectoBtn) {
                captureRectoBtn.addEventListener('click', function() {
                console.log('Capturing recto...');
                const video = document.getElementById('videoRecto');
                const canvas = document.getElementById('canvasRecto');
                const preview = document.getElementById('photoRectoPreview');
                const dataInput = document.getElementById('photoRectoData');
                
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                canvas.getContext('2d').drawImage(video, 0, 0);
                
                const dataUrl = canvas.toDataURL('image/jpeg');
                dataInput.value = dataUrl;
                
                preview.innerHTML = '<img src="' + dataUrl + '" class="img-fluid rounded mt-2" alt="Photo recto">';
                
                if (streamRecto) {
                    streamRecto.getTracks().forEach(track => track.stop());
                    const previewRecto = document.getElementById('cameraPreviewRecto');
                    if (previewRecto) previewRecto.style.display = 'none';
                }
            });
            }
            
            // Capture photo verso
            const captureVersoBtn = document.getElementById('captureVersoBtn');
            if (captureVersoBtn) {
                captureVersoBtn.addEventListener('click', function() {
                console.log('Capturing verso...');
                const video = document.getElementById('videoVerso');
                const canvas = document.getElementById('canvasVerso');
                const preview = document.getElementById('photoVersoPreview');
                const dataInput = document.getElementById('photoVersoData');
                
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                canvas.getContext('2d').drawImage(video, 0, 0);
                
                const dataUrl = canvas.toDataURL('image/jpeg');
                dataInput.value = dataUrl;
                
                preview.innerHTML = '<img src="' + dataUrl + '" class="img-fluid rounded mt-2" alt="Photo verso">';
                
                if (streamVerso) {
                    streamVerso.getTracks().forEach(track => track.stop());
                    const previewVerso = document.getElementById('cameraPreviewVerso');
                    if (previewVerso) previewVerso.style.display = 'none';
                }
            });
            }
            
            // ========== GESTION DE L'UPLOAD DES FICHIERS (PIÈCE D'IDENTITÉ) ==========
            const fileRectoInput = document.getElementById('fileRecto');
            const fileVersoInput = document.getElementById('fileVerso');
            
            // Fonction pour valider un fichier
            function validateFile(file, maxSizeMB = 5) {
                const maxSizeBytes = maxSizeMB * 1024 * 1024; // Convertir en bytes
                const allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
                const allowedExtensions = ['.jpg', '.jpeg', '.png', '.webp', '.pdf'];
                
                if (!file) {
                    return { valid: false, message: 'Aucun fichier sélectionné.' };
                }
                
                // Vérifier la taille
                if (file.size > maxSizeBytes) {
                    return { 
                        valid: false, 
                        message: `Le fichier est trop volumineux. Taille maximum : ${maxSizeMB}MB. Taille actuelle : ${(file.size / 1024 / 1024).toFixed(2)}MB.` 
                    };
                }
                
                // Vérifier le type MIME et l'extension du fichier
                const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
                const isTypeValid = allowedTypes.includes(file.type) || allowedExtensions.includes(fileExtension);
                
                // Vérification supplémentaire pour les fichiers JPEG qui peuvent avoir différents types MIME
                const isJPEG = file.type === 'image/jpeg' || 
                              file.type === 'image/jpg' || 
                              fileExtension === '.jpg' || 
                              fileExtension === '.jpeg';
                
                if (!isTypeValid && !isJPEG) {
                    console.warn('Type de fichier détecté:', file.type, 'Extension:', fileExtension);
                    return { 
                        valid: false, 
                        message: `Type de fichier non autorisé. Formats acceptés : JPG, JPEG, PNG, WEBP, PDF. Type détecté : ${file.type || 'inconnu'}` 
                    };
                }
                
                return { valid: true, message: 'Fichier valide.' };
            }
            
            // Fonction pour afficher un aperçu d'image
            function showImagePreview(file, previewContainerId) {
                const previewContainer = document.getElementById(previewContainerId);
                if (!previewContainer) return;
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (file.type === 'application/pdf') {
                        previewContainer.innerHTML = `
                            <div class="alert alert-info mt-2">
                                <i class="bi bi-file-earmark-pdf me-2"></i>
                                <strong>Fichier PDF :</strong> ${file.name}<br>
                                <small>Taille : ${(file.size / 1024).toFixed(2)} KB</small>
                            </div>
                        `;
                    } else {
                        previewContainer.innerHTML = `
                            <div class="mt-2">
                                <img src="${e.target.result}" class="img-fluid rounded border" style="max-height: 200px; max-width: 100%;" alt="Aperçu">
                                <div class="text-muted small mt-1">
                                    <i class="bi bi-check-circle text-success me-1"></i>
                                    ${file.name} (${(file.size / 1024).toFixed(2)} KB)
                                </div>
                            </div>
                        `;
                    }
                };
                reader.onerror = function() {
                    previewContainer.innerHTML = `
                        <div class="alert alert-danger mt-2">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Erreur lors de la lecture du fichier.
                        </div>
                    `;
                };
                reader.readAsDataURL(file);
            }
            
            // Gestion de l'upload du fichier recto
            if (fileRectoInput) {
                fileRectoInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    
                    if (!file) {
                        // Si aucun fichier, vider l'aperçu
                        const preview = document.getElementById('photoRectoPreview');
                        if (preview) preview.innerHTML = '';
                        return;
                    }
                    
                    // Valider le fichier
                    const validation = validateFile(file, 5);
                    
                    if (!validation.valid) {
                        showAlert(validation.message, 'error', 'Erreur de validation');
                        this.value = ''; // Réinitialiser l'input
                        const preview = document.getElementById('photoRectoPreview');
                        if (preview) preview.innerHTML = '';
                        return;
                    }
                    
                    // Afficher l'aperçu
                    showImagePreview(file, 'photoRectoPreview');
                    console.log('✓ Fichier recto validé et chargé:', file.name);
                    
                    // Sauvegarder les données du formulaire
                    if (typeof saveFormData === 'function') {
                        saveFormData();
                    }
                });
            }
            
            // Gestion de l'upload du fichier verso
            if (fileVersoInput) {
                fileVersoInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    
                    if (!file) {
                        // Si aucun fichier, vider l'aperçu
                        const preview = document.getElementById('photoVersoPreview');
                        if (preview) preview.innerHTML = '';
                        return;
                    }
                    
                    // Valider le fichier
                    const validation = validateFile(file, 5);
                    
                    if (!validation.valid) {
                        showAlert(validation.message, 'error', 'Erreur de validation');
                        this.value = ''; // Réinitialiser l'input
                        const preview = document.getElementById('photoVersoPreview');
                        if (preview) preview.innerHTML = '';
                        return;
                    }
                    
                    // Afficher l'aperçu
                    showImagePreview(file, 'photoVersoPreview');
                    console.log('✓ Fichier verso validé et chargé:', file.name);
                    
                    // Sauvegarder les données du formulaire
                    if (typeof saveFormData === 'function') {
                        saveFormData();
                    }
                });
            }
            
            // Gestion des restrictions de dates (arrivée et départ)
            const dateArriveeInput = document.getElementById('dateArrivee');
            const dateDepartInput = document.getElementById('dateDepart');
            
            if (!dateArriveeInput || !dateDepartInput) {
                console.warn('Date inputs not found');
            }
            
            // Fonction pour obtenir la date du jour au format YYYY-MM-DD
            function getTodayDate() {
                const today = new Date();
                const year = today.getFullYear();
                const month = String(today.getMonth() + 1).padStart(2, '0');
                const day = String(today.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            }
            
            // Fonction pour ajouter des jours à une date
            function addDays(dateString, days) {
                const date = new Date(dateString + 'T00:00:00');
                date.setDate(date.getDate() + days);
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            }
            
            // Initialiser la date minimum d'arrivée à aujourd'hui
            if (dateArriveeInput && dateDepartInput) {
                const todayDate = getTodayDate();
                dateArriveeInput.setAttribute('min', todayDate);
                dateDepartInput.setAttribute('min', addDays(todayDate, 1)); // Minimum = demain
                console.log('Date minimum d\'arrivée:', todayDate);
                
                // Fonction de validation des dates
                function validateDates() {
                    const arrivalDate = dateArriveeInput.value;
                    const departureDate = dateDepartInput.value;
                    
                    // Vérifier que la date d'arrivée n'est pas dans le passé
                    if (arrivalDate && arrivalDate < todayDate) {
                        dateArriveeInput.setCustomValidity('La date d\'arrivée ne peut pas être dans le passé.');
                        dateArriveeInput.reportValidity();
                        return false;
                    } else {
                        dateArriveeInput.setCustomValidity('');
                    }
                    
                    // Vérifier que la date de départ est après la date d'arrivée
                    if (arrivalDate && departureDate) {
                        const minDepartureDate = addDays(arrivalDate, 1);
                        if (departureDate <= arrivalDate) {
                            dateDepartInput.setCustomValidity('La date de départ doit être au moins le lendemain de la date d\'arrivée.');
                            dateDepartInput.reportValidity();
                            return false;
                        } else {
                            dateDepartInput.setCustomValidity('');
                        }
                    }
                    
                    return true;
                }
                
                // Quand la date d'arrivée change, mettre à jour la date minimum de départ
                dateArriveeInput.addEventListener('change', function() {
                    const selectedArrival = this.value;
                    
                    if (selectedArrival) {
                        // Vérifier que la date n'est pas dans le passé
                        if (selectedArrival < todayDate) {
                            this.value = '';
                            showAlert('La date d\'arrivée ne peut pas être dans le passé. Veuillez sélectionner une date à partir d\'aujourd\'hui.', 'warning');
                            return;
                        }
                        
                        // Calculer le lendemain de la date d'arrivée
                        const minDepartureDate = addDays(selectedArrival, 1);
                        
                        // Définir la date minimum de départ (le lendemain de l'arrivée)
                        dateDepartInput.setAttribute('min', minDepartureDate);
                        console.log('Date d\'arrivée sélectionnée:', selectedArrival);
                        console.log('Date minimum de départ:', minDepartureDate);
                        
                        // Si la date de départ est déjà sélectionnée et est invalide, la réinitialiser
                        if (dateDepartInput.value && dateDepartInput.value <= selectedArrival) {
                            dateDepartInput.value = '';
                            showAlert('La date de départ doit être postérieure à la date d\'arrivée (au moins le lendemain). Veuillez sélectionner une nouvelle date de départ.', 'warning');
                        }
                        
                        // Créer/mettre à jour le champ caché check_in_date
                        let checkInHidden = document.getElementById('check_in_date');
                        if (!checkInHidden) {
                            checkInHidden = document.createElement('input');
                            checkInHidden.type = 'hidden';
                            checkInHidden.name = 'check_in_date';
                            checkInHidden.id = 'check_in_date';
                            this.form.appendChild(checkInHidden);
                        }
                        checkInHidden.value = selectedArrival;
                    }
                    
                    // Valider et recalculer
                    validateDates();
                    calculateNights();
                    saveFormData(); // Sauvegarder les modifications
                });
                
                // Validation stricte pour la date de départ
                dateDepartInput.addEventListener('change', function() {
                    const arrivalDate = dateArriveeInput.value;
                    const departureDate = this.value;
                    
                    // Vérifier que la date d'arrivée est sélectionnée
                    if (!arrivalDate) {
                        this.value = '';
                        showAlert('Veuillez d\'abord sélectionner une date d\'arrivée.', 'warning');
                        return;
                    }
                    
                    // Vérifier que la date de départ est après la date d'arrivée
                    if (departureDate <= arrivalDate) {
                        const minDepartureDate = addDays(arrivalDate, 1);
                        this.value = '';
                        showAlert('La date de départ doit être postérieure à la date d\'arrivée.\n\nDate d\'arrivée : ' + arrivalDate + '\nDate de départ minimum : ' + minDepartureDate, 'warning');
                        return;
                    }
                    
                    // Créer/mettre à jour le champ caché check_out_date
                    if (departureDate) {
                        let checkOutHidden = document.getElementById('check_out_date');
                        if (!checkOutHidden) {
                            checkOutHidden = document.createElement('input');
                            checkOutHidden.type = 'hidden';
                            checkOutHidden.name = 'check_out_date';
                            checkOutHidden.id = 'check_out_date';
                            this.form.appendChild(checkOutHidden);
                        }
                        checkOutHidden.value = departureDate;
                    }
                    
                    // Valider et recalculer
                    validateDates();
                    calculateNights();
                    saveFormData(); // Sauvegarder les modifications
                });
                
                // Validation avant soumission du formulaire
                const form = document.getElementById('reservationForm');
                if (form) {
                    form.addEventListener('submit', function(e) {
                        // Désactiver les champs requis dans les sections cachées avant validation
                        toggleRequiredFields();
                        
                        // Vérifier les dates
                        if (!validateDates()) {
                            e.preventDefault();
                            showAlert('Veuillez corriger les dates avant de soumettre le formulaire.', 'error');
                            return false;
                        }
                        
                        // Vérifier la pièce d'identité recto (requis)
                        @if($formConfig->isRequired('piece_identite_recto'))
                        const isUploadVisible = uploadFilesSection && uploadFilesSection.classList.contains('show');
                        const isCameraVisible = cameraSection && cameraSection.classList.contains('show');
                        const hasFileRecto = fileRecto && fileRecto.files && fileRecto.files.length > 0;
                        const hasPhotoRecto = photoRectoData && photoRectoData.value && photoRectoData.value.trim() !== '';
                        
                        if ((isUploadVisible && !hasFileRecto) || (isCameraVisible && !hasPhotoRecto)) {
                            e.preventDefault();
                            showAlert('Veuillez fournir une pièce d\'identité (recto) en téléchargeant un fichier ou en prenant une photo.', 'error');
                            
                            // Afficher la section appropriée et mettre en focus
                            if (!isUploadVisible && !isCameraVisible) {
                                // Aucune méthode sélectionnée, afficher la section upload par défaut
                                if (uploadMethodBtn) {
                                    uploadMethodBtn.click();
                                }
                            }
                            
                            if (isUploadVisible && fileRecto) {
                                fileRecto.focus();
                            } else if (isCameraVisible && startCameraRectoBtn) {
                                startCameraRectoBtn.focus();
                            }
                            
                            return false;
                        }
                        @endif
                    });
                }
            }
            
            // Calcul automatique du nombre de nuits (méthode professionnelle hôtellerie)
            function calculateNights() {
                const arrivalDate = dateArriveeInput?.value;
                const departureDate = dateDepartInput?.value;
                const nombreNuitsInput = document.getElementById('nombreNuits');
                
                if (!nombreNuitsInput) {
                    console.error('nombreNuits input not found');
                    return;
                }
                
                // Si l'une des dates manque, vider le champ
                if (!arrivalDate || !departureDate) {
                    nombreNuitsInput.value = '';
                    return;
                }
                
                try {
                    // Créer les dates en UTC pour éviter les problèmes de fuseau horaire
                    const arrival = new Date(arrivalDate + 'T12:00:00');
                    const departure = new Date(departureDate + 'T12:00:00');
                    
                    // Vérifier que les dates sont valides
                    if (isNaN(arrival.getTime()) || isNaN(departure.getTime())) {
                        nombreNuitsInput.value = '';
                        console.error('Invalid date format');
                        return;
                    }
                    
                    // Vérifier que la date de départ est après la date d'arrivée
                    if (departure <= arrival) {
                        nombreNuitsInput.value = '';
                        return;
                    }
                    
                    // Calcul professionnel : nombre de nuits = date départ - date arrivée
                    // Exemple : Arrivée 01/01, Départ 03/01 = 2 nuits (nuit du 01 au 02, nuit du 02 au 03)
                    const diffTime = departure.getTime() - arrival.getTime();
                    const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
                    
                    // Validation : entre 1 et 365 nuits (limite raisonnable)
                    if (diffDays >= 1 && diffDays <= 365) {
                        nombreNuitsInput.value = diffDays;
                        console.log('✓ Nuitées calculées:', diffDays, 'nuit(s) entre', arrivalDate, 'et', departureDate);
                        
                        // Recalculer le prix total si une chambre est sélectionnée
                        if (typeof calculateTotalPrice === 'function') {
                            calculateTotalPrice();
                        }
                    } else if (diffDays < 1) {
                        nombreNuitsInput.value = '';
                        console.warn('Nombre de nuits invalide (minimum 1 nuit)');
                    } else {
                        nombreNuitsInput.value = '';
                        console.warn('Nombre de nuits trop élevé (maximum 365 nuits)');
                    }
                } catch (error) {
                    nombreNuitsInput.value = '';
                    console.error('Erreur lors du calcul des nuits:', error);
                }
            }
            
            // Calcul du prix total (prix chambre x nombre de nuits)
            const roomTypeSelectForPrice = document.getElementById('roomTypeSelect');
            const prixChambreInfo = document.getElementById('prixChambreInfo');
            const prixTotalSpan = document.getElementById('prixTotal');
            
            function calculateTotalPrice() {
                if (!roomTypeSelectForPrice || !prixChambreInfo || !prixTotalSpan) {
                    console.log('Price calculation elements not found');
                    return;
                }
                
                try {
                const selectedOption = roomTypeSelectForPrice.options[roomTypeSelectForPrice.selectedIndex];
                    const pricePerNight = selectedOption?.getAttribute('data-price');
                    const nombreNuitsInput = document.getElementById('nombreNuits');
                    const numberOfNights = nombreNuitsInput?.value;
                
                console.log('Calculating price - Price per night:', pricePerNight, 'Nights:', numberOfNights);
                
                if (pricePerNight && numberOfNights && numberOfNights > 0) {
                    const totalPrice = parseFloat(pricePerNight) * parseInt(numberOfNights);
                        
                        if (!isNaN(totalPrice) && totalPrice > 0) {
                    prixTotalSpan.textContent = new Intl.NumberFormat('fr-FR').format(totalPrice);
                    prixChambreInfo.style.display = 'block';
                            console.log('✓ Total price calculated:', totalPrice, 'FCFA');
                } else {
                    prixChambreInfo.style.display = 'none';
                            console.log('Invalid total price calculation');
                        }
                    } else {
                        prixChambreInfo.style.display = 'none';
                        console.log('Price info hidden - missing data (price:', pricePerNight, 'nights:', numberOfNights, ')');
                    }
                } catch (error) {
                    prixChambreInfo.style.display = 'none';
                    console.error('Error calculating total price:', error);
                }
            }
            
            // NOTE: L'événement change sur roomTypeSelect est géré dans la section GESTION DES CHAMBRES ci-dessous
            // pour éviter les doublons d'écouteurs d'événements
            
            // Validation de la date de naissance (au moins 18 ans)
            // Note: Le champ HTML5 date avec max/min gère déjà la restriction
            // Cette validation JS est un filet de sécurité supplémentaire
            const dateNaissance = document.getElementById('dateNaissance');
            if (dateNaissance) {
                // Définir dynamiquement les limites de date
                const maxDate = new Date();
                maxDate.setFullYear(maxDate.getFullYear() - 18);
                
                const minDate = new Date();
                minDate.setFullYear(minDate.getFullYear() - 120);
                
                // Formater en YYYY-MM-DD
                const maxDateStr = maxDate.toISOString().split('T')[0];
                const minDateStr = minDate.toISOString().split('T')[0];
                
                // Appliquer les limites
                dateNaissance.setAttribute('max', maxDateStr);
                dateNaissance.setAttribute('min', minDateStr);
                
                console.log('Date de naissance - Limites définies:', {
                    min: minDateStr,
                    max: maxDateStr
                });
                
                // Validation supplémentaire au changement
                dateNaissance.addEventListener('change', function() {
                    const selectedDate = new Date(this.value);
                    const today = new Date();
                    const age = today.getFullYear() - selectedDate.getFullYear();
                    const monthDiff = today.getMonth() - selectedDate.getMonth();
                    const dayDiff = today.getDate() - selectedDate.getDate();
                    
                    // Calculer l'âge exact
                    let calculatedAge = age;
                    if (monthDiff < 0 || (monthDiff === 0 && dayDiff < 0)) {
                        calculatedAge--;
                    }
                    
                    if (calculatedAge < 18) {
                        showAlert('Vous devez avoir au moins 18 ans pour effectuer une réservation.', 'warning');
                        this.value = '';
                    } else if (calculatedAge > 120) {
                        showAlert('La date de naissance n\'est pas valide.', 'error');
                        this.value = '';
                    }
                });
            }
            
            // Gestion des accompagnants (si nombre d'adultes >= 2)
            const nombreAdultes = document.getElementById('nombreAdultes');
            const accompagnantsFields = document.getElementById('accompagnantsFields');
            const accompagnantsContainer = document.getElementById('accompagnantsContainer');
            
            nombreAdultes.addEventListener('change', function() {
                const nbAdultes = parseInt(this.value) || 1;
                console.log('Nombre adultes:', nbAdultes);
                
                if (nbAdultes >= 2) {
                    // Afficher la section accompagnants
                    accompagnantsFields.classList.add('show');
                    
                    // Générer les champs pour les accompagnants (nbAdultes - 1)
                    const nbAccompagnants = nbAdultes - 1;
                    accompagnantsContainer.innerHTML = '';
                    
                    for (let i = 1; i <= nbAccompagnants; i++) {
                        const accompagnantDiv = document.createElement('div');
                        accompagnantDiv.className = 'row mb-2';
                        accompagnantDiv.innerHTML = `
                            <div class="col-md-6">
                                <input type="text" 
                                       name="accompagnant_nom_${i}" 
                                       class="form-control" 
                                       placeholder="Nom de l'accompagnant ${i}">
                            </div>
                            <div class="col-md-6">
                                <input type="text" 
                                       name="accompagnant_prenom_${i}" 
                                       class="form-control" 
                                       placeholder="Prénom de l'accompagnant ${i}">
                            </div>
                        `;
                        accompagnantsContainer.appendChild(accompagnantDiv);
                    }
                } else {
                    // Masquer la section accompagnants
                    accompagnantsFields.classList.remove('show');
                    accompagnantsContainer.innerHTML = '';
                }
            });
            
            // Validation de l'email en temps réel
            const emailInput = document.getElementById('emailInput');
            const emailError = document.getElementById('emailError');
            const emailErrorMessage = document.getElementById('emailErrorMessage');
            const showPreviewBtn = document.getElementById('showPreviewBtn');
            let isEmailValid = false;
            
            // Regex pour valider le format email
            const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            
            function validateEmail() {
                const emailValue = emailInput.value.trim();
                
                if (emailValue === '') {
                    // Email vide
                    emailError.style.display = 'none';
                    emailInput.style.borderColor = '';
                    isEmailValid = false;
                    updateSubmitButton();
                    return;
                }
                
                if (!emailRegex.test(emailValue)) {
                    // Email invalide
                    emailError.style.display = 'block';
                    emailErrorMessage.textContent = 'Format d\'email invalide. Exemple: nom@exemple.com';
                    emailInput.style.borderColor = '#dc3545';
                    isEmailValid = false;
                    updateSubmitButton();
                } else {
                    // Email valide
                    emailError.style.display = 'none';
                    emailInput.style.borderColor = '#198754';
                    isEmailValid = true;
                    updateSubmitButton();
                }
            }
            
            function updateSubmitButton() {
                if (!isEmailValid && emailInput.value.trim() !== '') {
                    // Désactiver le bouton si email invalide
                    showPreviewBtn.disabled = true;
                    showPreviewBtn.style.opacity = '0.5';
                    showPreviewBtn.style.cursor = 'not-allowed';
                } else {
                    // Activer le bouton si email valide ou vide (validation HTML5 se chargera du vide)
                    showPreviewBtn.disabled = false;
                    showPreviewBtn.style.opacity = '1';
                    showPreviewBtn.style.cursor = 'pointer';
                }
            }
            
            // Écouter les événements sur le champ email
            emailInput.addEventListener('input', validateEmail);
            emailInput.addEventListener('blur', validateEmail);
            emailInput.addEventListener('keyup', validateEmail);
            
            // Initialisation de la signature
            const canvas = document.getElementById('signaturePad');
            
            // Configuration pour mobile et desktop
            const signaturePad = new SignaturePad(canvas, {
                backgroundColor: 'rgb(255, 255, 255)',
                penColor: 'rgb(0, 0, 0)',
                minWidth: 0.5,
                maxWidth: 2.5,
                throttle: 16, // Améliore la performance sur mobile
                velocityFilterWeight: 0.7
            });
            
            // Responsive canvas - Amélioration pour mobile
            function resizeCanvas() {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                const canvasWidth = canvas.offsetWidth;
                const canvasHeight = canvas.offsetHeight;
                
                // Sauvegarder la signature existante
                const signatureData = signaturePad.isEmpty() ? null : signaturePad.toDataURL();
                
                // Redimensionner le canvas
                canvas.width = canvasWidth * ratio;
                canvas.height = canvasHeight * ratio;
                canvas.style.width = canvasWidth + 'px';
                canvas.style.height = canvasHeight + 'px';
                
                const context = canvas.getContext("2d");
                context.scale(ratio, ratio);
                
                // Restaurer la signature si elle existait
                if (signatureData) {
                    signaturePad.fromDataURL(signatureData);
                }
                
                console.log('Canvas resized:', canvasWidth, 'x', canvasHeight, 'ratio:', ratio);
            }
            
            window.addEventListener("resize", resizeCanvas);
            resizeCanvas();
            
            // Prévenir le scroll sur mobile quand on signe
            canvas.addEventListener('touchstart', function(e) {
                if (e.target === canvas) {
                    e.preventDefault();
                }
            }, { passive: false });
            
            canvas.addEventListener('touchmove', function(e) {
                if (e.target === canvas) {
                    e.preventDefault();
                }
            }, { passive: false });
            
            document.getElementById('clearSignatureBtn').addEventListener('click', function() {
                console.log('Clearing signature...');
                signaturePad.clear();
            });
            
            // Fonction d'aperçu
            document.getElementById('showPreviewBtn').addEventListener('click', function() {
                console.log('Showing preview...');
                
                // Désactiver les champs requis dans les sections cachées avant validation
                if (typeof toggleRequiredFields === 'function') {
                    toggleRequiredFields();
                }
                
                // Vérifier la validité de l'email avant tout
                const emailValue = emailInput.value.trim();
                if (emailValue !== '' && !emailRegex.test(emailValue)) {
                    showAlert('Veuillez saisir une adresse email valide avant de continuer.', 'warning');
                    emailInput.focus();
                    return;
                }
                
                const form = document.getElementById('reservationForm');
                
                // Vérifier la pièce d'identité recto avant validation HTML5
                @if($formConfig->isRequired('piece_identite_recto'))
                const uploadFilesSectionCheck = document.getElementById('uploadFilesSection');
                const cameraSectionCheck = document.getElementById('cameraSection');
                const fileRectoCheck = document.getElementById('fileRecto');
                const photoRectoDataElCheck = document.getElementById('photoRectoData');
                const uploadMethodBtnCheck = document.getElementById('uploadMethodBtn');
                const startCameraRectoBtnCheck = document.getElementById('startCameraRectoBtn');
                
                if (uploadFilesSectionCheck && cameraSectionCheck && fileRectoCheck && photoRectoDataElCheck) {
                    const isUploadVisible = uploadFilesSectionCheck.classList.contains('show');
                    const isCameraVisible = cameraSectionCheck.classList.contains('show');
                    const hasFileRecto = fileRectoCheck.files && fileRectoCheck.files.length > 0;
                    const hasPhotoRecto = photoRectoDataElCheck.value && photoRectoDataElCheck.value.trim() !== '';
                    
                    if ((isUploadVisible && !hasFileRecto) || (isCameraVisible && !hasPhotoRecto)) {
                        showAlert('Veuillez fournir une pièce d\'identité (recto) en téléchargeant un fichier ou en prenant une photo avant de continuer.', 'error');
                        
                        // Afficher la section appropriée
                        if (!isUploadVisible && !isCameraVisible) {
                            if (uploadMethodBtnCheck) {
                                uploadMethodBtnCheck.click();
                            }
                        }
                        
                        if (isUploadVisible && fileRectoCheck) {
                            fileRectoCheck.focus();
                        } else if (isCameraVisible && startCameraRectoBtnCheck) {
                            startCameraRectoBtnCheck.focus();
                        }
                        
                        return;
                    }
                }
                @endif
                
                if (!form.checkValidity()) {
                    // Réactiver les champs pour afficher les erreurs
                    const fileRectoValidation = document.getElementById('fileRecto');
                    const fileVersoValidation = document.getElementById('fileVerso');
                    if (fileRectoValidation) fileRectoValidation.removeAttribute('disabled');
                    if (fileVersoValidation) fileVersoValidation.removeAttribute('disabled');
                    form.reportValidity();
                    return;
                }
                
                if (signaturePad.isEmpty()) {
                    showAlert('Veuillez signer le formulaire avant de continuer.', 'warning');
                    return;
                }
                
                // Capturer la signature
                document.getElementById('signatureData').value = signaturePad.toDataURL();
                
                // Générer l'aperçu
                const formData = new FormData(form);
                let previewHTML = '';
                
                // Section Type de réservation
                previewHTML += '<div class="preview-section">';
                previewHTML += '<h6><i class="bi bi-bookmark me-2"></i>Type de Réservation</h6>';
                previewHTML += '<div class="preview-row"><div class="preview-label">Type:</div><div class="preview-value">' + formData.get('type_reservation') + '</div></div>';
                if (formData.get('nom_groupe')) {
                    previewHTML += '<div class="preview-row"><div class="preview-label">Nom du Groupe:</div><div class="preview-value">' + formData.get('nom_groupe') + '</div></div>';
                    previewHTML += '<div class="preview-row"><div class="preview-label">Code Groupe:</div><div class="preview-value">' + formData.get('code_groupe') + '</div></div>';
                }
                previewHTML += '</div>';
                
                // Section Informations personnelles
                previewHTML += '<div class="preview-section">';
                previewHTML += '<h6><i class="bi bi-person me-2"></i>Informations Personnelles</h6>';
                previewHTML += '<div class="preview-row"><div class="preview-label">Type Pièce:</div><div class="preview-value">' + formData.get('type_piece_identite') + '</div></div>';
                previewHTML += '<div class="preview-row"><div class="preview-label">N° Pièce:</div><div class="preview-value">' + formData.get('numero_piece_identite') + '</div></div>';
                
                // Afficher les images des pièces d'identité
                const photoRectoDataValue = document.getElementById('photoRectoData').value;
                const photoVersoDataValue = document.getElementById('photoVersoData').value;
                const fileRectoInput = document.getElementById('fileRecto');
                const fileVersoInput = document.getElementById('fileVerso');
                
                if (photoRectoDataValue || (fileRectoInput && fileRectoInput.files.length > 0)) {
                    previewHTML += '<div class="preview-row" style="grid-template-columns: 1fr; padding: 10px 0;">';
                    previewHTML += '<div class="preview-label mb-2"><strong>Pièce d\'Identité (Recto):</strong></div>';
                    if (photoRectoDataValue) {
                        previewHTML += '<img src="' + photoRectoDataValue + '" class="img-fluid rounded border" style="max-height: 200px;" alt="Recto">';
                    } else if (fileRectoInput && fileRectoInput.files.length > 0) {
                        previewHTML += '<div class="text-success"><i class="bi bi-check-circle me-1"></i>Fichier recto uploadé: ' + fileRectoInput.files[0].name + '</div>';
                    }
                    previewHTML += '</div>';
                }
                
                if (photoVersoDataValue || (fileVersoInput && fileVersoInput.files.length > 0)) {
                    previewHTML += '<div class="preview-row" style="grid-template-columns: 1fr; padding: 10px 0;">';
                    previewHTML += '<div class="preview-label mb-2"><strong>Pièce d\'Identité (Verso):</strong></div>';
                    if (photoVersoDataValue) {
                        previewHTML += '<img src="' + photoVersoDataValue + '" class="img-fluid rounded border" style="max-height: 200px;" alt="Verso">';
                    } else if (fileVersoInput && fileVersoInput.files.length > 0) {
                        previewHTML += '<div class="text-success"><i class="bi bi-check-circle me-1"></i>Fichier verso uploadé: ' + fileVersoInput.files[0].name + '</div>';
                    }
                    previewHTML += '</div>';
                }
                
                previewHTML += '<div class="preview-row"><div class="preview-label">Nom:</div><div class="preview-value">' + formData.get('nom') + '</div></div>';
                previewHTML += '<div class="preview-row"><div class="preview-label">Prénom:</div><div class="preview-value">' + formData.get('prenom') + '</div></div>';
                previewHTML += '<div class="preview-row"><div class="preview-label">Sexe:</div><div class="preview-value">' + formData.get('sexe') + '</div></div>';
                previewHTML += '<div class="preview-row"><div class="preview-label">Date de Naissance:</div><div class="preview-value">' + formData.get('date_naissance') + '</div></div>';
                previewHTML += '<div class="preview-row"><div class="preview-label">Lieu de Naissance:</div><div class="preview-value">' + formData.get('lieu_naissance') + '</div></div>';
                previewHTML += '<div class="preview-row"><div class="preview-label">Nationalité:</div><div class="preview-value">' + formData.get('nationalite') + '</div></div>';
                previewHTML += '</div>';
                
                // Section Coordonnées
                previewHTML += '<div class="preview-section">';
                previewHTML += '<h6><i class="bi bi-envelope me-2"></i>Coordonnées</h6>';
                previewHTML += '<div class="preview-row"><div class="preview-label">Adresse:</div><div class="preview-value">' + formData.get('adresse') + '</div></div>';
                
                // Récupérer le numéro de téléphone via intl-tel-input
                let telephoneNumber = '';
                try {
                    const phoneInputEl = document.querySelector("#telephone");
                    if (phoneInputEl && window.itiInstance) {
                        telephoneNumber = window.itiInstance.getNumber() || phoneInputEl.value || formData.get('telephone');
                    } else if (phoneInputEl) {
                        telephoneNumber = phoneInputEl.value || formData.get('telephone');
                    } else {
                        telephoneNumber = formData.get('telephone') || '';
                    }
                } catch (e) {
                    console.warn('Erreur lors de la récupération du numéro de téléphone:', e);
                    telephoneNumber = formData.get('telephone') || '';
                }
                previewHTML += '<div class="preview-row"><div class="preview-label">Téléphone:</div><div class="preview-value">' + (telephoneNumber || 'Non renseigné') + '</div></div>';
                previewHTML += '<div class="preview-row"><div class="preview-label">Email:</div><div class="preview-value">' + formData.get('email') + '</div></div>';
                previewHTML += '<div class="preview-row"><div class="preview-label">Profession:</div><div class="preview-value">' + formData.get('profession') + '</div></div>';
                previewHTML += '</div>';
                
                // Section Séjour
                previewHTML += '<div class="preview-section">';
                previewHTML += '<h6><i class="bi bi-calendar-check me-2"></i>Informations du Séjour</h6>';
                previewHTML += '<div class="preview-row"><div class="preview-label">Venant de:</div><div class="preview-value">' + formData.get('venant_de') + '</div></div>';
                previewHTML += '<div class="preview-row"><div class="preview-label">Date d\'Arrivée:</div><div class="preview-value">' + formData.get('date_arrivee') + '</div></div>';
                if (formData.get('heure_arrivee')) {
                    previewHTML += '<div class="preview-row"><div class="preview-label">Heure d\'Arrivée:</div><div class="preview-value">' + formData.get('heure_arrivee') + '</div></div>';
                }
                previewHTML += '<div class="preview-row"><div class="preview-label">Date de Départ:</div><div class="preview-value">' + formData.get('date_depart') + '</div></div>';
                previewHTML += '<div class="preview-row"><div class="preview-label">Nombre de Nuits:</div><div class="preview-value">' + formData.get('nombre_nuits') + '</div></div>';
                previewHTML += '<div class="preview-row"><div class="preview-label">Adultes:</div><div class="preview-value">' + formData.get('nombre_adultes') + '</div></div>';
                previewHTML += '<div class="preview-row"><div class="preview-label">Enfants:</div><div class="preview-value">' + formData.get('nombre_enfants') + '</div></div>';
                previewHTML += '<div class="preview-row"><div class="preview-label">Type de Chambre:</div><div class="preview-value">' + formData.get('type_chambre') + '</div></div>';
                
                // Afficher le numéro de chambre si sélectionné
                const roomSelect = document.getElementById('roomSelect');
                if (roomSelect && roomSelect.value) {
                    const selectedRoomOption = roomSelect.options[roomSelect.selectedIndex];
                    const roomNumber = selectedRoomOption.textContent;
                    previewHTML += '<div class="preview-row"><div class="preview-label">Numéro de Chambre:</div><div class="preview-value"><span class="badge bg-primary">' + roomNumber + '</span></div></div>';
                } else {
                    previewHTML += '<div class="preview-row"><div class="preview-label">Numéro de Chambre:</div><div class="preview-value"><span class="text-muted">Non spécifié (sera attribué par l\'hôtel)</span></div></div>';
                }
                
                if (formData.get('preferences')) {
                    previewHTML += '<div class="preview-row"><div class="preview-label">Préférences:</div><div class="preview-value">' + formData.get('preferences') + '</div></div>';
                }
                previewHTML += '</div>';
                
                // Accompagnants (si présents)
                const nbAdultes = parseInt(formData.get('nombre_adultes')) || 0;
                if (nbAdultes >= 2) {
                    let hasAccompagnants = false;
                    let accompagnantsHTML = '';
                    
                    for (let i = 1; i < nbAdultes; i++) {
                        const nom = formData.get('accompagnant_nom_' + i);
                        const prenom = formData.get('accompagnant_prenom_' + i);
                        
                        if (nom || prenom) {
                            if (!hasAccompagnants) {
                                accompagnantsHTML += '<div class="preview-section">';
                                accompagnantsHTML += '<h6><i class="bi bi-people me-2"></i>Accompagnants</h6>';
                                hasAccompagnants = true;
                            }
                            
                            accompagnantsHTML += '<div class="preview-row"><div class="preview-label">Accompagnant ' + i + ':</div><div class="preview-value">' + (prenom || '') + ' ' + (nom || '') + '</div></div>';
                        }
                    }
                    
                    if (hasAccompagnants) {
                        accompagnantsHTML += '</div>';
                        previewHTML += accompagnantsHTML;
                    }
                }
                
                // Signature
                previewHTML += '<div class="preview-section">';
                previewHTML += '<h6><i class="bi bi-pen me-2"></i>Signature</h6>';
                previewHTML += '<img src="' + signaturePad.toDataURL() + '" class="img-fluid border rounded" alt="Signature">';
                previewHTML += '</div>';
                
                document.getElementById('previewContent').innerHTML = previewHTML;
                
                const modal = new bootstrap.Modal(document.getElementById('previewModal'));
                modal.show();
            });
            
            // Fonction de confirmation et envoi
            document.getElementById('confirmSubmitBtn').addEventListener('click', async function() {
                console.log('Confirming and submitting...');
                document.getElementById('loadingOverlay').classList.add('show');
                
                bootstrap.Modal.getInstance(document.getElementById('previewModal')).hide();
                
                // Rafraîchir le token CSRF avant la soumission pour éviter "page expired"
                try {
                    const response = await fetch('{{ route("public.form", $hotel) }}', {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    
                    if (response.ok) {
                        const html = await response.text();
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const newToken = doc.querySelector('input[name="_token"]')?.value;
                        
                        if (newToken) {
                            // Mettre à jour le token dans le formulaire
                            const tokenInput = document.querySelector('input[name="_token"]');
                            if (tokenInput) {
                                tokenInput.value = newToken;
                                console.log('CSRF token refreshed successfully');
                            }
                        }
                    }
                } catch (error) {
                    console.error('Error refreshing CSRF token:', error);
                    // Continuer quand même avec le token existant
                }
                
                // Soumettre le formulaire
                setTimeout(() => {
                    document.getElementById('reservationForm').submit();
                }, 300);
            });
            
            // ========== BOUTON EFFACER TOUT ==========
            document.getElementById('resetFormBtn')?.addEventListener('click', function() {
                if (!confirm('Êtes-vous sûr de vouloir effacer tout le formulaire ? Cette action est irréversible.')) {
                    return;
                }
                
                console.log('Resetting entire form...');
                
                // 1. Réinitialiser le formulaire HTML
                document.getElementById('reservationForm').reset();
                
                // 2. Effacer le localStorage
                const storageKey = 'reservationForm_{{ $hotel->id }}';
                localStorage.removeItem(storageKey);
                
                // 3. Effacer la signature
                if (typeof signaturePad !== 'undefined' && signaturePad) {
                    signaturePad.clear();
                }
                const signatureData = document.getElementById('signatureData');
                if (signatureData) {
                    signatureData.value = '';
                }
                
                // 4. Réinitialiser Select2
                if (typeof jQuery !== 'undefined') {
                    jQuery('.select2').each(function() {
                        jQuery(this).val(null).trigger('change');
                    });
                }
                
                // 5. Réinitialiser intlTelInput
                if (typeof iti !== 'undefined' && iti) {
                    iti.setNumber('');
                }
                
                // 6. Effacer les aperçus d'images
                const frontPreview = document.getElementById('frontImagePreview');
                const backPreview = document.getElementById('backImagePreview');
                if (frontPreview) {
                    frontPreview.innerHTML = '<div class="upload-placeholder"><i class="bi bi-card-image"></i><span>Recto</span></div>';
                }
                if (backPreview) {
                    backPreview.innerHTML = '<div class="upload-placeholder"><i class="bi bi-card-image"></i><span>Verso</span></div>';
                }
                
                // 7. Réinitialiser les champs cachés
                document.getElementById('check_in_date')?.remove();
                document.getElementById('check_out_date')?.remove();
                
                // 8. Réinitialiser le select des chambres
                const roomSelect = document.getElementById('roomSelect');
                if (roomSelect) {
                    roomSelect.innerHTML = '<option value="">-- Aucune chambre spécifique (optionnel) --</option>';
                }
                
                // 9. Réinitialiser les champs de date
                const dateArrivee = document.getElementById('dateArrivee');
                if (dateArrivee) {
                    dateArrivee.value = '';
                }
                const dateDepart = document.getElementById('dateDepart');
                if (dateDepart) {
                    dateDepart.value = '';
                }
                
                // 10. Réinitialiser les fichiers uploadés
                const frontImage = document.getElementById('frontImage');
                if (frontImage) {
                    frontImage.value = '';
                }
                const backImage = document.getElementById('backImage');
                if (backImage) {
                    backImage.value = '';
                }
                
                // 11. Scroll vers le haut du formulaire
                window.scrollTo({ top: 0, behavior: 'smooth' });
                
                // 12. Afficher un message de confirmation
                alert('Le formulaire a été complètement réinitialisé.');
                
                console.log('Form reset complete!');
            });
            // ========== FIN BOUTON EFFACER TOUT ==========
            
            // ========== GESTION DES CHAMBRES ==========
            // Données des chambres passées depuis le serveur
            const roomsData = @json($rooms ?? []);
            console.log('Rooms data loaded:', roomsData.length, 'rooms available');
            
            // Fonction pour charger les chambres disponibles
            window.loadAvailableRooms = function(roomTypeId) {
                const roomSelect = document.getElementById('roomSelect');
                const roomSelectContainer = document.getElementById('roomSelectContainer');
                
                if (!roomSelect || !roomSelectContainer) {
                    console.error('Room select elements not found');
                    return;
                }
                
                // Réinitialiser le select
                roomSelect.innerHTML = '<option value="">-- Aucune chambre spécifique (optionnel) --</option>';
                
                if (!roomTypeId) {
                    console.log('No room type selected, hiding room container');
                    roomSelectContainer.style.display = 'none';
                    return;
                }
                
                console.log('=== Loading rooms for type ID:', roomTypeId, '===');
                
                // Vérifier que roomsData existe et est un tableau
                if (!Array.isArray(roomsData)) {
                    console.error('roomsData is not an array:', roomsData);
                    roomSelectContainer.style.display = 'none';
                    return;
                }
                
                // Filtrer les chambres pour ce type
                const availableRooms = roomsData.filter(room => {
                    return room.room_type_id == roomTypeId && room.status === 'available';
                });
                
                console.log('Available rooms for this type:', availableRooms.length);
                
                if (availableRooms.length > 0) {
                    availableRooms.forEach(room => {
                        const option = document.createElement('option');
                        option.value = room.id;
                        
                        // Construire le texte de l'option
                        let roomText = `Chambre ${room.room_number}`;
                        if (room.floor) {
                            roomText += ` - Étage ${room.floor}`;
                        }
                        
                        option.textContent = roomText;
                        roomSelect.appendChild(option);
                    });
                    
                    roomSelectContainer.style.display = 'block';
                    console.log('✓ Room select populated with', availableRooms.length, 'rooms');
                } else {
                    const noRoomsOption = document.createElement('option');
                    noRoomsOption.value = '';
                    noRoomsOption.textContent = '-- Aucune chambre disponible pour ce type --';
                    noRoomsOption.disabled = true;
                    roomSelect.appendChild(noRoomsOption);
                    
                    roomSelectContainer.style.display = 'block';
                    console.log('⚠ No rooms available for this type');
                }
            };
            
            // Gestionnaire UNIQUE pour le changement de type de chambre
            // ⚠️ UN SEUL EVENT LISTENER pour éviter les doublons !
            console.log('🔍 Initialisation de la gestion du type de chambre...');
            // Récupérer le select à nouveau pour s'assurer qu'il est accessible
            const roomTypeSelect = document.getElementById('roomTypeSelect');
            console.log('📋 Élément roomTypeSelect trouvé:', !!roomTypeSelect);
            
            if (roomTypeSelect) {
                // Vérifier si un listener existe déjà pour éviter les doublons
                if (!roomTypeSelect.hasAttribute('data-listener-attached')) {
                    roomTypeSelect.setAttribute('data-listener-attached', 'true');
                    
                    roomTypeSelect.addEventListener('change', function() {
                        const selectedOption = this.options[this.selectedIndex];
                        const typeName = selectedOption?.getAttribute('data-name');
                        const roomTypeId = this.value;
                        
                        console.log('🔄 Type de chambre changé:', roomTypeId, typeName);
                        
                        // 1. Mettre à jour l'ancien select (compatibilité)
                        const oldSelect = document.getElementById('typeChambre');
                        if (oldSelect && typeName) {
                            const matchingOption = Array.from(oldSelect.options).find(
                                opt => opt.value === typeName
                            );
                            if (matchingOption) {
                                oldSelect.value = matchingOption.value;
                            }
                        }
                        
                        // 2. Charger les chambres disponibles
                        if (roomTypeId) {
                            if (typeof loadAvailableRooms === 'function') {
                                console.log('📞 Appel de loadAvailableRooms avec ID:', roomTypeId);
                                loadAvailableRooms(roomTypeId);
                            } else {
                                console.warn('⚠️ loadAvailableRooms function not found');
                            }
                        } else {
                            // Cacher le conteneur si aucun type sélectionné
                            const roomSelectContainer = document.getElementById('roomSelectContainer');
                            if (roomSelectContainer) {
                                roomSelectContainer.style.display = 'none';
                            }
                        }
                        
                        // 3. Calculer le prix total
                        if (typeof calculateTotalPrice === 'function') {
                            console.log('💰 Calcul du prix total...');
                            calculateTotalPrice();
                        } else {
                            console.warn('⚠️ calculateTotalPrice function not found');
                        }
                    });
                    
                    console.log('✅ Event listener ajouté sur roomTypeSelect');
                } else {
                    console.log('ℹ️ Room type select listener already attached');
                }
                console.log('✅ Initialisation du type de chambre terminée');
            } else {
                console.warn('⚠️ Room type select element (roomTypeSelect) not found');
            }
            // ========== FIN GESTION DES CHAMBRES ==========
            
            console.log('═══════════════════════════════════════');
            console.log('✅ INITIALISATION DU FORMULAIRE TERMINÉE');
            console.log('═══════════════════════════════════════');
        });
        
        // Vérification immédiate que le script s'exécute
        console.log('📜 Script formulaire public chargé');
    </script>
</body>
</html>