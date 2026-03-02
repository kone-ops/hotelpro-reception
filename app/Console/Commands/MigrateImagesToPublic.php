<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class MigrateImagesToPublic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:migrate-to-public {--dry-run : Afficher les actions sans les exécuter}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrer les images de storage/app/public vers public/images/';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('🔍 MODE DRY-RUN : Aucune modification ne sera effectuée');
        } else {
            $this->info('🔄 Migration des images vers public/images/...');
        }

        $this->info('');
        
        // 1. Créer la structure de dossiers
        $directories = [
            public_path('images'),
            public_path('images/logos'),
            public_path('images/uploads'),
            public_path('images/uploads/documents'),
        ];
        
        foreach ($directories as $dir) {
            if (!File::exists($dir)) {
                if (!$dryRun) {
                    File::makeDirectory($dir, 0755, true);
                }
                $this->info("✅ Création du répertoire: {$dir}");
            }
        }

        // 2. Migrer les logos des hôtels
        $this->info('');
        $this->info('📁 Migration des logos d\'hôtels...');
        $this->migrateHotelLogos($dryRun);

        // 3. Migrer les documents d'identité
        $this->info('');
        $this->info('📁 Migration des documents d\'identité...');
        $this->migrateIdentityDocuments($dryRun);

        // 4. Migrer les images statiques Template/
        $this->info('');
        $this->info('📁 Migration des images statiques...');
        $this->migrateStaticImages($dryRun);

        $this->info('');
        if ($dryRun) {
            $this->warn('💡 Exécutez sans --dry-run pour appliquer les changements');
        } else {
            $this->info('✅ Migration terminée !');
        }

        return 0;
    }

    protected function migrateHotelLogos(bool $dryRun)
    {
        $hotels = DB::table('hotels')->whereNotNull('logo')->get();
        $migrated = 0;
        $errors = 0;

        foreach ($hotels as $hotel) {
            $oldPath = null;
            $newPath = 'images/logos/' . basename($hotel->logo);
            
            // Chercher le fichier dans storage/app/public
            if (strpos($hotel->logo, 'storage/') === 0) {
                $oldPath = storage_path('app/public/' . str_replace('storage/', '', $hotel->logo));
            } elseif (strpos($hotel->logo, 'hotels/') === 0 || strpos($hotel->logo, 'images/') === 0) {
                // Déjà dans le bon format ou déjà migré
                if (strpos($hotel->logo, 'images/') === 0) {
                    continue; // Déjà migré
                }
                $oldPath = storage_path('app/public/' . $hotel->logo);
            } else {
                // Essayer directement
                $oldPath = storage_path('app/public/' . $hotel->logo);
            }

            if (!$oldPath || !File::exists($oldPath)) {
                $this->warn("  ⚠️  Hotel {$hotel->id}: Fichier non trouvé: {$oldPath}");
                $errors++;
                continue;
            }

            $newFullPath = public_path($newPath);

            if ($dryRun) {
                $this->line("  Hotel {$hotel->id}: {$oldPath} → {$newPath}");
            } else {
                // Copier le fichier
                if (File::copy($oldPath, $newFullPath)) {
                    // Mettre à jour la base de données
                    DB::table('hotels')
                        ->where('id', $hotel->id)
                        ->update(['logo' => $newPath]);
                    $migrated++;
                } else {
                    $this->error("  ❌ Hotel {$hotel->id}: Erreur lors de la copie");
                    $errors++;
                }
            }
        }

        $this->info("  ✅ {$migrated} logo(s) migré(s)" . ($errors > 0 ? ", {$errors} erreur(s)" : ""));
    }

    protected function migrateIdentityDocuments(bool $dryRun)
    {
        $documents = DB::table('identity_documents')
            ->where(function($q) {
                $q->whereNotNull('front_path')
                  ->orWhereNotNull('back_path');
            })
            ->get();
        
        $migrated = 0;
        $errors = 0;

        foreach ($documents as $doc) {
            $updates = [];

            // Front path
            if ($doc->front_path && strpos($doc->front_path, 'images/') !== 0) {
                $oldPath = storage_path('app/public/' . str_replace('storage/', '', $doc->front_path));
                $newPath = 'images/uploads/documents/' . basename($doc->front_path);
                
                if (File::exists($oldPath)) {
                    if ($dryRun) {
                        $this->line("  Document {$doc->id} (front): {$oldPath} → {$newPath}");
                    } else {
                        $newFullPath = public_path($newPath);
                        if (File::copy($oldPath, $newFullPath)) {
                            $updates['front_path'] = $newPath;
                        } else {
                            $errors++;
                        }
                    }
                }
            }

            // Back path
            if ($doc->back_path && strpos($doc->back_path, 'images/') !== 0) {
                $oldPath = storage_path('app/public/' . str_replace('storage/', '', $doc->back_path));
                $newPath = 'images/uploads/documents/' . basename($doc->back_path);
                
                if (File::exists($oldPath)) {
                    if ($dryRun) {
                        $this->line("  Document {$doc->id} (back): {$oldPath} → {$newPath}");
                    } else {
                        $newFullPath = public_path($newPath);
                        if (File::copy($oldPath, $newFullPath)) {
                            $updates['back_path'] = $newPath;
                        } else {
                            $errors++;
                        }
                    }
                }
            }

            if (!empty($updates) && !$dryRun) {
                DB::table('identity_documents')
                    ->where('id', $doc->id)
                    ->update($updates);
                $migrated++;
            }
        }

        $this->info("  ✅ {$migrated} document(s) migré(s)" . ($errors > 0 ? ", {$errors} erreur(s)" : ""));
    }

    protected function migrateStaticImages(bool $dryRun)
    {
        $templateDir = public_path('Template');
        $imagesDir = public_path('images');

        if (!File::exists($templateDir)) {
            $this->info("  ℹ️  Le répertoire Template n'existe pas, rien à migrer");
            return;
        }

        $files = File::files($templateDir);
        $migrated = 0;

        foreach ($files as $file) {
            $filename = $file->getFilename();
            $newPath = $imagesDir . '/' . $filename;

            if ($dryRun) {
                $this->line("  Template/{$filename} → images/{$filename}");
            } else {
                if (File::copy($file->getPathname(), $newPath)) {
                    $migrated++;
                }
            }
        }

        if (!$dryRun && $migrated > 0) {
            $this->info("  ✅ {$migrated} fichier(s) statique(s) migré(s)");
            $this->warn("  💡 Vous pouvez maintenant supprimer le dossier Template/ s'il est vide");
        } elseif ($dryRun) {
            $this->info("  📋 {$migrated} fichier(s) à migrer");
        }
    }
}

