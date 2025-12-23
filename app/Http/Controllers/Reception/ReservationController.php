<?php

namespace App\Http\Controllers\Reception;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\ActivityLog;
use App\Services\NotificationService;
use App\Services\FormConfigService;
use App\Services\ReservationStatusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReservationController extends Controller
{
    protected NotificationService $notificationService;
    protected ReservationStatusService $statusService;

    public function __construct(NotificationService $notificationService, ReservationStatusService $statusService)
    {
        $this->notificationService = $notificationService;
        $this->statusService = $statusService;
    }

    /**
     * Afficher toutes les réservations
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $hotel = $user->hotel;
        $hotelId = $user->hotel_id;
        
        $query = Reservation::where('hotel_id', $hotelId)
            ->with(['room', 'roomType', 'validatedBy']);
        
        // Filtres
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('data->nom', 'like', "%{$search}%")
                  ->orWhere('data->prenom', 'like', "%{$search}%")
                  ->orWhere('data->email', 'like', "%{$search}%")
                  ->orWhere('data->telephone', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('check_in_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('check_in_date', '<=', $request->date_to);
        }
        
        $reservations = $query->latest()->paginate(20);
        
        // Statistiques
        $stats = [
            'total' => Reservation::where('hotel_id', $hotelId)->count(),
            'pending' => Reservation::where('hotel_id', $hotelId)->where('status', 'pending')->count(),
            'validated' => Reservation::where('hotel_id', $hotelId)->where('status', 'validated')->count(),
            'checked_in' => Reservation::where('hotel_id', $hotelId)->where('status', 'checked_in')->count(),
            'checked_out' => Reservation::where('hotel_id', $hotelId)->where('status', 'checked_out')->count(),
            'rejected' => Reservation::where('hotel_id', $hotelId)->where('status', 'rejected')->count(),
        ];
        
        return view('reception.reservations.index', compact('reservations', 'stats', 'hotel'));
    }
    
    /**
     * Afficher une réservation
     */
    public function show($id)
    {
        $hotelId = Auth::user()->hotel_id;
        $hotel = Auth::user()->hotel;
        
        $reservation = Reservation::with(['hotel', 'identityDocument', 'signature', 'room', 'roomType'])
            ->where('hotel_id', $hotelId)
            ->findOrFail($id);
        
        $formConfig = new FormConfigService($hotel);
        
        return view('reception.reservations.show', compact('reservation', 'formConfig'));
    }
    
    /**
     * Éditer une réservation
     */
    public function edit($id)
    {
        $hotelId = Auth::user()->hotel_id;
        $hotel = Auth::user()->hotel;
        
        $reservation = Reservation::with(['hotel', 'identityDocument', 'signature', 'room', 'roomType'])
            ->where('hotel_id', $hotelId)
            ->findOrFail($id);
        
        // Bloquer l'accès au formulaire si la réservation n'est pas en attente
        if ($reservation->status !== 'pending') {
            return redirect()->route('reception.reservations.show', $reservation->id)
                ->withErrors([
                    'error' => 'Cette réservation est validée ou traitée et ne peut plus être modifiée.'
                ]);
        }

        // Vérifier si la réservation peut être modifiée
        if (!$this->statusService->canBeModified($reservation)) {
            return redirect()->route('reception.reservations.show', $reservation->id)
                ->withErrors([
                    'error' => 'Cette réservation ne peut plus être modifiée. Le check-in a été effectué et les modifications sont verrouillées pour des raisons de sécurité et de traçabilité.'
                ]);
        }
        
        $roomTypes = RoomType::where('hotel_id', $hotelId)
            ->where('is_available', true)
            ->get();
        
        $rooms = Room::where('hotel_id', $hotelId)
            ->where('status', 'available')
            ->get();
        
        $formConfig = new FormConfigService($hotel);
        
        return view('reception.reservations.edit', compact('reservation', 'roomTypes', 'rooms', 'hotel', 'formConfig'));
    }
    
    /**
     * Mettre à jour une réservation
     */
    public function update(Request $request, $id)
    {
        $hotelId = Auth::user()->hotel_id;
        
        $reservation = Reservation::where('hotel_id', $hotelId)->findOrFail($id);

        // Bloquer toute modification si le statut n'est pas "pending"
        if ($reservation->status !== 'pending') {
            return back()->withErrors([
                'error' => 'Cette réservation est validée ou traitée et ne peut plus être modifiée.'
            ]);
        }
        
        // Vérifier si la réservation peut être modifiée
        if (!$this->statusService->canBeModified($reservation)) {
            return back()->withErrors([
                'error' => 'Cette réservation ne peut plus être modifiée. Le check-in a été effectué et les modifications sont verrouillées pour des raisons de sécurité et de traçabilité.'
            ]);
        }
        
        $validated = $request->validate([
            'room_type_id' => 'nullable|exists:room_types,id',
            'room_id' => 'nullable|exists:rooms,id',
            'check_in_date' => 'nullable|date',
            'check_out_date' => 'nullable|date|after:check_in_date',
            'status' => 'nullable|in:pending,validated,rejected,checked_in,checked_out',
            'notes' => 'nullable|string',
        ]);
        
        // Si un changement de statut est demandé, valider la transition
        if (isset($validated['status']) && $validated['status'] !== $reservation->status) {
            $validation = $this->statusService->validateTransition($reservation, $validated['status']);
            
            if (!$validation['allowed']) {
                return back()->withErrors(['error' => $validation['message']]);
            }
        }
        
        // Vérifier si les données critiques peuvent être modifiées
        if (!$this->statusService->canModifyCriticalData($reservation)) {
            // Bloquer la modification des champs critiques après check-in
            unset($validated['check_in_date']);
            unset($validated['check_out_date']);
            
            // Si on essaie de changer de chambre après check-in, refuser
            if ($request->filled('room_id') && $reservation->room_id != $request->room_id) {
                return back()->withErrors([
                    'error' => 'Impossible de changer de chambre après le check-in. Cette action est verrouillée pour des raisons de sécurité.'
                ]);
            }
        }
        
        try {
            DB::beginTransaction();
            
            // Libérer l'ancienne chambre si elle change (seulement si autorisé)
            if ($request->filled('room_id') && $reservation->room_id != $request->room_id && $this->statusService->canModifyCriticalData($reservation)) {
                if ($reservation->room) {
                    $reservation->room->update(['status' => 'available']);
                }
                
                // Marquer la nouvelle chambre comme occupée
                if ($request->room_id) {
                    $newRoom = Room::find($request->room_id);
                    if ($newRoom) {
                        $newRoom->update(['status' => 'occupied']);
                    }
                }
            }
            
            // Mettre à jour les données de la réservation (champ data)
            $currentData = $reservation->data ?? [];
            
            // Récupérer les données du formulaire à sauvegarder dans data
            $formData = [
                'type_reservation' => $request->input('type_reservation', $currentData['type_reservation'] ?? 'individuel'),
                'nom_groupe' => $request->input('nom_groupe', $currentData['nom_groupe'] ?? null),
                'code_groupe' => $request->input('code_groupe', $currentData['code_groupe'] ?? null),
            ];
            
            // Récupérer les accompagnants
            $accompagnants = [];
            $nombreAdultes = (int)($request->input('nombre_adultes', $currentData['nombre_adultes'] ?? 1));
            if ($nombreAdultes >= 2) {
                $nbAccompagnants = $nombreAdultes - 1;
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
                $formData['accompagnants'] = $accompagnants;
            } elseif (isset($currentData['accompagnants'])) {
                // Conserver les accompagnants existants si aucun nouveau n'est fourni
                $formData['accompagnants'] = $currentData['accompagnants'];
            }
            
            // Récupérer les autres champs du formulaire qui vont dans data
            $dataFields = [
                'type_piece_identite', 'numero_piece_identite', 'nom', 'prenom', 'sexe',
                'date_naissance', 'lieu_naissance', 'nationalite', 'adresse', 'ville',
                'code_postal', 'pays', 'telephone', 'email', 'profession',
                'venant_de', 'date_arrivee', 'heure_arrivee', 'date_depart',
                'nombre_nuits', 'nombre_adultes', 'nombre_enfants', 'type_chambre',
                'preferences', 'demandes_speciales'
            ];
            
            foreach ($dataFields as $field) {
                if ($request->has($field)) {
                    $formData[$field] = $request->input($field);
                } elseif (isset($currentData[$field])) {
                    // Conserver les valeurs existantes si non modifiées
                    $formData[$field] = $currentData[$field];
                }
            }
            
            // Récupérer les champs personnalisés
            $hotel = Auth::user()->hotel;
            $formConfig = new FormConfigService($hotel);
            $customFields = $formConfig->getCustomFields();
            foreach ($customFields as $field) {
                if ($request->has($field->key)) {
                    $value = $request->input($field->key);
                    // Pour les checkboxes, convertir en booléen
                    if ($field->type === 'checkbox') {
                        $formData[$field->key] = (bool)$value;
                    } else {
                        $formData[$field->key] = $value;
                    }
                } elseif (isset($currentData[$field->key])) {
                    // Conserver les valeurs existantes si non modifiées
                    $formData[$field->key] = $currentData[$field->key];
                }
            }
            
            // Fusionner avec les données existantes pour préserver les champs non modifiés
            $updatedData = array_merge($currentData, $formData);
            
            // Mettre à jour le statut et les autres champs
            $oldStatus = $reservation->status;
            $validated['data'] = $updatedData;
            $reservation->update($validated);
            $newStatus = $reservation->fresh()->status;
            
            // Détecter les changements de statut pour les notifications
            if ($oldStatus !== $newStatus) {
                // Logger la transition de statut
                ActivityLog::log(
                    "Transition de statut: {$oldStatus} → {$newStatus} - Réservation N°" . str_pad($reservation->id, 7, '0', STR_PAD_LEFT),
                    $reservation,
                    [
                        'action_type' => 'reservation_status_change',
                        'reservation_id' => $reservation->id,
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus,
                        'changed_by' => Auth::id(),
                        'changed_at' => now()->toDateTimeString(),
                    ],
                    'reservation',
                    'updated'
                );
                
                if ($newStatus === 'checked_in') {
                    $reservation->update([
                        'checked_in_at' => now(),
                        'checked_in_by' => Auth::id(),
                    ]);
                    try {
                        $this->notificationService->notifyCheckIn($reservation->fresh());
                    } catch (\Exception $e) {
                        Log::error('Erreur notification check-in', ['error' => $e->getMessage()]);
                    }
                } elseif ($newStatus === 'checked_out') {
                    $reservation->update([
                        'checked_out_at' => now(),
                        'checked_out_by' => Auth::id(),
                    ]);
                    try {
                        $this->notificationService->notifyCheckOut($reservation->fresh());
                    } catch (\Exception $e) {
                        Log::error('Erreur notification check-out', ['error' => $e->getMessage()]);
                    }
                }
            }
            
            DB::commit();
            
            Log::info('Réservation mise à jour', [
                'reservation_id' => $reservation->id,
                'updated_by' => Auth::id(),
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]);
            
            return redirect()->route('reception.reservations.show', $reservation->id)
                ->with('success', 'Réservation mise à jour avec succès');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Erreur mise à jour réservation', [
                'reservation_id' => $reservation->id,
                'error' => $e->getMessage(),
            ]);
            
            return back()->withErrors(['error' => 'Erreur lors de la mise à jour de la réservation'])->withInput();
        }
    }
    
    /**
     * Valider une réservation
     */
    public function validateReservation($id)
    {
        $hotelId = Auth::user()->hotel_id;
        
        $reservation = Reservation::where('hotel_id', $hotelId)->findOrFail($id);
        
        // Valider la transition vers validated
        $validation = $this->statusService->validateTransition($reservation, 'validated');
        
        if (!$validation['allowed']) {
            return back()->withErrors(['error' => $validation['message']]);
        }
            
        $reservation->update([
            'status' => 'validated',
            'validated_at' => now(),
            'validated_by' => Auth::id(),
        ]);
            
        // Si une chambre est assignée, la marquer comme occupée
        if ($reservation->room_id && $reservation->room) {
            $reservation->room->update(['status' => 'occupied']);
        }
        
        // Envoyer l'email de confirmation (synchrone - instantané)
        try {
            $reservation->load(['hotel', 'room', 'roomType', 'identityDocument']);
            if ($reservation->client_email) {
                \Illuminate\Support\Facades\Mail::to($reservation->client_email)
                    ->send(new \App\Mail\ReservationValidated($reservation));
            }
        } catch (\Exception $e) {
            Log::error('Erreur envoi email validation', [
                'reservation_id' => $reservation->id,
                'error' => $e->getMessage()
            ]);
        }
        
        // Générer la fiche de police (synchrone)
        try {
            $policeSheetService = app(\App\Services\PoliceSheetService::class);
            $policeSheetService->generateAndStore($reservation);
        } catch (\Exception $e) {
            Log::error('Erreur génération fiche de police', [
                'reservation_id' => $reservation->id,
                'error' => $e->getMessage()
            ]);
        }
        
        // Créer une notification
        try {
            $this->notificationService->notifyReservationValidated($reservation->fresh());
        } catch (\Exception $e) {
            Log::error('Erreur notification validation', ['error' => $e->getMessage()]);
        }
        
        // Logger l'activité critique
        ActivityLog::log(
            'Réservation validée - N°' . str_pad($reservation->id, 7, '0', STR_PAD_LEFT),
            $reservation,
            [
                'action_type' => 'reservation_validated',
                'reservation_id' => $reservation->id,
                'client_name' => $reservation->client_full_name,
                'hotel_name' => $reservation->hotel->name ?? null,
                'room_number' => $reservation->room->room_number ?? null,
            ],
            'reservation',
            'updated'
        );
        
        Log::info('Réservation validée', [
            'reservation_id' => $reservation->id,
            'validated_by' => Auth::id(),
        ]);
        
        return redirect()->route('reception.reservations.index')
            ->with('success', 'Réservation validée avec succès');
    }
    
    /**
     * Rejeter une réservation
     */
    public function reject(Request $request, $id)
    {
        $hotelId = Auth::user()->hotel_id;
        
        $reservation = Reservation::where('hotel_id', $hotelId)->findOrFail($id);
        
        // Valider la transition vers rejected
        $validation = $this->statusService->validateTransition($reservation, 'rejected');
        
        if (!$validation['allowed']) {
            return back()->withErrors(['error' => $validation['message']]);
        }
        
        $reason = $request->input('reason', null);
        $oldStatus = $reservation->status;
            
        $reservation->update([
            'status' => 'rejected',
            'validated_by' => Auth::id(),
        ]);
            
        // Libérer la chambre si elle était réservée
        if ($reservation->room_id && $reservation->room) {
                $reservation->room->update(['status' => 'available']);
        }
        
        // Envoyer l'email de rejet (synchrone - instantané)
        try {
            $reservation->load(['hotel']);
            if ($reservation->client_email) {
                \Illuminate\Support\Facades\Mail::to($reservation->client_email)
                    ->send(new \App\Mail\ReservationRejected($reservation, $reason ?? ''));
            }
        } catch (\Exception $e) {
            Log::error('Erreur envoi email rejet', [
                'reservation_id' => $reservation->id,
                'error' => $e->getMessage()
            ]);
        }
        
        // Logger l'activité critique
        ActivityLog::log(
            'Réservation rejetée - N°' . str_pad($reservation->id, 7, '0', STR_PAD_LEFT),
            $reservation,
            [
                'action_type' => 'reservation_rejected',
                'reservation_id' => $reservation->id,
                'client_name' => $reservation->client_full_name,
                'hotel_name' => $reservation->hotel->name ?? null,
                'reason' => $reason,
                'old_status' => $oldStatus,
                'new_status' => 'rejected',
            ],
            'reservation',
            'updated'
        );
        
        Log::info('Réservation rejetée', [
            'reservation_id' => $reservation->id,
            'rejected_by' => Auth::id(),
        ]);
        
        return redirect()->route('reception.reservations.index')
            ->with('success', 'Réservation rejetée');
    }

    
    /**
     * Effectuer le check-in d'un client
     */
    public function checkIn($id)
    {
        $hotelId = Auth::user()->hotel_id;
        
        $reservation = Reservation::where('hotel_id', $hotelId)->findOrFail($id);
        
        // Valider la transition vers checked_in
        $validation = $this->statusService->validateTransition($reservation, 'checked_in');
        
        if (!$validation['allowed']) {
            return back()->withErrors(['error' => $validation['message']]);
        }
        
        try {
            DB::beginTransaction();
            
            // Mettre à jour le statut de la réservation
            $reservation->update([
                'status' => 'checked_in',
                'checked_in_at' => now(),
                'checked_in_by' => Auth::id(),
            ]);
            
            // Marquer la chambre comme occupée
            if ($reservation->room) {
                $reservation->room->update(['status' => 'occupied']);
            }
            
            // Envoyer une notification
            try {
                $this->notificationService->notifyCheckIn($reservation->fresh());
            } catch (\Exception $e) {
                Log::error('Erreur notification check-in', ['error' => $e->getMessage()]);
            }
            
            // Logger l'activité critique
            ActivityLog::log(
                'Check-in effectué - Réservation N°' . str_pad($reservation->id, 7, '0', STR_PAD_LEFT),
                $reservation,
                [
                    'action_type' => 'reservation_checkin',
                    'reservation_id' => $reservation->id,
                    'client_name' => $reservation->client_full_name,
                    'hotel_name' => $reservation->hotel->name ?? null,
                    'room_number' => $reservation->room->room_number ?? null,
                    'checked_in_at' => now()->toDateTimeString(),
                ],
                'reservation',
                'updated'
            );
            
            DB::commit();
            
            Log::info('Check-in effectué', [
                'reservation_id' => $reservation->id,
                'room_id' => $reservation->room_id,
                'checked_in_by' => Auth::id(),
            ]);
            
            return redirect()->route('reception.reservations.show', $reservation->id)
                ->with('success', 'Check-in effectué avec succès. La chambre est maintenant occupée.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Erreur check-in', [
                'reservation_id' => $reservation->id,
                'error' => $e->getMessage(),
            ]);
            
            return back()->withErrors(['error' => 'Erreur lors du check-in: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Effectuer le check-out d'un client
     */
    public function checkOut($id)
    {
        $hotelId = Auth::user()->hotel_id;
        
        $reservation = Reservation::where('hotel_id', $hotelId)->findOrFail($id);
        
        // Valider la transition vers checked_out
        $validation = $this->statusService->validateTransition($reservation, 'checked_out');
        
        if (!$validation['allowed']) {
            return back()->withErrors(['error' => $validation['message']]);
        }
        
        // Vérifier qu'une chambre est assignée (condition hôtellerie)
        if (!$reservation->room_id) {
            // Envoyer une notification pour alerter qu'une chambre est requise
            try {
                $clientName = $reservation->client_full_name ?? 'Client';
                $this->notificationService->notifyHotelUsers(
                    Auth::user()->hotel_id,
                    'check_out_no_room',
                    '⚠️ Check-out Impossible',
                    "Impossible d'effectuer le check-out de {$clientName} : aucune chambre n'est assignée à cette réservation.",
                    'danger',
                    null,
                    $reservation,
                    [
                        'reservation_id' => $reservation->id,
                        'client_name' => $clientName,
                    ],
                    route('reception.reservations.show', $reservation),
                    'Voir la réservation'
                );
            } catch (\Exception $e) {
                Log::error('Erreur notification check-out sans chambre', ['error' => $e->getMessage()]);
            }
            
            return back()->withErrors(['error' => 'Impossible d\'effectuer le check-out : aucune chambre assignée à cette réservation']);
        }
        
        try {
            DB::beginTransaction();
            
            // Mettre à jour le statut de la réservation
            $reservation->update([
                'status' => 'checked_out',
                'checked_out_at' => now(),
                'checked_out_by' => Auth::id(),
            ]);
            
            // Libérer la chambre
            if ($reservation->room) {
                $reservation->room->update(['status' => 'available']);
            }
            
            // Envoyer une notification
            try {
                $this->notificationService->notifyCheckOut($reservation->fresh());
            } catch (\Exception $e) {
                Log::error('Erreur notification check-out', ['error' => $e->getMessage()]);
            }
            
            // Logger l'activité critique
            ActivityLog::log(
                'Check-out effectué - Réservation N°' . str_pad($reservation->id, 7, '0', STR_PAD_LEFT),
                $reservation,
                [
                    'action_type' => 'reservation_checkout',
                    'reservation_id' => $reservation->id,
                    'client_name' => $reservation->client_full_name,
                    'hotel_name' => $reservation->hotel->name ?? null,
                    'room_number' => $reservation->room->room_number ?? null,
                    'checked_out_at' => now()->toDateTimeString(),
                ],
                'reservation',
                'updated'
            );
            
            DB::commit();
            
            Log::info('Check-out effectué', [
                'reservation_id' => $reservation->id,
                'room_id' => $reservation->room_id,
                'checked_out_by' => Auth::id(),
            ]);
            
            return redirect()->route('reception.reservations.show', $reservation->id)
                ->with('success', 'Check-out effectué avec succès. La chambre est maintenant disponible.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Erreur check-out', [
                'reservation_id' => $reservation->id,
                'error' => $e->getMessage(),
            ]);
            
            return back()->withErrors(['error' => 'Erreur lors du check-out: ' . $e->getMessage()]);
        }
    }
}
