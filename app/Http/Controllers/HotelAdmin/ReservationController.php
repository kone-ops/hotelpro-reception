<?php

namespace App\Http\Controllers\HotelAdmin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\RoomType;
use App\Models\Room;
use App\Mail\ReservationValidated;
use App\Mail\ReservationRejected;
use App\Services\NotificationService;
use App\Services\ReservationStatusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
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

    public function index(Request $request)
    {
        $hotel = Auth::user()->hotel;
        
        if (!$hotel) {
            abort(404, 'Aucun hôtel assigné');
        }
        
        $query = $hotel->reservations();
        
        // Filtres
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        // Filtre par type de réservation (individuel/groupe)
        if ($request->has('type') && $request->type) {
            if ($request->type === 'groupe') {
                $query->group();
            } elseif ($request->type === 'individuel') {
                $query->individual();
            }
        }
        
        $reservations = $query->latest()->paginate(15);
        
        $stats = [
            'total' => $hotel->reservations()->count(),
            'pending' => $hotel->reservations()->where('status', 'pending')->count(),
            'validated' => $hotel->reservations()->where('status', 'validated')->count(),
            'checked_in' => $hotel->reservations()->where('status', 'checked_in')->count(),
            'checked_out' => $hotel->reservations()->where('status', 'checked_out')->count(),
            'rejected' => $hotel->reservations()->where('status', 'rejected')->count(),
            'individual' => $hotel->reservations()->individual()->count(),
            'group' => $hotel->reservations()->group()->count(),
        ];
        
        return view('hotel.reservations.index', compact('reservations', 'hotel', 'stats'));
    }

    public function show($id)
    {
        $hotel = Auth::user()->hotel;
        
        $reservation = Reservation::with(['hotel', 'identityDocument', 'signature', 'room', 'roomType'])
            ->where('hotel_id', $hotel->id)
            ->findOrFail($id);
        
        $formConfig = new \App\Services\FormConfigService($hotel);
        
        return view('hotel.reservations.show', compact('reservation', 'formConfig'));
    }

    public function validateReservation($id)
    {
        $hotel = Auth::user()->hotel;
        
        $reservation = Reservation::where('hotel_id', $hotel->id)->findOrFail($id);
        
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
        
        // Marquer la chambre comme occupée si une chambre est assignée
        if ($reservation->room_id && $reservation->room) {
            $reservation->room->updateStatus('occupied');
        }
        
        // Envoyer un email de notification au client
        try {
            $clientEmail = $reservation->client_email;
            if ($clientEmail) {
                Mail::to($clientEmail)->send(new ReservationValidated($reservation));
                
                Log::info('Email de validation envoyé', [
                    'reservation_id' => $reservation->id,
                    'email' => $clientEmail,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi de l\'email de validation', [
                'reservation_id' => $reservation->id,
                'error' => $e->getMessage(),
            ]);
        }
        
        // Créer une notification pour tous les utilisateurs de l'hôtel
        try {
            $this->notificationService->notifyReservationValidated($reservation);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création de la notification', [
                'reservation_id' => $reservation->id,
                'error' => $e->getMessage(),
            ]);
        }
        
        // Logger l'activité critique
        \App\Models\ActivityLog::log(
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
        
        return redirect()->route('hotel.reservations.index')->with('success', 'Réservation validée avec succès. Un email de confirmation a été envoyé au client.');
    }

    public function reject(Request $request, $id)
    {
        $hotel = Auth::user()->hotel;
        
        $reservation = Reservation::where('hotel_id', $hotel->id)->findOrFail($id);
        
        // Valider la transition vers rejected
        $validation = $this->statusService->validateTransition($reservation, 'rejected');
        
        if (!$validation['allowed']) {
            return back()->withErrors(['error' => $validation['message']]);
        }
        
        $reason = $request->input('reason', null);
        
        $reservation->update([
            'status' => 'rejected',
            'validated_by' => Auth::id(),
        ]);
        
        // Libérer la chambre si elle était réservée (retour à available)
        if ($reservation->room_id && $reservation->room) {
            $reservation->room->updateStatus('available');
        }
        
        // Envoyer un email de notification au client
        try {
            $clientEmail = $reservation->client_email;
            if ($clientEmail) {
                Mail::to($clientEmail)->send(new ReservationRejected($reservation, $reason));
                
                Log::info('Email de rejet envoyé', [
                    'reservation_id' => $reservation->id,
                    'email' => $clientEmail,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi de l\'email de rejet', [
                'reservation_id' => $reservation->id,
                'error' => $e->getMessage(),
            ]);
        }
        
        // Logger l'activité critique
        \App\Models\ActivityLog::log(
            'Réservation rejetée - N°' . str_pad($reservation->id, 7, '0', STR_PAD_LEFT),
            $reservation,
            [
                'action_type' => 'reservation_rejected',
                'reservation_id' => $reservation->id,
                'client_name' => $reservation->client_full_name,
                'hotel_name' => $reservation->hotel->name ?? null,
                'reason' => $reason,
            ],
            'reservation',
            'updated'
        );
        
        return redirect()->route('hotel.reservations.index')->with('success', 'Réservation rejetée. Un email a été envoyé au client.');
    }

    public function edit($id)
    {
        $hotel = Auth::user()->hotel;
        
        $reservation = Reservation::where('hotel_id', $hotel->id)->findOrFail($id);
        
        $roomTypes = $hotel->roomTypes()
            ->where('is_available', true)
            ->with(['rooms' => function($query) {
                $query->where('status', 'available')
                      ->orderBy('room_number')
                      ->orderBy('number');
            }])
            ->get();
        
        $rooms = $hotel->rooms()
            ->where('status', 'available')
            ->with('roomType')
            ->orderBy('room_number')
            ->orderBy('number')
            ->get();
        
        $reservation->load(['hotel', 'identityDocument', 'signature', 'room', 'roomType']);
        
        return view('hotel.reservations.edit', compact('reservation', 'hotel', 'roomTypes', 'rooms'));
    }

    public function update(Request $request, $id)
    {
        $hotel = Auth::user()->hotel;
        
        $reservation = Reservation::where('hotel_id', $hotel->id)->findOrFail($id);
        
        // Validation des données
        $validated = $request->validate([
            // Type de réservation
            'type_reservation' => 'required|in:Individuel,Groupe',
            'nom_groupe' => 'required_if:type_reservation,groupe|nullable|string|max:255',
            'code_groupe' => 'required_if:type_reservation,groupe|nullable|string|max:100',
            
            // Informations personnelles
            'type_piece_identite' => 'required|string|max:50',
            'numero_piece_identite' => 'required|string|max:100',
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'sexe' => 'required|in:Masculin,Féminin',
            'date_naissance' => 'required|date|before:10 years ago',
            'lieu_naissance' => 'required|string|max:255',
            'nationalite' => 'required|string|max:255',
            
            // Coordonnées
            'adresse' => 'nullable|string|max:500',
            'telephone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'profession' => 'nullable|string|max:255',
            
            // Séjour
            'venant_de' => 'nullable|string|max:255',
            'date_arrivee' => 'required|date|after_or_equal:today',
            'heure_arrivee' => 'nullable|date_format:H:i',
            'date_depart' => 'required|date|after:date_arrivee',
            'nombre_nuits' => 'nullable|integer|min:1',
            'nombre_adultes' => 'required|integer|min:1|max:20',
            'nombre_enfants' => 'nullable|integer|min:0|max:20',
            'type_chambre' => 'required|string|max:255',
            'room_type_id' => 'nullable|exists:room_types,id',
            'room_id' => 'nullable|exists:rooms,id',
            'preferences' => 'nullable|string|max:1000',
        ]);
        
        // Préparer les données pour la mise à jour
        $reservationData = [
            'type_reservation' => $request->type_reservation,
            'nom_groupe' => $request->nom_groupe,
            'code_groupe' => $request->code_groupe,
            'type_piece_identite' => $request->type_piece_identite,
            'numero_piece_identite' => $request->numero_piece_identite,
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'sexe' => $request->sexe,
            'date_naissance' => $request->date_naissance,
            'lieu_naissance' => $request->lieu_naissance,
            'nationalite' => $request->nationalite,
            'adresse' => $request->adresse,
            'telephone' => $request->telephone,
            'email' => $request->email,
            'profession' => $request->profession,
            'venant_de' => $request->venant_de,
            'date_arrivee' => $request->date_arrivee,
            'heure_arrivee' => $request->heure_arrivee,
            'date_depart' => $request->date_depart,
            'nombre_nuits' => $request->nombre_nuits,
            'nombre_adultes' => $request->nombre_adultes,
            'nombre_enfants' => $request->nombre_enfants ?? 0,
            'type_chambre' => $request->type_chambre,
            'preferences' => $request->preferences,
        ];
        
        // Ajouter les accompagnants si présents
        if ($request->nombre_adultes >= 2) {
            $accompagnants = [];
            for ($i = 1; $i < $request->nombre_adultes; $i++) {
                if ($request->has("accompagnant_nom_$i") || $request->has("accompagnant_prenom_$i")) {
                    $accompagnants[] = [
                        'nom' => $request->input("accompagnant_nom_$i"),
                        'prenom' => $request->input("accompagnant_prenom_$i"),
                    ];
                }
            }
            $reservationData['accompagnants'] = $accompagnants;
        }
        
        // Mettre à jour la réservation
        $reservation->update([
            'data' => $reservationData,
            'group_code' => $request->type_reservation === 'groupe' ? $request->code_groupe : null,
            'room_type_id' => $request->room_type_id ?? null,
            'room_id' => $request->room_id ?? null,
            'check_in_date' => $request->date_arrivee,
            'check_out_date' => $request->date_depart,
        ]);
        
        Log::info('Réservation modifiée', [
            'reservation_id' => $reservation->id,
            'modified_by' => Auth::id(),
        ]);
        
        return redirect()->route('hotel.reservations.show', $reservation->id)
            ->with('success', 'Réservation modifiée avec succès');
    }
}
