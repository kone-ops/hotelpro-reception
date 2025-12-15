<?php

namespace App\Services;

use App\Models\Printer;
use App\Models\PrintLog;
use Illuminate\Support\Facades\Log;

/**
 * Service d'impression simplifié - USB uniquement, imprimantes non-thermiques
 */
class PrintService
{
    /**
     * Imprimer un document sur l'imprimante par défaut de l'hôtel
     */
    public function printToDefault(int $hotelId, string $content, string $module = null): array
    {
        try {
            // Récupérer l'imprimante par défaut USB
            $printer = Printer::where('hotel_id', $hotelId)
                ->where('type', 'usb')
                ->where('is_active', true)
                ->where('is_default', true)
                ->first();
            
            if (!$printer) {
                return [
                    'success' => false,
                    'message' => 'Aucune imprimante USB par défaut configurée pour cet hôtel.',
                    'error_code' => 'NO_DEFAULT_PRINTER'
                ];
            }
            
            return $this->print($printer, $content);
            
        } catch (\Exception $e) {
            Log::error('Erreur PrintService::printToDefault', [
                'hotel_id' => $hotelId,
                'module' => $module,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Erreur lors de l\'impression: ' . $e->getMessage(),
                'error_code' => 'PRINT_EXCEPTION'
            ];
        }
    }
    
    /**
     * Imprimer un document sur une imprimante USB spécifique
     */
    public function print(Printer $printer, string $content, string $documentType = 'document'): array
    {
        try {
            // Vérifier que c'est une imprimante USB
            if ($printer->type !== 'usb') {
                return [
                    'success' => false,
                    'message' => "Seules les imprimantes USB sont supportées.",
                    'error_code' => 'INVALID_PRINTER_TYPE'
                ];
            }
            
            // Vérifier que l'imprimante est active
            if (!$printer->is_active) {
                return [
                    'success' => false,
                    'message' => "L'imprimante {$printer->name} est inactive.",
                    'error_code' => 'PRINTER_INACTIVE'
                ];
            }
            
            // Envoyer le document à l'imprimante système Windows
            $success = $this->sendToWindowsPrinter($printer->name, $content);
            
            // Logger l'impression
            $this->logPrint($printer, $documentType, $success);
            
            if ($success) {
                return [
                    'success' => true,
                    'message' => "Document envoyé avec succès à {$printer->name}",
                    'printer' => $printer
                ];
            }
            
            return [
                'success' => false,
                'message' => "Échec de l'envoi à l'imprimante {$printer->name}",
                'error_code' => 'SEND_FAILED'
            ];
            
        } catch (\Exception $e) {
            Log::error('Erreur PrintService::print', [
                'printer_id' => $printer->id,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage(),
                'error_code' => 'PRINT_EXCEPTION'
            ];
        }
    }
    
    /**
     * Envoyer un document à une imprimante Windows via COM
     */
    protected function sendToWindowsPrinter(string $printerName, string $content): bool
    {
        try {
            // Créer un fichier temporaire
            $tempFile = tempnam(sys_get_temp_dir(), 'print_');
            file_put_contents($tempFile, $content);
            
            // Utiliser la commande Windows print
            $command = sprintf('print /D:"%s" "%s"', $printerName, $tempFile);
            
            // Exécuter la commande
            exec($command, $output, $returnCode);
            
            // Supprimer le fichier temporaire
            @unlink($tempFile);
            
            return $returnCode === 0;
            
        } catch (\Exception $e) {
            Log::error('Erreur sendToWindowsPrinter', [
                'printer' => $printerName,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Logger l'impression
     */
    protected function logPrint(Printer $printer, string $documentType, bool $success): void
    {
        try {
            PrintLog::create([
                'printer_id' => $printer->id,
                'hotel_id' => $printer->hotel_id,
                'user_id' => auth()->id(),
                'document_type' => $documentType,
                'status' => $success ? 'success' : 'failed',
                'printed_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur logPrint', ['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Lister les imprimantes USB disponibles
     */
    public function getAvailableUsbPrinters(int $hotelId): array
    {
        return Printer::where('hotel_id', $hotelId)
            ->where('type', 'usb')
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->toArray();
    }
    
    /**
     * Tester une imprimante
     */
    public function testPrinter(Printer $printer): array
    {
        $testContent = "=== TEST D'IMPRESSION ===\n";
        $testContent .= "Imprimante: {$printer->name}\n";
        $testContent .= "Date: " . now()->format('d/m/Y H:i:s') . "\n";
        $testContent .= "Hôtel: " . $printer->hotel->name . "\n";
        $testContent .= "========================\n";
        
        return $this->print($printer, $testContent, 'test');
    }
}

