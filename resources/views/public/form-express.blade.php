<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Check-in - {{ $hotel->name }}</title>
    
    <!-- Assets locaux uniquement (offline) -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
    
    <style>
        :root {
            --primary-color: {{ $hotel->primary_color ?? '#1a4b8c' }};
            --secondary-color: {{ $hotel->secondary_color ?? '#2563a8' }};
        }
        
        body {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 100vh;
            padding: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .form-container {
            max-width: 700px;
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
            padding: 30px;
            text-align: center;
        }
        
        .hotel-logo {
            max-height: 80px;
            max-width: 150px;
            background: white;
            padding: 10px;
            border-radius: 10px;
            margin-bottom: 15px;
        }
        
        .form-header h1 {
            font-size: 28px;
            font-weight: 700;
            margin: 0 0 10px 0;
        }
        
        .form-body {
            padding: 30px;
        }
        
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        
        .form-control, .form-select {
            border-radius: 10px;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(26, 75, 140, 0.15);
        }
        
        .btn-submit {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 10px;
            font-size: 18px;
            font-weight: 600;
            width: 100%;
            margin-top: 20px;
            transition: all 0.3s ease;
        }
        
        .btn-submit:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
        }
        
        .section-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--primary-color);
            margin: 25px 0 15px 0;
            padding-bottom: 10px;
            border-bottom: 3px solid var(--primary-color);
        }
        
        .alert {
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 20px;
        }
        
        .required-star {
            color: #dc3545;
        }
        
        .help-text {
            font-size: 13px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="form-card">
            <!-- En-tête -->
            <div class="form-header">
                @if($hotel->logo_url)
                    <img src="{{ $hotel->logo_url }}" alt="{{ $hotel->name }}" class="hotel-logo">
                @endif
                <h1>{{ $hotel->name }}</h1>
                <p class="mb-0">Formulaire de check-in express</p>
            </div>
            
            <div class="form-body">
                <!-- Messages de succès/erreur -->
                @if(session('success'))
                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                </div>
                @endif

                @if($errors->any())
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Erreurs détectées :</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                
                <!-- Formulaire simplifié -->
                <form method="POST" action="{{ route('public.form.store', $hotel) }}" id="checkinForm">
                    @csrf
                    
                    <!-- Section 1 : Informations client -->
                    <h3 class="section-title">
                        <i class="bi bi-person me-2"></i>Vos informations
                    </h3>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Nom <span class="required-star">*</span>
                            </label>
                            <input 
                                type="text" 
                                name="nom" 
                                class="form-control" 
                                value="{{ old('nom') }}" 
                                required
                                placeholder="Votre nom"
                            >
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Prénom <span class="required-star">*</span>
                            </label>
                            <input 
                                type="text" 
                                name="prenom" 
                                class="form-control" 
                                value="{{ old('prenom') }}" 
                                required
                                placeholder="Votre prénom"
                            >
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Email <span class="required-star">*</span>
                            </label>
                            <input 
                                type="email" 
                                name="email" 
                                class="form-control" 
                                value="{{ old('email') }}" 
                                required
                                placeholder="votre@email.com"
                            >
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Téléphone <span class="required-star">*</span>
                            </label>
                            <input 
                                type="tel" 
                                name="telephone" 
                                class="form-control" 
                                value="{{ old('telephone') }}" 
                                required
                                placeholder="+33 6 12 34 56 78"
                            >
                        </div>
                    </div>
                    
                    <!-- Section 2 : Séjour -->
                    <h3 class="section-title">
                        <i class="bi bi-calendar-check me-2"></i>Votre séjour
                    </h3>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Date d'arrivée <span class="required-star">*</span>
                            </label>
                            <input 
                                type="date" 
                                name="date_arrivee" 
                                class="form-control" 
                                value="{{ old('date_arrivee', today()->format('Y-m-d')) }}" 
                                min="{{ today()->format('Y-m-d') }}"
                                required
                            >
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Date de départ <span class="required-star">*</span>
                            </label>
                            <input 
                                type="date" 
                                name="date_depart" 
                                class="form-control" 
                                value="{{ old('date_depart', today()->addDay()->format('Y-m-d')) }}" 
                                min="{{ today()->addDay()->format('Y-m-d') }}"
                                required
                            >
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">
                                Type de chambre <span class="required-star">*</span>
                            </label>
                            <select name="room_type_id" class="form-select" required>
                                <option value="">-- Sélectionner un type de chambre --</option>
                                @foreach($roomTypes as $type)
                                    <option value="{{ $type->id }}" {{ old('room_type_id') == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }} 
                                        @if($type->base_price)
                                            - {{ number_format($type->base_price, 2) }}€ / nuit
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <div class="help-text">
                                <i class="bi bi-info-circle me-1"></i>Le numéro de chambre sera attribué par la réception
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Nombre d'adultes <span class="required-star">*</span>
                            </label>
                            <input 
                                type="number" 
                                name="nombre_adultes" 
                                class="form-control" 
                                value="{{ old('nombre_adultes', 1) }}" 
                                min="1" 
                                max="10"
                                required
                            >
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Nombre d'enfants
                            </label>
                            <input 
                                type="number" 
                                name="nombre_enfants" 
                                class="form-control" 
                                value="{{ old('nombre_enfants', 0) }}" 
                                min="0" 
                                max="10"
                            >
                        </div>
                    </div>
                    
                    <!-- Bouton de soumission -->
                    <button type="submit" class="btn-submit">
                        <i class="bi bi-check-circle me-2"></i>Valider mon arrivée
                    </button>
                    
                    <div class="text-center mt-3">
                        <small class="text-muted">
                            <i class="bi bi-shield-check me-1"></i>
                            Vos données sont sécurisées et ne seront utilisées que pour votre séjour
                        </small>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Info -->
        <div class="text-center text-white">
            <p class="mb-1">
                <i class="bi bi-clock me-1"></i>Temps estimé : 2 minutes
            </p>
            <p class="small">
                La réception validera votre demande dans quelques instants
            </p>
        </div>
    </div>

    <!-- JS local uniquement -->
    <script src="{{ asset('assets/vendor/jquery/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
    
    <script>
    // Validation basique côté client
    document.getElementById('checkinForm').addEventListener('submit', function(e) {
        const dateArrivee = new Date(document.querySelector('[name="date_arrivee"]').value);
        const dateDepart = new Date(document.querySelector('[name="date_depart"]').value);
        
        if (dateDepart <= dateArrivee) {
            e.preventDefault();
            alert('La date de départ doit être après la date d\'arrivée');
            return false;
        }
        
        // Afficher loader
        document.querySelector('.btn-submit').innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Envoi en cours...';
        document.querySelector('.btn-submit').disabled = true;
    });
    </script>
</body>
</html>

