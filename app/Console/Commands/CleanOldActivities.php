<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ActivityLog;
use Carbon\Carbon;

class CleanOldActivities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activities:clean {--hours=24 : Nombre d\'heures après lesquelles les activités sont supprimées}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Nettoyer les activités de plus de 24 heures (par défaut)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hours = $this->option('hours');
        
        $this->info("🧹 Nettoyage des activités de plus de {$hours} heures...");
        
        // Calculer la date limite
        $threshold = Carbon::now()->subHours($hours);
        
        // Compter les activités à supprimer
        $count = ActivityLog::where('created_at', '<', $threshold)->count();
        
        if ($count === 0) {
            $this->info('✅ Aucune activité à nettoyer.');
            return 0;
        }
        
        // Afficher une barre de progression
        $bar = $this->output->createProgressBar($count);
        $bar->start();
        
        // Supprimer les anciennes activités par lots pour optimiser
        ActivityLog::where('created_at', '<', $threshold)
            ->chunkById(100, function ($activities) use ($bar) {
                foreach ($activities as $activity) {
                    $activity->delete();
                    $bar->advance();
                }
            });
        
        $bar->finish();
        $this->newLine();
        
        $this->info("✅ {$count} activité(s) supprimée(s) avec succès !");
        $this->comment("🕒 Activités antérieures à : {$threshold->format('d/m/Y H:i:s')}");
        
        return 0;
    }
}
