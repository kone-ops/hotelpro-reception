<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Fiche d'enregistrement N°{{ str_pad($reservation->id, 7, '0', STR_PAD_LEFT) }}</title>
    <style>
        @page {
            size: A5;
            margin: 10mm;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            font-size: 9pt;
            line-height: 1.3;
            color: #000;
        }
        
        /* En-tête */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 8px;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
        }
        .header-left {
            display: table-cell;
            width: 70%;
            vertical-align: top;
        }
        .header-right {
            display: table-cell;
            width: 30%;
            vertical-align: top;
            text-align: right;
        }
        .hotel-logo {
            max-height: 30px;
            max-width: 80px;
            margin-bottom: 3px;
        }
        .hotel-name {
            font-weight: bold;
            font-size: 10pt;
            margin-bottom: 2px;
        }
        .hotel-info {
            font-size: 8pt;
            line-height: 1.2;
        }
        .reservation-number {
            font-size: 8pt;
            margin-bottom: 2px;
        }
        .date-time {
            font-size: 8pt;
        }
        
        /* Titre principal */
        .main-title {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            margin: 8px 0 5px 0;
            text-transform: uppercase;
        }
        .manager-label {
            font-size: 9pt;
            margin-bottom: 8px;
        }
        
        /* Section informations */
        .info-section {
            margin-bottom: 6px;
        }
        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 3px;
            font-size: 8pt;
        }
        .info-label {
            display: table-cell;
            width: 35%;
            font-weight: bold;
            padding-right: 5px;
            vertical-align: top;
        }
        .info-value {
            display: table-cell;
            width: 65%;
            border-bottom: 1px dotted #666;
            padding-bottom: 1px;
        }
        .info-row-2col {
            display: table;
            width: 100%;
            margin-bottom: 3px;
        }
        .info-col {
            display: table-cell;
            width: 50%;
            padding-right: 10px;
        }
        
        /* Tableau récapitulatif */
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0;
            font-size: 8pt;
        }
        .summary-table th,
        .summary-table td {
            border: 1px solid #000;
            padding: 4px 6px;
            text-align: left;
        }
        .summary-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .summary-table td {
            text-align: center;
        }
        
        /* Signatures */
        .signatures {
            display: table;
            width: 100%;
            margin-top: 15px;
            margin-bottom: 10px;
        }
        .signature-left,
        .signature-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        .signature-box {
            border: 1px solid #000;
            height: 50px;
            margin-top: 5px;
            text-align: center;
            padding-top: 15px;
            font-size: 8pt;
        }
        .signature-label {
            font-weight: bold;
            font-size: 8pt;
            margin-bottom: 2px;
        }
        
        /* Responsive pour preview */
        @media screen {
            body {
                max-width: 148mm;
                margin: 0 auto;
                padding: 10mm;
                background: #f5f5f5;
            }
            .page {
                background: white;
                padding: 10mm;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <!-- En-tête -->
        <div class="header">
            <div class="header-left">
                @if($reservation->hotel->hasLogo())
                    @php
                        $logoPath = $reservation->hotel->logo;
                        // Compatibilité avec anciens chemins storage/
                        if (strpos($logoPath, 'storage/') === 0 || strpos($logoPath, 'hotels/') === 0) {
                            $logoPath = 'images/logos/' . basename($logoPath);
                        }
                        $fullLogoPath = public_path($logoPath);
                        if (file_exists($fullLogoPath)) {
                            $logoData = base64_encode(file_get_contents($fullLogoPath));
                            $logoMime = mime_content_type($fullLogoPath);
                            $logoBase64 = 'data:' . $logoMime . ';base64,' . $logoData;
                        }
                    @endphp
                    @if(isset($logoBase64))
                        <img src="{{ $logoBase64 }}" alt="Logo" class="hotel-logo">
                    @endif
                @endif
                <div class="hotel-name">{{ strtoupper($reservation->hotel->name) }}</div>
                <div class="hotel-info">
                    @if($reservation->hotel->address)
                        {{ $reservation->hotel->address }}
                    @endif
                    @if($reservation->hotel->city)
                        - {{ $reservation->hotel->city }}
                    @endif
                    @if($reservation->hotel->country)
                        - {{ $reservation->hotel->country }}
                    @endif
                    <br>
                    @if($reservation->hotel->phone)
                        TEL.: {{ $reservation->hotel->phone }}
                    @endif
                    @if($reservation->hotel->email)
                        - {{ $reservation->hotel->email }}
                    @endif
                </div>
            </div>
            <div class="header-right">
                <div class="reservation-number">{{ now()->format('d/m/Y') }}</div>
                <div class="date-time">{{ now()->format('H:i:s') }}</div>
                <div class="date-time" style="margin-top: 3px;">1 / 1</div>
            </div>
        </div>
        
        <!-- Titre principal -->
        <div class="main-title">
            FICHE DE RESERVATION N°{{ str_pad($reservation->id, 7, '0', STR_PAD_LEFT) }}
        </div>
        <div class="manager-label">MANAGER</div>
        
        <!-- Informations personnelles -->
        <div class="info-section">
            <div class="info-row">
                <div class="info-label">Noms (surname in block capitals):</div>
                <div class="info-value">{{ strtoupper($reservation->data['nom'] ?? '') }}</div>
            </div>
            
            @if(isset($reservation->data['nom_jeune_fille']))
            <div class="info-row">
                <div class="info-label">Nom de Jeune fille (Maiden name If applicable):</div>
                <div class="info-value">{{ $reservation->data['nom_jeune_fille'] }}</div>
            </div>
            @endif
            
            <div class="info-row">
                <div class="info-label">Prénoms (Christian name):</div>
                <div class="info-value">{{ $reservation->data['prenom'] ?? '' }}</div>
            </div>
            
            <div class="info-row-2col">
                <div class="info-col">
                    <div class="info-row">
                        <div class="info-label">Date de naissance (Date of birth):</div>
                        <div class="info-value">{{ isset($reservation->data['date_naissance']) ? \Carbon\Carbon::parse($reservation->data['date_naissance'])->format('d/m/Y') : '' }}</div>
                    </div>
                </div>
                <div class="info-col">
                    <div class="info-row">
                        <div class="info-label">Lieu de naissance (Place of birth):</div>
                        <div class="info-value">{{ $reservation->data['lieu_naissance'] ?? '' }}</div>
                    </div>
                </div>
            </div>
            
            <div class="info-row-2col">
                <div class="info-col">
                    <div class="info-row">
                        <div class="info-label">Nationalite (Nationality):</div>
                        <div class="info-value">{{ $reservation->data['nationalite'] ?? '' }}</div>
                    </div>
                </div>
                <div class="info-col">
                    <div class="info-row">
                        <div class="info-label">Pays de résidence (Country of permanence residence):</div>
                        <div class="info-value">{{ $reservation->data['pays_residence'] ?? ($reservation->data['pays'] ?? '') }}</div>
                    </div>
                </div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Adresse (Address):</div>
                <div class="info-value">{{ $reservation->data['adresse'] ?? '' }}</div>
            </div>
            
            <div class="info-row-2col">
                <div class="info-col">
                    <div class="info-row">
                        <div class="info-label">Tél. (Telephone):</div>
                        <div class="info-value">{{ $reservation->data['telephone'] ?? '' }}</div>
                    </div>
                </div>
                <div class="info-col">
                    <div class="info-row">
                        <div class="info-label">Fax.:</div>
                        <div class="info-value">{{ $reservation->data['fax'] ?? '' }}</div>
                    </div>
                </div>
            </div>
            
            <div class="info-row">
                <div class="info-label">E-mail:</div>
                <div class="info-value">{{ $reservation->data['email'] ?? '' }}</div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Profession (Occupation):</div>
                <div class="info-value">{{ $reservation->data['profession'] ?? '' }}</div>
            </div>
            
            <div class="info-row">
                <div class="info-label">C.N.I (N.I. Card):</div>
                <div class="info-value">{{ $reservation->data['numero_piece_identite'] ?? '' }}</div>
            </div>
            
            {{-- Afficher les champs personnalisés de la section 2 (Informations personnelles) --}}
            @php
                $section2Fields = array_filter($formConfig->getCustomFieldsWithValues($reservation->data), function($item) {
                    return $item['field']->section == 2;
                });
            @endphp
            @foreach($section2Fields as $item)
                <div class="info-row">
                    <div class="info-label">{{ $item['field']->label }}:</div>
                    <div class="info-value">{{ $item['formatted_value'] }}</div>
                </div>
            @endforeach
        </div>
        
        <!-- Informations d'enregistrement -->
        <div class="info-section">
            <div class="info-row-2col">
                <div class="info-col">
                    <div class="info-row">
                        <div class="info-label">Venant de (Arriving from):</div>
                        <div class="info-value">{{ $reservation->data['venant_de'] ?? '' }}</div>
                    </div>
                </div>
                <div class="info-col">
                    <div class="info-row">
                        <div class="info-label">Se rendant à (Travelling to):</div>
                        <div class="info-value">{{ $reservation->data['se_rendant_a'] ?? ($reservation->data['venant_de'] ?? '') }}</div>
                    </div>
                </div>
            </div>
            
            <div class="info-row-2col">
                <div class="info-col">
                    <div class="info-row">
                        <div class="info-label">Nombre de personnes (Number of person):</div>
                        <div class="info-value">{{ ($reservation->data['nombre_adultes'] ?? 0) + ($reservation->data['nombre_enfants'] ?? 0) }}</div>
                    </div>
                </div>
                <div class="info-col">
                    <div class="info-row">
                        <div class="info-label">Adultes (Adults):</div>
                        <div class="info-value">{{ $reservation->data['nombre_adultes'] ?? 1 }}</div>
                    </div>
                </div>
            </div>
            
            <div class="info-row-2col">
                <div class="info-col">
                    <div class="info-row">
                        <div class="info-label">Enfants (Childs):</div>
                        <div class="info-value">{{ $reservation->data['nombre_enfants'] ?? 0 }}</div>
                    </div>
                </div>
                <div class="info-col">
                    <div class="info-row">
                        <div class="info-label">Mode de transport (Transport mode):</div>
                        <div class="info-value">{{ $reservation->data['mode_transport'] ?? 'BUS' }}</div>
                    </div>
                </div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Véhicule N° (Car Number):</div>
                <div class="info-value">{{ $reservation->data['vehicule_numero'] ?? '' }}</div>
            </div>
            
            {{-- Afficher les champs personnalisés de la section 4 (Informations de séjour) --}}
            @php
                $section4Fields = array_filter($formConfig->getCustomFieldsWithValues($reservation->data), function($item) {
                    return $item['field']->section == 4;
                });
            @endphp
            @foreach($section4Fields as $item)
                <div class="info-row">
                    <div class="info-label">{{ $item['field']->label }}:</div>
                    <div class="info-value">{{ $item['formatted_value'] }}</div>
                </div>
            @endforeach
        </div>
        
        <!-- Tableau récapitulatif -->
        <table class="summary-table">
            <thead>
                <tr>
                    <th>Date entrée<br>(Entry Date)</th>
                    <th>Date sortie<br>(Exit Date)</th>
                    <th>Total nuitées<br>(Total Nights)</th>
                    <th>Chambre N°<br>(Room Number)</th>
                    <th>Caution<br>(Deposit)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $reservation->check_in_date->format('d/m/Y') }}</td>
                    <td>{{ $reservation->check_out_date->format('d/m/Y') }}</td>
                    <td>{{ $reservation->check_in_date->diffInDays($reservation->check_out_date) }}</td>
                    <td>{{ $reservation->room->room_number ?? 'N/A' }}</td>
                    <td>{{ number_format($reservation->paid_amount ?? 0, 0, ',', ' ') }}</td>
                </tr>
            </tbody>
        </table>
        
        <!-- Signatures -->
        <div class="signatures">
            <div class="signature-left">
                <div class="signature-label">HÔTEL</div>
                <div class="signature-box"></div>
            </div>
            <div class="signature-right">
                <div class="signature-label">CLIENT</div>
                <div class="signature-box">
                    @if($reservation->signature && $reservation->signature->image_base64)
                        @php
                            $signatureSrc = $reservation->signature->image_base64;
                            // Si la signature ne contient pas déjà le préfixe data:image, l'ajouter
                            if (!str_starts_with($signatureSrc, 'data:image')) {
                                $signatureSrc = 'data:image/png;base64,' . $signatureSrc;
                            }
                        @endphp
                        <img src="{{ $signatureSrc }}" style="max-height: 40px; max-width: 100%;" alt="Signature du client">
                    @endif
                </div>
            </div>
        </div>
    </div>
</body>
</html>
