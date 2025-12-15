<?php

namespace App\Jobs;

use App\Models\Printer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class CheckPrinterStatus implements ShouldQueue
{
    use Queueable;

    protected $printer;

    /**
     * Create a new job instance.
     */
    public function __construct(Printer $printer = null)
    {
        $this->printer = $printer;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Si une imprimante spécifique est fournie, la vérifier
        if ($this->printer) {
            $this->checkPrinter($this->printer);
            return;
        }

        // Sinon, vérifier toutes les imprimantes actives
        $printers = Printer::where('is_active', true)->get();

        Log::info("Vérification de l'état de {$printers->count()} imprimante(s)");

        foreach ($printers as $printer) {
            $this->checkPrinter($printer);
        }
    }

    /**
     * Vérifier l'état d'une imprimante
     */
    protected function checkPrinter(Printer $printer): void
    {
        $startTime = microtime(true);

        // Tester la connexion
        $isOnline = $this->testPrinterConnection($printer);

        // Calculer le temps de réponse
        $responseTime = round((microtime(true) - $startTime) * 1000); // en millisecondes

        // Déterminer le nouveau statut
        $newStatus = $isOnline ? 'online' : 'offline';

        // Incrémenter ou réinitialiser le compteur d'échecs
        $failedCount = $isOnline ? 0 : ($printer->failed_checks_count + 1);

        // Mettre à jour le statut de l'imprimante
        $printer->update([
            'connection_status' => $newStatus,
            'last_checked_at' => now(),
            'response_time_ms' => $isOnline ? $responseTime : null,
            'failed_checks_count' => $failedCount,
        ]);

        Log::info("Imprimante vérifiée", [
            'printer_id' => $printer->id,
            'name' => $printer->name,
            'status' => $newStatus,
            'response_time_ms' => $responseTime,
            'failed_checks' => $failedCount,
        ]);

        // Si l'imprimante est hors ligne depuis 3 vérifications consécutives,
        // désactiver automatiquement (optionnel)
        if ($failedCount >= 5) {
            Log::warning("Imprimante désactivée après {$failedCount} échecs consécutifs", [
                'printer_id' => $printer->id,
                'name' => $printer->name,
            ]);
            // Optionnel: désactiver l'imprimante
            // $printer->update(['is_active' => false]);
        }
    }

    /**
     * Tester la connexion à l'imprimante
     */
    protected function testPrinterConnection(Printer $printer): bool
    {
        try {
            $port = $printer->port ?? 9100;
            $timeout = 2; // 2 secondes

            // Utiliser fsockopen pour tester la connexion
            $connection = @fsockopen($printer->ip_address, $port, $errno, $errstr, $timeout);

            if ($connection) {
                fclose($connection);
                return true;
            }

            return false;

        } catch (\Exception $e) {
            Log::error("Erreur lors du test de connexion de l'imprimante", [
                'printer_id' => $printer->id,
                'ip' => $printer->ip_address,
                'port' => $printer->port ?? 9100,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
