<?php

namespace App\Console\Commands;

use App\Jobs\CheckPrinterStatus;
use App\Models\Printer;
use Illuminate\Console\Command;

class CheckPrintersStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'printers:check-status {--printer= : ID de l\'imprimante spécifique à vérifier}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Vérifier l\'état de toutes les imprimantes ou d\'une imprimante spécifique';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $printerId = $this->option('printer');

        if ($printerId) {
            // Vérifier une imprimante spécifique
            $printer = Printer::find($printerId);

            if (!$printer) {
                $this->error("Imprimante #{$printerId} introuvable.");
                return 1;
            }

            $this->info("Vérification de l'imprimante: {$printer->name}");
            CheckPrinterStatus::dispatch($printer);
            $this->info("Vérification lancée pour l'imprimante: {$printer->name}");

        } else {
            // Vérifier toutes les imprimantes actives
            $printers = Printer::where('is_active', true)->get();

            if ($printers->isEmpty()) {
                $this->warn("Aucune imprimante active trouvée.");
                return 0;
            }

            $this->info("Vérification de {$printers->count()} imprimante(s) active(s)...");

            $bar = $this->output->createProgressBar($printers->count());
            $bar->start();

            foreach ($printers as $printer) {
                CheckPrinterStatus::dispatchSync($printer);
                $bar->advance();
            }

            $bar->finish();
            $this->newLine(2);

            // Afficher un résumé
            $this->displaySummary();
        }

        return 0;
    }

    /**
     * Afficher un résumé de l'état des imprimantes
     */
    protected function displaySummary()
    {
        $printers = Printer::where('is_active', true)->get();

        $online = $printers->where('connection_status', 'online')->count();
        $offline = $printers->where('connection_status', 'offline')->count();
        $checking = $printers->where('connection_status', 'checking')->count();

        $this->table(
            ['Statut', 'Nombre'],
            [
                ['<fg=green>En ligne</>', $online],
                ['<fg=red>Hors ligne</>', $offline],
                ['<fg=yellow>Vérification...</>', $checking],
                ['<fg=blue>Total</>', $printers->count()],
            ]
        );

        // Afficher les imprimantes hors ligne
        if ($offline > 0) {
            $this->newLine();
            $this->warn("Imprimantes hors ligne:");
            
            $offlinePrinters = $printers->where('connection_status', 'offline');
            
            $data = [];
            foreach ($offlinePrinters as $printer) {
                $data[] = [
                    $printer->id,
                    $printer->name,
                    $printer->ip_address . ':' . $printer->port,
                    $printer->failed_checks_count . ' échec(s)',
                    $printer->last_checked_at?->diffForHumans() ?? 'Jamais',
                ];
            }
            
            $this->table(
                ['ID', 'Nom', 'Adresse', 'Échecs', 'Dernière vérif.'],
                $data
            );
        }
    }
}
