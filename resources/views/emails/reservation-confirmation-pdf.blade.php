<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de Réservation</title>
    <style>
        @page {
            margin: 15mm;
        }
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            font-size: 11px;
            line-height: 1.5;
            color: #000;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #000;
        }
        .hotelpro-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #1a4b8c;
        }
        .hotelpro-subtitle {
            font-size: 10px;
            color: #666;
            margin-bottom: 15px;
            font-style: italic;
        }
        .confirmation-title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            margin: 15px 0;
            text-transform: uppercase;
        }
        .reservation-number {
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            margin: 10px 0 20px;
        }
        .hotel-info {
            text-align: left;
            margin: 15px 0;
            font-size: 11px;
        }
        .hotel-name {
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 5px;
        }
        .hotel-details {
            line-height: 1.6;
        }
        .dates-section {
            display: table;
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
        }
        .date-box {
            display: table-cell;
            width: 33.33%;
            padding: 10px;
            border: 1px solid #000;
            text-align: center;
            vertical-align: top;
        }
        .date-label {
            font-weight: bold;
            font-size: 10px;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .date-value {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 3px;
        }
        .day-name {
            font-size: 10px;
            color: #666;
            font-style: italic;
        }
        .tarif-box {
            margin: 15px 0;
            padding: 10px;
            border: 1px solid #000;
            text-align: center;
        }
        .tarif-label {
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .tarif-value {
            font-size: 14px;
            font-weight: bold;
        }
        .room-section {
            margin: 20px 0;
        }
        .room-type-title {
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .client-name {
            font-weight: bold;
            margin-bottom: 10px;
        }
        .amenities {
            font-size: 10px;
            line-height: 1.8;
            margin: 10px 0;
            color: #333;
        }
        .room-info-box {
            margin: 15px 0;
            padding: 10px;
            background: #f9f9f9;
            border-left: 3px solid #1a4b8c;
        }
        .room-info-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .room-info-text {
            font-size: 10px;
            line-height: 1.6;
        }
        .payment-info {
            margin: 15px 0;
            padding: 10px;
            background: #fff9e6;
            border-left: 3px solid #f59e0b;
        }
        .payment-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .payment-text {
            font-size: 10px;
            line-height: 1.6;
        }
        .cancellation-info {
            margin: 15px 0;
            padding: 10px;
            background: #fee2e2;
            border-left: 3px solid #dc2626;
        }
        .cancellation-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .cancellation-text {
            font-size: 10px;
            line-height: 1.6;
        }
        .important-info {
            margin: 20px 0;
            padding: 12px;
            background: #eff6ff;
            border: 1px solid #3b82f6;
        }
        .important-title {
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 8px;
            color: #1e40af;
        }
        .important-text {
            font-size: 10px;
            line-height: 1.7;
            margin-bottom: 8px;
        }
        .conditions-section {
            margin: 20px 0;
        }
        .conditions-title {
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .condition-item {
            margin: 8px 0;
            font-size: 10px;
            line-height: 1.6;
        }
        .condition-label {
            font-weight: bold;
        }
        .formalities-section {
            margin: 25px 0;
            padding: 15px;
            background: #f0f9ff;
            border: 1px solid #0284c7;
        }
        .formalities-title {
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 12px;
            text-transform: uppercase;
            color: #0c4a6e;
        }
        .formalities-subtitle {
            font-weight: bold;
            font-size: 11px;
            margin-top: 15px;
            margin-bottom: 8px;
            color: #075985;
        }
        .formalities-text {
            font-size: 10px;
            line-height: 1.7;
            margin-bottom: 10px;
        }
        .formalities-list {
            font-size: 10px;
            line-height: 1.8;
            margin-left: 15px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ccc;
            text-align: right;
            font-size: 9px;
            color: #666;
        }
        .separator {
            margin: 15px 0;
            text-align: center;
            font-size: 14px;
            color: #999;
        }
    </style>
</head>
<body>
    @php
        $data = $reservation->data ?? [];
        $nom = strtoupper($data['nom'] ?? '');
        $prenom = $data['prenom'] ?? '';
        $clientFullName = trim($prenom . ' ' . $nom);
        
        $checkIn = \Carbon\Carbon::parse($reservation->check_in_date);
        $checkOut = \Carbon\Carbon::parse($reservation->check_out_date);
        $nuits = $checkIn->diffInDays($checkOut);
        
        // Jours de la semaine en français
        $jours = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
        $jourArrivee = $jours[$checkIn->dayOfWeek];
        $jourDepart = $jours[$checkOut->dayOfWeek];
        
        // Tarif
        $tarif = $reservation->total_amount ?? $reservation->roomType->price ?? 0;
        $tarifFormatted = number_format($tarif, 0, ',', ' ');
        
        // Équipements de la chambre (depuis description ou valeurs par défaut)
        $roomDescription = $reservation->roomType->description ?? '';
        $amenities = !empty($roomDescription) ? $roomDescription : 'douche - climatisation - articles de toilette gratuits - toilettes - salle de bains - chaînes de câble - télévision à écran plat - armoire / penderie - serviettes - étendoir';
        
        // Date limite d'annulation gratuite (1 jour avant)
        $dateLimiteAnnulation = $checkIn->copy()->subDay();
        $fraisAnnulation = $tarif;
        
        // Date et heure actuelle
        $now = now();
        $dateHeure = $now->format('dmY') . ' - ' . $now->format('H:i');
    @endphp

    <!-- En-tête HotelPro -->
    <div class="header">
        <div class="hotelpro-title">HotelPro</div>
        <div class="hotelpro-subtitle">logiciel africain de Gestion hôtelière recommandé par les hôteliers</div>
        
        <div class="confirmation-title">Africa Confirmation de réservation</div>
        
        <div class="reservation-number">
            NUMÉRO DE RÉSERVATION : {{ str_pad($reservation->id, 7, '0', STR_PAD_LEFT) }}
        </div>
    </div>

    <div class="separator">.</div>

    <!-- Informations de l'hôtel -->
    <div class="hotel-info">
        <div class="hotel-name">Nom de l'hôtel : {{ $reservation->hotel->name }}@if($reservation->hotel->settings && isset($reservation->hotel->settings['stars'])) {{ str_repeat('*', $reservation->hotel->settings['stars']) }}@endif</div>
        <div class="hotel-details">
            @if($reservation->hotel->address)
                {{ $reservation->hotel->address }}@if($reservation->hotel->city), {{ $reservation->hotel->city }}@endif
                @if($reservation->hotel->country) - {{ $reservation->hotel->country }}@endif<br>
            @endif
            @if($reservation->hotel->phone)
                TEL. : {{ $reservation->hotel->phone }}<br>
            @endif
            @if($reservation->hotel->email)
                {{ $reservation->hotel->email }}
            @endif
        </div>
    </div>

    <!-- Dates -->
    <div class="dates-section">
        <div class="date-box">
            <div class="date-label">ARRIVÉE</div>
            <div class="date-value">{{ $checkIn->format('d/m/Y') }}</div>
            <div class="day-name">{{ $jourArrivee }}</div>
        </div>
        <div class="date-box">
            <div class="date-label">DÉPART</div>
            <div class="date-value">{{ $checkOut->format('d/m/Y') }}</div>
            <div class="day-name">{{ $jourDepart }}</div>
        </div>
        <div class="date-box">
            <div class="date-label">NUITS</div>
            <div class="date-value" style="font-size: 16px;">{{ $nuits }}</div>
        </div>
    </div>

    <!-- Tarif -->
    <div class="tarif-box">
        <div class="tarif-label">TARIF (XAF)</div>
        <div class="tarif-value">{{ $tarifFormatted }}</div>
    </div>

    <div class="separator">.</div>

    <!-- Type de chambre -->
    <div class="room-section">
        <div class="room-type-title">{{ $reservation->roomType->name ?? 'CHAMBRE STANDARD' }}</div>
        <div class="client-name">Client: {{ $clientFullName }}</div>
        <div class="amenities">{{ $amenities }}</div>
    </div>

    <!-- Chambre réservée -->
    <div class="room-info-box">
        <div class="room-info-title">Chambre réservée :</div>
        <div class="room-info-text">
            @if($reservation->room)
                la chambre {{ $reservation->room->room_number }} a été réservée provisoirement pour vous.
            @else
                une chambre a été réservée provisoirement pour vous.
            @endif
            une autre chambre peut vous être allouée à votre arrivée.
        </div>
    </div>

    <!-- Prépaiment / Dépôt -->
    <div class="payment-info">
        <div class="payment-title">Prépaiement / Dépôt de garantie :</div>
        <div class="payment-text">
            L'établissement demande un prépaiement à l'arrivée.
        </div>
    </div>

    <!-- Frais d'annulation -->
    <div class="cancellation-info">
        <div class="cancellation-title">Frais d'annulation :</div>
        <div class="cancellation-text">
            jusqu'au {{ $dateLimiteAnnulation->format('d/m/Y') }} : 0 XAF<br>
            à partir du {{ $checkIn->format('d/m/Y') }} : {{ $tarifFormatted }} XAF
        </div>
    </div>

    <!-- Informations importantes -->
    <div class="important-info">
        <div class="important-title">Informations importantes</div>
        <div class="important-text">
            Veuillez noter qu'à l'arrivée, vous devez présenter une carte de crédit pour garantir la réservation. Les personnes sans carte de crédit sont tenues de payer la totalité de leur séjour en espèces.
        </div>
        <div class="important-text">
            Veuillez noter que pour tous les séjours, les chambres sont nettoyées tous les jours. Le linge et les serviettes de toilette sont également remplacés chaque jour.
        </div>
        <div class="important-text">
            Veuillez noter que le ménage est compris, quelle que soit la durée de votre séjour. La Taxe de séjour peut vous être facturée en plus.
        </div>
    </div>

    <!-- Conditions de l'établissement -->
    <div class="conditions-section">
        <div class="conditions-title">Conditions de l'établissement</div>
        
        <div class="condition-item">
            <span class="condition-label">Stationnement</span><br>
            - L'établissement dispose d'un parking gardé.
        </div>
        
        <div class="condition-item">
            <span class="condition-label">Internet</span><br>
            - Une connexion WIFI est disponible dans tout l'établissement gratuitement.
        </div>
        
        <div class="condition-item">
            <span class="condition-label">Enfants et lits d'appoint</span><br>
            - Tous les enfants sont les bienvenus.<br>
            - Un lit d'appoint peut être installé dans la chambre.
        </div>
    </div>

    <!-- Formalités d'entrée Cameroun -->
    <div class="formalities-section">
        <div class="formalities-title">Cameroun - Formalités d'entrée</div>
        
        <div class="formalities-subtitle">le Visa d'entrée</div>
        <div class="formalities-text">
            Un visa touristique est délivré par les représentations diplomatiques et consulaires aux ressortissants étrangers venant effectuer un voyage d'agrément. Sa validité est de 30 jours non renouvelable, avec plusieurs entrées et sorties possibles.
        </div>
        <div class="formalities-text">
            Les touristes en provenance de pays où Cameroun n'est pas représenté peuvent obtenir ce visa au poste frontière de leur lieu d'entrée (idem pour les touristes en voyages organisés).
        </div>
        
        <div class="formalities-subtitle">les pièces requises pour obtenir un visa touristique</div>
        <ul class="formalities-list">
            <li>Un passeport en cours de validité</li>
            <li>Un billet d'avion aller / retour</li>
            <li>Un certificat international de vaccination (fièvre jaune)</li>
            <li>Deux photos d'identité récentes</li>
            <li>Une réservation d'hôtel, ou – si vous comptez résider chez l'habitant, le certificat d'hébergement que la personne qui vous accueille aura fait légaliser dans sa commune</li>
            <li>Un formulaire de demande de visa retiré au consulat</li>
            <li>Les frais de timbres, vendus au consulat.</li>
        </ul>
        
        <div class="formalities-subtitle">Santé : les vaccins</div>
        <div class="formalities-text">
            Le seul vaccin obligatoire est le vaccin contre la fièvre jaune. Il doit être fait 10 jours avant le départ. Il est valable 10 ans. Un test PCR COVID-19 négatif de moins de 72h est requis.
        </div>
        
        <div class="formalities-subtitle">Santé : les médicaments</div>
        <div class="formalities-text">
            Demandez à votre médecin :
        </div>
        <ul class="formalities-list">
            <li>un traitement préventif du paludisme</li>
            <li>des médicaments efficaces en cas de diarrhées sérieuses</li>
            <li>des antibiotiques à titre préventif</li>
            <li>des antidouleurs</li>
            <li>si vous voyagez dans le nord et l'extrême-nord Cameroun, des vents chargés de sable soufflent parfois : il conviendra de protéger vos yeux et de les soulager avec des dosettes de collyre apaisant. Les lentilles sont déconseillées dans ce cas.</li>
        </ul>
    </div>

    <!-- Footer avec date et heure -->
    <div class="footer">
        {{ $dateHeure }}
    </div>
</body>
</html>
