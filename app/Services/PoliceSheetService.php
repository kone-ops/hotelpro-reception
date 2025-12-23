<?php

namespace App\Services;

use App\Models\Reservation;
use App\Services\FormConfigService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PoliceSheetService
{
    /**
     * Génère une fiche de police en PDF et la stocke dans storage/app/public/police-sheets
     * Retourne le chemin relatif du fichier stocké (ou null en cas d'erreur).
     */
    public function generateAndStore(Reservation $reservation): ?string
    {
        try {
            // Recharger les relations nécessaires pour être sûr d'avoir les données à jour
            $reservation->loadMissing(['hotel', 'room', 'roomType', 'identityDocument', 'signature']);

            $pdf = Pdf::loadView('reception.police-sheet.pdf', [
                'reservation' => $reservation,
                'formConfig' => new FormConfigService($reservation->hotel),
            ]);

            $pdf->setPaper('a5', 'portrait');
            $pdf->setOption('enable-local-file-access', true);

            $filename = 'fiche-reservation-' . str_pad($reservation->id, 7, '0', STR_PAD_LEFT) . '-' . now()->format('Y-m-d-His') . '.pdf';
            $path = 'public/police-sheets/' . $filename;

            Storage::put($path, $pdf->output());

            Log::info('Fiche de police générée et stockée', [
                'reservation_id' => $reservation->id,
                'path' => $path,
            ]);

            return $path;
        } catch (\Throwable $e) {
            Log::error('Erreur génération fiche de police', [
                'reservation_id' => $reservation->id ?? null,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}

