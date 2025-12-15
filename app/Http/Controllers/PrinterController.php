<?php

namespace App\Http\Controllers;

use App\Models\Printer;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PrinterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $user = auth()->user();
        
        // Si super-admin, voir toutes les imprimantes
        // Si hotel-admin, voir uniquement les imprimantes de son hôtel
        if ($user->hasRole('super-admin')) {
            $printers = Printer::with('hotel')->orderBy('name')->get();
        } else {
            $printers = Printer::where('hotel_id', $user->hotel_id)
                ->orderBy('name')
                ->get();
        }
        
        return view('admin.printers.index', compact('printers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $user = auth()->user();
        
        // Si super-admin, afficher la liste des hôtels
        if ($user->hasRole('super-admin')) {
            $hotels = \App\Models\Hotel::orderBy('name')->get();
            return view('admin.printers.create', compact('hotels'));
        }
        
        return view('admin.printers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        
        $validated = $request->validate([
            'hotel_id' => $user->hasRole('super-admin') ? 'required|exists:hotels,id' : 'nullable',
            'name' => 'required|string|max:255',
            'ip_address' => 'required|ip',
            'port' => 'required|integer|min:1|max:65535',
            'type' => 'required|in:ticket,a4',
            'is_active' => 'boolean'
        ]);

        // Si hotel-admin, forcer le hotel_id de l'utilisateur
        if (!$user->hasRole('super-admin')) {
            $validated['hotel_id'] = $user->hotel_id;
        }
        
        // Vérifier si une imprimante avec cette IP:Port existe déjà pour cet hôtel
        $exists = Printer::where('ip_address', $validated['ip_address'])
            ->where('port', $validated['port'])
            ->where('hotel_id', $validated['hotel_id'])
            ->exists();
        
        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['ip_address' => 'Une imprimante avec cette adresse IP et ce port existe déjà.']);
        }

        $printer = Printer::create($validated);
        
        // Tester automatiquement la connexion après création
        $isOnline = $printer->testConnection();
        $printer->update(['is_active' => $isOnline]);

        $route = $user->hasRole('super-admin') ? 'super.printers.index' : 'hotel.printers.index';
        $statusMessage = $isOnline ? 
            'Imprimante créée avec succès et détectée en ligne.' : 
            'Imprimante créée mais non accessible sur le réseau.';
        
        return redirect()->route($route)
            ->with('success', $statusMessage);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Printer $printer): View
    {
        $user = auth()->user();
        
        // Vérifier les permissions
        if (!$user->hasRole('super-admin') && $printer->hotel_id !== $user->hotel_id) {
            abort(403, 'Accès non autorisé à cette imprimante');
        }
        
        // Si super-admin, afficher la liste des hôtels
        if ($user->hasRole('super-admin')) {
            $hotels = \App\Models\Hotel::orderBy('name')->get();
            return view('admin.printers.edit', compact('printer', 'hotels'));
        }
        
        return view('admin.printers.edit', compact('printer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Printer $printer): RedirectResponse
    {
        $user = auth()->user();
        
        // Vérifier les permissions
        if (!$user->hasRole('super-admin') && $printer->hotel_id !== $user->hotel_id) {
            abort(403, 'Accès non autorisé');
        }
        
        $validated = $request->validate([
            'hotel_id' => $user->hasRole('super-admin') ? 'required|exists:hotels,id' : 'nullable',
            'name' => 'required|string|max:255',
            'ip_address' => 'required|ip',
            'port' => 'required|integer|min:1|max:65535',
            'type' => 'required|in:ticket,a4',
            'is_active' => 'boolean'
        ]);

        // Si hotel-admin, forcer le hotel_id de l'utilisateur
        if (!$user->hasRole('super-admin')) {
            $validated['hotel_id'] = $user->hotel_id;
        }

        $printer->update($validated);
        
        // Tester automatiquement la connexion après mise à jour
        $isOnline = $printer->testConnection();
        $printer->update(['is_active' => $isOnline]);

        $route = $user->hasRole('super-admin') ? 'super.printers.index' : 'hotel.printers.index';
        $statusMessage = $isOnline ? 
            'Imprimante mise à jour avec succès et détectée en ligne.' : 
            'Imprimante mise à jour mais non accessible sur le réseau.';
        
        return redirect()->route($route)
            ->with('success', $statusMessage);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Printer $printer): RedirectResponse
    {
        $user = auth()->user();
        
        // Vérifier les permissions
        if (!$user->hasRole('super-admin') && $printer->hotel_id !== $user->hotel_id) {
            abort(403, 'Accès non autorisé');
        }
        
        $printer->delete();

        $route = $user->hasRole('super-admin') ? 'super.printers.index' : 'hotel.printers.index';
        return redirect()->route($route)
            ->with('success', 'Imprimante supprimée avec succès.');
    }

    /**
     * Tester la connexion à une imprimante
     */
    public function test(Printer $printer)
    {
        $user = auth()->user();
        
        // Vérifier les permissions
        if (!$user->hasRole('super-admin') && $printer->hotel_id !== $user->hotel_id) {
            abort(403, 'Accès non autorisé à cette imprimante');
        }
        
        // Tester la connexion
        $isOnline = $printer->testConnection();
        
        // Mettre à jour le statut
        $printer->update(['is_active' => $isOnline]);
        
        // Préparer les détails
        $testDetails = [
            'printer' => $printer,
            'is_online' => $isOnline,
            'test_date' => now(),
            'status_message' => $isOnline 
                ? 'Connexion réussie à l\'imprimante' 
                : 'Impossible de se connecter à l\'imprimante',
            'help_message' => $isOnline 
                ? 'L\'imprimante est accessible et prête à recevoir des impressions.' 
                : 'Vérifiez que l\'imprimante est allumée et connectée au réseau.'
        ];
        
        return view('admin.printers.test', $testDetails);
    }

    /**
     * Envoyer un test d'impression
     */
    public function printTest(Printer $printer)
    {
        $user = auth()->user();
        
        // Vérifier les permissions
        if (!$user->hasRole('super-admin') && $printer->hotel_id !== $user->hotel_id) {
            abort(403, 'Accès non autorisé à cette imprimante');
        }
        
        $result = [
            'printer' => $printer,
            'test_date' => now(),
            'success' => false,
            'message' => '',
            'details' => []
        ];
        
        try {
            // Vérifier la connexion
            $isOnline = $printer->testConnection();
            
            if (!$isOnline) {
                $result['message'] = 'Imprimante non accessible';
                $result['details'][] = 'L\'imprimante ne répond pas sur le réseau.';
                $result['details'][] = 'Vérifiez qu\'elle est allumée.';
                $result['details'][] = 'Vérifiez la connexion réseau.';
                
                return view('admin.printers.print-test', $result);
            }

            // Créer un document de test
            $testContent = $this->generateTestDocument($printer);
            
            // Envoyer à l'imprimante
            $success = $printer->sendToPrinter($testContent);
            
            if ($success) {
                $result['success'] = true;
                $result['message'] = 'Test d\'impression envoyé avec succès !';
                $result['details'][] = 'Le document de test a été envoyé à l\'imprimante.';
                $result['details'][] = 'Vérifiez que le papier sort de l\'imprimante.';
                $result['details'][] = 'Type d\'imprimante: ' . strtoupper($printer->type);
            } else {
                $result['message'] = 'Échec de l\'envoi à l\'imprimante';
                $result['details'][] = 'La connexion est OK mais l\'envoi a échoué.';
                $result['details'][] = 'Vérifiez les logs Laravel pour plus de détails.';
            }
            
        } catch (\Exception $e) {
            $result['message'] = 'Erreur lors de l\'envoi du test';
            $result['details'][] = 'Exception: ' . $e->getMessage();
            $result['details'][] = 'Consultez storage/logs/laravel.log';
        }
        
        return view('admin.printers.print-test', $result);
    }
    
    /**
     * Générer un document de test
     */
    private function generateTestDocument(Printer $printer): string
    {
        $testContent = "=================================\n";
        $testContent .= "   TEST D'IMPRESSION\n";
        $testContent .= "=================================\n\n";
        $testContent .= "Imprimante: {$printer->name}\n";
        $testContent .= "Type: " . strtoupper($printer->type) . "\n";
        $testContent .= "Adresse: {$printer->ip_address}:" . ($printer->port ?? 9100) . "\n";
        $testContent .= "Date: " . now()->format('d/m/Y H:i:s') . "\n\n";
        $testContent .= "Si vous voyez ce message,\n";
        $testContent .= "l'imprimante fonctionne correctement!\n\n";
        $testContent .= "=================================\n";
        
        return $testContent;
    }
}