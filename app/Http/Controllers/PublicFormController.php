<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use App\Models\Reservation;
use App\Models\IdentityDocument;
use App\Models\Signature;
use App\Models\Room;
use App\Models\User;
use App\Models\NotificationLog;
use App\Http\Requests\StoreReservationRequest;
use App\Services\DocumentService;
use App\Services\FormConfigService;
use App\Services\ClientService;
use App\Services\ClientNotificationService;
use App\Mail\NewReservationHotel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class PublicFormController extends Controller
{
    protected $documentService;
    protected $clientService;
    protected $clientNotificationService;

    public function __construct(DocumentService $documentService, ClientService $clientService, ClientNotificationService $clientNotificationService)
    {
        $this->documentService = $documentService;
        $this->clientService = $clientService;
        $this->clientNotificationService = $clientNotificationService;
    }

    /**
     * Afficher le formulaire de réservation (check-in)
     */
	public function show(Hotel $hotel)
	{
        // Charger les types de chambre disponibles avec leurs chambres
        $roomTypes = $hotel->roomTypes()
            ->where('is_available', true)
            ->with(['rooms' => function($query) {
                $query->where('status', 'available')
                      ->orderBy('room_number');
            }])
            ->get();
        
        // Charger aussi toutes les chambres disponibles pour faciliter la sélection
        $rooms = $hotel->rooms()
            ->where('status', 'available')
            ->with('roomType')
            ->orderBy('room_number')
            ->get();
        
        // Créer le service de configuration du formulaire
        $formConfig = new FormConfigService($hotel);
        
        return view('public.form', [
            'hotel' => $hotel,
            'roomTypes' => $roomTypes,
            'rooms' => $rooms,
            'formConfig' => $formConfig
        ]);
    }

    /**
     * Enregistrer une réservation (check-in)
     */
    public function store(StoreReservationRequest $request, Hotel $hotel)
    {
        try {
            DB::beginTransaction();

            // 1. Préparer les données complètes de la réservation
            $reservationData = [
                'type_reservation' => $request->type_reservation ?? 'Individuel',
                
                // Informations groupe (si applicable)
                'nom_groupe' => $request->nom_groupe,
                'code_groupe' => $request->code_groupe,
                
                // Informations personnelles
                'type_piece_identite' => $request->type_piece_identite,
                'numero_piece_identite' => $request->numero_piece_identite,
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'sexe' => $request->sexe,
                'date_naissance' => $request->date_naissance,
                'lieu_naissance' => $request->lieu_naissance,
                'nationalite' => $request->nationalite,
                
                // Coordonnées
                'adresse' => $request->adresse,
                'ville' => $request->ville,
                'code_postal' => $request->code_postal,
                'pays' => $request->pays,
                'telephone' => $request->telephone,
                'email' => $request->email,
                'profession' => $request->profession,
                
                // Séjour
                'venant_de' => $request->venant_de,
                'date_arrivee' => $request->date_arrivee,
                'heure_arrivee' => $request->heure_arrivee,
                'date_depart' => $request->date_depart,
                'nombre_nuits' => $request->nombre_nuits,
                'nombre_adultes' => $request->nombre_adultes,
                'nombre_enfants' => $request->nombre_enfants ?? 0,
                'type_chambre' => $request->type_chambre,
                'preferences' => $request->preferences,
                'demandes_speciales' => $request->demandes_speciales,
                
                // Validation
                'confirmation_exactitude' => true,
                'acceptation_conditions' => true,
            ];
            
            // Ajouter les champs personnalisés
            $formConfig = new FormConfigService($hotel);
            $customFields = $formConfig->getCustomFields();
            foreach ($customFields as $field) {
                if ($request->has($field->key)) {
                    $reservationData[$field->key] = $request->input($field->key);
                }
            }

            // Récupérer les accompagnants (si nombre d'adultes >= 2)
            $accompagnants = [];
            if ($request->nombre_adultes >= 2) {
                $nbAccompagnants = $request->nombre_adultes - 1;
                for ($i = 1; $i <= $nbAccompagnants; $i++) {
                    $nom = $request->input("accompagnant_nom_{$i}");
                    $prenom = $request->input("accompagnant_prenom_{$i}");
                    
                    if ($nom || $prenom) {
                        $accompagnants[] = [
                            'nom' => $nom,
                            'prenom' => $prenom,
                        ];
                    }
                }
            }
            
            if (!empty($accompagnants)) {
                $reservationData['accompagnants'] = $accompagnants;
            }

            // 2. Vérifier la disponibilité des chambres
            if ($request->filled('room_id')) {
                // Vérifier la disponibilité de la chambre spécifique
                $room = Room::find($request->room_id);
                if (!$room) {
                    throw new \Exception('La chambre sélectionnée n\'existe pas.');
                }
                if (!$room->isAvailableForPeriod($request->date_arrivee, $request->date_depart)) {
                    throw new \Exception('Cette chambre n\'est plus disponible pour ces dates.');
                }
                // Vérifier que la chambre appartient bien à l'hôtel
                if ($room->hotel_id !== $hotel->id) {
                    throw new \Exception('Cette chambre n\'appartient pas à cet hôtel.');
                }
            } elseif ($request->filled('room_type_id')) {
                // Vérifier la disponibilité au niveau du type de chambre
                $roomType = $hotel->roomTypes()->find($request->room_type_id);
                if (!$roomType) {
                    throw new \Exception('Le type de chambre sélectionné n\'existe pas pour cet hôtel.');
                }
                if (!$roomType->is_available) {
                    throw new \Exception('Ce type de chambre n\'est plus disponible.');
                }
                
                // Vérifier s'il y a au moins une chambre disponible de ce type pour la période
                $availableRooms = Room::getAvailableRooms(
                    $hotel->id,
                    $request->room_type_id,
                    $request->date_arrivee,
                    $request->date_depart
                );
                
                if ($availableRooms->isEmpty()) {
                    throw new \Exception('Aucune chambre de ce type n\'est disponible pour ces dates. Veuillez choisir d\'autres dates ou un autre type de chambre.');
                }
            } else {
                throw new \Exception('Veuillez sélectionner un type de chambre.');
            }

            // 3. Créer la réservation
            // Note: La validation des doublons est déjà effectuée dans StoreReservationRequest
            $model = Reservation::class;
            
            $reservation = $model::create([
                'hotel_id' => $hotel->id,
                'room_type_id' => $request->room_type_id ?? null,
                'room_id' => $request->room_id ?? null,
                'check_in_date' => $request->date_arrivee,
                'check_out_date' => $request->date_depart,
                'status' => 'pending',
                'group_code' => $request->type_reservation === 'Groupe' ? $request->code_groupe : null,
                'data' => $reservationData,
            ]);

            // 5. Gérer les documents d'identité
            $identityDocument = $this->handleIdentityDocuments($request, $reservation);

            // 6. Créer ou mettre à jour le client avec les pièces d'identité
            try {
                $client = $this->clientService->createOrUpdateFromReservation($hotel, $reservationData, $identityDocument);
                Log::info('Client synchronisé avec succès', [
                    'hotel_id' => $hotel->id,
                    'client_id' => $client->id,
                    'email' => $client->email,
                ]);
            } catch (\Exception $e) {
                // Log l'erreur mais ne pas bloquer la création de la réservation
                Log::warning('Erreur lors de la synchronisation du client', [
                    'hotel_id' => $hotel->id,
                    'error' => $e->getMessage(),
                ]);
            }

            // 6. Gérer la signature
            $this->handleSignature($request, $reservation);

            DB::commit();

            // Log de succès
            Log::info('Nouvelle réservation créée', [
                'hotel_id' => $hotel->id,
                'reservation_id' => $reservation->id,
                'email' => $request->email,
            ]);

            // Envoyer les notifications client (email / SMS / WhatsApp selon config super-admin)
            try {
                $this->clientNotificationService->sendReservationCreated($reservation);
            } catch (\Exception $e) {
                Log::error('Erreur envoi notification client (réservation créée)', [
                    'reservation_id' => $reservation->id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Envoyer email de notification à l'hôtel (admin + réception)
            try {
                $hotelUsers = User::where('hotel_id', $hotel->id)
                    ->whereHas('roles', function($q) {
                        $q->whereIn('name', ['Hotel Admin', 'Reception']);
                    })
                    ->get();

                $mail = new NewReservationHotel($reservation);
                $subject = $mail->envelope()->subject;
                $successCount = 0;
                $failedCount = 0;

                foreach ($hotelUsers as $user) {
                    try {
                        Mail::to($user->email)->send($mail);
                        
                        // Enregistrer la notification en base de données
                        NotificationLog::create([
                            'reservation_id' => $reservation->id,
                            'type' => 'new_reservation_hotel',
                            'recipient_type' => 'hotel_staff',
                            'recipient_email' => $user->email,
                            'subject' => $subject,
                            'status' => 'success',
                            'sent_at' => now(),
                        ]);
                        
                        $successCount++;
                    } catch (\Exception $userMailError) {
                        // Enregistrer l'échec pour cet utilisateur spécifique
                        try {
                            NotificationLog::create([
                                'reservation_id' => $reservation->id,
                                'type' => 'new_reservation_hotel',
                                'recipient_type' => 'hotel_staff',
                                'recipient_email' => $user->email,
                                'subject' => $subject,
                                'status' => 'failed',
                                'error_message' => $userMailError->getMessage(),
                            ]);
                        } catch (\Exception $logError) {
                            // Si l'enregistrement du log échoue, on continue
                        }
                        
                        $failedCount++;
                        Log::error('Erreur lors de l\'envoi de l\'email à un utilisateur hôtel', [
                            'reservation_id' => $reservation->id,
                            'user_id' => $user->id,
                            'email' => $user->email,
                            'error' => $userMailError->getMessage(),
                        ]);
                    }
                }

                Log::info('Emails de notification hôtel envoyés', [
                    'reservation_id' => $reservation->id,
                    'hotel_id' => $hotel->id,
                    'users_count' => $hotelUsers->count(),
                    'success_count' => $successCount,
                    'failed_count' => $failedCount,
                ]);
            } catch (\Exception $e) {
                Log::error('Erreur lors de l\'envoi des emails à l\'hôtel', [
                    'reservation_id' => $reservation->id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Créer une notification pour tous les utilisateurs de l'hôtel
            try {
                app(\App\Services\NotificationService::class)->notifyNewReservation($reservation);
            } catch (\Exception $e) {
                Log::error('Erreur lors de la création de la notification', [
                    'reservation_id' => $reservation->id,
                    'error' => $e->getMessage(),
                ]);
            }

            return redirect()
                ->route('public.form', $hotel)
                ->with('success', '✅ Votre réservation a été enregistrée avec succès ! Vous allez recevoir un email de confirmation. L\'hôtel vous contactera prochainement.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Erreur lors de la création de la réservation', [
                'hotel_id' => $hotel->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Message d'erreur plus spécifique
            $errorMessage = 'Une erreur est survenue lors de l\'enregistrement.';
            if (str_contains($e->getMessage(), 'piece_identite') || str_contains($e->getMessage(), 'pièce d\'identité')) {
                $errorMessage = 'Veuillez fournir la ou les pièces d\'identité demandées (recto et/ou verso) en téléchargeant un fichier ou en prenant une photo.';
            } elseif (str_contains($e->getMessage(), 'chambre') || str_contains($e->getMessage(), 'room')) {
                $errorMessage = $e->getMessage();
            } elseif (str_contains($e->getMessage(), 'date') || str_contains($e->getMessage(), 'Date')) {
                $errorMessage = $e->getMessage();
            }

            return back()
                ->withInput()
                ->withErrors(['error' => $errorMessage])
                ->with('error', $errorMessage);
        }
    }

    /**
     * Gérer les documents d'identité (upload ou photo)
     * Retourne l'IdentityDocument créé pour pouvoir le sauvegarder dans le client
     */
    protected function handleIdentityDocuments(StoreReservationRequest $request, $reservation): ?IdentityDocument
    {
        $frontPath = null;
        $backPath = null;

        // Traiter le RECTO
        if ($request->hasFile('piece_identite_recto')) {
            // Upload depuis fichier
            $frontPath = $this->documentService->saveUploadedFile(
                $request->file('piece_identite_recto'),
                'documents', // Stocké dans images/uploads/documents
                'recto'
            );
        } elseif ($request->filled('photo_recto')) {
            // Upload depuis photo caméra (base64)
            $frontPath = $this->documentService->saveBase64Image(
                $request->photo_recto,
                'documents', // Stocké dans images/uploads/documents
                'recto'
            );
        }

        // Traiter le VERSO
        if ($request->hasFile('piece_identite_verso')) {
            // Upload depuis fichier
            $backPath = $this->documentService->saveUploadedFile(
                $request->file('piece_identite_verso'),
                'documents', // Stocké dans images/uploads/documents
                'verso'
            );
        } elseif ($request->filled('photo_verso')) {
            // Upload depuis photo caméra (base64)
            $backPath = $this->documentService->saveBase64Image(
                $request->photo_verso,
                'documents', // Stocké dans images/uploads/documents
                'verso'
            );
        }

        // Créer l'enregistrement du document d'identité
        if ($frontPath || $backPath) {
            $identityDocument = IdentityDocument::create([
                'reservation_id' => $reservation->id,
                'type' => $request->type_piece_identite,
                'front_path' => $frontPath,
                'back_path' => $backPath,
                'number' => $request->input('document_number') ?? $request->numero_piece_identite,
                'delivery_date' => $request->input('document_delivery_date'),
                'delivery_place' => $request->input('document_delivery_place'),
            ]);

            // Optimiser les images (optionnel)
            if ($frontPath) {
                $this->documentService->optimizeImage($frontPath, 1920, 1080);
            }
            if ($backPath) {
                $this->documentService->optimizeImage($backPath, 1920, 1080);
            }

            return $identityDocument;
        }

        return null;
    }

    /**
     * Gérer la signature électronique
     */
    protected function handleSignature(StoreReservationRequest $request, $reservation): void
    {
        if ($request->filled('signature')) {
            Signature::create([
                'reservation_id' => $reservation->id,
                'image_base64' => $request->signature,
            ]);
        }
        // La signature est optionnelle, donc on ne fait rien si elle n'est pas fournie
    }
}
