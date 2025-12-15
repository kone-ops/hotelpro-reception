<?php

namespace App\Services;

use App\Models\Reservation;
use Illuminate\Support\Collection;

/**
 * Service d'export de données
 * Supporte Excel, PDF et CSV
 */
class ExportService
{
    /**
     * Exporter en Excel (CSV pour compatibilité)
     */
    public function toExcel(Collection $reservations, string $filename = 'reservations.csv')
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function() use ($reservations) {
            $file = fopen('php://output', 'w');
            
            // BOM pour Excel UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // En-têtes
            fputcsv($file, [
                'ID',
                'Type',
                'Statut',
                'Nom',
                'Prénom',
                'Email',
                'Téléphone',
                'Type Pièce',
                'N° Pièce',
                'Date Naissance',
                'Nationalité',
                'Date Arrivée',
                'Date Départ',
                'Nuits',
                'Adultes',
                'Enfants',
                'Type Chambre',
                'Nom Groupe',
                'Code Groupe',
                'Date Création',
            ], ';');

            // Données
            foreach ($reservations as $reservation) {
                $data = $reservation->data;
                
                fputcsv($file, [
                    $reservation->id,
                    $data['type_reservation'] ?? 'Individuel',
                    $reservation->status,
                    $data['nom'] ?? '',
                    $data['prenom'] ?? '',
                    $data['email'] ?? '',
                    $data['telephone'] ?? '',
                    $data['type_piece_identite'] ?? '',
                    $data['numero_piece_identite'] ?? '',
                    $data['date_naissance'] ?? '',
                    $data['nationalite'] ?? '',
                    $data['date_arrivee'] ?? '',
                    $data['date_depart'] ?? '',
                    $data['nombre_nuits'] ?? '',
                    $data['nombre_adultes'] ?? '',
                    $data['nombre_enfants'] ?? '',
                    $data['type_chambre'] ?? '',
                    $data['nom_groupe'] ?? '',
                    $data['code_groupe'] ?? '',
                    $reservation->created_at->format('d/m/Y H:i'),
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Exporter en PDF (simplifié - HTML to PDF)
     */
    public function toPdf(Collection $reservations, string $filename = 'reservations.pdf')
    {
        // Note: Nécessite dompdf (déjà installé dans le projet)
        $pdf = app('dompdf.wrapper');
        
        $html = view('exports.reservations-pdf', [
            'reservations' => $reservations,
            'exportDate' => now()->format('d/m/Y H:i'),
        ])->render();

        $pdf->loadHTML($html);
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download($filename);
    }

    /**
     * Exporter les statistiques
     */
    public function exportStats(array $stats, string $format = 'json')
    {
        switch ($format) {
            case 'csv':
                return $this->statsToCsv($stats);
            case 'json':
                return response()->json($stats, 200, [
                    'Content-Disposition' => 'attachment; filename="stats.json"'
                ]);
            default:
                return response()->json($stats);
        }
    }

    /**
     * Convertir les stats en CSV
     */
    private function statsToCsv(array $stats)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="stats.csv"',
        ];

        $callback = function() use ($stats) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, ['Métrique', 'Valeur']);
            
            foreach ($stats as $key => $value) {
                fputcsv($file, [ucfirst(str_replace('_', ' ', $key)), $value]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}





