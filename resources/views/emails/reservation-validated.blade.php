<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enregistrement validé</title>
    <style>
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
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
            background: {{ $reservation->hotel->primary_color ?? '#1a4b8c' }};
            color: white;
            padding: 25px;
            text-align: center;
        }
        .hotelpro-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .hotelpro-subtitle {
            font-size: 11px;
            font-style: italic;
            margin-bottom: 15px;
            opacity: 0.9;
        }
        .hotelpro-link {
            color: white;
            text-decoration: underline;
            font-size: 11px;
        }
        .divider {
            text-align: center;
            margin: 15px 0;
            color: #999;
            font-size: 14px;
        }
        .email-body {
            padding: 30px;
        }
        .hotel-info {
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 6px;
        }
        .hotel-name {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 8px;
            color: {{ $reservation->hotel->primary_color ?? '#1a4b8c' }};
        }
        .hotel-details {
            font-size: 13px;
            line-height: 1.8;
            color: #555;
        }
        .confirmation-title {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            color: #10b981;
            margin: 25px 0;
            text-transform: uppercase;
        }
        .greeting {
            text-align: center;
            font-size: 16px;
            margin: 20px 0;
        }
        .reservation-number {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin: 20px 0;
            padding: 15px;
            background: #eff6ff;
            border-left: 4px solid {{ $reservation->hotel->primary_color ?? '#1a4b8c' }};
            border-radius: 4px;
        }
        .dates-info {
            margin: 20px 0;
            padding: 15px;
            background: #f0f9ff;
            border-radius: 6px;
        }
        .date-item {
            margin: 10px 0;
            font-size: 14px;
        }
        .date-label {
            font-weight: bold;
            color: #555;
        }
        .date-value {
            color: #111;
        }
        .room-info {
            margin: 20px 0;
            padding: 15px;
            background: #fff9e6;
            border-left: 4px solid #f59e0b;
            border-radius: 4px;
            font-size: 14px;
        }
        .pdf-notice {
            margin: 25px 0;
            padding: 15px;
            background: #f0fdf4;
            border-left: 4px solid #10b981;
            border-radius: 4px;
            font-size: 14px;
            text-align: center;
        }
        .thank-you {
            text-align: center;
            font-size: 14px;
            color: #555;
            margin-top: 25px;
            font-style: italic;
        }
        .email-footer {
            background: #f9fafb;
            padding: 20px;
            text-align: center;
            color: #6b7280;
            font-size: 12px;
            border-top: 1px solid #e5e7eb;
        }
        .footer-link {
            color: #1a4b8c;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header HotelPro -->
        <div class="email-header">
            <div class="hotelpro-title">HotelPro</div>
            <div class="hotelpro-subtitle">logiciel Africain de Gestion Hôtelière</div>
            <div>
                <a href="http://www.hotelproafrica.com" class="hotelpro-link">http://www.hotelproafrica.com</a>
            </div>
        </div>

        <!-- Body -->
        <div class="email-body">
            <!-- Informations de l'hôtel -->
            <div class="hotel-info">
                <div class="hotel-name">{{ $reservation->hotel->name }}@if($reservation->hotel->settings && isset($reservation->hotel->settings['stars'])) {{ str_repeat('*', $reservation->hotel->settings['stars']) }}@endif</div>
                <div class="hotel-details">
                    @if($reservation->hotel->address)
                        {{ $reservation->hotel->address }}@if($reservation->hotel->city), {{ $reservation->hotel->city }}@endif
                        @if($reservation->hotel->country) - {{ $reservation->hotel->country }}@endif<br>
                    @endif
                    @if($reservation->hotel->phone)
                        TEL. : {{ $reservation->hotel->phone }}<br>
                    @endif
                    @if($reservation->hotel->email)
                        {{ $reservation->hotel->email }}<br>
                    @endif
                    <a href="http://www.hotelproafrica.com" class="footer-link">www.hotelproafrica.com</a>
                </div>
            </div>

            <div class="divider">-</div>

            <!-- Titre de confirmation -->
            <div class="confirmation-title">Enregistrement confirmé!!!</div>

            <div class="divider">-</div>

            @php
                $data = $reservation->data ?? [];
                $nom = strtoupper($data['nom'] ?? '');
                $prenom = $data['prenom'] ?? '';
                $clientFullName = trim($prenom . ' ' . $nom);
                // Créer FormConfigService pour les champs personnalisés
                $formConfig = new \App\Services\FormConfigService($reservation->hotel);
                $customFieldsWithValues = $formConfig->getCustomFieldsWithValues($data);
            @endphp

            <!-- Message de félicitation -->
            <div class="greeting">
                Bravo <strong>{{ $clientFullName }}</strong>!
            </div>

            <div class="divider">-</div>

            <!-- Numéro d'enregistrement -->
            <div class="reservation-number">
                Votre enregistrement N.{{ str_pad($reservation->id, 7, '0', STR_PAD_LEFT) }} a été confirmée.
            </div>

            <div class="divider">-</div>

            <!-- Dates -->
            <div class="dates-info">
                @if($reservation->check_in_date)
                <div class="date-item">
                    <span class="date-label">Votre date d'arrivée :</span>
                    <span class="date-value"> {{ \Carbon\Carbon::parse($reservation->check_in_date)->format('d/m/Y') }}.</span>
                </div>
                @endif
                
                @if($reservation->check_out_date)
                <div class="date-item">
                    <span class="date-label">Votre date de départ probable:</span>
                    <span class="date-value"> {{ \Carbon\Carbon::parse($reservation->check_out_date)->format('d/m/Y') }}.</span>
                </div>
                @endif
            </div>

            <!-- Informations sur la chambre -->
            @if($reservation->room)
            <div class="room-info">
                Le logement <strong>{{ $reservation->room->room_number }}</strong> vous a été temporairement attribué. Il est modifiable.
            </div>
            @endif

            <div class="divider">-</div>

            <!-- Notice PDF -->
            <div class="pdf-notice">
                Veuillez télécharger votre enregistrement en fichier PDF attaché.
            </div>

            <div class="divider">-</div>

            {{-- Afficher les champs personnalisés s'il y en a --}}
            @if(count($customFieldsWithValues) > 0)
            <div style="margin: 20px 0; padding: 15px; background: #f9f9f9; border-radius: 6px;">
                <h4 style="font-size: 16px; margin-bottom: 15px; color: #333;">Informations supplémentaires</h4>
                <table style="width: 100%; border-collapse: collapse;">
                    @foreach($customFieldsWithValues as $item)
                    <tr style="border-bottom: 1px solid #e0e0e0;">
                        <td style="padding: 8px 0; font-weight: bold; width: 40%; color: #555;">{{ $item['field']->label }}:</td>
                        <td style="padding: 8px 0; color: #333;">{!! $item['formatted_value'] !!}</td>
                    </tr>
                    @endforeach
                </table>
            </div>
            <div class="divider">-</div>
            @endif

            <!-- Message de remerciement -->
            <div class="thank-you">
                Merci pour votre confiance...
            </div>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <p style="margin: 0; padding-bottom: 10px;">
                <strong>{{ $reservation->hotel->name }}</strong>
            </p>
            @if($reservation->hotel->address)
            <p style="margin: 5px 0; font-size: 11px;">
                {{ $reservation->hotel->address }}@if($reservation->hotel->city), {{ $reservation->hotel->city }}@endif
                @if($reservation->hotel->country) - {{ $reservation->hotel->country }}@endif
            </p>
            @endif
            @if($reservation->hotel->phone)
            <p style="margin: 5px 0; font-size: 11px;">
                TEL. : {{ $reservation->hotel->phone }}
            </p>
            @endif
            @if($reservation->hotel->email)
            <p style="margin: 5px 0; font-size: 11px;">
                {{ $reservation->hotel->email }}
            </p>
            @endif
            <p style="margin-top: 15px; margin-bottom: 0; font-size: 11px;">
                <a href="http://www.hotelproafrica.com" class="footer-link">www.hotelproafrica.com</a>
            </p>
        </div>
    </div>
</body>
</html>
