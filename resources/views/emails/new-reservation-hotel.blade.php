<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle Réservation</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.8;
            color: #333;
            max-width: 700px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .email-container {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .email-header {
            background: linear-gradient(135deg, {{ $reservation->hotel->primary_color ?? '#1a4b8c' }} 0%, {{ $reservation->hotel->secondary_color ?? '#2563a8' }} 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .hotel-logo {
            max-width: 120px;
            max-height: 100px;
            margin: 0 auto 15px;
            background: white;
            padding: 10px;
            border-radius: 8px;
            display: block;
        }
        .hotel-logo img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        .email-body {
            padding: 30px;
        }
        .confirmation-box {
            background: #f0f9ff;
            border-left: 4px solid {{ $reservation->hotel->primary_color ?? '#1a4b8c' }};
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
            text-align: center;
        }
        .reservation-number {
            font-size: 20px;
            font-weight: bold;
            color: {{ $reservation->hotel->primary_color ?? '#1a4b8c' }};
            margin: 15px 0;
        }
        .info-section {
            margin: 25px 0;
            background: #f9fafb;
            padding: 20px;
            border-radius: 8px;
        }
        .info-item {
            margin: 12px 0;
            padding: 10px;
            background: white;
            border-radius: 5px;
            border-left: 3px solid {{ $reservation->hotel->primary_color ?? '#1a4b8c' }};
        }
        .info-label {
            font-weight: 600;
            color: #6b7280;
            font-size: 13px;
            display: block;
            margin-bottom: 5px;
        }
        .info-value {
            color: #111827;
            font-size: 15px;
        }
        .download-box {
            background: #eff6ff;
            border: 2px solid {{ $reservation->hotel->primary_color ?? '#1a4b8c' }};
            padding: 20px;
            border-radius: 8px;
            margin: 25px 0;
            text-align: center;
        }
        .email-footer {
            background: #f9fafb;
            padding: 25px;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
            border-top: 1px solid #e5e7eb;
        }
        .footer-info {
            margin: 10px 0;
            line-height: 1.8;
        }
        .footer-links {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }
        .footer-links a {
            color: {{ $reservation->hotel->primary_color ?? '#1a4b8c' }};
            text-decoration: none;
            margin: 0 10px;
        }
        h1 {
            margin: 0;
            font-size: 24px;
        }
        h2 {
            color: #111827;
            font-size: 20px;
            margin-bottom: 15px;
        }
        .divider {
            height: 1px;
            background: #e5e7eb;
            margin: 25px 0;
        }
        .note-box {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .client-info {
            margin: 20px 0;
        }
        .client-info-item {
            margin: 8px 0;
            padding: 8px;
            background: #f9fafb;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            @if(!empty($reservation->hotel->logo))
                <img src="{{ asset('storage/' . $reservation->hotel->logo) }}" alt="{{ $reservation->hotel->name }}" class="hotel-logo">
            @endif
            <h1>{{ $reservation->hotel->name }}</h1>
            @if($reservation->hotel->address)
                <p style="margin: 10px 0 0 0; opacity: 0.95;">{{ $reservation->hotel->address }}</p>
            @endif
            @if($reservation->hotel->city && $reservation->hotel->country)
                <p style="margin: 5px 0 0 0; opacity: 0.95;">{{ $reservation->hotel->city }}, {{ $reservation->hotel->country }}</p>
            @endif
        </div>

        <!-- Body -->
        <div class="email-body">
            @php
                $data = $reservation->data ?? [];
                $nom = $data['nom'] ?? '';
                $prenom = $data['prenom'] ?? '';
                $sexe = $data['sexe'] ?? '';
                $date_naissance = $data['date_naissance'] ?? '';
                $lieu_naissance = $data['lieu_naissance'] ?? '';
                $nationalite = $data['nationalite'] ?? '';
                $adresse = $data['adresse'] ?? '';
                $telephone = $data['telephone'] ?? '';
                $email = $data['email'] ?? '';
                $profession = $data['profession'] ?? '';
                $type_reservation = $data['type_reservation'] ?? 'Individuel';
                $nom_groupe = $data['nom_groupe'] ?? '';
                $code_groupe = $data['code_groupe'] ?? '';
                $venant_de = $data['venant_de'] ?? '';
                $preferences = $data['preferences'] ?? '';
                $nombre_adultes = $data['nombre_adultes'] ?? 1;
                $nombre_enfants = $data['nombre_enfants'] ?? 0;
                $heure_arrivee = $data['heure_arrivee'] ?? '';
            @endphp

            <div class="confirmation-box">
                <h2 style="margin-top: 0; color: {{ $reservation->hotel->primary_color ?? '#1a4b8c' }};">Réservation Confirmée!!!</h2>
                <p style="font-size: 18px; margin: 10px 0;"><strong>Bravo {{ $prenom }} {{ $nom }}!</strong></p>
                <div class="reservation-number">
                    Votre Réservation N.{{ str_pad($reservation->id, 7, '0', STR_PAD_LEFT) }} a été confirmée.
                </div>
            </div>

            <!-- Informations Client -->
            <div class="client-info">
                <h3 style="color: {{ $reservation->hotel->primary_color ?? '#1a4b8c' }}; margin-bottom: 15px;">Informations Client</h3>
                <div class="client-info-item"><strong>Nom complet :</strong> {{ $prenom }} {{ $nom }}</div>
                @if($sexe)
                <div class="client-info-item"><strong>Sexe :</strong> {{ $sexe }}</div>
                @endif
                @if($date_naissance)
                <div class="client-info-item"><strong>Date de naissance :</strong> {{ $date_naissance }}</div>
                @endif
                @if($lieu_naissance)
                <div class="client-info-item"><strong>Lieu de naissance :</strong> {{ $lieu_naissance }}</div>
                @endif
                @if($nationalite)
                <div class="client-info-item"><strong>Nationalité :</strong> {{ $nationalite }}</div>
                @endif
                @if($profession)
                <div class="client-info-item"><strong>Profession :</strong> {{ $profession }}</div>
                @endif
                @if($adresse)
                <div class="client-info-item"><strong>Adresse :</strong> {{ $adresse }}</div>
                @endif
                @if($telephone)
                <div class="client-info-item"><strong>Téléphone :</strong> {{ $telephone }}</div>
                @endif
                @if($email)
                <div class="client-info-item"><strong>Email :</strong> {{ $email }}</div>
                @endif
            </div>

            <div class="info-section">
                @if($reservation->check_in_date)
                <div class="info-item">
                    <span class="info-label">Date d'arrivée</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($reservation->check_in_date)->format('d/m/Y') }}</span>
                </div>
                @endif

                @if($reservation->check_out_date)
                <div class="info-item">
                    <span class="info-label">Date de départ probable</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($reservation->check_out_date)->format('d/m/Y') }}</span>
                </div>
                @endif

                @if($heure_arrivee)
                <div class="info-item">
                    <span class="info-label">Heure d'arrivée</span>
                    <span class="info-value">{{ $heure_arrivee }}</span>
                </div>
                @endif

                @if($reservation->room)
                <div class="info-item">
                    <span class="info-label">Le logement</span>
                    <span class="info-value">{{ $reservation->room->room_number }}</span>
                </div>
                @endif

                @if($reservation->roomType)
                <div class="info-item">
                    <span class="info-label">Type de chambre</span>
                    <span class="info-value">{{ $reservation->roomType->name }}</span>
                </div>
                @endif

                @if($type_reservation)
                <div class="info-item">
                    <span class="info-label">Type de réservation</span>
                    <span class="info-value">{{ $type_reservation }}</span>
                </div>
                @endif

                @if($type_reservation === 'Groupe' && $nom_groupe)
                <div class="info-item">
                    <span class="info-label">Nom du groupe</span>
                    <span class="info-value">{{ $nom_groupe }}</span>
                </div>
                @endif

                @if($type_reservation === 'Groupe' && $code_groupe)
                <div class="info-item">
                    <span class="info-label">Code groupe</span>
                    <span class="info-value">{{ $code_groupe }}</span>
                </div>
                @endif

                @if($nombre_adultes)
                <div class="info-item">
                    <span class="info-label">Nombre d'adultes</span>
                    <span class="info-value">{{ $nombre_adultes }}</span>
                </div>
                @endif

                @if($nombre_enfants)
                <div class="info-item">
                    <span class="info-label">Nombre d'enfants</span>
                    <span class="info-value">{{ $nombre_enfants }}</span>
                </div>
                @endif

                @if($venant_de)
                <div class="info-item">
                    <span class="info-label">Venant de</span>
                    <span class="info-value">{{ $venant_de }}</span>
                </div>
                @endif

                @if($preferences)
                <div class="info-item">
                    <span class="info-label">Préférences / Demandes spéciales</span>
                    <span class="info-value">{{ $preferences }}</span>
                </div>
                @endif

                <div class="info-item" style="background: #fee2e2; border-left-color: #dc2626;">
                    <span class="info-label">Statut</span>
                    <span class="info-value"><strong>{{ ucfirst($reservation->status) }}</strong> - En attente de validation</span>
                </div>
            </div>

            @if($reservation->room)
            <div class="note-box">
                <strong>Note importante :</strong> Le logement <strong>{{ $reservation->room->room_number }}</strong> a été temporairement attribué. Il est modifiable.
            </div>
            @endif

            <!-- Téléchargement PDF -->
            <div class="download-box">
                <h3 style="margin-top: 0; color: {{ $reservation->hotel->primary_color ?? '#1a4b8c' }};">📄 Télécharger la Réservation</h3>
                <p>Veuillez télécharger la Réservation en fichier PDF attaché.</p>
            </div>

            <div class="divider"></div>

            <p style="text-align: center; color: #6b7280; font-size: 14px; margin-top: 25px;">
                <strong>Merci pour votre confiance...</strong>
            </p>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <div class="footer-info">
                <p><strong>{{ $reservation->hotel->name }}</strong></p>
                @if($reservation->hotel->address)
                    <p>{{ $reservation->hotel->address }}</p>
                @endif
                @if($reservation->hotel->city && $reservation->hotel->country)
                    <p>{{ $reservation->hotel->city }}, {{ $reservation->hotel->country }}</p>
                @endif
                @if($reservation->hotel->phone)
                    <p><strong>TEL. :</strong> {{ $reservation->hotel->phone }}</p>
                @endif
                @if($reservation->hotel->email)
                    <p>{{ $reservation->hotel->email }}</p>
                @endif
            </div>

            <div class="footer-links">
                <p style="margin: 0; font-size: 12px; color: #9ca3af;">
                    HotelPro - Logiciel Africain de Gestion Hôtelière<br>
                    <a href="http://www.hotelproafrica.com" target="_blank">www.hotelproafrica.com</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
