<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Renommer la table pre_reservations → reservations (seulement si pre_reservations existe et reservations n'existe pas)
        $needsRename = Schema::hasTable('pre_reservations') && !Schema::hasTable('reservations');
        if ($needsRename) {
            Schema::rename('pre_reservations', 'reservations');
        }
        
        // Vérifier si la table reservations existe (soit après renommage, soit déjà créée)
        if (!Schema::hasTable('reservations')) {
            return; // Si la table n'existe toujours pas, on ne peut pas continuer
        }
        
        // 2. Ajouter les nouveaux champs pour vraie réservation (seulement si elles n'existent pas)
        Schema::table('reservations', function (Blueprint $table) {
            if (!Schema::hasColumn('reservations', 'checked_in_at')) {
                $table->timestamp('checked_in_at')->nullable()->after('validated_at');
            }
            if (!Schema::hasColumn('reservations', 'checked_in_by')) {
                $table->foreignId('checked_in_by')->nullable()->constrained('users')->after('checked_in_at');
            }
            if (!Schema::hasColumn('reservations', 'checked_out_at')) {
                $table->timestamp('checked_out_at')->nullable()->after('checked_in_by');
            }
            if (!Schema::hasColumn('reservations', 'checked_out_by')) {
                $table->foreignId('checked_out_by')->nullable()->constrained('users')->after('checked_out_at');
            }
            if (!Schema::hasColumn('reservations', 'total_amount')) {
                $table->decimal('total_amount', 10, 2)->nullable()->after('data');
            }
            if (!Schema::hasColumn('reservations', 'paid_amount')) {
                $table->decimal('paid_amount', 10, 2)->default(0)->after('total_amount');
            }
            if (!Schema::hasColumn('reservations', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('paid_amount');
            }
            if (!Schema::hasColumn('reservations', 'oracle_synced_at')) {
                $table->timestamp('oracle_synced_at')->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('reservations', 'oracle_id')) {
                $table->string('oracle_id', 50)->nullable()->after('oracle_synced_at');
            }
        });
        // Index pour performance (chaque index dans un try/catch au niveau migration)
        if (Schema::hasColumn('reservations', 'oracle_id')) {
            try {
                Schema::table('reservations', fn (Blueprint $t) => $t->index('oracle_id'));
            } catch (\Throwable $e) { /* déjà existant */ }
        }
        if (Schema::hasColumn('reservations', 'oracle_synced_at')) {
            try {
                Schema::table('reservations', fn (Blueprint $t) => $t->index('oracle_synced_at'));
            } catch (\Throwable $e) { /* déjà existant */ }
        }
        try {
            Schema::table('reservations', fn (Blueprint $t) => $t->index(['hotel_id', 'status']));
        } catch (\Throwable $e) { /* déjà existant */ }
        if (Schema::hasColumn('reservations', 'check_in_date')) {
            try {
                Schema::table('reservations', fn (Blueprint $t) => $t->index(['hotel_id', 'check_in_date']));
            } catch (\Throwable $e) { /* déjà existant */ }
        }
        if (Schema::hasColumn('reservations', 'check_out_date')) {
            try {
                Schema::table('reservations', fn (Blueprint $t) => $t->index(['hotel_id', 'check_out_date']));
            } catch (\Throwable $e) { /* déjà existant */ }
        }

        // SQLite: supprimer les index orphelins (référençant des colonnes absentes) avant alter table
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            foreach (['reservations_hotel_id_check_in_date_index', 'reservations_hotel_id_check_out_date_index'] as $idx) {
                try {
                    DB::statement("DROP INDEX IF EXISTS {$idx}");
                } catch (\Throwable $e) { /* ignorer */ }
            }
        }
        
        // 3. Mettre à jour les foreign keys dans identity_documents
        if (Schema::hasTable('identity_documents')) {
            Schema::table('identity_documents', function (Blueprint $table) {
                // Supprimer ancienne foreign key
                $table->dropForeign(['pre_reservation_id']);
                
                // Renommer colonne
                $table->renameColumn('pre_reservation_id', 'reservation_id');
            });
            
            // Ajouter nouvelle foreign key
            Schema::table('identity_documents', function (Blueprint $table) {
                $table->foreign('reservation_id')->references('id')->on('reservations')->onDelete('cascade');
            });
        }
        
        // 4. Mettre à jour les foreign keys dans signatures
        if (Schema::hasTable('signatures')) {
            Schema::table('signatures', function (Blueprint $table) {
                $table->dropForeign(['pre_reservation_id']);
                $table->renameColumn('pre_reservation_id', 'reservation_id');
            });
            
            Schema::table('signatures', function (Blueprint $table) {
                $table->foreign('reservation_id')->references('id')->on('reservations')->onDelete('cascade');
            });
        }
        
        // 5. Mettre à jour les foreign keys dans accompaniments (si existe)
        if (Schema::hasTable('accompaniments') && Schema::hasColumn('accompaniments', 'pre_reservation_id')) {
            Schema::table('accompaniments', function (Blueprint $table) {
                $table->dropForeign(['pre_reservation_id']);
                $table->renameColumn('pre_reservation_id', 'reservation_id');
            });
            
            Schema::table('accompaniments', function (Blueprint $table) {
                $table->foreign('reservation_id')->references('id')->on('reservations')->onDelete('cascade');
            });
        }
        
        // 6. Mettre à jour les statuts existants
        DB::table('reservations')
            ->where('status', 'pending')
            ->whereNotNull('validated_at')
            ->update(['status' => 'validated']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Inverser les changements dans identity_documents
        if (Schema::hasTable('identity_documents')) {
            Schema::table('identity_documents', function (Blueprint $table) {
                $table->dropForeign(['reservation_id']);
                $table->renameColumn('reservation_id', 'pre_reservation_id');
            });
            
            Schema::table('identity_documents', function (Blueprint $table) {
                $table->foreign('pre_reservation_id')->references('id')->on('pre_reservations')->onDelete('cascade');
            });
        }
        
        // Inverser les changements dans signatures
        if (Schema::hasTable('signatures')) {
            Schema::table('signatures', function (Blueprint $table) {
                $table->dropForeign(['reservation_id']);
                $table->renameColumn('reservation_id', 'pre_reservation_id');
            });
            
            Schema::table('signatures', function (Blueprint $table) {
                $table->foreign('pre_reservation_id')->references('id')->on('pre_reservations')->onDelete('cascade');
            });
        }
        
        // Inverser les changements dans accompaniments
        if (Schema::hasTable('accompaniments') && Schema::hasColumn('accompaniments', 'reservation_id')) {
            Schema::table('accompaniments', function (Blueprint $table) {
                $table->dropForeign(['reservation_id']);
                $table->renameColumn('reservation_id', 'pre_reservation_id');
            });
            
            Schema::table('accompaniments', function (Blueprint $table) {
                $table->foreign('pre_reservation_id')->references('id')->on('pre_reservations')->onDelete('cascade');
            });
        }
        
        // Supprimer les nouvelles colonnes
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropForeign(['checked_in_by']);
            $table->dropForeign(['checked_out_by']);
            
            $table->dropIndex(['oracle_id']);
            $table->dropIndex(['oracle_synced_at']);
            $table->dropIndex(['hotel_id', 'status']);
            $table->dropIndex(['hotel_id', 'check_in_date']);
            $table->dropIndex(['hotel_id', 'check_out_date']);
            
            $table->dropColumn([
                'checked_in_at',
                'checked_in_by',
                'checked_out_at',
                'checked_out_by',
                'total_amount',
                'paid_amount',
                'payment_method',
                'oracle_synced_at',
                'oracle_id'
            ]);
        });
        
        // Renommer la table
        Schema::rename('reservations', 'pre_reservations');
    }
};

