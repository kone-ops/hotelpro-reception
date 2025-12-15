<?php

namespace App\Http\Controllers\Reception;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Services\FormConfigService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class PoliceSheetController extends Controller
{
    /**
     * Générer et télécharger la fiche de police en PDF
     * ⚠️ UNIQUEMENT pour les réservations VALIDÉES
     */
    public function generate($id)
    {
        $user = Auth::user();
        
        $reservation = Reservation::where('hotel_id', $user->hotel_id)->findOrFail($id);
        
        // ✅ VÉRIFICATION : La fiche de police ne peut être générée QUE si la réservation est validée
        if ($reservation->status !== 'validated' && $reservation->status !== 'checked_in' && $reservation->status !== 'checked_out') {
            return back()->with('error', 'La fiche de police ne peut être générée que pour les réservations validées.');
        }
        
        // Charger les relations
        $reservation->load(['hotel', 'room', 'roomType', 'identityDocument', 'signature']);
        
        // Créer le service de configuration pour les champs personnalisés
        $formConfig = new FormConfigService($reservation->hotel);
        
        $pdf = Pdf::loadView('reception.police-sheet.pdf', compact('reservation', 'formConfig'));
        
        // Configuration du PDF en format A5
        $pdf->setPaper('a5', 'portrait');
        $pdf->setOption('enable-local-file-access', true);
        
        $filename = 'fiche-reservation-' . str_pad($reservation->id, 7, '0', STR_PAD_LEFT) . '-' . now()->format('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }
    
    /**
     * Prévisualiser la fiche de police
     * ⚠️ UNIQUEMENT pour les réservations VALIDÉES
     */
    public function preview($id)
    {
        $user = Auth::user();
        
        $reservation = Reservation::where('hotel_id', $user->hotel_id)->findOrFail($id);
        
        // ✅ VÉRIFICATION : La fiche de police ne peut être prévisualisée QUE si la réservation est validée
        if ($reservation->status !== 'validated' && $reservation->status !== 'checked_in' && $reservation->status !== 'checked_out') {
            return back()->with('error', 'La fiche de police ne peut être prévisualisée que pour les réservations validées.');
        }
        
        // Charger les relations
        $reservation->load(['hotel', 'room', 'roomType', 'identityDocument', 'signature']);
        
        // Créer le service de configuration pour les champs personnalisés
        $formConfig = new FormConfigService($reservation->hotel);
        
        return view('reception.police-sheet.preview', compact('reservation', 'formConfig'));
    }
    
    /**
     * Générer plusieurs fiches de police en lot (PDF)
     * ⚠️ UNIQUEMENT pour les réservations VALIDÉES
     */
    public function generateBatch(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'reservation_ids' => 'required|array',
            'reservation_ids.*' => 'exists:reservations,id',
        ]);
        
        // ✅ Récupérer UNIQUEMENT les réservations validées
        $reservations = Reservation::where('hotel_id', $user->hotel_id)
            ->whereIn('id', $request->reservation_ids)
            ->whereIn('status', ['validated', 'checked_in', 'checked_out'])
            ->with(['hotel', 'room', 'roomType', 'identityDocument', 'signature'])
            ->get();
        
        if ($reservations->isEmpty()) {
            return back()->with('error', 'Aucune réservation validée sélectionnée. Les fiches de police ne peuvent être générées que pour les réservations validées.');
        }
        
        // Créer le service de configuration pour les champs personnalisés
        $formConfig = new FormConfigService($user->hotel);
        
        $pdf = Pdf::loadView('reception.police-sheet.pdf-batch', compact('reservations', 'formConfig'));
        
        $filename = 'fiches-police-' . now()->format('Y-m-d-His') . '.pdf';
        
        return $pdf->download($filename);
    }
}
