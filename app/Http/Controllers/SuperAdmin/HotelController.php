<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\User;
use App\Models\FormField;
use App\Models\Reservation;
use App\Models\Signature;
use App\Models\IdentityDocument;
use App\Models\Printer;
use App\Models\Setting;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class HotelController extends Controller
{
    public function index(Request $request)
    {
        $query = Hotel::withCount(['users', 'reservations'])
            ->select('id', 'name', 'logo', 'address', 'city', 'country', 'primary_color', 'secondary_color');

        // Recherche
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('city', 'LIKE', "%{$search}%")
                  ->orWhere('country', 'LIKE', "%{$search}%")
                  ->orWhere('address', 'LIKE', "%{$search}%");
            });
        }

        // Tri
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        
        if ($sortBy === 'users_count') {
            $query->orderBy('users_count', $sortOrder);
        } elseif ($sortBy === 'reservations_count') {
            $query->orderBy('reservations_count', $sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        $hotels = $query->get();
        
        return view('super.hotels.index', compact('hotels'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|file|mimes:jpeg,jpg,png,svg|max:2048',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'primary_color' => 'nullable|string|max:7',
            'secondary_color' => 'nullable|string|max:7',
            'oracle_dsn' => 'nullable|string|max:255',
            'oracle_username' => 'nullable|string|max:100',
            'oracle_password' => 'nullable|string|max:100',
        ]);

        // Gérer l'upload du logo
        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('hotels/logos', 'public');
        }

        $hotel = Hotel::create($data);
        
        // Créer les types de chambre et leurs chambres
        if ($request->has('room_types')) {
            foreach ($request->room_types as $roomTypeData) {
                $roomType = $hotel->roomTypes()->create([
                    'name' => $roomTypeData['name'],
                    'price' => $roomTypeData['price'],
                    'description' => $roomTypeData['description'] ?? null,
                    'capacity' => $roomTypeData['capacity'] ?? null,
                    'is_available' => $roomTypeData['is_available'] ?? true,
                ]);
                
                // Créer les chambres pour ce type si spécifié
                if (isset($roomTypeData['rooms']) && is_array($roomTypeData['rooms'])) {
                    foreach ($roomTypeData['rooms'] as $roomData) {
                        if (!empty($roomData['number'])) {
                        $hotel->rooms()->create([
                            'room_type_id' => $roomType->id,
                                'room_number' => $roomData['number'],
                            'floor' => $roomData['floor'] ?? null,
                                'status' => $roomData['status'] ?? 'available',
                                'notes' => null,
                        ]);
                        }
                    }
                }
            }
        }
        
        // Créer un admin par défaut pour l'hôtel avec email unique
        $admin = User::create([
            'hotel_id' => $hotel->id,
            'name' => 'Admin ' . $hotel->name,
            'email' => 'admin.hotel' . $hotel->id . '@' . strtolower(str_replace(' ', '', $hotel->name)) . '.local',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('hotel-admin');
        
        // Initialiser les champs de formulaire prédéfinis
        $this->initializeFormFields($hotel);

        return redirect()->route('super.hotels.index')->with('success', 'Hôtel créé avec succès ! Le formulaire de pré-réservation a été automatiquement configuré.');
    }

    public function show(Hotel $hotel)
    {
        // Si c'est une requête AJAX, retourner JSON
        if (request()->wantsJson() || request()->ajax()) {
            // Nettoyer tout output buffer pour éviter les BOM
            if (ob_get_length()) ob_clean();
            return response()->json($hotel, 200, [], JSON_UNESCAPED_UNICODE);
        }
        
        $hotel->load(['users', 'reservations' => function($query) {
            $query->latest()->limit(10);
        }]);
        
        $qrUrl = route('public.form', $hotel);
        $qrSvg = QrCode::format('svg')->size(200)->generate($qrUrl);
        
        return view('super.hotels.show', compact('hotel', 'qrSvg', 'qrUrl'));
    }

    public function update(Request $request, Hotel $hotel)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|file|mimes:jpeg,jpg,png,svg|max:2048',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'primary_color' => 'nullable|string|max:7',
            'secondary_color' => 'nullable|string|max:7',
            'oracle_dsn' => 'nullable|string|max:255',
            'oracle_username' => 'nullable|string|max:100',
            'oracle_password' => 'nullable|string|max:100',
        ]);

        // Gérer l'upload du logo
        if ($request->hasFile('logo')) {
            // Supprimer l'ancien logo si existe
            if ($hotel->logo && Storage::disk('public')->exists($hotel->logo)) {
                Storage::disk('public')->delete($hotel->logo);
            }
            $data['logo'] = $request->file('logo')->store('hotels/logos', 'public');
        } else {
            // Si aucun nouveau logo n'est fourni, retirer le champ du tableau pour conserver l'ancien
            unset($data['logo']);
        }

        $hotel->update($data);
        
        // Gérer les types de chambre
        if ($request->has('room_types') && is_array($request->room_types)) {
            $existingIds = [];
            
            foreach ($request->room_types as $roomTypeData) {
                if (isset($roomTypeData['id']) && $roomTypeData['id']) {
                    // Mettre à jour un type existant
                    $roomType = $hotel->roomTypes()->find($roomTypeData['id']);
                    if ($roomType) {
                        $roomType->update([
                            'name' => $roomTypeData['name'],
                            'price' => $roomTypeData['price'],
                            'description' => $roomTypeData['description'] ?? null,
                            'capacity' => $roomTypeData['capacity'] ?? null,
                            'is_available' => $roomTypeData['is_available'] ?? true,
                        ]);
                        $existingIds[] = $roomType->id;
                        
                        // Gérer les chambres pour ce type
                        if (isset($roomTypeData['rooms']) && is_array($roomTypeData['rooms'])) {
                            $existingRoomIds = [];
                            
                            foreach ($roomTypeData['rooms'] as $roomData) {
                                if (isset($roomData['id']) && $roomData['id']) {
                                    // Mettre à jour une chambre existante
                                    $room = $roomType->rooms()->find($roomData['id']);
                                    if ($room) {
                                        $room->update([
                                            'room_number' => $roomData['room_number'],
                                            'floor' => $roomData['floor'] ?? null,
                                            'status' => $roomData['status'] ?? 'available',
                                        ]);
                                        $existingRoomIds[] = $room->id;
                                    }
                                } else {
                                    // Créer une nouvelle chambre
                                    $newRoom = $hotel->rooms()->create([
                                        'room_type_id' => $roomType->id,
                                        'room_number' => $roomData['room_number'],
                                        'floor' => $roomData['floor'] ?? null,
                                        'status' => $roomData['status'] ?? 'available',
                                        'notes' => null,
                                    ]);
                                    $existingRoomIds[] = $newRoom->id;
                                }
                            }
                            
                            // Supprimer les chambres de ce type qui ne sont plus dans la liste
                            if (!empty($existingRoomIds)) {
                                $roomType->rooms()->whereNotIn('id', $existingRoomIds)->delete();
                            } else {
                                $roomType->rooms()->delete();
                                }
                        } else {
                            // Si aucune chambre n'est spécifiée, supprimer toutes les chambres de ce type
                            $roomType->rooms()->delete();
                        }
                    }
                } else {
                    // Créer un nouveau type
                    $newRoomType = $hotel->roomTypes()->create([
                        'name' => $roomTypeData['name'],
                        'price' => $roomTypeData['price'],
                        'description' => $roomTypeData['description'] ?? null,
                        'capacity' => $roomTypeData['capacity'] ?? null,
                        'is_available' => $roomTypeData['is_available'] ?? true,
                    ]);
                    $existingIds[] = $newRoomType->id;
                    
                    // Créer les chambres pour ce nouveau type
                    if (isset($roomTypeData['rooms']) && is_array($roomTypeData['rooms'])) {
                        foreach ($roomTypeData['rooms'] as $roomData) {
                            $hotel->rooms()->create([
                                'room_type_id' => $newRoomType->id,
                                'room_number' => $roomData['room_number'],
                                'floor' => $roomData['floor'] ?? null,
                                'status' => $roomData['status'] ?? 'available',
                                'notes' => null,
                            ]);
                        }
                    }
                }
            }
            
            // Supprimer les types qui ne sont plus dans la liste
            if (!empty($existingIds)) {
                $hotel->roomTypes()->whereNotIn('id', $existingIds)->delete();
            } else {
                // Si plus aucun type, tout supprimer
                $hotel->roomTypes()->delete();
            }
        }
        // Note: Si room_types n'est pas dans la requête, on ne touche pas aux types existants
        
        // Redirection intelligente : retour à la page précédente si c'était show, sinon vers index
        $previousUrl = $request->headers->get('referer');
        if ($previousUrl && str_contains($previousUrl, "/super/hotels/{$hotel->id}")) {
            return redirect()->route('super.hotels.show', $hotel)->with('success', 'Hôtel mis à jour avec succès');
        }
        
        return redirect()->route('super.hotels.index')->with('success', 'Hôtel mis à jour avec succès');
    }

    public function destroy(Hotel $hotel)
    {
        $hotel->delete();
        return redirect()->route('super.hotels.index')->with('success', 'Hôtel supprimé');
    }
    
    /**
     * Récupérer les types de chambre d'un hôtel avec leurs chambres
     */
    public function getRoomTypes(Hotel $hotel)
    {
        // Nettoyer tout output buffer pour éviter les BOM
        if (ob_get_length()) ob_clean();
        $roomTypes = $hotel->roomTypes()->with('rooms')->get();
        return response()->json($roomTypes, 200, [], JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * Initialiser les champs de formulaire prédéfinis pour un hôtel
     */
    private function initializeFormFields(Hotel $hotel)
    {
        // Vérifier si l'hôtel a déjà des champs
        if ($hotel->formFields()->count() > 0) {
            return;
        }
        
        $fields = [
            // Type de réservation
            ['label' => 'Type de réservation', 'key' => 'type_reservation', 'type' => 'select', 'required' => true, 'position' => 1],
            
            // Informations personnelles  
            ['label' => 'Type de pièce d\'identité', 'key' => 'type_piece_identite', 'type' => 'select', 'required' => true, 'position' => 10],
            ['label' => 'Numéro de pièce d\'identité', 'key' => 'numero_piece_identite', 'type' => 'text', 'required' => true, 'position' => 11],
            ['label' => 'Nom de famille', 'key' => 'nom', 'type' => 'text', 'required' => true, 'position' => 12],
            ['label' => 'Prénom(s)', 'key' => 'prenom', 'type' => 'text', 'required' => true, 'position' => 13],
            ['label' => 'Date de naissance', 'key' => 'date_naissance', 'type' => 'date', 'required' => true, 'position' => 14],
            ['label' => 'Nationalité', 'key' => 'nationalite', 'type' => 'text', 'required' => true, 'position' => 15],
            
            // Coordonnées
            ['label' => 'Téléphone', 'key' => 'telephone', 'type' => 'tel', 'required' => true, 'position' => 20],
            ['label' => 'Email', 'key' => 'email', 'type' => 'email', 'required' => true, 'position' => 21],
            
            // Séjour
            ['label' => 'Date d\'arrivée', 'key' => 'date_arrivee', 'type' => 'date', 'required' => true, 'position' => 30],
            ['label' => 'Date de départ', 'key' => 'date_depart', 'type' => 'date', 'required' => true, 'position' => 31],
            ['label' => 'Nombre d\'adultes', 'key' => 'nombre_adultes', 'type' => 'number', 'required' => true, 'position' => 32],
            ['label' => 'Nombre d\'enfants', 'key' => 'nombre_enfants', 'type' => 'number', 'required' => false, 'position' => 33],
            ['label' => 'Type de chambre', 'key' => 'type_chambre', 'type' => 'select', 'required' => true, 'position' => 34],
        ];
        
        foreach ($fields as $field) {
            FormField::create([
                'hotel_id' => $hotel->id,
                'label' => $field['label'],
                'key' => $field['key'],
                'type' => $field['type'],
                'required' => $field['required'],
                'position' => $field['position'],
                'active' => true,
                'options' => null,
            ]);
        }
    }

    /**
     * Supprimer plusieurs hôtels
     */
    public function destroyMultiple(Request $request)
    {
        $request->validate([
            'hotel_ids' => 'required|array',
            'hotel_ids.*' => 'exists:hotels,id',
        ]);

        try {
            DB::beginTransaction();
            
            $hotelIds = $request->hotel_ids;
            $deleted = 0;

            foreach ($hotelIds as $hotelId) {
                try {
                    $hotel = Hotel::findOrFail($hotelId);
                    
                    // Récupérer les IDs des réservations avant de les supprimer
                    $reservationIds = Reservation::withoutGlobalScopes()->where('hotel_id', $hotel->id)->pluck('id')->toArray();
                    
                    // Supprimer les signatures et documents d'identité
                    if (!empty($reservationIds)) {
                        Signature::whereIn('reservation_id', $reservationIds)->delete();
                        IdentityDocument::whereIn('reservation_id', $reservationIds)->delete();
                    }
                    
                    // Supprimer les réservations
                    Reservation::withoutGlobalScopes()->where('hotel_id', $hotel->id)->delete();
                    
                    // Supprimer les autres données associées
                    $hotel->rooms()->delete();
                    $hotel->roomTypes()->delete();
                    $hotel->formFields()->delete();
                    
                    // Supprimer les clients
                    \App\Models\Client::where('hotel_id', $hotel->id)->delete();
                    
                    // Supprimer les imprimantes
                    Printer::where('hotel_id', $hotel->id)->delete();
                    
                    // Supprimer les paramètres
                    Setting::where('hotel_id', $hotel->id)->delete();
                    
                    // Supprimer les logs
                    \App\Models\PrintLog::where('hotel_id', $hotel->id)->delete();
                    \App\Models\ActivityLog::where('hotel_id', $hotel->id)->delete();
                    
                    // Supprimer les utilisateurs (sauf super-admin)
                    $hotel->users()->whereDoesntHave('roles', function($q) {
                        $q->where('name', 'super-admin');
                    })->delete();
                    
                    // Supprimer l'hôtel
                    $hotel->delete();
                    $deleted++;
                } catch (\Exception $e) {
                    \Log::error('Erreur lors de la suppression de l\'hôtel ID: ' . $hotelId, [
                        'error' => $e->getMessage(),
                    ]);
                    // Continuer avec les autres hôtels même en cas d'erreur
                }
            }

            DB::commit();

            return redirect()->route('super.hotels.index')
                ->with('success', "✅ {$deleted} hôtel(s) supprimé(s) avec succès.");
        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Erreur lors de la suppression multiple des hôtels', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()
                ->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }
}

