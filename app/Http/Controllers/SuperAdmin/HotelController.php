<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Core\SettingsResolver;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Hotel;
use App\Models\User;
use App\Models\FormField;
use App\Models\Reservation;
use App\Models\Signature;
use App\Models\IdentityDocument;
use App\Models\Setting;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
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

    /**
     * Liste des hôtels avec accès à l'activation des modules (housekeeping, etc.).
     */
    public function modulesIndex()
    {
        $hotels = Hotel::orderBy('name')->get(['id', 'name', 'logo', 'city', 'country', 'settings']);
        return view('super.hotels.modules-index', compact('hotels'));
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
            try {
                $extension = $request->file('logo')->getClientOriginalExtension();
                $filename = 'logo_' . Str::random(40) . '.' . $extension;
                $directory = public_path('images/logos');
                
                // Créer le répertoire s'il n'existe pas
                if (!File::exists($directory)) {
                    File::makeDirectory($directory, 0755, true, true);
                }
                
                // Vérifier que le répertoire est accessible en écriture
                if (!is_writable($directory)) {
                    \Log::error('Dossier logos non accessible en écriture', ['directory' => $directory]);
                    throw new \Exception('Le dossier des logos n\'est pas accessible en écriture');
                }
                
                // Déplacer le fichier
                $file = $request->file('logo');
                $fullPath = $directory . '/' . $filename;
                
                if (!$file->move($directory, $filename)) {
                    throw new \Exception('Impossible de déplacer le fichier uploadé');
                }
                
                // Vérifier que le fichier existe bien
                if (!File::exists($fullPath)) {
                    throw new \Exception('Le fichier n\'a pas été créé correctement');
                }
                
                $data['logo'] = 'images/logos/' . $filename;
                
                \Log::info('Logo uploadé avec succès', [
                    'filename' => $filename,
                    'path' => $data['logo'],
                    'full_path' => $fullPath
                ]);
            } catch (\Exception $e) {
                \Log::error('Erreur lors de l\'upload du logo', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['logo' => 'Erreur lors de l\'upload du logo: ' . $e->getMessage()]);
            }
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
                    'is_available' => isset($roomTypeData['is_available']) ? (bool)$roomTypeData['is_available'] : true,
                ]);

                // Créer les chambres pour ce type
                if (isset($roomTypeData['rooms']) && is_array($roomTypeData['rooms'])) {
                    foreach ($roomTypeData['rooms'] as $roomData) {
                        // Support ancien format (string) et nouveau format (array)
                        if (is_string($roomData)) {
                            $roomNumber = $roomData;
                            $floor = null;
                            $status = 'available';
                        } else {
                            $roomNumber = $roomData['number'] ?? $roomData['room_number'] ?? null;
                            $floor = $roomData['floor'] ?? null;
                            $status = $roomData['status'] ?? 'available';
                        }
                        
                        if ($roomNumber) {
                            $hotel->rooms()->create([
                                'room_type_id' => $roomType->id,
                                'room_number' => $roomNumber,
                                'floor' => $floor,
                                'status' => $status,
                            ]);
                        }
                    }
                }
            }
        }

        // Créer un utilisateur admin par défaut pour cet hôtel
        $adminUser = User::create([
            'hotel_id' => $hotel->id,
            'name' => 'Admin ' . $hotel->name,
            'email' => 'admin@' . Str::slug($hotel->name) . '.test',
            'password' => Hash::make('password'),
        ]);
        $adminUser->assignRole('hotel-admin');

        return redirect()->route('super.hotels.index')
            ->with('success', 'Hôtel créé avec succès. Un compte admin a été créé: ' . $adminUser->email);
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
        
        // Générer l'URL du formulaire (utilise route() pour une URL dynamique)
        $qrUrl = route('public.form', $hotel);

        // Générer le QR code avec logo si imagick est disponible, sinon SVG
        $primaryRgb = $this->hexToRgb($hotel->primary_color ?? '#020220');
        
        // Vérifier si imagick est disponible pour PNG + logo
        $logoPath = null;
        if (extension_loaded('imagick') && $hotel->hasLogo() && $hotel->logo) {
            $logoPath = public_path($hotel->logo);
            // Compatibilité avec anciens chemins
            if (strpos($hotel->logo, 'storage/') === 0 || strpos($hotel->logo, 'hotels/') === 0) {
                $logoPath = public_path('images/logos/' . basename($hotel->logo));
            }
            
            if (!File::exists($logoPath)) {
                $logoPath = null;
            }
        }
        
        if ($logoPath && extension_loaded('imagick')) {
            // Générer en PNG avec logo
            $qrImage = QrCode::format('png')
                ->size(200)
                ->margin(2)
                ->errorCorrection('H')
                ->color($primaryRgb[0], $primaryRgb[1], $primaryRgb[2])
                ->backgroundColor(255, 255, 255)
                ->merge($logoPath, 0.25, true)
                ->generate($qrUrl);
            
            // Convertir PNG en base64 pour l'affichage
            $qrSvg = '<img src="data:image/png;base64,' . base64_encode($qrImage) . '" alt="QR Code" style="max-width: 100%; height: auto;">';
        } else {
            // Générer en SVG (fonctionne sans imagick)
            $qrSvg = QrCode::format('svg')
                ->size(200)
                ->margin(2)
                ->errorCorrection('H')
                ->color($primaryRgb[0], $primaryRgb[1], $primaryRgb[2])
                ->backgroundColor(255, 255, 255)
                ->generate($qrUrl);
        }
        
        return view('super.hotels.show', compact('hotel', 'qrSvg', 'qrUrl'));
    }

    /**
     * Retourner les types de chambres d'un hôtel en JSON (pour l'éditeur)
     */
    public function getRoomTypes(Hotel $hotel)
    {
        try {
            $roomTypes = $hotel->roomTypes()
                ->with('rooms:id,room_type_id,room_number,floor,status')
                ->get()
                ->map(function($roomType) {
                    return [
                        'id' => $roomType->id,
                        'name' => $roomType->name,
                        'price' => $roomType->price,
                        'description' => $roomType->description,
                        'capacity' => $roomType->capacity,
                        'is_available' => $roomType->is_available,
                        'rooms' => $roomType->rooms->map(function($room) {
                            return [
                                'id' => $room->id,
                                'room_number' => $room->room_number,
                                'floor' => $room->floor,
                                'status' => $room->status,
                            ];
                        })->toArray(),
                    ];
                });

            return response()->json($roomTypes);
        } catch (\Exception $e) {
            \Log::error('Erreur getRoomTypes', [
                'hotel_id' => $hotel->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
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
            try {
                // Supprimer l'ancien logo si existe
                if ($hotel->logo) {
                    $oldPath = public_path($hotel->logo);
                    // Compatibilité avec anciens chemins
                    if (strpos($hotel->logo, 'storage/') === 0 || strpos($hotel->logo, 'hotels/') === 0) {
                        $oldPath = public_path('images/logos/' . basename($hotel->logo));
                    }
                    if (File::exists($oldPath)) {
                        File::delete($oldPath);
                        \Log::info('Ancien logo supprimé', ['path' => $oldPath]);
                    }
                }
                
                $extension = $request->file('logo')->getClientOriginalExtension();
                $filename = 'logo_' . Str::random(40) . '.' . $extension;
                $directory = public_path('images/logos');
                
                // Créer le répertoire s'il n'existe pas
                if (!File::exists($directory)) {
                    File::makeDirectory($directory, 0755, true, true);
                }
                
                // Vérifier que le répertoire est accessible en écriture
                if (!is_writable($directory)) {
                    \Log::error('Dossier logos non accessible en écriture', ['directory' => $directory]);
                    throw new \Exception('Le dossier des logos n\'est pas accessible en écriture');
                }
                
                // Déplacer le fichier
                $file = $request->file('logo');
                $fullPath = $directory . '/' . $filename;
                
                if (!$file->move($directory, $filename)) {
                    throw new \Exception('Impossible de déplacer le fichier uploadé');
                }
                
                // Vérifier que le fichier existe bien
                if (!File::exists($fullPath)) {
                    throw new \Exception('Le fichier n\'a pas été créé correctement');
                }
                
                $data['logo'] = 'images/logos/' . $filename;
                
                \Log::info('Logo uploadé avec succès (update)', [
                    'hotel_id' => $hotel->id,
                    'filename' => $filename,
                    'path' => $data['logo'],
                    'full_path' => $fullPath
                ]);
            } catch (\Exception $e) {
                \Log::error('Erreur lors de l\'upload du logo (update)', [
                    'hotel_id' => $hotel->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['logo' => 'Erreur lors de l\'upload du logo: ' . $e->getMessage()]);
            }
        } else {
            // Si aucun nouveau logo n'est fourni, retirer le champ du tableau pour conserver l'ancien
            unset($data['logo']);
        }

        $hotel->update($data);
        
        return redirect()->route('super.hotels.show', $hotel)
            ->with('success', 'Hôtel modifié avec succès');
    }

    /**
     * Mise à jour des modules activés pour un hôtel (SuperAdmin).
     */
    public function updateModules(Request $request, Hotel $hotel)
    {
        $request->validate([
            'modules' => 'nullable|array',
            'modules.housekeeping' => 'nullable|boolean',
            'modules.laundry' => 'nullable|boolean',
        ]);

        $settings = $hotel->settings ?? [];
        $settings['modules'] = $settings['modules'] ?? [];
        $settings['modules']['housekeeping'] = (bool) ($request->input('modules.housekeeping', false));
        $settings['modules']['laundry'] = (bool) ($request->input('modules.laundry', false));
        $hotel->update(['settings' => $settings]);

        ActivityLog::log(
            "Modules de l'hôtel mis à jour : {$hotel->name}",
            $hotel,
            [
                'action_type' => 'hotel_modules_updated',
                'hotel_name' => $hotel->name,
                'modules' => $settings['modules'],
            ],
            'application',
            'updated'
        );

        return redirect()->route('super.hotels.show', $hotel)
            ->with('success', 'Modules mis à jour.');
    }

    public function destroy(Hotel $hotel)
    {
        // Supprimer le logo si existe
        if ($hotel->logo) {
            $logoPath = public_path($hotel->logo);
            // Compatibilité avec anciens chemins
            if (strpos($hotel->logo, 'storage/') === 0 || strpos($hotel->logo, 'hotels/') === 0) {
                $logoPath = public_path('images/logos/' . basename($hotel->logo));
            }
            if (File::exists($logoPath)) {
                File::delete($logoPath);
            }
        }
        
        $hotel->delete();
        
        return redirect()->route('super.hotels.index')
            ->with('success', 'Hôtel supprimé avec succès');
    }

    public function destroyMultiple(Request $request)
    {
        $request->validate([
            'hotel_ids' => 'required|array',
            'hotel_ids.*' => 'exists:hotels,id',
        ]);

        $hotelIds = $request->hotel_ids;
        $deleted = 0;

        try {
            DB::beginTransaction();

            foreach ($hotelIds as $hotelId) {
                $hotel = Hotel::findOrFail($hotelId);

                // Supprimer le logo si existe
                if ($hotel->logo) {
                    $logoPath = public_path($hotel->logo);
                    // Compatibilité avec anciens chemins
                    if (strpos($hotel->logo, 'storage/') === 0 || strpos($hotel->logo, 'hotels/') === 0) {
                        $logoPath = public_path('images/logos/' . basename($hotel->logo));
                    }
                    if (File::exists($logoPath)) {
                        File::delete($logoPath);
                    }
                }

                // Récupérer les IDs de réservations pour suppressions associées
                $reservationIds = Reservation::withoutGlobalScopes()->where('hotel_id', $hotel->id)->pluck('id');

                // Supprimer les données associées dans l'ordre correct
                if ($reservationIds->isNotEmpty()) {
                    Signature::whereIn('reservation_id', $reservationIds)->delete();
                    IdentityDocument::whereIn('reservation_id', $reservationIds)->delete();
                }
                Reservation::withoutGlobalScopes()->where('hotel_id', $hotel->id)->delete();
                \App\Models\Client::where('hotel_id', $hotel->id)->delete();
                Room::where('hotel_id', $hotel->id)->delete();
                RoomType::where('hotel_id', $hotel->id)->delete();
                DB::table('printers')->where('hotel_id', $hotel->id)->delete();
                Setting::where('hotel_id', $hotel->id)->delete();
                DB::table('form_fields')->where('hotel_id', $hotel->id)->delete();
                User::where('hotel_id', $hotel->id)
                    ->whereDoesntHave('roles', function($q) {
                        $q->where('name', 'super-admin');
                    })->delete();
                DB::table('print_logs')->where('hotel_id', $hotel->id)->delete();
                \App\Models\ActivityLog::where('hotel_id', $hotel->id)->delete();

                // Supprimer l'hôtel
                $hotel->delete();
                $deleted++;
            }

            \Illuminate\Support\Facades\Cache::flush();

            DB::commit();
            return redirect()->route('super.hotels.index')
                ->with('success', "✅ {$deleted} hôtel(s) supprimé(s) avec succès. Toutes les données associées ont été purgées.");
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

    protected function hexToRgb(string $hex): array
    {
        $hex = str_replace('#', '', trim($hex));
        if (!preg_match('/^[0-9A-Fa-f]{3}$|^[0-9A-Fa-f]{6}$/', $hex)) {
            return [34, 34, 34]; // Default dark gray
        }
        if (strlen($hex) == 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2))
        ];
    }
}
