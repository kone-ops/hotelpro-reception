<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservation Rejetée</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .email-container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .email-header {
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .hotel-logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 15px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .hotel-logo img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        .email-body {
            padding: 30px;
        }
        .error-icon {
            width: 60px;
            height: 60px;
            background: #dc2626;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            margin: 0 auto 20px;
        }
        .reservation-number {
            background: #fef2f2;
            border-left: 4px solid #dc2626;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .reason-box {
            background: #fef3c7;
            border: 1px solid #fbbf24;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .info-grid {
            display: grid;
            gap: 15px;
            margin: 20px 0;
        }
        .info-item {
            background: #f9fafb;
            padding: 12px;
            border-radius: 6px;
            display: flex;
            justify-content: space-between;
        }
        .info-label {
            font-weight: 600;
            color: #6b7280;
        }
        .info-value {
            color: #111827;
        }
        .btn-primary {
            display: inline-block;
            background: {{ $reservation->hotel->primary_color ?? '#2563eb' }};
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
            text-align: center;
        }
        .email-footer {
            background: #f9fafb;
            padding: 20px;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
        }
        .divider {
            height: 1px;
            background: #e5e7eb;
            margin: 20px 0;
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
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            @if($reservation->hotel->logo_url)
            <div class="hotel-logo">
                <img src="{{ asset('storage/' . $reservation->hotel->logo_url) }}" alt="{{ $reservation->hotel->name }}">
            </div>
            @endif
            <h1>{{ $reservation->hotel->name }}</h1>
        </div>

        <!-- Body -->
        <div class="email-body">
            <div class="error-icon">✕</div>
            
            <h2 style="text-align: center;">Réservation Rejetée</h2>
            
            @php
                $data = $reservation->data ?? [];
                $nom = $data['nom'] ?? '';
                $prenom = $data['prenom'] ?? '';
            @endphp
            
            <p>Bonjour <strong>{{ $prenom }} {{ $nom }}</strong>,</p>
            
            <p>Nous regrettons de vous informer que votre demande de pré-réservation a été rejetée.</p>

            <div class="reservation-number">
                <strong>Numéro de réservation :</strong> #{{ $reservation->id }}
            </div>

            @if($reason)
            <div class="reason-box">
                <strong>Raison du rejet :</strong><br>
                {{ $reason }}
            </div>
            @endif

            <h2>Détails de la réservation</h2>
            <div class="info-grid">
                @if($reservation->roomType)
                <div class="info-item">
                    <span class="info-label">Type de chambre</span>
                    <span class="info-value">{{ $reservation->roomType->name }}</span>
                </div>
                @endif

                @if($reservation->check_in_date)
                <div class="info-item">
                    <span class="info-label">Date d'arrivée</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($reservation->check_in_date)->format('d/m/Y') }}</span>
                </div>
                @endif

                @if($reservation->check_out_date)
                <div class="info-item">
                    <span class="info-label">Date de départ</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($reservation->check_out_date)->format('d/m/Y') }}</span>
                </div>
                @endif
            </div>

            <div class="divider"></div>

            <h2>Que faire maintenant ?</h2>
            <p>
                ✓ Vous pouvez soumettre une nouvelle demande en corrigeant les informations<br>
                ✓ Contactez-nous directement pour plus d'informations<br>
                ✓ Nos équipes restent à votre disposition
            </p>

            <p style="margin-top: 25px;">
                Si vous pensez qu'il s'agit d'une erreur ou si vous avez des questions, n'hésitez pas à nous contacter directement.
            </p>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <p><strong>{{ $reservation->hotel->name }}</strong></p>
            <p>
                {{ $reservation->hotel->address }}<br>
                {{ $reservation->hotel->city }}, {{ $reservation->hotel->country }}
            </p>
            @if($reservation->hotel->email)
            <p>📧 {{ $reservation->hotel->email }}</p>
            @endif
            @if($reservation->hotel->phone)
            <p>📞 {{ $reservation->hotel->phone }}</p>
            @endif
            <p style="margin-top: 15px; font-size: 12px; color: #9ca3af;">
                Cet email a été envoyé automatiquement, merci de ne pas y répondre.
            </p>
        </div>
    </div>
</body>
</html>
