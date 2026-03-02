<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FixAbsolutePaths extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'paths:fix-absolute {--dry-run : Afficher les chemins à corriger sans les modifier}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corriger tous les chemins absolus dans la base de données pour les convertir en chemins relatifs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('🔍 MODE DRY-RUN : Aucune modification ne sera effectuée');
        } else {
            $this->info('🔧 Correction des chemins absolus dans la base de données...');
        }

        $totalFixed = 0;

        // 1. Corriger les logos des hôtels
        $this->info('');
        $this->info('📁 Vérification des logos des hôtels...');
        $hotels = DB::table('hotels')->whereNotNull('logo')->get();
        $hotelsFixed = 0;
        
        foreach ($hotels as $hotel) {
            $fixedPath = $this->fixPath($hotel->logo);
            if ($fixedPath !== $hotel->logo) {
                $this->line("  Hotel ID {$hotel->id}:");
                $this->line("    Ancien: {$hotel->logo}");
                $this->line("    Nouveau: {$fixedPath}");
                
                if (!$dryRun) {
                    DB::table('hotels')
                        ->where('id', $hotel->id)
                        ->update(['logo' => $fixedPath]);
                }
                $hotelsFixed++;
            }
        }
        
        if ($hotelsFixed > 0) {
            $this->info("  ✅ {$hotelsFixed} logo(s) d'hôtel(s) corrigé(s)");
            $totalFixed += $hotelsFixed;
        } else {
            $this->info("  ✅ Aucun chemin absolu trouvé dans les logos d'hôtels");
        }

        // 2. Corriger les documents d'identité
        $this->info('');
        $this->info('📁 Vérification des documents d\'identité...');
        $documents = DB::table('identity_documents')
            ->where(function($q) {
                $q->whereNotNull('front_path')
                  ->orWhereNotNull('back_path');
            })
            ->get();
        
        $docsFixed = 0;
        foreach ($documents as $doc) {
            $updates = [];
            
            if ($doc->front_path) {
                $fixedFront = $this->fixPath($doc->front_path);
                if ($fixedFront !== $doc->front_path) {
                    $this->line("  Document ID {$doc->id} (front_path):");
                    $this->line("    Ancien: {$doc->front_path}");
                    $this->line("    Nouveau: {$fixedFront}");
                    $updates['front_path'] = $fixedFront;
                    $docsFixed++;
                }
            }
            
            if ($doc->back_path) {
                $fixedBack = $this->fixPath($doc->back_path);
                if ($fixedBack !== $doc->back_path) {
                    $this->line("  Document ID {$doc->id} (back_path):");
                    $this->line("    Ancien: {$doc->back_path}");
                    $this->line("    Nouveau: {$fixedBack}");
                    $updates['back_path'] = $fixedBack;
                    $docsFixed++;
                }
            }
            
            if (!empty($updates) && !$dryRun) {
                DB::table('identity_documents')
                    ->where('id', $doc->id)
                    ->update($updates);
            }
        }
        
        if ($docsFixed > 0) {
            $this->info("  ✅ {$docsFixed} chemin(s) de document(s) corrigé(s)");
            $totalFixed += $docsFixed;
        } else {
            $this->info("  ✅ Aucun chemin absolu trouvé dans les documents d'identité");
        }

        // 3. Corriger les logos d'imprimantes (si la table existe — module imprimantes optionnel)
        $this->info('');
        $this->info('📁 Vérification des logos d\'imprimantes...');
        if (!Schema::hasTable('printers')) {
            $this->info("  ⏭️  Table printers absente (module retiré), ignoré.");
        } else {
            $printers = DB::table('printers')->whereNotNull('logo_path')->get();
            $printersFixed = 0;

            foreach ($printers as $printer) {
                $fixedPath = $this->fixPath($printer->logo_path);
                if ($fixedPath !== $printer->logo_path) {
                    $this->line("  Printer ID {$printer->id}:");
                    $this->line("    Ancien: {$printer->logo_path}");
                    $this->line("    Nouveau: {$fixedPath}");

                    if (!$dryRun) {
                        DB::table('printers')
                            ->where('id', $printer->id)
                            ->update(['logo_path' => $fixedPath]);
                    }
                    $printersFixed++;
                }
            }

            if ($printersFixed > 0) {
                $this->info("  ✅ {$printersFixed} logo(s) d'imprimante(s) corrigé(s)");
                $totalFixed += $printersFixed;
            } else {
                $this->info("  ✅ Aucun chemin absolu trouvé dans les logos d'imprimantes");
            }
        }

        // Résumé
        $this->info('');
        if ($dryRun) {
            if ($totalFixed > 0) {
                $this->warn("⚠️  {$totalFixed} chemin(s) seraient corrigé(s) en exécutant cette commande sans --dry-run");
            } else {
                $this->info('✅ Aucun chemin absolu détecté dans la base de données');
            }
        } else {
            if ($totalFixed > 0) {
                $this->info("✅ {$totalFixed} chemin(s) corrigé(s) avec succès !");
            } else {
                $this->info('✅ Aucun chemin absolu trouvé dans la base de données');
            }
        }

        return 0;
    }

    /**
     * Convertir un chemin absolu en chemin relatif
     *
     * @param string $path
     * @return string
     */
    protected function fixPath(string $path): string
    {
        // Si c'est déjà un chemin relatif, le retourner tel quel
        if (!$this->isAbsolutePath($path)) {
            return $path;
        }

        // Normaliser les séparateurs de chemins
        $normalizedPath = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $path);

        // Patterns de chemins absolus à détecter et convertir
        $patterns = [
            // Linux/Mac: /media/bachir/.../storage/app/public/...
            '/^.*storage[\/\\\\]app[\/\\\\]public[\/\\\\](.+)$/i',
            // Windows: C:\...\storage\app\public\...
            '/^[A-Z]:[\/\\\\].*storage[\/\\\\]app[\/\\\\]public[\/\\\\](.+)$/i',
            // Chemins avec public/storage
            '/^.*public[\/\\\\]storage[\/\\\\](.+)$/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $normalizedPath, $matches)) {
                // Extraire le chemin relatif après storage/app/public ou public/storage
                $relativePath = $matches[1];
                // Normaliser les séparateurs pour Unix (utilisé par Laravel)
                $relativePath = str_replace('\\', '/', $relativePath);
                return $relativePath;
            }
        }

        // Si aucun pattern ne correspond, essayer d'extraire après le dernier "storage/app/public"
        $lastPos = strrpos($normalizedPath, 'storage' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);
        if ($lastPos !== false) {
            $relativePath = substr($normalizedPath, $lastPos + strlen('storage' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR));
            $relativePath = str_replace('\\', '/', $relativePath);
            return $relativePath;
        }

        // Si on ne peut pas convertir, retourner tel quel (sera logué)
        return $path;
    }

    /**
     * Vérifier si un chemin est absolu
     *
     * @param string $path
     * @return bool
     */
    protected function isAbsolutePath(string $path): bool
    {
        // Windows: C:\ ou D:\ etc.
        if (preg_match('/^[A-Z]:[\/\\\\]/i', $path)) {
            return true;
        }

        // Unix/Linux/Mac: commence par /
        if (strpos($path, '/') === 0) {
            return true;
        }

        return false;
    }
}

