<?php

namespace App\Http\Controllers;

use App\Models\PrintLog;
use App\Models\Printer;
use App\Services\AdvancedPrintService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class PrintLogController extends Controller
{
    protected $printService;

    public function __construct(AdvancedPrintService $printService)
    {
        $this->printService = $printService;
    }

    /**
     * Afficher la liste des logs d'impression
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        
        $query = PrintLog::with(['printer', 'user', 'hotel'])
            ->orderBy('created_at', 'desc');

        // Filtrage par hôtel si nécessaire
        if (!$user->hasRole('super-admin') && $user->hotel_id) {
            $query->where('hotel_id', $user->hotel_id);
        }

        // Filtres
        if ($request->filled('printer_id')) {
            $query->where('printer_id', $request->printer_id);
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('type_document')) {
            $query->where('type_document', $request->type_document);
        }

        if ($request->filled('date_debut')) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }

        $printLogs = $query->paginate(20);

        // Données pour les filtres
        $printers = Printer::active()->get();
        $statuts = PrintLog::getStatistiques();
        $typesDocuments = PrintLog::getTypesDocuments();

        return view('admin.print-logs.index', compact('printLogs', 'printers', 'statuts', 'typesDocuments'));
    }

    /**
     * Afficher les détails d'un log d'impression
     */
    public function show(PrintLog $printLog): View
    {
        $printLog->load(['printer', 'user', 'hotel']);
        
        return view('admin.print-logs.show', compact('printLog'));
    }

    /**
     * Relancer une impression échouée
     */
    public function retry(PrintLog $printLog): JsonResponse
    {
        try {
            if (!$printLog->isEchec() && !$printLog->isAnnule()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Seules les impressions échouées ou annulées peuvent être relancées'
                ], 400);
            }

            $printer = $printLog->printer;
            if (!$printer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Imprimante non trouvée'
                ], 404);
            }

            // Relancer l'impression
            $newPrintLog = $this->printService->imprimerAvancee(
                $printer,
                $printLog->contenu,
                $printLog->type_document,
                $printLog->reference . '_RETRY',
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => 'Impression relancée avec succès',
                'print_log_id' => $newPrintLog->id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du relancement: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Annuler une impression en cours
     */
    public function cancel(PrintLog $printLog): JsonResponse
    {
        try {
            if (!$printLog->isEnAttente() && !$printLog->isEnCours()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Seules les impressions en attente ou en cours peuvent être annulées'
                ], 400);
            }

            $printLog->marquerAnnule();

            return response()->json([
                'success' => true,
                'message' => 'Impression annulée avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'annulation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les statistiques des logs d'impression
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            $hotelId = null;

            if (!$user->hasRole('super-admin') && $user->hotel_id) {
                $hotelId = $user->hotel_id;
            }

            $dateDebut = $request->get('date_debut', now()->startOfDay());
            $dateFin = $request->get('date_fin', now()->endOfDay());

            $statistiques = PrintLog::getStatistiques($dateDebut, $dateFin, $hotelId);

            return response()->json([
                'success' => true,
                'data' => $statistiques
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Obtenir les logs d'impression
     */
    public function apiIndex(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            
            $query = PrintLog::with(['printer', 'user', 'hotel'])
                ->orderBy('created_at', 'desc');

            // Filtrage par hôtel si nécessaire
            if (!$user->hasRole('super-admin') && $user->hotel_id) {
                $query->where('hotel_id', $user->hotel_id);
            }

            // Filtres
            if ($request->filled('printer_id')) {
                $query->where('printer_id', $request->printer_id);
            }

            if ($request->filled('statut')) {
                $query->where('statut', $request->statut);
            }

            if ($request->filled('type_document')) {
                $query->where('type_document', $request->type_document);
            }

            if ($request->filled('date_debut')) {
                $query->whereDate('created_at', '>=', $request->date_debut);
            }

            if ($request->filled('date_fin')) {
                $query->whereDate('created_at', '<=', $request->date_fin);
            }

            $printLogs = $query->paginate($request->get('per_page', 20));

            return response()->json([
                'success' => true,
                'data' => $printLogs
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des logs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Obtenir les détails d'un log
     */
    public function apiShow(PrintLog $printLog): JsonResponse
    {
        try {
            $printLog->load(['printer', 'user', 'hotel']);

            return response()->json([
                'success' => true,
                'data' => $printLog
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du log: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Relancer une impression
     */
    public function apiRetry(PrintLog $printLog): JsonResponse
    {
        return $this->retry($printLog);
    }

    /**
     * API: Annuler une impression
     */
    public function apiCancel(PrintLog $printLog): JsonResponse
    {
        return $this->cancel($printLog);
    }

    /**
     * Export des logs d'impression en CSV
     */
    public function export(Request $request)
    {
        try {
            $user = auth()->user();
            
            $query = PrintLog::with(['printer', 'user', 'hotel'])
                ->orderBy('created_at', 'desc');

            // Filtrage par hôtel si nécessaire
            if (!$user->hasRole('super-admin') && $user->hotel_id) {
                $query->where('hotel_id', $user->hotel_id);
            }

            // Appliquer les mêmes filtres que l'index
            if ($request->filled('printer_id')) {
                $query->where('printer_id', $request->printer_id);
            }

            if ($request->filled('statut')) {
                $query->where('statut', $request->statut);
            }

            if ($request->filled('type_document')) {
                $query->where('type_document', $request->type_document);
            }

            if ($request->filled('date_debut')) {
                $query->whereDate('created_at', '>=', $request->date_debut);
            }

            if ($request->filled('date_fin')) {
                $query->whereDate('created_at', '<=', $request->date_fin);
            }

            $printLogs = $query->get();

            $filename = 'print_logs_' . now()->format('Y-m-d_H-i-s') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($printLogs) {
                $file = fopen('php://output', 'w');
                
                // En-têtes CSV
                fputcsv($file, [
                    'ID',
                    'Date',
                    'Imprimante',
                    'Utilisateur',
                    'Type Document',
                    'Référence',
                    'Statut',
                    'Tentatives',
                    'Durée (s)',
                    'Erreur'
                ]);

                // Données
                foreach ($printLogs as $log) {
                    fputcsv($file, [
                        $log->id,
                        $log->created_at->format('d/m/Y H:i:s'),
                        $log->printer->name ?? 'N/A',
                        $log->user->name ?? 'N/A',
                        $log->type_document_label,
                        $log->reference,
                        $log->statut_label,
                        $log->tentatives,
                        $log->duree_impression ?? 'N/A',
                        $log->erreur ?? 'N/A'
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'export: ' . $e->getMessage()
            ], 500);
        }
    }
}