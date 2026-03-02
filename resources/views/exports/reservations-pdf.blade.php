<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Export Pré-enregistrements</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background-color: #f0f0f0;
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            font-size: 9px;
        }
        td {
            border: 1px solid #ddd;
            padding: 6px;
            font-size: 9px;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }
        .badge-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        .badge-info {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📋 Export des Pré-enregistrements</h1>
        <p>Généré le {{ $exportDate }}</p>
        <p>Total: {{ $reservations->count() }} enregistrement(s)</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Type</th>
                <th>Client</th>
                <th>Email</th>
                <th>Téléphone</th>
                <th>Arrivée</th>
                <th>Départ</th>
                <th>Nuits</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reservations as $reservation)
                <tr>
                    <td>#{{ $reservation->id }}</td>
                    <td>
                        @if(isset($reservation->data['type_reservation']) && $reservation->data['type_reservation'] === 'groupe')
                            <span class="badge badge-info">Groupe</span>
                        @else
                            <span class="badge badge-info">Individuel</span>
                        @endif
                    </td>
                    <td>
                        @if(isset($reservation->data['type_reservation']) && $reservation->data['type_reservation'] === 'groupe')
                            {{ $reservation->data['nom_groupe'] ?? 'Groupe' }}
                        @else
                            {{ $reservation->data['nom'] ?? '' }} {{ $reservation->data['prenom'] ?? '' }}
                        @endif
                    </td>
                    <td>{{ $reservation->data['email'] ?? 'N/A' }}</td>
                    <td>{{ $reservation->data['telephone'] ?? 'N/A' }}</td>
                    <td>{{ isset($reservation->data['date_arrivee']) ? \Carbon\Carbon::parse($reservation->data['date_arrivee'])->format('d/m/Y') : 'N/A' }}</td>
                    <td>{{ isset($reservation->data['date_depart']) ? \Carbon\Carbon::parse($reservation->data['date_depart'])->format('d/m/Y') : 'N/A' }}</td>
                    <td>{{ $reservation->data['nombre_nuits'] ?? 'N/A' }}</td>
                    <td>
                        @if($reservation->status === 'pending')
                            <span class="badge badge-warning">En attente</span>
                        @elseif($reservation->status === 'validated')
                            <span class="badge badge-success">Validée</span>
                        @else
                            <span class="badge badge-danger">Rejetée</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Document généré automatiquement - {{ config('app.name') }}</p>
    </div>
</body>
</html>





