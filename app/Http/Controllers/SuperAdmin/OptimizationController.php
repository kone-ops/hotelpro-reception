<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class OptimizationController extends Controller
{
    /**
     * Afficher la page d'optimisation
     */
    public function index()
    {
        // Statistiques système
        $stats = [
            'cache_size' => $this->getCacheSize(),
            'log_size' => $this->getLogSize(),
            'session_count' => DB::table('sessions')->count(),
            'old_sessions' => DB::table('sessions')
                ->where('last_activity', '<', now()->subDays(30)->timestamp)
                ->count(),
            'database_size' => $this->getDatabaseSize(),
        ];

        return view('super.optimization.index', compact('stats'));
    }

    /**
     * Vider les caches
     */
    public function clearCaches(Request $request)
    {
        try {
            // Vider tous les caches Laravel
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            
            // Vider le cache de l'application
            Cache::flush();

            Log::info('Caches vidés par ' . auth()->user()->name);

            return redirect()->route('super.optimization.index')
                ->with('success', 'Tous les caches ont été vidés avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur lors du vidage des caches: ' . $e->getMessage());
            
            return redirect()->route('super.optimization.index')
                ->with('error', 'Une erreur est survenue lors du vidage des caches.');
        }
    }

    /**
     * Optimiser la base de données
     */
    public function optimizeDatabase(Request $request)
    {
        try {
            $connection = DB::connection()->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME);
            
            if ($connection === 'mysql') {
                // Pour MySQL
                $tables = DB::select('SHOW TABLES');
                $databaseName = DB::connection()->getDatabaseName();
                $tableKey = 'Tables_in_' . $databaseName;
                
                foreach ($tables as $table) {
                    $tableName = $table->$tableKey;
                    DB::statement("OPTIMIZE TABLE `{$tableName}`");
                }
            } elseif ($connection === 'sqlite') {
                // Pour SQLite
                DB::statement('VACUUM');
            } elseif ($connection === 'pgsql') {
                // Pour PostgreSQL
                DB::statement('VACUUM ANALYZE');
            }

            Log::info('Base de données optimisée par ' . auth()->user()->name);

            return redirect()->route('super.optimization.index')
                ->with('success', 'La base de données a été optimisée avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'optimisation de la base de données: ' . $e->getMessage());
            
            return redirect()->route('super.optimization.index')
                ->with('error', 'Une erreur est survenue lors de l\'optimisation de la base de données.');
        }
    }

    /**
     * Nettoyer les anciennes données
     */
    public function cleanOldData(Request $request)
    {
        try {
            $deleted = 0;
            
            // Supprimer les sessions expirées (plus de 30 jours)
            $deleted += DB::table('sessions')
                ->where('last_activity', '<', now()->subDays(30)->timestamp)
                ->delete();
            
            // Supprimer les logs d'activité anciens (plus de 90 jours)
            if (DB::getSchemaBuilder()->hasTable('activity_logs')) {
                $deleted += DB::table('activity_logs')
                    ->where('created_at', '<', now()->subDays(90))
                    ->delete();
            }
            
            // Supprimer les notifications lues anciennes (plus de 60 jours)
            if (DB::getSchemaBuilder()->hasTable('user_notifications')) {
                $deleted += DB::table('user_notifications')
                    ->where('read', true)
                    ->where('read_at', '<', now()->subDays(60))
                    ->delete();
            }

            Log::info('Nettoyage des anciennes données effectué par ' . auth()->user()->name . ' - ' . $deleted . ' enregistrements supprimés');

            return redirect()->route('super.optimization.index')
                ->with('success', "Nettoyage terminé : {$deleted} enregistrement(s) supprimé(s).");
        } catch (\Exception $e) {
            Log::error('Erreur lors du nettoyage des anciennes données: ' . $e->getMessage());
            
            return redirect()->route('super.optimization.index')
                ->with('error', 'Une erreur est survenue lors du nettoyage des anciennes données.');
        }
    }

    /**
     * Optimisation complète
     */
    public function fullOptimization(Request $request)
    {
        try {
            // Vider les caches
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            Cache::flush();

            // Optimiser la base de données
            $connection = DB::connection()->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME);
            if ($connection === 'mysql') {
                $tables = DB::select('SHOW TABLES');
                $databaseName = DB::connection()->getDatabaseName();
                $tableKey = 'Tables_in_' . $databaseName;
                foreach ($tables as $table) {
                    $tableName = $table->$tableKey;
                    DB::statement("OPTIMIZE TABLE `{$tableName}`");
                }
            } elseif ($connection === 'sqlite') {
                DB::statement('VACUUM');
            } elseif ($connection === 'pgsql') {
                DB::statement('VACUUM ANALYZE');
            }

            // Nettoyer les anciennes données
            $deleted = 0;
            $deleted += DB::table('sessions')
                ->where('last_activity', '<', now()->subDays(30)->timestamp)
                ->delete();

            Log::info('Optimisation complète effectuée par ' . auth()->user()->name);

            return redirect()->route('super.optimization.index')
                ->with('success', 'Optimisation complète terminée avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'optimisation complète: ' . $e->getMessage());
            
            return redirect()->route('super.optimization.index')
                ->with('error', 'Une erreur est survenue lors de l\'optimisation complète.');
        }
    }

    /**
     * Obtenir la taille du cache
     */
    private function getCacheSize()
    {
        try {
            $cachePath = storage_path('framework/cache');
            if (is_dir($cachePath)) {
                $size = 0;
                $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($cachePath));
                foreach ($files as $file) {
                    if ($file->isFile()) {
                        $size += $file->getSize();
                    }
                }
                return $this->formatBytes($size);
            }
        } catch (\Exception $e) {
            // Ignorer les erreurs
        }
        return 'N/A';
    }

    /**
     * Obtenir la taille des logs
     */
    private function getLogSize()
    {
        try {
            $logPath = storage_path('logs');
            if (is_dir($logPath)) {
                $size = 0;
                $files = glob($logPath . '/*.log');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        $size += filesize($file);
                    }
                }
                return $this->formatBytes($size);
            }
        } catch (\Exception $e) {
            // Ignorer les erreurs
        }
        return 'N/A';
    }

    /**
     * Obtenir la taille de la base de données
     */
    private function getDatabaseSize()
    {
        try {
            $connection = DB::connection()->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME);
            
            if ($connection === 'mysql') {
                $result = DB::select("SELECT 
                    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
                    FROM information_schema.tables 
                    WHERE table_schema = ?", [DB::connection()->getDatabaseName()]);
                return isset($result[0]) ? $result[0]->size_mb . ' MB' : 'N/A';
            } elseif ($connection === 'sqlite') {
                $dbPath = database_path('database.sqlite');
                if (file_exists($dbPath)) {
                    return $this->formatBytes(filesize($dbPath));
                }
            } elseif ($connection === 'pgsql') {
                $result = DB::select("SELECT pg_size_pretty(pg_database_size(?)) AS size", [DB::connection()->getDatabaseName()]);
                return isset($result[0]) ? $result[0]->size : 'N/A';
            }
        } catch (\Exception $e) {
            // Ignorer les erreurs
        }
        return 'N/A';
    }

    /**
     * API: Récupérer les statistiques en temps réel
     */
    public function getStats(Request $request)
    {
        try {
            $stats = [
                'cache_size' => $this->getCacheSize(),
                'log_size' => $this->getLogSize(),
                'session_count' => DB::table('sessions')->count(),
                'old_sessions' => DB::table('sessions')
                    ->where('last_activity', '<', now()->subDays(30)->timestamp)
                    ->count(),
                'database_size' => $this->getDatabaseSize(),
                'timestamp' => now()->toIso8601String(),
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats,
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des stats: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération des statistiques',
            ], 500);
        }
    }

    /**
     * Formater les bytes en format lisible
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
