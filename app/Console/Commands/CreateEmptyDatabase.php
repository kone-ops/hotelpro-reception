<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class CreateEmptyDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:create-empty {--name=database_vide.sqlite : Nom de la base de données}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crée une base de données vierge avec uniquement la structure et les données essentielles';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dbName = $this->option('name');
        $dbPath = database_path($dbName);

        $this->info('🗄️  Création d\'une base de données vierge...');
        $this->info("📁 Nom du fichier: {$dbName}");

        // Vérifier si le fichier existe déjà
        if (File::exists($dbPath)) {
            if (!$this->confirm("⚠️  Le fichier {$dbName} existe déjà. Voulez-vous le supprimer et en créer un nouveau ?", false)) {
                $this->warn('❌ Opération annulée.');
                return 1;
            }
            File::delete($dbPath);
            $this->info('✅ Ancien fichier supprimé.');
        }

        // Sauvegarder l'ancienne configuration
        $originalDatabase = config('database.connections.sqlite.database');
        
        try {
            // Créer le fichier SQLite vide
            File::put($dbPath, '');
            $this->info('✅ Fichier de base de données créé.');

            // Configurer Laravel pour utiliser cette base temporairement
            config(['database.connections.sqlite.database' => $dbPath]);
            DB::purge('sqlite');
            DB::reconnect('sqlite');

            $this->info('🔧 Exécution des migrations...');
            
            // Exécuter migrate:fresh (supprime toutes les tables puis recrée) pour une base vraiment vierge
            Artisan::call('migrate:fresh', [
                '--database' => 'sqlite',
                '--force' => true,
            ]);

            $this->info('✅ Migrations exécutées avec succès.');

            // Créer les données essentielles
            $this->info('📦 Création des données essentielles...');
            
            $this->createEssentialData();

            $this->info('');
            $this->info('✅ Base de données vierge créée avec succès !');
            $this->info('');
            $this->info("📁 Fichier: {$dbPath}");
            $this->info('');
            $this->warn('💡 Pour utiliser cette base de données:');
            $this->warn('   1. Modifiez .env: DB_DATABASE=' . $dbName);
            $this->warn('   2. Ou copiez le fichier vers votre base de production');
            $this->info('');

            return 0;

        } catch (\Exception $e) {
            // Restaurer la configuration originale en cas d'erreur
            config(['database.connections.sqlite.database' => $originalDatabase]);
            DB::purge('sqlite');
            
            $this->error('❌ Erreur lors de la création: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            
            // Supprimer le fichier en cas d'erreur
            if (File::exists($dbPath)) {
                File::delete($dbPath);
            }
            
            return 1;
        } finally {
            // Restaurer la configuration originale
            config(['database.connections.sqlite.database' => $originalDatabase]);
            DB::purge('sqlite');
        }
    }

    /**
     * Créer les données essentielles (rôles, permissions, super-admin)
     */
    protected function createEssentialData()
    {
        // Créer les rôles et permissions (sans créer d'hôtel de test)
        $this->line('  → Création des rôles et permissions...');
        
        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin']);
        $hotelAdminRole = Role::firstOrCreate(['name' => 'hotel-admin']);
        $receptionistRole = Role::firstOrCreate(['name' => 'receptionist']);

        $permissions = [
            'manage-hotels',
            'manage-users',
            'manage-forms',
            'view-reservations',
            'validate-reservations',
            'print-police-form',
        ];
        
        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        $superAdminRole->givePermissionTo(Permission::all());
        $hotelAdminRole->givePermissionTo([
            'manage-users', 'manage-forms', 'view-reservations', 'validate-reservations', 'print-police-form',
        ]);
        $receptionistRole->givePermissionTo(['view-reservations', 'validate-reservations', 'print-police-form']);
        
        $this->info('    ✅ Rôles et permissions créés.');

        // Créer UNIQUEMENT le super-admin (sans hôtel de test)
        $this->line('  → Création du compte super-admin...');
        
        $superAdmin = \App\Models\User::firstOrCreate(
            ['email' => 'admin@hotelpro.test'],
            [
                'hotel_id' => null, // Super admin n'appartient à aucun hôtel
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        
        if (!$superAdmin->hasRole('super-admin')) {
            $superAdmin->syncRoles([$superAdminRole]);
        }
        
        $this->info('    ✅ Compte super-admin créé.');

        // Créer les paramètres système essentiels
        $this->line('  → Création des paramètres système...');
        try {
            $settingsSeeder = new \Database\Seeders\SettingsSeeder();
            $settingsSeeder->setCommand($this);
            $settingsSeeder->run();
            $this->info('    ✅ Paramètres système créés.');
        } catch (\Exception $e) {
            $this->warn('    ⚠️  Erreur lors de la création des paramètres: ' . $e->getMessage());
        }

        // Afficher les informations de connexion
        $this->line('');
        $this->warn('📧 Compte super-admin créé:');
        $this->warn("    Email: admin@hotelpro.test");
        $this->warn("    Mot de passe: password");
        $this->warn("    ⚠️  CHANGEZ CE MOT DE PASSE IMMÉDIATEMENT !");
    }
}

