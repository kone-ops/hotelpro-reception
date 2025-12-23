<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\Reservation;
use App\Models\Printer;
use App\Models\Setting;
use App\Models\User;
use App\Models\IdentityDocument;
use App\Models\Signature;
use App\Models\PrintLog;
use App\Models\ActivityLog;
use App\Models\RoomType;
use App\Models\Room;
use App\Models\Group;
use App\Models\Client;
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
        // Statistiques globales
        $stats = [
            'total_hotels' => Hotel::count(),
            'total_users' => User::count(),
            'total_reservations' => Reservation::count(),
            'total_rooms' => Room::count(),
            'total_room_types' => RoomType::count(),
            'total_printers' => Printer::count(),
            'total_settings' => Setting::count(),
            'total_groups' => Group::count(),
            'total_clients' => Client::count(),
        ];

        // Compter les super-admins
        $superAdmins = User::role('super-admin')->get();
        $stats['total_super_admins'] = $superAdmins->count();
        $stats['super_admins'] = $superAdmins;

        return view('super.database.index', compact('stats'));
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
                $printers = Printer::all()->toArray();
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

            DB::beginTransaction();

            Log::critical('[DatabaseController] PURGE GLOBALE - SUPPRESSION TOTALE DE LA BASE', [
                'user_id' => auth()->id(),
                'user_email' => auth()->user()->email,
            ]);

            $deleted = [];

            // 1. Supprimer toutes les signatures
            try {
                $deleted['signatures'] = Signature::query()->delete();
            } catch (\Exception $e) {
                $deleted['signatures'] = 0;
            }

            // 2. Supprimer tous les documents d'identité
            try {
                $deleted['identity_documents'] = IdentityDocument::query()->delete();
            } catch (\Exception $e) {
                $deleted['identity_documents'] = 0;
            }

            // 3. Supprimer toutes les réservations
            $deleted['reservations'] = Reservation::query()->delete();

            // 4. Supprimer toutes les chambres
            try {
                $deleted['rooms'] = Room::query()->delete();
            } catch (\Exception $e) {
                $deleted['rooms'] = 0;
            }

            // 5. Supprimer tous les types de chambres
            try {
                $deleted['room_types'] = RoomType::query()->delete();
            } catch (\Exception $e) {
                $deleted['room_types'] = 0;
            }

            // 6. Supprimer toutes les imprimantes
            try {
                $deleted['printers'] = Printer::query()->delete();
            } catch (\Exception $e) {
                $deleted['printers'] = 0;
            }

            // 7. Supprimer tous les paramètres
            try {
                $deleted['settings'] = Setting::query()->delete();
            } catch (\Exception $e) {
                $deleted['settings'] = 0;
            }

            // 8. Supprimer tous les champs de formulaire
            try {
                $deleted['form_fields'] = DB::table('form_fields')->delete();
            } catch (\Exception $e) {
                $deleted['form_fields'] = 0;
            }

            // 9. Supprimer tous les groupes
            try {
                $deleted['groups'] = Group::query()->delete();
            } catch (\Exception $e) {
                $deleted['groups'] = 0;
            }

            // 10. Supprimer tous les clients
            try {
                $deleted['clients'] = Client::query()->delete();
            } catch (\Exception $e) {
                $deleted['clients'] = 0;
            }

            // 11. Supprimer TOUS les hôtels
            try {
                $deleted['hotels'] = Hotel::query()->delete();
            } catch (\Exception $e) {
                $deleted['hotels'] = 0;
            }

            // 12. Supprimer TOUS les utilisateurs SAUF les super-admins
            $currentUserId = auth()->id();
            try {
                // Supprimer tous les utilisateurs qui ne sont pas super-admin
                $deleted['users'] = User::whereDoesntHave('roles', function($q) {
                    $q->where('name', 'super-admin');
                })->delete();
                
                // Note: Les super-admins sont conservés automatiquement
                $deleted['super_admins_deleted'] = 0;
            } catch (\Exception $e) {
                $deleted['users'] = 0;
                $deleted['super_admins_deleted'] = 0;
            }

            // 13. Supprimer tous les logs d'impression
            try {
                $deleted['print_logs'] = PrintLog::query()->delete();
            } catch (\Exception $e) {
                $deleted['print_logs'] = 0;
            }

            // 14. Supprimer tous les logs d'activité
            try {
                $deleted['activity_logs'] = ActivityLog::query()->delete();
            } catch (\Exception $e) {
                $deleted['activity_logs'] = 0;
            }

            // 15. Nettoyer tout le cache
            Cache::flush();

            DB::commit();

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

            // 1. Importer les hôtels
            if (isset($data['data']['hotels']) && is_array($data['data']['hotels'])) {
                try {
                    $imported['hotels'] = 0;
                    foreach ($data['data']['hotels'] as $hotelData) {
                        // Ignorer l'ID pour éviter les conflits
                        unset($hotelData['id']);
                        Hotel::create($hotelData);
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
                        
                        unset($userData['id'], $userData['password'], $userData['remember_token']);
                        if (!isset($userData['password'])) {
                            // Générer un mot de passe temporaire
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

            // 3. Importer les types de chambres
            if (isset($data['data']['room_types']) && is_array($data['data']['room_types'])) {
                try {
                    $imported['room_types'] = 0;
                    foreach ($data['data']['room_types'] as $roomTypeData) {
                        unset($roomTypeData['id']);
                        RoomType::create($roomTypeData);
                        $imported['room_types']++;
                    }
                } catch (\Exception $e) {
                    $imported['room_types'] = 0;
                    Log::warning('[DatabaseController] Erreur import types de chambres', ['error' => $e->getMessage()]);
                }
            }

            // 4. Importer les chambres
            if (isset($data['data']['rooms']) && is_array($data['data']['rooms'])) {
                try {
                    $imported['rooms'] = 0;
                    foreach ($data['data']['rooms'] as $roomData) {
                        unset($roomData['id']);
                        Room::create($roomData);
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
                        Printer::create($printerData);
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

            DB::commit();

            $totalImported = array_sum(array_filter($imported, 'is_numeric'));

            Log::info('[DatabaseController] IMPORT GLOBAL TERMINÉ', [
                'user_id' => auth()->id(),
                'imported' => $imported,
                'total' => $totalImported
            ]);

            return redirect()->route('super.database.index')
                ->with('success', "✅ IMPORT GLOBAL RÉUSSI ! Données importées : " . 
                       "{$imported['hotels']} hôtels, {$imported['users']} utilisateurs, " .
                       "{$imported['reservations']} réservations, {$imported['rooms']} chambres, " .
                       "{$imported['room_types']} types de chambres. Total : {$totalImported} éléments. " .
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

