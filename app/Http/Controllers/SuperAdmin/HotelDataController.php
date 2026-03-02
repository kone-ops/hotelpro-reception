<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\Reservation;
use App\Models\Setting;
use App\Models\User;
use App\Models\IdentityDocument;
use App\Models\Signature;
use App\Models\ActivityLog;
use App\Models\RoomType;
use App\Models\Room;
use App\Models\RoomStateHistory;
use App\Models\Client;
use App\Modules\Housekeeping\Models\HousekeepingTask;
use App\Modules\Laundry\Models\ClientLinen;
use App\Modules\Laundry\Models\LaundryCollection;
use App\Modules\Laundry\Models\LaundryCollectionLine;
use App\Modules\Laundry\Models\LaundryItemType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class HotelDataController extends Controller
{
    /**
     * Afficher la page de gestion des données
     */
    public function index()
    {
        $hotels = Hotel::withCount([
            'reservations',
            'users'
        ])->get();

        return view('super.hotel-data.index', compact('hotels'));
    }

    /**
     * Afficher les détails des données d'un hôtel
     */
    public function show(Hotel $hotel)
    {
        $stats = [
            'reservations' => Reservation::where('hotel_id', $hotel->id)->count(),
            'reservations_pending' => Reservation::where('hotel_id', $hotel->id)->where('status', 'pending')->count(),
            'reservations_validated' => Reservation::where('hotel_id', $hotel->id)->where('status', 'validated')->count(),
            'reservations_checked_in' => Reservation::where('hotel_id', $hotel->id)->where('status', 'checked_in')->count(),
            'reservations_checked_out' => Reservation::where('hotel_id', $hotel->id)->where('status', 'checked_out')->count(),
            'reservations_rejected' => Reservation::where('hotel_id', $hotel->id)->where('status', 'rejected')->count(),
            'users' => User::where('hotel_id', $hotel->id)->count(),
        ];

        // Stats optionnelles (tables qui peuvent ne pas exister)
        try {
            $stats['room_types'] = RoomType::where('hotel_id', $hotel->id)->count();
        } catch (\Exception $e) {
            $stats['room_types'] = 0;
        }

        try {
            $stats['rooms'] = Room::where('hotel_id', $hotel->id)->count();
        } catch (\Exception $e) {
            $stats['rooms'] = 0;
        }

        try {
            $stats['printers'] = DB::table('printers')->where('hotel_id', $hotel->id)->count();
        } catch (\Exception $e) {
            $stats['printers'] = 0;
        }

        try {
            $stats['settings'] = Setting::where('hotel_id', $hotel->id)->count();
        } catch (\Exception $e) {
            $stats['settings'] = 0;
        }

        try {
            $stats['print_logs'] = DB::table('print_logs')->where('hotel_id', $hotel->id)->count();
        } catch (\Exception $e) {
            $stats['print_logs'] = 0;
        }

        try {
            $stats['activity_logs'] = ActivityLog::where('hotel_id', $hotel->id)->count();
        } catch (\Exception $e) {
            $stats['activity_logs'] = 0;
        }

        try {
            $stats['identity_documents'] = IdentityDocument::whereHas('reservation', function($q) use ($hotel) {
                $q->where('hotel_id', $hotel->id);
            })->count();
        } catch (\Exception $e) {
            $stats['identity_documents'] = 0;
        }

        try {
            $stats['signatures'] = Signature::whereHas('reservation', function($q) use ($hotel) {
                $q->where('hotel_id', $hotel->id);
            })->count();
        } catch (\Exception $e) {
            $stats['signatures'] = 0;
        }

        try {
            $stats['housekeeping_tasks'] = HousekeepingTask::where('hotel_id', $hotel->id)->count();
        } catch (\Exception $e) {
            $stats['housekeeping_tasks'] = 0;
        }

        try {
            $roomIds = Room::where('hotel_id', $hotel->id)->pluck('id');
            $stats['room_state_history'] = $roomIds->isEmpty() ? 0 : RoomStateHistory::whereIn('room_id', $roomIds)->count();
        } catch (\Exception $e) {
            $stats['room_state_history'] = 0;
        }

        try {
            $stats['laundry_collections'] = LaundryCollection::where('hotel_id', $hotel->id)->count();
        } catch (\Exception $e) {
            $stats['laundry_collections'] = 0;
        }

        try {
            $stats['laundry_item_types'] = LaundryItemType::where('hotel_id', $hotel->id)->count();
        } catch (\Exception $e) {
            $stats['laundry_item_types'] = 0;
        }

        try {
            $stats['client_linen'] = ClientLinen::where('hotel_id', $hotel->id)->count();
        } catch (\Exception $e) {
            $stats['client_linen'] = 0;
        }

        // Charger les données détaillées pour l'affichage
        $users = User::where('hotel_id', $hotel->id)
            ->with('roles')
            ->latest()
            ->limit(5)
            ->get();

        $Reservations = Reservation::where('hotel_id', $hotel->id)
            ->latest()
            ->limit(5)
            ->get();

        $reservations = Reservation::where('hotel_id', $hotel->id)
            ->latest()
            ->limit(5)
            ->get();

        try {
            $printers = DB::table('printers')->where('hotel_id', $hotel->id)
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();
        } catch (\Exception $e) {
            $printers = collect();
        }

        try {
            $settings = Setting::where('hotel_id', $hotel->id)
                ->latest()
                ->limit(10)
                ->get();
        } catch (\Exception $e) {
            $settings = collect();
        }

        // Alias pour la compatibilité avec la vue
        $recentReservations = $reservations;

        $hasPrintersModule = Schema::hasTable('printers');

        return view('super.hotel-data.show', compact('hotel', 'stats', 'users', 'Reservations', 'reservations', 'recentReservations', 'printers', 'settings', 'hasPrintersModule'));
    }

    /**
     * Réinitialiser les données transactionnelles d'un hôtel (conserve config)
     */
    public function reset(Request $request, Hotel $hotel)
    {
        $request->validate([
            'confirmation' => 'required|string|in:RESET',
        ]);

        try {
            DB::beginTransaction();

            Log::warning('[HotelDataController] Réinitialisation des données transactionnelles de l\'hôtel', [
                'hotel_id' => $hotel->id,
                'hotel_name' => $hotel->name,
                'user_id' => auth()->id(),
            ]);

            // Récupérer les IDs des réservations pour supprimer les données liées
            $reservationIds = Reservation::where('hotel_id', $hotel->id)->pluck('id');
            
            // 1. Supprimer les signatures
            try {
                $signaturesCount = Signature::whereIn('reservation_id', $reservationIds)->delete();
            } catch (\Exception $e) {
                $signaturesCount = 0;
            }
            
            // 2. Supprimer les documents d'identité
            try {
                $documentsCount = IdentityDocument::whereIn('reservation_id', $reservationIds)->delete();
            } catch (\Exception $e) {
                $documentsCount = 0;
            }
            
            // 3. Supprimer les réservations
            $reservationsCount = Reservation::where('hotel_id', $hotel->id)->delete();
            
            // 4. Supprimer les tâches housekeeping, buanderie et l'historique des états des chambres
            $housekeepingCount = 0;
            $roomStateHistoryCount = 0;
            $laundryCollectionsCount = 0;
            try {
                $housekeepingCount = HousekeepingTask::where('hotel_id', $hotel->id)->delete();
            } catch (\Exception $e) {}
            try {
                $roomIds = Room::where('hotel_id', $hotel->id)->pluck('id');
                $roomStateHistoryCount = $roomIds->isEmpty() ? 0 : RoomStateHistory::whereIn('room_id', $roomIds)->delete();
            } catch (\Exception $e) {}
            try {
                $laundryCollectionsCount = LaundryCollection::where('hotel_id', $hotel->id)->delete();
            } catch (\Exception $e) {}
            try {
                ClientLinen::where('hotel_id', $hotel->id)->delete();
            } catch (\Exception $e) {}
            try {
                LaundryItemType::where('hotel_id', $hotel->id)->delete();
            } catch (\Exception $e) {}
            
            // 5. Réinitialiser les états des chambres (occupation, nettoyage, technique)
            try {
                Room::where('hotel_id', $hotel->id)->update([
                    'occupation_state' => 'free',
                    'cleaning_state' => 'none',
                    'technical_state' => 'normal',
                    'status' => 'available',
                ]);
            } catch (\Exception $e) {}
            
            // 6. Supprimer les logs
            try {
                $printLogsCount = DB::table('print_logs')->where('hotel_id', $hotel->id)->delete();
            } catch (\Exception $e) {
                $printLogsCount = 0;
            }
            
            try {
                $activityLogsCount = ActivityLog::where('hotel_id', $hotel->id)->delete();
            } catch (\Exception $e) {
                $activityLogsCount = 0;
            }

            // Note: On conserve les imprimantes, paramètres et utilisateurs

            DB::commit();

            // Log de l'action (dans une nouvelle transaction)
            try {
                ActivityLog::create([
                    'hotel_id' => $hotel->id,
                    'user_id' => auth()->id(),
                    'action' => 'hotel_data_reset',
                    'description' => "Réinitialisation des données transactionnelles de l'hôtel {$hotel->name}",
                    'metadata' => [
                        'reservations_deleted' => $reservationsCount,
                        'documents_deleted' => $documentsCount,
                        'signatures_deleted' => $signaturesCount,
                        'housekeeping_tasks_deleted' => $housekeepingCount,
                        'room_state_history_deleted' => $roomStateHistoryCount,
                        'laundry_collections_deleted' => $laundryCollectionsCount,
                        'print_logs_deleted' => $printLogsCount,
                        'activity_logs_deleted' => $activityLogsCount,
                    ]
                ]);
            } catch (\Exception $e) {}

            $totalDeleted = $reservationsCount + $documentsCount + $signaturesCount + $housekeepingCount + $roomStateHistoryCount + $laundryCollectionsCount + $printLogsCount + $activityLogsCount;

            return redirect()->route('super.hotel-data.show', $hotel)
                ->with('success', "✅ Réinitialisation réussie ! Données transactionnelles supprimées : {$reservationsCount} réservations, {$documentsCount} documents, {$signaturesCount} signatures, {$housekeepingCount} tâches étages, {$roomStateHistoryCount} historiques d'états, {$laundryCollectionsCount} collectes buanderie, {$printLogsCount} logs d'impression, {$activityLogsCount} logs d'activité. Total : {$totalDeleted} éléments. ✅ Configuration conservée : types de chambres, chambres, utilisateurs, imprimantes, paramètres.");

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('[HotelDataController] Erreur lors de la réinitialisation', [
                'hotel_id' => $hotel->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', '❌ Erreur lors de la réinitialisation : ' . $e->getMessage());
        }
    }

    /**
     * Exporter TOUTES les données d'un hôtel (comme la purge)
     */
    public function export(Hotel $hotel)
    {
        try {
            // Nettoyer le buffer pour éviter les BOM
            if (ob_get_length()) ob_clean();
            
            $data = [
                'hotel' => $hotel->toArray(),
                'export_date' => now()->toDateTimeString(),
                'version' => '2.0',
                'description' => 'Export complet de toutes les donnees de l\'hotel',
                'data' => [],
                'stats' => []
            ];

            // 1. Réservations SANS les signatures binaires (pour éviter les problèmes JSON)
            try {
                $reservations = Reservation::where('hotel_id', $hotel->id)
                    ->with(['identityDocument'])
                    ->get()
                    ->map(function($reservation) {
                        $array = $reservation->toArray();
                        
                        // Nettoyer les relations
                        unset($array['room'], $array['roomType'], $array['hotel'], $array['signature']);
                        
                        // Nettoyer le champ data s'il existe
                        if (isset($array['data']) && is_array($array['data'])) {
                            // S'assurer que toutes les valeurs sont UTF-8 valides
                            $array['data'] = array_map(function($value) {
                                if (is_string($value)) {
                                    // Nettoyer et valider UTF-8
                                    $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
                                    return $value;
                                }
                                return $value;
                            }, $array['data']);
                        }
                        
                        return $array;
                    })
                    ->toArray();
                $data['data']['reservations'] = $reservations;
                $data['stats']['reservations_exported'] = count($reservations);
            } catch (\Exception $e) {
                $data['data']['reservations'] = [];
                $data['stats']['reservations_exported'] = 0;
                Log::warning('Erreur export reservations: ' . $e->getMessage());
            }

            // 2. Types de chambres
            try {
                $roomTypes = RoomType::where('hotel_id', $hotel->id)->get()->toArray();
                $data['data']['room_types'] = $roomTypes;
                $data['stats']['room_types_exported'] = count($roomTypes);
            } catch (\Exception $e) {
                $data['data']['room_types'] = [];
                $data['stats']['room_types_exported'] = 0;
            }

            // 3. Chambres
            try {
                $rooms = Room::where('hotel_id', $hotel->id)->get()->toArray();
                $data['data']['rooms'] = $rooms;
                $data['stats']['rooms_exported'] = count($rooms);
            } catch (\Exception $e) {
                $data['data']['rooms'] = [];
                $data['stats']['rooms_exported'] = 0;
            }

            // 4. Utilisateurs avec leurs rôles (sans mots de passe)
            try {
                $users = User::where('hotel_id', $hotel->id)
                    ->with('roles')
                    ->get()
                    ->makeHidden(['password', 'remember_token', 'hotel'])
                    ->toArray();
                $data['data']['users'] = $users;
                $data['stats']['users_exported'] = count($users);
            } catch (\Exception $e) {
                $data['data']['users'] = [];
                $data['stats']['users_exported'] = 0;
            }

            // 5. Imprimantes
            try {
                $printers = DB::table('printers')->where('hotel_id', $hotel->id)->get()->map(fn ($p) => (array) $p)->toArray();
                $data['data']['printers'] = $printers;
                $data['stats']['printers_exported'] = count($printers);
            } catch (\Exception $e) {
                $data['data']['printers'] = [];
                $data['stats']['printers_exported'] = 0;
            }

            // 6. Paramètres
            try {
                $settings = Setting::where('hotel_id', $hotel->id)->get()->toArray();
                $data['data']['settings'] = $settings;
                $data['stats']['settings_exported'] = count($settings);
            } catch (\Exception $e) {
                $data['data']['settings'] = [];
                $data['stats']['settings_exported'] = 0;
            }

            // 7. Champs de formulaire
            try {
                $formFields = DB::table('form_fields')->where('hotel_id', $hotel->id)->get()->toArray();
                $data['data']['form_fields'] = json_decode(json_encode($formFields), true);
                $data['stats']['form_fields_exported'] = count($formFields);
            } catch (\Exception $e) {
                $data['data']['form_fields'] = [];
                $data['stats']['form_fields_exported'] = 0;
            }

            // 8. Tâches housekeeping (service des étages)
            try {
                $housekeepingTasks = HousekeepingTask::where('hotel_id', $hotel->id)->get()->toArray();
                $data['data']['housekeeping_tasks'] = $housekeepingTasks;
                $data['stats']['housekeeping_tasks_exported'] = count($housekeepingTasks);
            } catch (\Exception $e) {
                $data['data']['housekeeping_tasks'] = [];
                $data['stats']['housekeeping_tasks_exported'] = 0;
            }

            // 9. Historique des états des chambres
            try {
                $roomIds = Room::where('hotel_id', $hotel->id)->pluck('id');
                $roomStateHistory = $roomIds->isEmpty() ? [] : RoomStateHistory::whereIn('room_id', $roomIds)->get()->toArray();
                $data['data']['room_state_history'] = $roomStateHistory;
                $data['stats']['room_state_history_exported'] = count($roomStateHistory);
            } catch (\Exception $e) {
                $data['data']['room_state_history'] = [];
                $data['stats']['room_state_history_exported'] = 0;
            }

            // 10. Buanderie : types de linge + collectes + lignes (via relations)
            try {
                $laundryItemTypes = LaundryItemType::where('hotel_id', $hotel->id)->get()->toArray();
                $data['data']['laundry_item_types'] = $laundryItemTypes;
                $data['stats']['laundry_item_types_exported'] = count($laundryItemTypes);
            } catch (\Exception $e) {
                $data['data']['laundry_item_types'] = [];
                $data['stats']['laundry_item_types_exported'] = 0;
            }
            try {
                $laundryCollections = LaundryCollection::where('hotel_id', $hotel->id)->with('lines')->get()->toArray();
                $data['data']['laundry_collections'] = $laundryCollections;
                $data['stats']['laundry_collections_exported'] = count($laundryCollections);
            } catch (\Exception $e) {
                $data['data']['laundry_collections'] = [];
                $data['stats']['laundry_collections_exported'] = 0;
            }
            try {
                $clientLinen = ClientLinen::where('hotel_id', $hotel->id)->get()->toArray();
                $data['data']['client_linen'] = $clientLinen;
                $data['stats']['client_linen_exported'] = count($clientLinen);
            } catch (\Exception $e) {
                $data['data']['client_linen'] = [];
                $data['stats']['client_linen_exported'] = 0;
            }

            $filename = 'hotel_' . $hotel->id . '_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $hotel->name) . '_' . now()->format('Y-m-d_His') . '.json';
            
            // Encoder en JSON avec options pour éviter les erreurs
            $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR);
            
            if ($json === false) {
                // Essayer sans PRETTY_PRINT si ça échoue
                $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                
                if ($json === false) {
                    throw new \Exception('Erreur d\'encodage JSON : ' . json_last_error_msg());
                }
            }
            
            Log::info('[HotelDataController] Export créé', [
                'hotel_id' => $hotel->id,
                'filename' => $filename,
                'size' => strlen($json),
                'stats' => $data['stats']
            ]);

            // Log de l'action
            try {
                ActivityLog::create([
                    'hotel_id' => $hotel->id,
                    'user_id' => auth()->id(),
                    'action' => 'hotel_data_export_complete',
                    'description' => "Export complet des donnees de l'hotel {$hotel->name}",
                    'metadata' => [
                        'filename' => $filename,
                        'stats' => $data['stats']
                    ]
                ]);
            } catch (\Exception $e) {}

            return response($json, 200)
                ->header('Content-Type', 'application/json; charset=UTF-8')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');

        } catch (\Exception $e) {
            Log::error('[HotelDataController] Erreur lors de l\'export', [
                'hotel_id' => $hotel->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Erreur lors de l\'export : ' . $e->getMessage());
        }
    }

    /**
     * Importer TOUTES les données pour un hôtel
     */
    public function import(Request $request, Hotel $hotel)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:json,txt|max:20480',
        ]);

        try {
            // Lire le contenu du fichier
            $filePath = $request->file('import_file')->getRealPath();
            $content = file_get_contents($filePath);
            
            // Nettoyer les BOM et espaces blancs
            $content = trim($content);
            $content = preg_replace('/^\xEF\xBB\xBF/', '', $content); // Supprimer UTF-8 BOM
            
            // Décoder le JSON
            $data = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Erreur de décodage JSON : ' . json_last_error_msg() . ' - Vérifiez que le fichier est un JSON valide exporté depuis cette application.');
            }

            if (!$data || !isset($data['data'])) {
                throw new \Exception('Format de fichier invalide. Le fichier doit contenir une clé "data". Veuillez utiliser un fichier exporté depuis cette application.');
            }
            
            Log::info('[HotelDataController] Import démarré', [
                'hotel_id' => $hotel->id,
                'file_name' => $request->file('import_file')->getClientOriginalName(),
                'file_size' => $request->file('import_file')->getSize(),
                'version' => $data['version'] ?? 'unknown'
            ]);

            DB::beginTransaction();

            $imported = [];
            $errors = [];
            $oldToNewRoomTypeIds = [];
            $oldToNewRoomIds = [];

            // 1. Importer les types de chambres EN PREMIER
            if (isset($data['data']['room_types']) && is_array($data['data']['room_types']) && count($data['data']['room_types']) > 0) {
                foreach ($data['data']['room_types'] as $roomType) {
                    try {
                        $oldId = $roomType['id'] ?? null;
                        unset($roomType['id'], $roomType['created_at'], $roomType['updated_at']);
                        $roomType['hotel_id'] = $hotel->id;
                        
                        $newRoomType = RoomType::create($roomType);
                        if ($oldId) {
                            $oldToNewRoomTypeIds[$oldId] = $newRoomType->id;
                        }
                        $imported['room_types'] = ($imported['room_types'] ?? 0) + 1;
                    } catch (\Exception $e) {
                        $errors[] = "Type de chambre: " . $e->getMessage();
                    }
                }
            }

            // 2. Importer les chambres (avec mapping ancien ID -> nouveau ID pour housekeeping / room_state_history)
            if (isset($data['data']['rooms']) && is_array($data['data']['rooms']) && count($data['data']['rooms']) > 0) {
                foreach ($data['data']['rooms'] as $room) {
                    try {
                        $oldRoomId = $room['id'] ?? null;
                        $oldRoomTypeId = $room['room_type_id'] ?? null;
                        unset($room['id'], $room['created_at'], $room['updated_at']);
                        $room['hotel_id'] = $hotel->id;
                    
                        // Mapper l'ancien ID au nouveau ID
                        if ($oldRoomTypeId && isset($oldToNewRoomTypeIds[$oldRoomTypeId])) {
                            $room['room_type_id'] = $oldToNewRoomTypeIds[$oldRoomTypeId];
                        }
                        
                        $newRoom = Room::create($room);
                        if ($oldRoomId !== null) {
                            $oldToNewRoomIds[$oldRoomId] = $newRoom->id;
                        }
                        $imported['rooms'] = ($imported['rooms'] ?? 0) + 1;
                    } catch (\Exception $e) {
                        $errors[] = "Chambre: " . $e->getMessage();
                    }
                }
            }

            // 3. Importer les utilisateurs avec leurs rôles
            if (isset($data['data']['users']) && is_array($data['data']['users']) && count($data['data']['users']) > 0) {
                foreach ($data['data']['users'] as $user) {
                        try {
                        $roles = $user['roles'] ?? [];
                        unset($user['id'], $user['roles'], $user['created_at'], $user['updated_at'], $user['email_verified_at']);
                        $user['hotel_id'] = $hotel->id;
                        
                        // Générer un nouveau mot de passe par défaut
                        $user['password'] = Hash::make('password123');
                        
                        // Ajouter un suffixe à l'email pour éviter les doublons
                        $user['email'] = $user['email'] . '.imported_' . now()->timestamp;
                        
                        $newUser = User::create($user);
                        
                        // Assigner les rôles
                        if (is_array($roles) && count($roles) > 0) {
                            foreach ($roles as $role) {
                                if (isset($role['name'])) {
                                    try {
                                        $newUser->assignRole($role['name']);
                        } catch (\Exception $e) {}
                    }
                            }
                        }
                        
                        $imported['users'] = ($imported['users'] ?? 0) + 1;
                    } catch (\Exception $e) {
                        $errors[] = "Utilisateur: " . $e->getMessage();
                    }
                }
            }

            // 4. Importer les réservations avec documents (sans signatures binaires)
            if (isset($data['data']['reservations']) && is_array($data['data']['reservations']) && count($data['data']['reservations']) > 0) {
                foreach ($data['data']['reservations'] as $reservation) {
                    try {
                        $identityDoc = $reservation['identity_document'] ?? null;
                        
                        // Nettoyer les champs
                        unset(
                            $reservation['id'], 
                            $reservation['identity_document'], 
                            $reservation['signature'], 
                            $reservation['created_at'], 
                            $reservation['updated_at'], 
                            $reservation['room'], 
                            $reservation['room_type']
                        );
                        
                        $reservation['hotel_id'] = $hotel->id;
                    
                        $newReservation = Reservation::create($reservation);
                    
                    // Importer le document d'identité
                        if ($identityDoc && is_array($identityDoc)) {
                        try {
                            unset($identityDoc['id'], $identityDoc['created_at'], $identityDoc['updated_at']);
                                $identityDoc['reservation_id'] = $newReservation->id;
                            IdentityDocument::create($identityDoc);
                            } catch (\Exception $e) {
                                $errors[] = "Document d'identité: " . $e->getMessage();
                            }
                    }
                    
                        // Note: Les signatures ne sont pas importées car elles contiennent des données binaires
                        
                        $imported['reservations'] = ($imported['reservations'] ?? 0) + 1;
                    } catch (\Exception $e) {
                        $errors[] = "Réservation: " . $e->getMessage();
                        Log::warning("Détails erreur réservation: " . $e->getMessage());
                    }
                }
            }

            // 5. Importer les imprimantes
            if (isset($data['data']['printers']) && is_array($data['data']['printers']) && count($data['data']['printers']) > 0) {
                    foreach ($data['data']['printers'] as $printer) {
                    try {
                        unset($printer['id'], $printer['created_at'], $printer['updated_at']);
                        $printer['hotel_id'] = $hotel->id;
                        $printer['created_at'] = now();
                        $printer['updated_at'] = now();
                        DB::table('printers')->insert($printer);
                        $imported['printers'] = ($imported['printers'] ?? 0) + 1;
                    } catch (\Exception $e) {
                        $errors[] = "Imprimante: " . $e->getMessage();
                    }
                }
            }

            // 6. Importer les paramètres
            if (isset($data['data']['settings']) && is_array($data['data']['settings']) && count($data['data']['settings']) > 0) {
                foreach ($data['data']['settings'] as $setting) {
                    try {
                        unset($setting['id'], $setting['created_at'], $setting['updated_at']);
                        $setting['hotel_id'] = $hotel->id;
                        
                        Setting::create($setting);
                        $imported['settings'] = ($imported['settings'] ?? 0) + 1;
                    } catch (\Exception $e) {
                        $errors[] = "Paramètre: " . $e->getMessage();
                    }
                }
            }

            // 7. Importer les champs de formulaire
            if (isset($data['data']['form_fields']) && is_array($data['data']['form_fields']) && count($data['data']['form_fields']) > 0) {
                foreach ($data['data']['form_fields'] as $field) {
                    try {
                        $fieldArray = (array) $field;
                        unset($fieldArray['id'], $fieldArray['created_at'], $fieldArray['updated_at']);
                        $fieldArray['hotel_id'] = $hotel->id;
                        
                        DB::table('form_fields')->insert($fieldArray);
                        $imported['form_fields'] = ($imported['form_fields'] ?? 0) + 1;
                    } catch (\Exception $e) {
                        $errors[] = "Champ formulaire: " . $e->getMessage();
                    }
                }
            }

            // 8. Importer l'historique des états des chambres (mapping room_id)
            if (isset($data['data']['room_state_history']) && is_array($data['data']['room_state_history']) && count($data['data']['room_state_history']) > 0) {
                foreach ($data['data']['room_state_history'] as $row) {
                    try {
                        $oldRoomId = $row['room_id'] ?? null;
                        if ($oldRoomId === null || !isset($oldToNewRoomIds[$oldRoomId])) {
                            continue;
                        }
                        unset($row['id'], $row['created_at'], $row['updated_at']);
                        $row['room_id'] = $oldToNewRoomIds[$oldRoomId];
                        $row['changed_by'] = null; // pas de mapping utilisateur à l'import
                        RoomStateHistory::create($row);
                        $imported['room_state_history'] = ($imported['room_state_history'] ?? 0) + 1;
                    } catch (\Exception $e) {
                        $errors[] = "Historique état chambre: " . $e->getMessage();
                    }
                }
            }

            // 9. Importer les tâches housekeeping (mapping room_id, assigned_to = null)
            if (isset($data['data']['housekeeping_tasks']) && is_array($data['data']['housekeeping_tasks']) && count($data['data']['housekeeping_tasks']) > 0) {
                foreach ($data['data']['housekeeping_tasks'] as $task) {
                    try {
                        $oldRoomId = $task['room_id'] ?? null;
                        if ($oldRoomId === null || !isset($oldToNewRoomIds[$oldRoomId])) {
                            continue;
                        }
                        unset($task['id'], $task['created_at'], $task['updated_at']);
                        $task['hotel_id'] = $hotel->id;
                        $task['room_id'] = $oldToNewRoomIds[$oldRoomId];
                        $task['assigned_to'] = null;
                        HousekeepingTask::create($task);
                        $imported['housekeeping_tasks'] = ($imported['housekeeping_tasks'] ?? 0) + 1;
                    } catch (\Exception $e) {
                        $errors[] = "Tâche étages: " . $e->getMessage();
                    }
                }
            }

            $oldToNewLaundryItemTypeIds = [];
            // 10. Importer les types de linge (buanderie)
            if (isset($data['data']['laundry_item_types']) && is_array($data['data']['laundry_item_types']) && count($data['data']['laundry_item_types']) > 0) {
                foreach ($data['data']['laundry_item_types'] as $item) {
                    try {
                        $oldId = $item['id'] ?? null;
                        unset($item['id'], $item['created_at'], $item['updated_at']);
                        $item['hotel_id'] = $hotel->id;
                        $newItem = LaundryItemType::create($item);
                        if ($oldId !== null) {
                            $oldToNewLaundryItemTypeIds[$oldId] = $newItem->id;
                        }
                        $imported['laundry_item_types'] = ($imported['laundry_item_types'] ?? 0) + 1;
                    } catch (\Exception $e) {
                        $errors[] = "Type linge: " . $e->getMessage();
                    }
                }
            }

            // 11. Importer les collectes buanderie + lignes (mapping room_id, laundry_item_type_id)
            if (isset($data['data']['laundry_collections']) && is_array($data['data']['laundry_collections']) && !empty($oldToNewRoomIds)) {
                foreach ($data['data']['laundry_collections'] as $coll) {
                    try {
                        $oldRoomId = $coll['room_id'] ?? null;
                        if ($oldRoomId === null || !isset($oldToNewRoomIds[$oldRoomId])) {
                            continue;
                        }
                        $lines = $coll['lines'] ?? [];
                        unset($coll['id'], $coll['created_at'], $coll['updated_at'], $coll['lines']);
                        $coll['hotel_id'] = $hotel->id;
                        $coll['room_id'] = $oldToNewRoomIds[$oldRoomId];
                        $coll['collected_by'] = null;
                        $coll['housekeeping_task_id'] = null;
                        $newCollection = LaundryCollection::create($coll);
                        foreach ($lines as $line) {
                            $oldTypeId = $line['laundry_item_type_id'] ?? null;
                            if ($oldTypeId !== null && isset($oldToNewLaundryItemTypeIds[$oldTypeId])) {
                                LaundryCollectionLine::create([
                                    'laundry_collection_id' => $newCollection->id,
                                    'laundry_item_type_id' => $oldToNewLaundryItemTypeIds[$oldTypeId],
                                    'quantity' => (int) ($line['quantity'] ?? 0),
                                ]);
                            }
                        }
                        $imported['laundry_collections'] = ($imported['laundry_collections'] ?? 0) + 1;
                    } catch (\Exception $e) {
                        $errors[] = "Collecte buanderie: " . $e->getMessage();
                    }
                }
            }

            if (isset($data['data']['client_linen']) && is_array($data['data']['client_linen'])) {
                foreach ($data['data']['client_linen'] as $row) {
                    try {
                        $oldRoomId = $row['room_id'] ?? null;
                        $newRoomId = isset($oldRoomId, $oldToNewRoomIds[$oldRoomId]) ? $oldToNewRoomIds[$oldRoomId] : null;
                        ClientLinen::create([
                            'hotel_id' => $hotel->id,
                            'source' => $row['source'] ?? ClientLinen::SOURCE_RECEPTION,
                            'room_id' => $newRoomId,
                            'reservation_id' => null,
                            'housekeeping_task_id' => null,
                            'received_at' => $row['received_at'] ?? now(),
                            'received_by' => null,
                            'status' => $row['status'] ?? ClientLinen::STATUS_PENDING_PICKUP,
                            'description' => $row['description'] ?? null,
                            'notes' => $row['notes'] ?? null,
                            'client_name' => $row['client_name'] ?? null,
                            'picked_up_at' => $row['picked_up_at'] ?? null,
                            'picked_up_by' => null,
                        ]);
                        $imported['client_linen'] = ($imported['client_linen'] ?? 0) + 1;
                    } catch (\Exception $e) {
                        $errors[] = "Linge client: " . $e->getMessage();
                    }
                }
            }

            DB::commit();

            $totalImported = array_sum($imported);

            // Log de l'action
            try {
                ActivityLog::create([
                    'hotel_id' => $hotel->id,
                    'user_id' => auth()->id(),
                    'action' => 'hotel_data_import_complete',
                    'description' => "Import complet de données pour l'hôtel {$hotel->name}",
                    'metadata' => [
                        'imported' => $imported,
                        'total' => $totalImported,
                        'errors_count' => count($errors)
                    ]
                ]);
            } catch (\Exception $e) {}

            $message = sprintf(
                '✅ Import réussi ! %d éléments importés : %d types de chambres, %d chambres, %d utilisateurs, %d réservations, %d imprimantes, %d paramètres, %d champs de formulaire%s%s',
                $totalImported,
                $imported['room_types'] ?? 0,
                $imported['rooms'] ?? 0,
                $imported['users'] ?? 0,
                $imported['reservations'] ?? 0,
                $imported['printers'] ?? 0,
                $imported['settings'] ?? 0,
                $imported['form_fields'] ?? 0,
                isset($imported['room_state_history']) ? ', ' . $imported['room_state_history'] . ' historiques d\'états' : '',
                isset($imported['housekeeping_tasks']) ? ', ' . $imported['housekeeping_tasks'] . ' tâches étages' : '',
                (isset($imported['laundry_item_types']) || isset($imported['laundry_collections'])) ? ', ' . ($imported['laundry_item_types'] ?? 0) . ' types linge, ' . ($imported['laundry_collections'] ?? 0) . ' collectes buanderie' : '',
                isset($imported['client_linen']) ? ', ' . $imported['client_linen'] . ' linge client' : ''
            );

            if (count($errors) > 0) {
                $message .= sprintf(' ⚠️ %d avertissements (voir les logs).', count($errors));
            }

            return redirect()->route('super.hotel-data.show', $hotel)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('[HotelDataController] Erreur lors de l\'import', [
                'hotel_id' => $hotel->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file_info' => [
                    'name' => $request->file('import_file') ? $request->file('import_file')->getClientOriginalName() : 'N/A',
                    'size' => $request->file('import_file') ? $request->file('import_file')->getSize() : 0,
                ]
            ]);

            $errorMessage = $e->getMessage();
            
            // Message d'aide selon le type d'erreur
            if (strpos($errorMessage, 'JSON') !== false) {
                $errorMessage .= ' Assurez-vous d\'utiliser un fichier exporté depuis cette application. Ne modifiez pas le fichier JSON manuellement.';
            }

            return redirect()->back()
                ->with('error', '❌ Erreur lors de l\'import : ' . $errorMessage);
        }
    }

    /**
     * Purger les anciennes données (soft clean)
     */
    /**
     * Purge complète - Supprime ABSOLUMENT TOUTES les données de l'hôtel (TRÈS DANGEREUX)
     */
    public function purge(Request $request, Hotel $hotel)
    {
        $request->validate([
            'confirmation' => 'required|string|in:PURGE',
            'password' => 'required|string',
        ]);

        // Vérifier le mot de passe de l'utilisateur connecté
        if (!Hash::check($request->password, auth()->user()->password)) {
            return redirect()->back()
                ->with('error', '❌ Mot de passe incorrect. Action annulée.');
        }

        try {
            DB::beginTransaction();

            Log::critical('[HotelDataController] PURGE COMPLÈTE de l\'hôtel - SUPPRESSION TOTALE', [
                'hotel_id' => $hotel->id,
                'hotel_name' => $hotel->name,
                'user_id' => auth()->id(),
            ]);

            // Récupérer les IDs des réservations AVANT de les supprimer
            $reservationIds = Reservation::withoutGlobalScopes()->where('hotel_id', $hotel->id)->pluck('id')->toArray();
            
            $deleted = [];

            // 1. Supprimer les signatures (seulement s'il y a des réservations)
            try {
                if (!empty($reservationIds)) {
                    $deleted['signatures'] = Signature::whereIn('reservation_id', $reservationIds)->delete();
                } else {
                    $deleted['signatures'] = 0;
                }
            } catch (\Exception $e) {
                $deleted['signatures'] = 0;
                Log::warning('[HotelDataController] Erreur suppression signatures', ['error' => $e->getMessage()]);
            }
            
            // 2. Supprimer les documents d'identité (seulement s'il y a des réservations)
            try {
                if (!empty($reservationIds)) {
                    $deleted['identity_documents'] = IdentityDocument::whereIn('reservation_id', $reservationIds)->delete();
                } else {
                    $deleted['identity_documents'] = 0;
                }
            } catch (\Exception $e) {
                $deleted['identity_documents'] = 0;
                Log::warning('[HotelDataController] Erreur suppression documents', ['error' => $e->getMessage()]);
            }
            
            // 3. Supprimer TOUTES les réservations (sans les scopes globaux)
            try {
                $deleted['reservations'] = Reservation::withoutGlobalScopes()->where('hotel_id', $hotel->id)->delete();
            } catch (\Exception $e) {
                $deleted['reservations'] = 0;
                Log::warning('[HotelDataController] Erreur suppression réservations', ['error' => $e->getMessage()]);
            }
            
            // 4. Supprimer les tâches housekeeping et l'historique des états des chambres (avant les chambres)
            try {
                $deleted['housekeeping_tasks'] = HousekeepingTask::where('hotel_id', $hotel->id)->delete();
            } catch (\Exception $e) {
                $deleted['housekeeping_tasks'] = 0;
            }
            try {
                $roomIds = Room::where('hotel_id', $hotel->id)->pluck('id')->toArray();
                $deleted['room_state_history'] = empty($roomIds) ? 0 : RoomStateHistory::whereIn('room_id', $roomIds)->delete();
            } catch (\Exception $e) {
                $deleted['room_state_history'] = 0;
            }
            try {
                $deleted['laundry_collections'] = LaundryCollection::where('hotel_id', $hotel->id)->delete();
            } catch (\Exception $e) {
                $deleted['laundry_collections'] = 0;
            }
            try {
                $deleted['client_linen'] = ClientLinen::where('hotel_id', $hotel->id)->delete();
            } catch (\Exception $e) {
                $deleted['client_linen'] = 0;
            }
            try {
                $deleted['laundry_item_types'] = LaundryItemType::where('hotel_id', $hotel->id)->delete();
            } catch (\Exception $e) {
                $deleted['laundry_item_types'] = 0;
            }
            
            // 5. Supprimer TOUTES les chambres
            try {
                $deleted['rooms'] = Room::where('hotel_id', $hotel->id)->delete();
            } catch (\Exception $e) {
                $deleted['rooms'] = 0;
            }
            
            // 6. Supprimer TOUS les types de chambres
            try {
                $deleted['room_types'] = RoomType::where('hotel_id', $hotel->id)->delete();
            } catch (\Exception $e) {
                $deleted['room_types'] = 0;
            }
            
            // 6. Supprimer les imprimantes
            try {
                $deleted['printers'] = DB::table('printers')->where('hotel_id', $hotel->id)->delete();
            } catch (\Exception $e) {
                $deleted['printers'] = 0;
            }

            // 8. Supprimer les paramètres
            try {
                $deleted['settings'] = Setting::where('hotel_id', $hotel->id)->delete();
            } catch (\Exception $e) {
                $deleted['settings'] = 0;
            }
            
            // 9. Supprimer les champs de formulaire
            try {
                $deleted['form_fields'] = DB::table('form_fields')->where('hotel_id', $hotel->id)->delete();
            } catch (\Exception $e) {
                $deleted['form_fields'] = 0;
            }
            
            // 10. Supprimer les clients de l'hôtel
            try {
                $deleted['clients'] = Client::where('hotel_id', $hotel->id)->delete();
            } catch (\Exception $e) {
                $deleted['clients'] = 0;
                Log::warning('[HotelDataController] Erreur suppression clients', ['error' => $e->getMessage()]);
            }
            
            // 11. Supprimer TOUS les utilisateurs (sauf super-admin)
            try {
                $deleted['users'] = User::where('hotel_id', $hotel->id)
                    ->whereDoesntHave('roles', function($q) {
                        $q->where('name', 'super-admin');
                    })
                    ->delete();
            } catch (\Exception $e) {
                $deleted['users'] = 0;
                Log::warning('[HotelDataController] Erreur suppression utilisateurs', ['error' => $e->getMessage()]);
            }
            
            // 12. Supprimer les logs d'impression
            try {
                $deleted['print_logs'] = DB::table('print_logs')->where('hotel_id', $hotel->id)->delete();
            } catch (\Exception $e) {
                $deleted['print_logs'] = 0;
                Log::warning('[HotelDataController] Erreur suppression print_logs', ['error' => $e->getMessage()]);
            }

            // 12. Supprimer les logs d'activité
            try {
                $deleted['activity_logs'] = ActivityLog::where('hotel_id', $hotel->id)->delete();
            } catch (\Exception $e) {
                $deleted['activity_logs'] = 0;
                Log::warning('[HotelDataController] Erreur suppression activity_logs', ['error' => $e->getMessage()]);
            }

            // 14. Nettoyer le cache
            try {
                Cache::forget('hotel.' . $hotel->id . '.*');
                Cache::flush(); // Nettoyer tout le cache pour être sûr
            } catch (\Exception $e) {
                Log::warning('[HotelDataController] Erreur nettoyage cache', ['error' => $e->getMessage()]);
            }

            DB::commit();

            $totalDeleted = array_sum(array_filter($deleted, 'is_numeric'));

            // Log de l'action (dans une nouvelle transaction)
            try {
                ActivityLog::create([
                    'hotel_id' => $hotel->id,
                    'user_id' => auth()->id(),
                    'action' => 'hotel_data_purge_complete',
                    'description' => "PURGE COMPLÈTE de l'hôtel {$hotel->name} - Toutes les données supprimées",
                    'metadata' => $deleted
                ]);
            } catch (\Exception $e) {}

            // Construire un message de résumé
            $summary = [];
            if (isset($deleted['reservations'])) $summary[] = "{$deleted['reservations']} réservations";
            if (isset($deleted['signatures'])) $summary[] = "{$deleted['signatures']} signatures";
            if (isset($deleted['identity_documents'])) $summary[] = "{$deleted['identity_documents']} documents d'identité";
            if (isset($deleted['housekeeping_tasks'])) $summary[] = "{$deleted['housekeeping_tasks']} tâches étages";
            if (isset($deleted['room_state_history'])) $summary[] = "{$deleted['room_state_history']} historiques d'états";
            if (isset($deleted['laundry_collections'])) $summary[] = "{$deleted['laundry_collections']} collectes buanderie";
            if (isset($deleted['client_linen'])) $summary[] = "{$deleted['client_linen']} linge client";
            if (isset($deleted['laundry_item_types'])) $summary[] = "{$deleted['laundry_item_types']} types de linge";
            if (isset($deleted['rooms'])) $summary[] = "{$deleted['rooms']} chambres";
            if (isset($deleted['room_types'])) $summary[] = "{$deleted['room_types']} types de chambres";
            if (isset($deleted['clients'])) $summary[] = "{$deleted['clients']} clients";
            if (isset($deleted['users'])) $summary[] = "{$deleted['users']} utilisateurs";
            if (isset($deleted['printers'])) $summary[] = "{$deleted['printers']} imprimantes";
            if (isset($deleted['settings'])) $summary[] = "{$deleted['settings']} paramètres";
            if (isset($deleted['form_fields'])) $summary[] = "{$deleted['form_fields']} champs de formulaire";
            if (isset($deleted['print_logs'])) $summary[] = "{$deleted['print_logs']} logs d'impression";
            if (isset($deleted['activity_logs'])) $summary[] = "{$deleted['activity_logs']} logs d'activité";
            
            $summaryText = !empty($summary) ? implode(', ', $summary) : "0 éléments";
            
            return redirect()->route('super.hotel-data.show', $hotel)
                ->with('success', "⚠️ Purge complète effectuée ! TOUTES les données supprimées : {$summaryText}. Total : {$totalDeleted} éléments.");

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('[HotelDataController] Erreur lors de la purge complète', [
                'hotel_id' => $hotel->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', '❌ Erreur lors de la purge : ' . $e->getMessage());
        }
    }
}
