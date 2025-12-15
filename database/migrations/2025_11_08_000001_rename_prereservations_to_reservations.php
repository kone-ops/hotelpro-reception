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
        // 1. Renommer la table pre_reservations → reservations
        Schema::rename('pre_reservations', 'reservations');
        
        // 2. Ajouter les nouveaux champs pour vraie réservation
        Schema::table('reservations', function (Blueprint $table) {
            // Check-in
            $table->timestamp('checked_in_at')->nullable()->after('validated_at');
            $table->foreignId('checked_in_by')->nullable()->constrained('users')->after('checked_in_at');
            
            // Check-out
            $table->timestamp('checked_out_at')->nullable()->after('checked_in_by');
            $table->foreignId('checked_out_by')->nullable()->constrained('users')->after('checked_out_at');
            
            // Facturation
            $table->decimal('total_amount', 10, 2)->nullable()->after('data');
            $table->decimal('paid_amount', 10, 2)->default(0)->after('total_amount');
            $table->string('payment_method')->nullable()->after('paid_amount');
            
            // Synchronisation Oracle
            $table->timestamp('oracle_synced_at')->nullable()->after('payment_method');
            $table->string('oracle_id', 50)->nullable()->after('oracle_synced_at');
            
            // Index pour performance
            $table->index('oracle_id');
            $table->index('oracle_synced_at');
            $table->index(['hotel_id', 'status']);
            $table->index(['hotel_id', 'check_in_date']);
            $table->index(['hotel_id', 'check_out_date']);
        });
        
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

