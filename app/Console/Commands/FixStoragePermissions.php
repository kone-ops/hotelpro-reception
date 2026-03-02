<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class FixStoragePermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:fix-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix storage permissions and recreate storage link';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Correction des permissions et du lien storage...');

        // 1. Créer le répertoire public si nécessaire
        $publicPath = storage_path('app/public');
        if (!File::exists($publicPath)) {
            File::makeDirectory($publicPath, 0755, true);
            $this->info('✅ Répertoire storage/app/public créé');
        }

        // 2. Corriger les permissions des répertoires
        $directories = [
            storage_path('app/public'),
            storage_path('app/public/hotels'),
            storage_path('app/public/hotels/logos'),
            storage_path('app/public/identity_documents'),
            storage_path('app/public/documents'),
        ];

        foreach ($directories as $dir) {
            if (!File::exists($dir)) {
                File::makeDirectory($dir, 0755, true);
                $this->info("✅ Répertoire créé: {$dir}");
            } else {
                chmod($dir, 0755);
            }
        }

        // 3. Corriger les permissions des fichiers
        $files = File::allFiles(storage_path('app/public'));
        foreach ($files as $file) {
            chmod($file->getPathname(), 0644);
        }
        $this->info('✅ Permissions des fichiers corrigées (644)');

        // 4. Supprimer l'ancien lien symbolique s'il existe
        $linkPath = public_path('storage');
        if (is_link($linkPath)) {
            unlink($linkPath);
            $this->info('✅ Ancien lien symbolique supprimé');
        } elseif (File::exists($linkPath)) {
            $this->warn('⚠️  public/storage existe mais n\'est pas un lien symbolique');
        }

        // 5. Créer le nouveau lien symbolique
        try {
            $this->call('storage:link');
            $this->info('✅ Lien symbolique storage créé');
        } catch (\Exception $e) {
            // Si le lien existe déjà, c'est OK
            if (is_link($linkPath)) {
                $this->info('✅ Lien symbolique storage existe déjà');
            } else {
                $this->error('❌ Erreur lors de la création du lien: ' . $e->getMessage());
            }
        }

        // 6. Vérifier que le lien fonctionne
        if (is_link($linkPath)) {
            $target = readlink($linkPath);
            $expectedTarget = storage_path('app/public');
            if ($target === $expectedTarget || realpath($target) === realpath($expectedTarget)) {
                $this->info('✅ Lien symbolique pointe vers le bon répertoire');
            } else {
                $this->warn("⚠️  Le lien symbolique pointe vers: {$target}");
                $this->warn("   Attendu: {$expectedTarget}");
            }
        }

        // 7. Test de lecture d'un fichier
        $testFiles = [
            'hotels/logos',
            'identity_documents',
        ];

        foreach ($testFiles as $testDir) {
            $fullPath = storage_path("app/public/{$testDir}");
            if (File::exists($fullPath)) {
                $count = count(File::files($fullPath));
                $this->info("📁 {$testDir}: {$count} fichier(s) trouvé(s)");
            }
        }

        $this->info('');
        $this->info('✅ Correction terminée !');
        $this->info('');
        $this->warn('💡 Si les images ne s\'affichent toujours pas, vérifiez:');
        $this->warn('   1. APP_URL dans .env est correct (ex: http://votre-domaine.com)');
        $this->warn('   2. Les permissions du serveur web (nginx/apache) permettent de lire storage/app/public');
        $this->warn('   3. Le serveur web suit les liens symboliques (Options +FollowSymLinks)');

        return 0;
    }
}

