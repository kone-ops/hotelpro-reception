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
use App\Models\Group;
use App\Models\Client;
use App\Models\FormField;
use App\Modules\Housekeeping\Models\HousekeepingTask;
use App\Modules\Laundry\Models\LaundryCollection;
use App\Modules\Laundry\Models\LaundryCollectionLine;
use App\Modules\Laundry\Models\LaundryItemType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class DatabaseController extends Controller
{
    /**
     * Afficher la page de gestion globale de la base de données
     */
    public function index()
    {
        // Statistiques globales (module imprimantes optionnel)
        $stats = [
            'total_hotels' => Hotel::count(),
            'total_users' => User::count(),
            'total_reservations' => Reservation::count(),
            'total_rooms' => Room::count(),
            'total_room_types' => RoomType::count(),
            'total_settings' => Setting::count(),
            'total_groups' => Group::count(),
            'total_clients' => Client::count(),
        ];
        try {
            $stats['total_printers'] = \Illuminate\Support\Facades\Schema::hasTable('printers')
                ? DB::table('printers')->count()
                : 0;
        } catch (\Throwable $e) {
            $stats['total_printers'] = 0;
        }
        $hasPrintersModule = ($stats['total_printers'] ?? 0) > 0 || \Illuminate\Support\Facades\Schema::hasTable('printers');
        try {
            $stats['total_housekeeping_tasks'] = HousekeepingTask::count();
            $stats['total_room_state_history'] = RoomStateHistory::count();
        } catch (\Exception $e) {
            $stats['total_housekeeping_tasks'] = 0;
            $stats['total_room_state_history'] = 0;
        }
        try {
            $stats['total_laundry_collections'] = LaundryCollection::count();
            $stats['total_laundry_item_types'] = LaundryItemType::count();
        } catch (\Exception $e) {
            $stats['total_laundry_collections'] = 0;
            $stats['total_laundry_item_types'] = 0;
        }

        // Compter les super-admins
        $superAdmins = User::role('super-admin')->get();
        $stats['total_super_admins'] = $superAdmins->count();
        $stats['super_admins'] = $superAdmins;

        return view('super.database.index', compact('stats', 'hasPrintersModule'));
    }

    /**
     * Exporter TOUTES les données de la base (sauvegarde complète)
     */
    public function exportGlobal()
    {
        try {
            if (ob_get_length()) ob_clean();
            
            $data = [
                'export_date' => now()->toDateTimeString(),
                'version' => '2.0',
                'description' => 'Export complet de toutes les donnees de la base de donnees',
                'data' => [],
                'stats' => []
            ];

            // 1. Tous les hôtels
            try {
                $hotels = Hotel::all()->toArray();
                $data['data']['hotels'] = $hotels;
                $data['stats']['hotels_exported'] = count($hotels);
            } catch (\Exception $e) {
                $data['data']['hotels'] = [];
                $data['stats']['hotels_exported'] = 0;
            }

            // 2. Toutes les réservations (sans signatures binaires)
            try {
                $reservations = Reservation::with(['identityDocument'])
                    ->get()
                    ->map(function($reservation) {
                        $array = $reservation->toArray();
                        unset($array['room'], $array['roomType'], $array['hotel'], $array['signature']);
                        return $array;
                    })
                    ->toArray();
                $data['data']['reservations'] = $reservations;
                $data['stats']['reservations_exported'] = count($reservations);
            } catch (\Exception $e) {
                $data['data']['reservations'] = [];
                $data['stats']['reservations_exported'] = 0;
            }

            // 3. Tous les types de chambres
            try {
                $roomTypes = RoomType::all()->toArray();
                $data['data']['room_types'] = $roomTypes;
                $data['stats']['room_types_exported'] = count($roomTypes);
            } catch (\Exception $e) {
                $data['data']['room_types'] = [];
                $data['stats']['room_types_exported'] = 0;
            }

            // 4. Toutes les chambres
            try {
                $rooms = Room::all()->toArray();
                $data['data']['rooms'] = $rooms;
                $data['stats']['rooms_exported'] = count($rooms);
            } catch (\Exception $e) {
                $data['data']['rooms'] = [];
                $data['stats']['rooms_exported'] = 0;
            }

            // 5. Tous les utilisateurs (sans mots de passe)
            try {
                $users = User::with('roles')
                    ->get()
                    ->makeHidden(['password', 'remember_token'])
                    ->toArray();
                $data['data']['users'] = $users;
                $data['stats']['users_exported'] = count($users);
            } catch (\Exception $e) {
                $data['data']['users'] = [];
                $data['stats']['users_exported'] = 0;
            }

            // 6. Toutes les imprimantes
            try {
                $printers = DB::table('printers')->get()->map(fn ($p) => (array) $p)->toArray();
                $data['data']['printers'] = $printers;
                $data['stats']['printers_exported'] = count($printers);
            } catch (\Exception $e) {
                $data['data']['printers'] = [];
                $data['stats']['printers_exported'] = 0;
            }

            // 7. Tous les paramètres
            try {
                $settings = Setting::all()->toArray();
                $data['data']['settings'] = $settings;
                $data['stats']['settings_exported'] = count($settings);
            } catch (\Exception $e) {
                $data['data']['settings'] = [];
                $data['stats']['settings_exported'] = 0;
            }

            // 8. Tous les groupes
            try {
                $groups = Group::all()->toArray();
                $data['data']['groups'] = $groups;
                $data['stats']['groups_exported'] = count($groups);
            } catch (\Exception $e) {
                $data['data']['groups'] = [];
                $data['stats']['groups_exported'] = 0;
            }

            // 9. Tous les clients
            try {
                $clients = Client::all()->toArray();
                $data['data']['clients'] = $clients;
                $data['stats']['clients_exported'] = count($clients);
            } catch (\Exception $e) {
                $data['data']['clients'] = [];
                $data['stats']['clients_exported'] = 0;
            }

            // 10. Tâches housekeeping (service des étages)
            try {
                $housekeepingTasks = HousekeepingTask::all()->toArray();
                $data['data']['housekeeping_tasks'] = $housekeepingTasks;
                $data['stats']['housekeeping_tasks_exported'] = count($housekeepingTasks);
            } catch (\Exception $e) {
                $data['data']['housekeeping_tasks'] = [];
                $data['stats']['housekeeping_tasks_exported'] = 0;
            }

            // 11. Historique des états des chambres
            try {
                $roomStateHistory = RoomStateHistory::all()->toArray();
                $data['data']['room_state_history'] = $roomStateHistory;
                $data['stats']['room_state_history_exported'] = count($roomStateHistory);
            } catch (\Exception $e) {
                $data['data']['room_state_history'] = [];
                $data['stats']['room_state_history_exported'] = 0;
            }

            // 12. Buanderie : types de linge
            try {
                $laundryItemTypes = LaundryItemType::all()->toArray();
                $data['data']['laundry_item_types'] = $laundryItemTypes;
                $data['stats']['laundry_item_types_exported'] = count($laundryItemTypes);
            } catch (\Exception $e) {
                $data['data']['laundry_item_types'] = [];
                $data['stats']['laundry_item_types_exported'] = 0;
            }

            // 13. Buanderie : collectes avec lignes
            try {
                $laundryCollections = LaundryCollection::with('lines')->get()->toArray();
                $data['data']['laundry_collections'] = $laundryCollections;
                $data['stats']['laundry_collections_exported'] = count($laundryCollections);
            } catch (\Exception $e) {
                $data['data']['laundry_collections'] = [];
                $data['stats']['laundry_collections_exported'] = 0;
            }

            $filename = 'global_backup_' . now()->format('Y-m-d_His') . '.json';
            
            $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR);
            
            if ($json === false) {
                $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                if ($json === false) {
                    throw new \Exception('Erreur d\'encodage JSON : ' . json_last_error_msg());
                }
            }
            
            Log::info('[DatabaseController] Export global créé', [
                'filename' => $filename,
                'size' => strlen($json),
                'stats' => $data['stats']
            ]);

            return response($json, 200)
                ->header('Content-Type', 'application/json; charset=UTF-8')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');

        } catch (\Exception $e) {
            Log::error('[DatabaseController] Erreur lors de l\'export global', [
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Erreur lors de l\'export : ' . $e->getMessage());
        }
    }

    /**
     * Purge GLOBALE - Supprime TOUTES les données sauf les super-admins
     * TRÈS DANGEREUX - Double confirmation requise
     */
    public function purgeGlobal(Request $request)
    {
        $request->validate([
            'confirmation' => 'required|string|in:PURGE_GLOBAL',
            'password' => 'required|string',
        ]);

        // Vérifier le mot de passe
        if (!Hash::check($request->password, auth()->user()->password)) {
            return redirect()->back()
                ->with('error', '❌ Mot de passe incorrect. Action annulée.');
        }

        // Vérifier que l'utilisateur est super-admin
        if (!auth()->user()->hasRole('super-admin')) {
            return redirect()->back()
                ->with('error', '❌ Accès refusé. Seuls les super-admins peuvent effectuer cette action.');
        }

        try {
            // Export automatique avant purge
            try {
                $this->exportGlobal();
                Log::info('[DatabaseController] Export automatique créé avant purge globale');
            } catch (\Exception $e) {
                Log::warning('[DatabaseController] Échec export automatique avant purge', [
                    'error' => $e->getMessage()
                ]);
            }

            Log::critical('[DatabaseController] PURGE GLOBALE - SUPPRESSION TOTALE DE LA BASE', [
                'user_id' => auth()->id(),
                'user_email' => auth()->user()->email,
            ]);

            // Récupérer les IDs des super-admins AVANT de commencer la transaction
            $superAdminIds = [];
            try {
                $superAdminIds = DB::table('model_has_roles')
                    ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                    ->where('roles.name', 'super-admin')
                    ->where('model_has_roles.model_type', 'App\Models\User')
                    ->pluck('model_has_roles.model_id')
                    ->toArray();
                
                if (empty($superAdminIds)) {
                    // Si aucun super-admin trouvé, on ne continue pas pour sécurité
                    Log::warning('[DatabaseController] Aucun super-admin trouvé, purge annulée pour sécurité');
                    return redirect()->back()
                        ->with('error', '❌ Aucun super-admin trouvé. La purge globale a été annulée pour des raisons de sécurité.');
                }
            } catch (\Exception $e) {
                Log::error('[DatabaseController] Erreur lors de la récupération des super-admins', ['error' => $e->getMessage()]);
                return redirect()->back()
                    ->with('error', '❌ Erreur lors de la vérification des super-admins : ' . $e->getMessage());
            }

            // Désactiver temporairement les contraintes de clés étrangères
            $driver = DB::connection()->getDriverName();
            if ($driver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = OFF');
            } else {
                DB::statement('SET FOREIGN_KEY_CHECKS=0');
            }

            DB::beginTransaction();

            $deleted = [];

            // Liste de toutes les tables à vider (ordre : tables dépendantes avant rooms)
            $tablesToTruncate = [
                'signatures',
                'identity_documents',
                'reservations',
                'housekeeping_tasks',   // FK room_id, hotel_id
                'room_state_history',   // FK room_id
                'laundry_collection_lines', // FK laundry_collection_id, laundry_item_type_id
                'laundry_collections',   // FK room_id, hotel_id
                'laundry_item_types',    // FK hotel_id
                'rooms',
                'room_types',
                'printers',
                'settings',
                'form_fields',
                'groups',
                'clients',
                'hotels',
                'print_logs',
                'activity_logs',
            ];

            // Supprimer toutes les données des tables listées
            foreach ($tablesToTruncate as $table) {
                try {
                    $count = DB::table($table)->count();
                    DB::table($table)->delete();
                    $deleted[$table] = $count;
                } catch (\Exception $e) {
                    $deleted[$table] = 0;
                    Log::warning("[DatabaseController] Erreur suppression table {$table}", ['error' => $e->getMessage()]);
                }
            }

            // Supprimer les utilisateurs SAUF les super-admins
            try {
                $deleted['users'] = DB::table('users')
                    ->whereNotIn('id', $superAdminIds)
                    ->delete();
            } catch (\Exception $e) {
                $deleted['users'] = 0;
                Log::warning('[DatabaseController] Erreur suppression users', ['error' => $e->getMessage()]);
            }

            // Nettoyer les tables de relations (model_has_roles, etc.) pour les utilisateurs supprimés
            try {
                $deleted['model_has_roles'] = DB::table('model_has_roles')
                    ->where('model_type', 'App\Models\User')
                    ->whereNotIn('model_id', $superAdminIds)
                    ->delete();
            } catch (\Exception $e) {
                $deleted['model_has_roles'] = 0;
                Log::warning('[DatabaseController] Erreur nettoyage model_has_roles', ['error' => $e->getMessage()]);
            }

            // Supprimer les sessions (sauf celles des super-admins)
            try {
                $deleted['sessions'] = DB::table('sessions')
                    ->where(function($query) use ($superAdminIds) {
                        $query->whereNull('user_id')
                              ->orWhereNotIn('user_id', $superAdminIds);
                    })
                    ->delete();
            } catch (\Exception $e) {
                $deleted['sessions'] = 0;
                Log::warning('[DatabaseController] Erreur suppression sessions', ['error' => $e->getMessage()]);
            }

            // Nettoyer tout le cache
            Cache::flush();

            DB::commit();

            // Réactiver les contraintes de clés étrangères
            if ($driver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = ON');
            } else {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            }

            $totalDeleted = array_sum(array_filter($deleted, 'is_numeric'));

            Log::critical('[DatabaseController] PURGE GLOBALE TERMINÉE', [
                'user_id' => auth()->id(),
                'deleted' => $deleted,
                'total' => $totalDeleted
            ]);

            return redirect()->route('super.database.index')
                ->with('success', "⚠️ PURGE GLOBALE EFFECTUÉE ! Toutes les données supprimées sauf les comptes super-admin. Total : {$totalDeleted} éléments supprimés. Un export automatique a été créé avant la purge.");

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('[DatabaseController] Erreur lors de la purge globale', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', '❌ Erreur lors de la purge globale : ' . $e->getMessage());
        }
    }

    /**
     * Importer des données depuis un fichier JSON
     */
    public function importGlobal(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:json|max:102400', // 100MB max
        ]);

        // Vérifier que l'utilisateur est super-admin
        if (!auth()->user()->hasRole('super-admin')) {
            return redirect()->back()
                ->with('error', '❌ Accès refusé. Seuls les super-admins peuvent effectuer cette action.');
        }

        try {
            $file = $request->file('import_file');
            $content = file_get_contents($file->getRealPath());
            $data = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Fichier JSON invalide : ' . json_last_error_msg());
            }

            if (!isset($data['data']) || !is_array($data['data'])) {
                throw new \Exception('Format de fichier invalide. Le fichier doit contenir une clé "data" avec les données à importer.');
            }

            // Créer un export de sauvegarde avant l'import
            try {
                $this->exportGlobal();
                Log::info('[DatabaseController] Sauvegarde automatique créée avant import global');
            } catch (\Exception $e) {
                Log::warning('[DatabaseController] Échec sauvegarde automatique avant import', [
                    'error' => $e->getMessage()
                ]);
            }

            DB::beginTransaction();

            Log::critical('[DatabaseController] IMPORT GLOBAL - Import de données', [
                'user_id' => auth()->id(),
                'user_email' => auth()->user()->email,
                'file_size' => $file->getSize(),
            ]);

            $imported = [];
            $oldToNewHotelIds = [];
            $oldToNewRoomTypeIds = [];
            $oldToNewRoomIds = [];
            $oldToNewLaundryItemTypeIds = [];

            // 1. Importer les hôtels (avec mapping ancien ID -> nouveau ID)
            if (isset($data['data']['hotels']) && is_array($data['data']['hotels'])) {
                try {
                    $imported['hotels'] = 0;
                    foreach ($data['data']['hotels'] as $hotelData) {
                        $oldId = $hotelData['id'] ?? null;
                        unset($hotelData['id']);
                        $newHotel = Hotel::create($hotelData);
                        if ($oldId !== null) {
                            $oldToNewHotelIds[$oldId] = $newHotel->id;
                        }
                        $imported['hotels']++;
                    }
                } catch (\Exception $e) {
                    $imported['hotels'] = 0;
                    Log::warning('[DatabaseController] Erreur import hôtels', ['error' => $e->getMessage()]);
                }
            }

            // 2. Importer les utilisateurs (sans mots de passe)
            if (isset($data['data']['users']) && is_array($data['data']['users'])) {
                try {
                    $imported['users'] = 0;
                    foreach ($data['data']['users'] as $userData) {
                        // Ne pas importer les super-admins
                        if (isset($userData['roles']) && is_array($userData['roles'])) {
                            $hasSuperAdminRole = false;
                            foreach ($userData['roles'] as $role) {
                                if (is_array($role) && isset($role['name']) && $role['name'] === 'super-admin') {
                                    $hasSuperAdminRole = true;
                                    break;
                                } elseif (is_string($role) && $role === 'super-admin') {
                                    $hasSuperAdminRole = true;
                                    break;
                                }
                            }
                            if ($hasSuperAdminRole) {
                                continue; // Ignorer les super-admins
                            }
                        }
                        
                        $oldHotelId = $userData['hotel_id'] ?? null;
                        unset($userData['id'], $userData['password'], $userData['remember_token']);
                        if ($oldHotelId !== null && isset($oldToNewHotelIds[$oldHotelId])) {
                            $userData['hotel_id'] = $oldToNewHotelIds[$oldHotelId];
                        }
                        if (!isset($userData['password'])) {
                            $userData['password'] = Hash::make('temp_password_change_me_' . time());
                        }
                        User::create($userData);
                        $imported['users']++;
                    }
                } catch (\Exception $e) {
                    $imported['users'] = 0;
                    Log::warning('[DatabaseController] Erreur import utilisateurs', ['error' => $e->getMessage()]);
                }
            }

            // 3. Importer les types de chambres (avec mapping hotel_id et ancien ID -> nouveau ID)
            if (isset($data['data']['room_types']) && is_array($data['data']['room_types'])) {
                try {
                    $imported['room_types'] = 0;
                    foreach ($data['data']['room_types'] as $roomTypeData) {
                        $oldId = $roomTypeData['id'] ?? null;
                        $oldHotelId = $roomTypeData['hotel_id'] ?? null;
                        unset($roomTypeData['id']);
                        if ($oldHotelId !== null && isset($oldToNewHotelIds[$oldHotelId])) {
                            $roomTypeData['hotel_id'] = $oldToNewHotelIds[$oldHotelId];
                        }
                        $newRoomType = RoomType::create($roomTypeData);
                        if ($oldId !== null) {
                            $oldToNewRoomTypeIds[$oldId] = $newRoomType->id;
                        }
                        $imported['room_types']++;
                    }
                } catch (\Exception $e) {
                    $imported['room_types'] = 0;
                    Log::warning('[DatabaseController] Erreur import types de chambres', ['error' => $e->getMessage()]);
                }
            }

            // 4. Importer les chambres (avec mapping room_type_id, hotel_id et ancien ID -> nouveau ID)
            if (isset($data['data']['rooms']) && is_array($data['data']['rooms'])) {
                try {
                    $imported['rooms'] = 0;
                    foreach ($data['data']['rooms'] as $roomData) {
                        $oldId = $roomData['id'] ?? null;
                        $oldHotelId = $roomData['hotel_id'] ?? null;
                        $oldRoomTypeId = $roomData['room_type_id'] ?? null;
                        unset($roomData['id']);
                        if ($oldHotelId !== null && isset($oldToNewHotelIds[$oldHotelId])) {
                            $roomData['hotel_id'] = $oldToNewHotelIds[$oldHotelId];
                        }
                        if ($oldRoomTypeId !== null && isset($oldToNewRoomTypeIds[$oldRoomTypeId])) {
                            $roomData['room_type_id'] = $oldToNewRoomTypeIds[$oldRoomTypeId];
                        }
                        $newRoom = Room::create($roomData);
                        if ($oldId !== null) {
                            $oldToNewRoomIds[$oldId] = $newRoom->id;
                        }
                        $imported['rooms']++;
                    }
                } catch (\Exception $e) {
                    $imported['rooms'] = 0;
                    Log::warning('[DatabaseController] Erreur import chambres', ['error' => $e->getMessage()]);
                }
            }

            // 5. Importer les réservations
            if (isset($data['data']['reservations']) && is_array($data['data']['reservations'])) {
                try {
                    $imported['reservations'] = 0;
                    foreach ($data['data']['reservations'] as $reservationData) {
                        unset($reservationData['id']);
                        Reservation::create($reservationData);
                        $imported['reservations']++;
                    }
                } catch (\Exception $e) {
                    $imported['reservations'] = 0;
                    Log::warning('[DatabaseController] Erreur import réservations', ['error' => $e->getMessage()]);
                }
            }

            // 6. Importer les clients
            if (isset($data['data']['clients']) && is_array($data['data']['clients'])) {
                try {
                    $imported['clients'] = 0;
                    foreach ($data['data']['clients'] as $clientData) {
                        unset($clientData['id']);
                        Client::create($clientData);
                        $imported['clients']++;
                    }
                } catch (\Exception $e) {
                    $imported['clients'] = 0;
                    Log::warning('[DatabaseController] Erreur import clients', ['error' => $e->getMessage()]);
                }
            }

            // 7. Importer les groupes
            if (isset($data['data']['groups']) && is_array($data['data']['groups'])) {
                try {
                    $imported['groups'] = 0;
                    foreach ($data['data']['groups'] as $groupData) {
                        unset($groupData['id']);
                        Group::create($groupData);
                        $imported['groups']++;
                    }
                } catch (\Exception $e) {
                    $imported['groups'] = 0;
                    Log::warning('[DatabaseController] Erreur import groupes', ['error' => $e->getMessage()]);
                }
            }

            // 8. Importer les imprimantes
            if (isset($data['data']['printers']) && is_array($data['data']['printers'])) {
                try {
                    $imported['printers'] = 0;
                    foreach ($data['data']['printers'] as $printerData) {
                        unset($printerData['id']);
                        $printerData['created_at'] = now();
                        $printerData['updated_at'] = now();
                        DB::table('printers')->insert($printerData);
                        $imported['printers']++;
                    }
                } catch (\Exception $e) {
                    $imported['printers'] = 0;
                    Log::warning('[DatabaseController] Erreur import imprimantes', ['error' => $e->getMessage()]);
                }
            }

            // 9. Importer les paramètres
            if (isset($data['data']['settings']) && is_array($data['data']['settings'])) {
                try {
                    $imported['settings'] = 0;
                    foreach ($data['data']['settings'] as $settingData) {
                        unset($settingData['id']);
                        Setting::create($settingData);
                        $imported['settings']++;
                    }
                } catch (\Exception $e) {
                    $imported['settings'] = 0;
                    Log::warning('[DatabaseController] Erreur import paramètres', ['error' => $e->getMessage()]);
                }
            }

            // 10. Importer l'historique des états des chambres (mapping room_id)
            if (isset($data['data']['room_state_history']) && is_array($data['data']['room_state_history']) && !empty($oldToNewRoomIds)) {
                try {
                    $imported['room_state_history'] = 0;
                    foreach ($data['data']['room_state_history'] as $row) {
                        $oldRoomId = $row['room_id'] ?? null;
                        if ($oldRoomId === null || !isset($oldToNewRoomIds[$oldRoomId])) {
                            continue;
                        }
                        unset($row['id'], $row['created_at'], $row['updated_at']);
                        $row['room_id'] = $oldToNewRoomIds[$oldRoomId];
                        $row['changed_by'] = null;
                        RoomStateHistory::create($row);
                        $imported['room_state_history']++;
                    }
                } catch (\Exception $e) {
                    $imported['room_state_history'] = 0;
                    Log::warning('[DatabaseController] Erreur import room_state_history', ['error' => $e->getMessage()]);
                }
            }

            // 11. Importer les tâches housekeeping (mapping hotel_id, room_id)
            if (isset($data['data']['housekeeping_tasks']) && is_array($data['data']['housekeeping_tasks']) && !empty($oldToNewRoomIds) && !empty($oldToNewHotelIds)) {
                try {
                    $imported['housekeeping_tasks'] = 0;
                    foreach ($data['data']['housekeeping_tasks'] as $task) {
                        $oldRoomId = $task['room_id'] ?? null;
                        $oldHotelId = $task['hotel_id'] ?? null;
                        if ($oldRoomId === null || !isset($oldToNewRoomIds[$oldRoomId]) || $oldHotelId === null || !isset($oldToNewHotelIds[$oldHotelId])) {
                            continue;
                        }
                        unset($task['id'], $task['created_at'], $task['updated_at']);
                        $task['hotel_id'] = $oldToNewHotelIds[$oldHotelId];
                        $task['room_id'] = $oldToNewRoomIds[$oldRoomId];
                        $task['assigned_to'] = null;
                        HousekeepingTask::create($task);
                        $imported['housekeeping_tasks']++;
                    }
                } catch (\Exception $e) {
                    $imported['housekeeping_tasks'] = 0;
                    Log::warning('[DatabaseController] Erreur import housekeeping_tasks', ['error' => $e->getMessage()]);
                }
            }

            // 12. Importer les types de linge (buanderie) - mapping hotel_id
            if (isset($data['data']['laundry_item_types']) && is_array($data['data']['laundry_item_types']) && !empty($oldToNewHotelIds)) {
                try {
                    $imported['laundry_item_types'] = 0;
                    foreach ($data['data']['laundry_item_types'] as $item) {
                        $oldId = $item['id'] ?? null;
                        $oldHotelId = $item['hotel_id'] ?? null;
                        if ($oldHotelId === null || !isset($oldToNewHotelIds[$oldHotelId])) {
                            continue;
                        }
                        unset($item['id'], $item['created_at'], $item['updated_at']);
                        $item['hotel_id'] = $oldToNewHotelIds[$oldHotelId];
                        $newItem = LaundryItemType::create($item);
                        if ($oldId !== null) {
                            $oldToNewLaundryItemTypeIds[$oldId] = $newItem->id;
                        }
                        $imported['laundry_item_types']++;
                    }
                } catch (\Exception $e) {
                    $imported['laundry_item_types'] = 0;
                    Log::warning('[DatabaseController] Erreur import laundry_item_types', ['error' => $e->getMessage()]);
                }
            }

            // 13. Importer les collectes buanderie + lignes (mapping hotel_id, room_id, laundry_item_type_id)
            if (isset($data['data']['laundry_collections']) && is_array($data['data']['laundry_collections']) && !empty($oldToNewRoomIds) && !empty($oldToNewHotelIds)) {
                try {
                    $imported['laundry_collections'] = 0;
                    foreach ($data['data']['laundry_collections'] as $coll) {
                        $oldRoomId = $coll['room_id'] ?? null;
                        $oldHotelId = $coll['hotel_id'] ?? null;
                        if ($oldRoomId === null || !isset($oldToNewRoomIds[$oldRoomId]) || $oldHotelId === null || !isset($oldToNewHotelIds[$oldHotelId])) {
                            continue;
                        }
                        $lines = $coll['lines'] ?? [];
                        unset($coll['id'], $coll['created_at'], $coll['updated_at'], $coll['lines']);
                        $coll['hotel_id'] = $oldToNewHotelIds[$oldHotelId];
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
                        $imported['laundry_collections']++;
                    }
                } catch (\Exception $e) {
                    $imported['laundry_collections'] = 0;
                    Log::warning('[DatabaseController] Erreur import laundry_collections', ['error' => $e->getMessage()]);
                }
            }

            DB::commit();

            $totalImported = array_sum(array_filter($imported, 'is_numeric'));

            Log::info('[DatabaseController] IMPORT GLOBAL TERMINÉ', [
                'user_id' => auth()->id(),
                'imported' => $imported,
                'total' => $totalImported
            ]);

            $msgExtra = '';
            if (isset($imported['room_state_history']) && $imported['room_state_history'] > 0) {
                $msgExtra .= ", {$imported['room_state_history']} historiques d'états";
            }
            if (isset($imported['housekeeping_tasks']) && $imported['housekeeping_tasks'] > 0) {
                $msgExtra .= ", {$imported['housekeeping_tasks']} tâches étages";
            }
            if ((isset($imported['laundry_item_types']) && $imported['laundry_item_types'] > 0) || (isset($imported['laundry_collections']) && $imported['laundry_collections'] > 0)) {
                $msgExtra .= ", " . ($imported['laundry_item_types'] ?? 0) . " types linge, " . ($imported['laundry_collections'] ?? 0) . " collectes buanderie";
            }
            return redirect()->route('super.database.index')
                ->with('success', "✅ IMPORT GLOBAL RÉUSSI ! Données importées : " . 
                       ($imported['hotels'] ?? 0) . " hôtels, " . ($imported['users'] ?? 0) . " utilisateurs, " .
                       ($imported['reservations'] ?? 0) . " réservations, " . ($imported['rooms'] ?? 0) . " chambres, " .
                       ($imported['room_types'] ?? 0) . " types de chambres" . $msgExtra . ". Total : {$totalImported} éléments. " .
                       "Une sauvegarde automatique a été créée avant l'import.");

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('[DatabaseController] Erreur lors de l\'import global', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', '❌ Erreur lors de l\'import : ' . $e->getMessage());
        }
    }
}


