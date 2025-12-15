<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Fix: Gérer la migration des tables reservations
     */
    public function up(): void
    {
        try {
            // 1. Supprimer la table reservations vide si elle existe
            if (Schema::hasTable('reservations')) {
                $count = DB::table('reservations')->count();
                if ($count == 0) {
                    Schema::dropIfExists('reservations');
                }
            }
            
            // 2. Si pre_reservations existe et reservations n'existe pas, renommer
            if (Schema::hasTable('pre_reservations') && !Schema::hasTable('reservations')) {
                Schema::rename('pre_reservations', 'reservations');
            }
            
            // 3. Ajouter les nouveaux champs nécessaires
            if (Schema::hasTable('reservations')) {
                Schema::table('reservations', function (Blueprint $table) {
                    if (!Schema::hasColumn('reservations', 'checked_in_at')) {
                        $table->timestamp('checked_in_at')->nullable();
                    }
                    if (!Schema::hasColumn('reservations', 'checked_in_by')) {
                        $table->unsignedBigInteger('checked_in_by')->nullable();
                    }
                    if (!Schema::hasColumn('reservations', 'checked_out_at')) {
                        $table->timestamp('checked_out_at')->nullable();
                    }
                    if (!Schema::hasColumn('reservations', 'checked_out_by')) {
                        $table->unsignedBigInteger('checked_out_by')->nullable();
                    }
                    if (!Schema::hasColumn('reservations', 'total_amount')) {
                        $table->decimal('total_amount', 10, 2)->nullable();
                    }
                    if (!Schema::hasColumn('reservations', 'paid_amount')) {
                        $table->decimal('paid_amount', 10, 2)->default(0);
                    }
                    if (!Schema::hasColumn('reservations', 'payment_method')) {
                        $table->string('payment_method')->nullable();
                    }
                    if (!Schema::hasColumn('reservations', 'oracle_synced_at')) {
                        $table->timestamp('oracle_synced_at')->nullable();
                    }
                    if (!Schema::hasColumn('reservations', 'oracle_id')) {
                        $table->string('oracle_id', 50)->nullable();
                    }
                });
            }
            
            // 4. Mettre à jour identity_documents seulement si reservations existe
            if (Schema::hasTable('reservations') && Schema::hasTable('identity_documents') && Schema::hasColumn('identity_documents', 'pre_reservation_id')) {
                // Créer nouvelle colonne
                if (!Schema::hasColumn('identity_documents', 'reservation_id')) {
                    Schema::table('identity_documents', function (Blueprint $table) {
                        $table->unsignedBigInteger('reservation_id')->nullable();
                    });
                }
                
                // Copier les données
                DB::statement('UPDATE identity_documents SET reservation_id = pre_reservation_id WHERE pre_reservation_id IS NOT NULL');
                
                // Supprimer l'ancienne colonne
                Schema::table('identity_documents', function (Blueprint $table) {
                    $table->dropColumn('pre_reservation_id');
                });
            }
            
            // 5. Mettre à jour signatures seulement si reservations existe
            if (Schema::hasTable('reservations') && Schema::hasTable('signatures') && Schema::hasColumn('signatures', 'pre_reservation_id')) {
                // Créer nouvelle colonne
                if (!Schema::hasColumn('signatures', 'reservation_id')) {
                    Schema::table('signatures', function (Blueprint $table) {
                        $table->unsignedBigInteger('reservation_id')->nullable();
                    });
                }
                
                // Copier les données
                DB::statement('UPDATE signatures SET reservation_id = pre_reservation_id WHERE pre_reservation_id IS NOT NULL');
                
                // Supprimer l'ancienne colonne
                Schema::table('signatures', function (Blueprint $table) {
                    $table->dropColumn('pre_reservation_id');
                });
            }
            
        } catch (\Exception $e) {
            \Log::error('Erreur migration fix_reservations_tables', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Pas de rollback - migration one-way
    }
};
