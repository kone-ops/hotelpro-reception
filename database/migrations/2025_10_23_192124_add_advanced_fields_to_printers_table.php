<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('printers', function (Blueprint $table) {
            // Champs pour le système d'impression avancé
            if (!Schema::hasColumn('printers', 'technologie')) {
                $table->string('technologie')->default('thermique')->after('type'); // thermique, laser, jet_encre
            }
            if (!Schema::hasColumn('printers', 'logo_path')) {
                $table->string('logo_path')->nullable()->after('description'); // Chemin vers le logo
            }
            if (!Schema::hasColumn('printers', 'config')) {
                $table->json('config')->nullable()->after('logo_path'); // Configuration avancée
            }
            if (!Schema::hasColumn('printers', 'disponible')) {
                $table->boolean('disponible')->default(false)->after('is_active'); // Statut de disponibilité
            }
            if (!Schema::hasColumn('printers', 'test_statut')) {
                $table->enum('test_statut', ['non_teste', 'succes', 'echec'])->default('non_teste')->after('disponible');
            }
            if (!Schema::hasColumn('printers', 'last_successful_check')) {
                $table->timestamp('last_successful_check')->nullable()->after('test_statut');
            }
            if (!Schema::hasColumn('printers', 'notes')) {
                $table->text('notes')->nullable()->after('last_successful_check');
            }
            
            // Index pour les performances
            $table->index(['disponible', 'is_active']);
            $table->index(['technologie', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('printers', function (Blueprint $table) {
            $table->dropIndex(['disponible', 'is_active']);
            $table->dropIndex(['technologie', 'type']);
            $table->dropColumn([
                'technologie',
                'logo_path',
                'config',
                'disponible',
                'test_statut',
                'last_successful_check',
                'notes'
            ]);
        });
    }
};
