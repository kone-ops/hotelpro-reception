<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Hotel;
use App\Models\Reservation;
use App\Models\IdentityDocument;
use Illuminate\Support\Facades\Log;

class ClientService
{
    /**
     * Rechercher un client par email, téléphone ou numéro de pièce d'identité
     */
    public function searchClient(Hotel $hotel, string $query): ?Client
    {
        // Nettoyer la requête
        $query = trim($query);
        
        if (empty($query)) {
            return null;
        }

        // Rechercher par email
        if (filter_var($query, FILTER_VALIDATE_EMAIL)) {
            return Client::where('hotel_id', $hotel->id)
                ->where('email', $query)
                ->first();
        }

        // Rechercher par téléphone (supprimer les espaces et caractères spéciaux)
        $phoneQuery = preg_replace('/[^0-9+]/', '', $query);
        if (strlen($phoneQuery) >= 8) {
            $client = Client::where('hotel_id', $hotel->id)
                ->where('telephone', 'like', '%' . $phoneQuery . '%')
                ->first();
            
            if ($client) {
                return $client;
            }
        }

        // Rechercher par numéro de pièce d'identité
        $client = Client::where('hotel_id', $hotel->id)
            ->where('numero_piece_identite', $query)
            ->first();

        return $client;
    }

    /**
     * Créer ou mettre à jour un client à partir des données de réservation
     * Inclut la sauvegarde des pièces d'identité
     */
    public function createOrUpdateFromReservation(Hotel $hotel, array $reservationData, $identityDocument = null): Client
    {
        $identifier = $this->getClientIdentifier($reservationData);
        
        if (!$identifier) {
            throw new \Exception('Impossible d\'identifier le client : email, téléphone ou numéro de pièce d\'identité requis.');
        }

        // Rechercher le client existant
        $client = $this->findExistingClient($hotel, $reservationData);

        if ($client) {
            // Mettre à jour les données du client
            $this->updateClientData($client, $reservationData);
            
            // Mettre à jour les pièces d'identité si disponibles
            if ($identityDocument) {
                $this->updateClientIdentityDocument($client, $identityDocument);
            }
            
            $client->incrementReservationsCount();
            return $client;
        }

        // Créer un nouveau client
        $client = $this->createNewClient($hotel, $reservationData);
        
        // Ajouter les pièces d'identité si disponibles
        if ($identityDocument) {
            $this->updateClientIdentityDocument($client, $identityDocument);
        }
        
        return $client;
    }

    /**
     * Mettre à jour les pièces d'identité du client
     * Accepte IdentityDocument ou un objet avec les propriétés nécessaires
     */
    protected function updateClientIdentityDocument(Client $client, $identityDocument): void
    {
        if (!$identityDocument) {
            return;
        }
        
        $updateData = [];
        
        $frontPath = $identityDocument->front_path ?? null;
        $backPath = $identityDocument->back_path ?? null;
        $deliveryDate = $identityDocument->delivery_date ?? null;
        $deliveryPlace = $identityDocument->delivery_place ?? null;
        $ocrData = $identityDocument->ocr_data ?? null;
        
        if ($frontPath && empty($client->piece_identite_recto_path)) {
            $updateData['piece_identite_recto_path'] = $frontPath;
        }
        
        if ($backPath && empty($client->piece_identite_verso_path)) {
            $updateData['piece_identite_verso_path'] = $backPath;
        }
        
        if ($deliveryDate && empty($client->piece_identite_delivery_date)) {
            $updateData['piece_identite_delivery_date'] = $deliveryDate;
        }
        
        if ($deliveryPlace && empty($client->piece_identite_delivery_place)) {
            $updateData['piece_identite_delivery_place'] = $deliveryPlace;
        }
        
        if ($ocrData && empty($client->piece_identite_ocr_data)) {
            $updateData['piece_identite_ocr_data'] = $ocrData;
        }
        
        if (!empty($updateData)) {
            $client->update($updateData);
        }
    }

    /**
     * Obtenir un identifiant unique pour le client
     */
    protected function getClientIdentifier(array $data): ?string
    {
        if (!empty($data['email'])) {
            return 'email:' . $data['email'];
        }
        if (!empty($data['telephone'])) {
            return 'telephone:' . $data['telephone'];
        }
        if (!empty($data['numero_piece_identite'])) {
            return 'identity:' . $data['numero_piece_identite'];
        }
        return null;
    }

    /**
     * Trouver un client existant
     */
    protected function findExistingClient(Hotel $hotel, array $data): ?Client
    {
        // Rechercher par email
        if (!empty($data['email'])) {
            $client = Client::where('hotel_id', $hotel->id)
                ->where('email', $data['email'])
                ->first();
            
            if ($client) {
                return $client;
            }
        }

        // Rechercher par téléphone
        if (!empty($data['telephone'])) {
            $phone = preg_replace('/[^0-9+]/', '', $data['telephone']);
            $client = Client::where('hotel_id', $hotel->id)
                ->where('telephone', 'like', '%' . $phone . '%')
                ->first();
            
            if ($client) {
                return $client;
            }
        }

        // Rechercher par numéro de pièce d'identité
        if (!empty($data['numero_piece_identite'])) {
            $client = Client::where('hotel_id', $hotel->id)
                ->where('numero_piece_identite', $data['numero_piece_identite'])
                ->first();
            
            if ($client) {
                return $client;
            }
        }

        return null;
    }


    /**
     * Obtenir les données du client pour pré-remplir le formulaire
     * Inclut les pièces d'identité depuis le client ou les réservations existantes
     */
    public function getClientFormData(Client $client): array
    {
        $data = [
            'type_piece_identite' => $client->type_piece_identite,
            'numero_piece_identite' => $client->numero_piece_identite,
            'nom' => $client->nom,
            'prenom' => $client->prenom,
            'sexe' => $client->sexe,
            'date_naissance' => $client->date_naissance ? $client->date_naissance->format('Y-m-d') : null,
            'lieu_naissance' => $client->lieu_naissance,
            'nationalite' => $client->nationalite,
            'adresse' => $client->adresse,
            'telephone' => $client->telephone,
            'email' => $client->email,
            'profession' => $client->profession,
        ];

        // Récupérer les pièces d'identité depuis le client ou les réservations existantes
        $identityDoc = $this->getClientIdentityDocument($client);
        
        if ($identityDoc) {
            if ($identityDoc->front_path) {
                $data['piece_identite_recto_url'] = $identityDoc->front_url;
                $data['piece_identite_recto_path'] = $identityDoc->front_path;
            }
            if ($identityDoc->back_path) {
                $data['piece_identite_verso_url'] = $identityDoc->back_url;
                $data['piece_identite_verso_path'] = $identityDoc->back_path;
            }
            if ($identityDoc->delivery_date) {
                $data['piece_identite_delivery_date'] = $identityDoc->delivery_date->format('Y-m-d');
            }
            if ($identityDoc->delivery_place) {
                $data['piece_identite_delivery_place'] = $identityDoc->delivery_place;
            }
            if ($identityDoc->number) {
                $data['document_number'] = $identityDoc->number;
            }
        } else {
            // Fallback sur les données du client si disponibles
            if ($client->piece_identite_recto_path) {
                $data['piece_identite_recto_url'] = $client->piece_identite_recto_url;
                $data['piece_identite_recto_path'] = $client->piece_identite_recto_path;
            }
            if ($client->piece_identite_verso_path) {
                $data['piece_identite_verso_url'] = $client->piece_identite_verso_url;
                $data['piece_identite_verso_path'] = $client->piece_identite_verso_path;
            }
            if ($client->piece_identite_delivery_date) {
                $data['piece_identite_delivery_date'] = $client->piece_identite_delivery_date->format('Y-m-d');
            }
            if ($client->piece_identite_delivery_place) {
                $data['piece_identite_delivery_place'] = $client->piece_identite_delivery_place;
            }
        }

        // Récupérer les champs personnalisés depuis la dernière réservation du client
        $lastReservation = \App\Models\Reservation::where('hotel_id', $client->hotel_id)
            ->where(function($query) use ($client) {
                if ($client->email) {
                    $query->where('data->email', $client->email);
                }
                if ($client->telephone) {
                    $query->orWhere('data->telephone', 'like', '%' . preg_replace('/[^0-9+]/', '', $client->telephone) . '%');
                }
            })
            ->orderBy('created_at', 'desc')
            ->first();
            
        if ($lastReservation && $lastReservation->data) {
            $reservationData = $lastReservation->data;
            
            // Récupérer les champs personnalisés (tout ce qui n'est pas un champ standard)
            $standardFields = [
                'type_reservation', 'nom_groupe', 'code_groupe',
                'type_piece_identite', 'numero_piece_identite', 'nom', 'prenom', 'sexe',
                'date_naissance', 'lieu_naissance', 'nationalite', 'adresse', 'ville',
                'code_postal', 'pays', 'telephone', 'email', 'profession',
                'venant_de', 'date_arrivee', 'heure_arrivee', 'date_depart',
                'nombre_nuits', 'nombre_adultes', 'nombre_enfants', 'type_chambre',
                'preferences', 'demandes_speciales', 'accompagnants',
                'confirmation_exactitude', 'acceptation_conditions',
                'piece_identite_recto_path', 'piece_identite_verso_path',
                'piece_identite_delivery_date', 'piece_identite_delivery_place',
                'document_number', 'piece_identite_recto_url', 'piece_identite_verso_url'
            ];
            
            foreach ($reservationData as $key => $value) {
                // Si ce n'est pas un champ standard, c'est probablement un champ personnalisé
                if (!in_array($key, $standardFields) && !empty($value)) {
                    $data[$key] = $value;
                }
            }
        }

        return $data;
    }

    /**
     * Récupérer le document d'identité du client depuis ses réservations
     * Retourne un objet avec les propriétés nécessaires (peut être un IdentityDocument ou un objet virtuel)
     */
    protected function getClientIdentityDocument(Client $client)
    {
        // D'abord vérifier si le client a des données directement
        if ($client->piece_identite_recto_path) {
            // Créer un objet virtuel pour compatibilité avec IdentityDocument
            $virtualDoc = new \stdClass();
            $virtualDoc->front_path = $client->piece_identite_recto_path;
            $virtualDoc->back_path = $client->piece_identite_verso_path;
            $virtualDoc->delivery_date = $client->piece_identite_delivery_date;
            $virtualDoc->delivery_place = $client->piece_identite_delivery_place;
            $virtualDoc->number = $client->numero_piece_identite;
            $virtualDoc->front_url = $client->piece_identite_recto_url;
            $virtualDoc->back_url = $client->piece_identite_verso_url;
            return $virtualDoc;
        }

        // Sinon, chercher dans les réservations existantes
        $reservation = Reservation::where('hotel_id', $client->hotel_id)
            ->where(function($query) use ($client) {
                if ($client->email) {
                    $query->where('data->email', $client->email);
                }
                if ($client->telephone) {
                    $query->orWhere('data->telephone', $client->telephone);
                }
                if ($client->numero_piece_identite) {
                    $query->orWhere('data->numero_piece_identite', $client->numero_piece_identite);
                }
            })
            ->with('identityDocument')
            ->latest()
            ->first();

        return $reservation?->identityDocument;
    }

    /**
     * Vérifier les doublons potentiels avant création/mise à jour
     * Retourne un tableau avec les informations sur les doublons trouvés
     */
    public function checkDuplicates(Hotel $hotel, array $data, ?Client $excludeClient = null): array
    {
        $duplicates = [];

        // Vérifier l'email
        if (!empty($data['email'])) {
            $query = Client::where('hotel_id', $hotel->id)
                ->where('email', $data['email']);
            
            if ($excludeClient) {
                $query->where('id', '!=', $excludeClient->id);
            }
            
            $existing = $query->first();
            if ($existing) {
                $duplicates['email'] = [
                    'field' => 'email',
                    'value' => $data['email'],
                    'message' => 'Cet email est déjà utilisé par un autre client (' . $existing->full_name . ')',
                    'existing_client' => $existing->full_name,
                ];
            }
        }

        // Vérifier le téléphone
        if (!empty($data['telephone'])) {
            $phone = preg_replace('/[^0-9+]/', '', $data['telephone']);
            $query = Client::where('hotel_id', $hotel->id)
                ->where(function($q) use ($phone) {
                    $q->where('telephone', 'like', '%' . $phone . '%')
                      ->orWhereRaw('REPLACE(REPLACE(REPLACE(telephone, " ", ""), "-", ""), "+", "") LIKE ?', ['%' . $phone . '%']);
                });
            
            if ($excludeClient) {
                $query->where('id', '!=', $excludeClient->id);
            }
            
            $existing = $query->first();
            if ($existing) {
                $duplicates['telephone'] = [
                    'field' => 'telephone',
                    'value' => $data['telephone'],
                    'message' => 'Ce numéro de téléphone est déjà utilisé par un autre client (' . $existing->full_name . ')',
                    'existing_client' => $existing->full_name,
                ];
            }
        }

        // Vérifier le numéro de pièce d'identité
        if (!empty($data['numero_piece_identite'])) {
            $query = Client::where('hotel_id', $hotel->id)
                ->where('numero_piece_identite', $data['numero_piece_identite']);
            
            if ($excludeClient) {
                $query->where('id', '!=', $excludeClient->id);
            }
            
            $existing = $query->first();
            if ($existing) {
                $duplicates['numero_piece_identite'] = [
                    'field' => 'numero_piece_identite',
                    'value' => $data['numero_piece_identite'],
                    'message' => 'Ce numéro de pièce d\'identité est déjà utilisé par un autre client (' . $existing->full_name . ')',
                    'existing_client' => $existing->full_name,
                ];
            }
        }

        return $duplicates;
    }

    /**
     * Mettre à jour les données d'un client existant avec les pièces d'identité
     */
    protected function updateClientData(Client $client, array $data): void
    {
        $updateData = [];

        // Mettre à jour uniquement les champs qui ont des valeurs
        $fields = [
            'email',
            'telephone',
            'numero_piece_identite',
            'type_piece_identite',
            'nom',
            'prenom',
            'sexe',
            'date_naissance',
            'lieu_naissance',
            'nationalite',
            'adresse',
            'profession',
            'piece_identite_recto_path',
            'piece_identite_verso_path',
            'piece_identite_delivery_date',
            'piece_identite_delivery_place',
            'piece_identite_ocr_data',
        ];

        foreach ($fields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) {
                // Ne pas écraser une valeur existante si la nouvelle est vide
                if (empty($client->$field) || $data[$field] !== $client->$field) {
                    $updateData[$field] = $data[$field];
                }
            }
        }

        if (!empty($updateData)) {
            $client->update($updateData);
        }
    }

    /**
     * Créer un nouveau client avec les pièces d'identité
     */
    protected function createNewClient(Hotel $hotel, array $data): Client
    {
        $clientData = [
            'hotel_id' => $hotel->id,
            'email' => $data['email'] ?? null,
            'telephone' => $data['telephone'] ?? null,
            'numero_piece_identite' => $data['numero_piece_identite'] ?? null,
            'type_piece_identite' => $data['type_piece_identite'] ?? null,
            'nom' => $data['nom'] ?? null,
            'prenom' => $data['prenom'] ?? null,
            'sexe' => $data['sexe'] ?? null,
            'date_naissance' => !empty($data['date_naissance']) ? $data['date_naissance'] : null,
            'lieu_naissance' => $data['lieu_naissance'] ?? null,
            'nationalite' => $data['nationalite'] ?? null,
            'adresse' => $data['adresse'] ?? null,
            'profession' => $data['profession'] ?? null,
            'piece_identite_recto_path' => $data['piece_identite_recto_path'] ?? null,
            'piece_identite_verso_path' => $data['piece_identite_verso_path'] ?? null,
            'piece_identite_delivery_date' => !empty($data['piece_identite_delivery_date']) ? $data['piece_identite_delivery_date'] : null,
            'piece_identite_delivery_place' => $data['piece_identite_delivery_place'] ?? null,
            'piece_identite_ocr_data' => $data['piece_identite_ocr_data'] ?? null,
            'reservations_count' => 1,
            'first_seen_at' => now(),
            'last_reservation_at' => now(),
        ];

        $client = Client::create($clientData);

        Log::info('Nouveau client créé', [
            'hotel_id' => $hotel->id,
            'client_id' => $client->id,
            'email' => $client->email,
        ]);

        return $client;
    }
}

