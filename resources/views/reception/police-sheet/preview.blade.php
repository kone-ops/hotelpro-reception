<x-app-layout>
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-0">
                <i class="bi bi-file-earmark-text me-2"></i>{{ __('Fiche de police') }} N°{{ str_pad($reservation->id, 7, '0', STR_PAD_LEFT) }}
            </h2>
            <div>
                <a href="{{ route('reception.police-sheet.generate', $reservation->id) }}" class="btn btn-success btn-sm">
                    <i class="bi bi-download me-1"></i>Télécharger PDF
                </a>
                <a href="{{ route('reception.reservations.show', $reservation->id) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Retour
                </a>
            </div>
        </div>

    <div class="py-4">
        <div class="container-fluid">
            <!-- Aperçu de la fiche - Format A5 -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4" style="background: var(--card-bg, #ffffff);">
                    <div class="police-sheet-preview" style="max-width: 148mm; margin: 0 auto; padding: 10mm; background: var(--card-bg, #ffffff); box-shadow: 0 0 10px rgba(0,0,0,0.1);">
                        
                        <!-- En-tête -->
                        <div class="d-flex justify-content-between align-items-start mb-3 pb-2" style="border-bottom: 1px solid #000;">
                            <div style="flex: 1;">
                                @if($reservation->hotel->logo_url)
                                    <img src="{{ $reservation->hotel->logo_url }}" alt="Logo" style="max-height: 30px; max-width: 80px; margin-bottom: 5px;" loading="lazy">
                                @endif
                                <div style="font-weight: bold; font-size: 10pt; margin-bottom: 2px;">{{ strtoupper($reservation->hotel->name) }}</div>
                                <div style="font-size: 8pt; line-height: 1.2;">
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
                            <div style="text-align: right; font-size: 8pt;">
                                <div>{{ now()->format('d/m/Y') }}</div>
                                <div>{{ now()->format('H:i:s') }}</div>
                                <div style="margin-top: 3px;">1 / 1</div>
                            </div>
                        </div>
                        
                        <!-- Titre principal -->
                        <div style="text-align: center; font-size: 14pt; font-weight: bold; margin: 10px 0 5px 0; text-transform: uppercase;">
                            FICHE DE POLICE N°{{ str_pad($reservation->id, 7, '0', STR_PAD_LEFT) }}
                        </div>
                        
                        <!-- Informations personnelles -->
                        <div class="mb-3">
                            <div class="d-flex mb-1" style="font-size: 8pt;">
                                <div style="width: 35%; font-weight: bold; padding-right: 5px;">Noms (surname in block capitals):</div>
                                <div style="width: 65%; border-bottom: 1px dotted #666; padding-bottom: 1px;">{{ strtoupper($reservation->data['nom'] ?? '') }}</div>
                            </div>
                            
                            @if(isset($reservation->data['nom_jeune_fille']))
                            <div class="d-flex mb-1" style="font-size: 8pt;">
                                <div style="width: 35%; font-weight: bold; padding-right: 5px;">Nom de Jeune fille (Maiden name If applicable):</div>
                                <div style="width: 65%; border-bottom: 1px dotted #666; padding-bottom: 1px;">{{ $reservation->data['nom_jeune_fille'] }}</div>
                            </div>
                            @endif
                            
                            <div class="d-flex mb-1" style="font-size: 8pt;">
                                <div style="width: 35%; font-weight: bold; padding-right: 5px;">Prénoms (Christian name):</div>
                                <div style="width: 65%; border-bottom: 1px dotted #666; padding-bottom: 1px;">{{ $reservation->data['prenom'] ?? '' }}</div>
                            </div>
                            
                            <div class="row mb-1">
                                <div class="col-6">
                                    <div class="d-flex" style="font-size: 8pt;">
                                        <div style="width: 50%; font-weight: bold; padding-right: 5px;">Date de naissance (Date of birth):</div>
                                        <div style="width: 50%; border-bottom: 1px dotted #666; padding-bottom: 1px;">{{ isset($reservation->data['date_naissance']) ? \Carbon\Carbon::parse($reservation->data['date_naissance'])->format('d/m/Y') : '' }}</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="d-flex" style="font-size: 8pt;">
                                        <div style="width: 50%; font-weight: bold; padding-right: 5px;">Lieu de naissance (Place of birth):</div>
                                        <div style="width: 50%; border-bottom: 1px dotted #666; padding-bottom: 1px;">{{ $reservation->data['lieu_naissance'] ?? '' }}</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-1">
                                <div class="col-6">
                                    <div class="d-flex" style="font-size: 8pt;">
                                        <div style="width: 50%; font-weight: bold; padding-right: 5px;">Nationalite (Nationality):</div>
                                        <div style="width: 50%; border-bottom: 1px dotted #666; padding-bottom: 1px;">{{ $reservation->data['nationalite'] ?? '' }}</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="d-flex" style="font-size: 8pt;">
                                        <div style="width: 50%; font-weight: bold; padding-right: 5px;">Pays de résidence (Country of permanence residence):</div>
                                        <div style="width: 50%; border-bottom: 1px dotted #666; padding-bottom: 1px;">{{ $reservation->data['pays_residence'] ?? ($reservation->data['pays'] ?? '') }}</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex mb-1" style="font-size: 8pt;">
                                <div style="width: 35%; font-weight: bold; padding-right: 5px;">Adresse (Address):</div>
                                <div style="width: 65%; border-bottom: 1px dotted #666; padding-bottom: 1px;">{{ $reservation->data['adresse'] ?? '' }}</div>
                            </div>
                            
                            <div class="row mb-1">
                                <div class="col-6">
                                    <div class="d-flex" style="font-size: 8pt;">
                                        <div style="width: 50%; font-weight: bold; padding-right: 5px;">Tél. (Telephone):</div>
                                        <div style="width: 50%; border-bottom: 1px dotted #666; padding-bottom: 1px;">{{ $reservation->data['telephone'] ?? '' }}</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="d-flex" style="font-size: 8pt;">
                                        <div style="width: 50%; font-weight: bold; padding-right: 5px;">Fax.:</div>
                                        <div style="width: 50%; border-bottom: 1px dotted #666; padding-bottom: 1px;">{{ $reservation->data['fax'] ?? '' }}</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex mb-1" style="font-size: 8pt;">
                                <div style="width: 35%; font-weight: bold; padding-right: 5px;">E-mail:</div>
                                <div style="width: 65%; border-bottom: 1px dotted #666; padding-bottom: 1px;">{{ $reservation->data['email'] ?? '' }}</div>
                            </div>
                            
                            <div class="d-flex mb-1" style="font-size: 8pt;">
                                <div style="width: 35%; font-weight: bold; padding-right: 5px;">Profession (Occupation):</div>
                                <div style="width: 65%; border-bottom: 1px dotted #666; padding-bottom: 1px;">{{ $reservation->data['profession'] ?? '' }}</div>
                            </div>
                            
                            <div class="d-flex mb-1" style="font-size: 8pt;">
                                <div style="width: 35%; font-weight: bold; padding-right: 5px;">Numéro Pièce d'identité:</div>
                                <div style="width: 65%; border-bottom: 1px dotted #666; padding-bottom: 1px;">{{ $reservation->data['numero_piece_identite'] ?? '' }}</div>
                            </div>
                            
                            {{-- Afficher les champs personnalisés de la section 2 (Informations personnelles) --}}
                            @php
                                $customFieldsSection2 = $formConfig->getCustomFieldsBySection(2);
                                $customFieldsWithValues = $formConfig->getCustomFieldsWithValues($reservation->data);
                                $section2Fields = array_filter($customFieldsWithValues, function($item) {
                                    return $item['field']->section == 2;
                                });
                            @endphp
                            @foreach($section2Fields as $item)
                                <div class="d-flex mb-1" style="font-size: 8pt;">
                                    <div style="width: 35%; font-weight: bold; padding-right: 5px;">{{ $item['field']->label }}:</div>
                                    <div style="width: 65%; border-bottom: 1px dotted #666; padding-bottom: 1px;">{{ $item['formatted_value'] }}</div>
                                </div>
                            @endforeach
                        </div>
                        
                        <!-- Informations d'enregistrement -->
                        <div class="mb-3">
                            <div class="row mb-1">
                                <div class="col-6">
                                    <div class="d-flex" style="font-size: 8pt;">
                                        <div style="width: 50%; font-weight: bold; padding-right: 5px;">Venant de (Arriving from):</div>
                                        <div style="width: 50%; border-bottom: 1px dotted #666; padding-bottom: 1px;">{{ $reservation->data['venant_de'] ?? '' }}</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="d-flex" style="font-size: 8pt;">
                                        <div style="width: 50%; font-weight: bold; padding-right: 5px;">Se rendant à (Travelling to):</div>
                                        <div style="width: 50%; border-bottom: 1px dotted #666; padding-bottom: 1px;">{{ $reservation->data['se_rendant_a'] ?? ($reservation->data['venant_de'] ?? '') }}</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-1">
                                <div class="col-6">
                                    <div class="d-flex" style="font-size: 8pt;">
                                        <div style="width: 50%; font-weight: bold; padding-right: 5px;">Nombre de personnes (Number of person):</div>
                                        <div style="width: 50%; border-bottom: 1px dotted #666; padding-bottom: 1px;">{{ ($reservation->data['nombre_adultes'] ?? 0) + ($reservation->data['nombre_enfants'] ?? 0) }}</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="d-flex" style="font-size: 8pt;">
                                        <div style="width: 50%; font-weight: bold; padding-right: 5px;">Adultes (Adults):</div>
                                        <div style="width: 50%; border-bottom: 1px dotted #666; padding-bottom: 1px;">{{ $reservation->data['nombre_adultes'] ?? 1 }}</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-1">
                                <div class="col-6">
                                    <div class="d-flex" style="font-size: 8pt;">
                                        <div style="width: 50%; font-weight: bold; padding-right: 5px;">Enfants (Childs):</div>
                                        <div style="width: 50%; border-bottom: 1px dotted #666; padding-bottom: 1px;">{{ $reservation->data['nombre_enfants'] ?? 0 }}</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="d-flex" style="font-size: 8pt;">
                                        <div style="width: 50%; font-weight: bold; padding-right: 5px;">Mode de transport (Transport mode):</div>
                                        <div style="width: 50%; border-bottom: 1px dotted #666; padding-bottom: 1px;">{{ $reservation->data['mode_transport'] ?? 'BUS' }}</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex mb-1" style="font-size: 8pt;">
                                <div style="width: 35%; font-weight: bold; padding-right: 5px;">Véhicule N° (Car Number):</div>
                                <div style="width: 65%; border-bottom: 1px dotted #666; padding-bottom: 1px;">{{ $reservation->data['vehicule_numero'] ?? '' }}</div>
                            </div>
                            
                            {{-- Afficher les champs personnalisés de la section 4 (Informations de séjour) --}}
                            @php
                                $section4Fields = array_filter($formConfig->getCustomFieldsWithValues($reservation->data), function($item) {
                                    return $item['field']->section == 4;
                                });
                            @endphp
                            @foreach($section4Fields as $item)
                                <div class="d-flex mb-1" style="font-size: 8pt;">
                                    <div style="width: 35%; font-weight: bold; padding-right: 5px;">{{ $item['field']->label }}:</div>
                                    <div style="width: 65%; border-bottom: 1px dotted #666; padding-bottom: 1px;">{{ $item['formatted_value'] }}</div>
                                </div>
                            @endforeach
                        </div>
                        
                        <!-- Tableau récapitulatif -->
                        <table class="table table-bordered table-sm mb-3" style="font-size: 8pt;">
                            <thead>
                                <tr style="background-color: #f0f0f0;">
                                    <th style="padding: 4px 6px;">Date entrée<br>(Entry Date)</th>
                                    <th style="padding: 4px 6px;">Date sortie<br>(Exit Date)</th>
                                    <th style="padding: 4px 6px;">Total nuitées<br>(Total Nights)</th>
                                    <th style="padding: 4px 6px;">Chambre N°<br>(Room Number)</th>
                                    <th style="padding: 4px 6px;">Caution<br>(Deposit)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="text-align: center; padding: 4px 6px;">{{ $reservation->check_in_date->format('d/m/Y') }}</td>
                                    <td style="text-align: center; padding: 4px 6px;">{{ $reservation->check_out_date->format('d/m/Y') }}</td>
                                    <td style="text-align: center; padding: 4px 6px;">{{ $reservation->check_in_date->diffInDays($reservation->check_out_date) }}</td>
                                    <td style="text-align: center; padding: 4px 6px;">{{ $reservation->room->room_number ?? 'N/A' }}</td>
                                    <td style="text-align: center; padding: 4px 6px;">{{ number_format($reservation->paid_amount ?? 0, 0, ',', ' ') }}</td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <!-- Signatures -->
                        <div class="d-flex justify-content-between mt-4">
                            <div style="width: 48%;">
                                <div style="font-weight: bold; font-size: 8pt; margin-bottom: 2px;">HÔTEL</div>
                                <div style="border: 1px solid #000; height: 50px; margin-top: 5px; text-align: center; padding-top: 15px; font-size: 8pt;"></div>
                            </div>
                            <div style="width: 48%;">
                                <div style="font-weight: bold; font-size: 8pt; margin-bottom: 2px;">CLIENT</div>
                                <div style="border: 1px solid #000; height: 50px; margin-top: 5px; text-align: center; padding-top: 5px; font-size: 8pt;">
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
                </div>
            </div>
        </div>
    </div>
    
    <style>
        @media print {
            .card-body {
                padding: 0 !important;
            }
            .police-sheet-preview {
                box-shadow: none !important;
                padding: 0 !important;
            }
        }
        @media (max-width: 768px) {
            .police-sheet-preview {
                max-width: 100%;
                padding: 5mm;
            }
        }
    </style>
</x-app-layout>
