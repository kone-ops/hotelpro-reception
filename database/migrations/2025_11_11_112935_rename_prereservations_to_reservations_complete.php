<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Cette migration crée la table reservations avec tous les champs nécessaires
     */
    public function up(): void
    {
        // 1. Créer la table reservations si elle n'existe pas
        if (!Schema::hasTable('reservations')) {
            Schema::create('reservations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('hotel_id')->constrained('hotels')->cascadeOnDelete();
                $table->foreignId('room_type_id')->nullable()->constrained('room_types')->nullOnDelete();
                $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
                $table->string('status')->default('pending');
                $table->string('group_code')->nullable();
                $table->json('data');
                $table->date('check_in_date');
                $table->date('check_out_date');
                
                // Champs de validation
                $table->timestamp('validated_at')->nullable();
                $table->unsignedBigInteger('validated_by')->nullable();
                
                // Champs de check-in/out
                $table->timestamp('checked_in_at')->nullable();
                $table->unsignedBigInteger('checked_in_by')->nullable();
                $table->timestamp('checked_out_at')->nullable();
                $table->unsignedBigInteger('checked_out_by')->nullable();
                
                // Champs de paiement
                $table->decimal('total_amount', 10, 2)->nullable();
                $table->decimal('paid_amount', 10, 2)->default(0);
                $table->string('payment_method')->nullable();
                
                // Synchronisation Oracle
                $table->timestamp('oracle_synced_at')->nullable();
                $table->string('oracle_id', 50)->nullable();
                
                $table->timestamps();
                
                // Index pour performance
                $table->index('hotel_id');
                $table->index('status');
                $table->index(['check_in_date', 'check_out_date']);
            });
        }
        
        // 2. Si pre_reservations existe, migrer les données vers reservations
        if (Schema::hasTable('pre_reservations')) {
            // Récupérer toutes les données
            $preReservations = DB::table('pre_reservations')->get();
            
            foreach ($preReservations as $preRes) {
                DB::table('reservations')->insert([
                    'id' => $preRes->id,
                    'hotel_id' => $preRes->hotel_id,
                    'room_type_id' => $preRes->room_type_id ?? null,
                    'room_id' => $preRes->room_id ?? null,
                    'status' => $preRes->status,
                    'group_code' => $preRes->group_code,
                    'data' => $preRes->data,
                    'check_in_date' => $preRes->check_in_date ?? now(),
                    'check_out_date' => $preRes->check_out_date ?? now()->addDay(),
                    'validated_at' => $preRes->validated_at ?? null,
                    'validated_by' => $preRes->validated_by ?? null,
                    'created_at' => $preRes->created_at,
                    'updated_at' => $preRes->updated_at,
                ]);
            }
            
            // Supprimer pre_reservations
            Schema::dropIfExists('pre_reservations');
        }
        
        // 3. Recréer identity_documents avec reservation_id au lieu de pre_reservation_id
        if (Schema::hasTable('identity_documents') && Schema::hasColumn('identity_documents', 'pre_reservation_id')) {
            // Sauvegarder les données existantes
            $identityDocs = DB::table('identity_documents')->get();
            
            // Recréer la table
            Schema::dropIfExists('identity_documents');
            Schema::create('identity_documents', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('reservation_id')->nullable();
                $table->string('type');
                $table->string('number')->nullable();
                $table->string('front_path')->nullable();
                $table->string('back_path')->nullable();
                $table->date('delivery_date')->nullable();
                $table->string('delivery_place')->nullable();
                $table->timestamps();
                
                $table->index('reservation_id');
            });
            
            // Réinsérer les données
            foreach ($identityDocs as $doc) {
                DB::table('identity_documents')->insert([
                    'id' => $doc->id,
                    'reservation_id' => $doc->pre_reservation_id ?? $doc->reservation_id ?? null,
                    'type' => $doc->type,
                    'number' => $doc->number ?? null,
                    'front_path' => $doc->front_path ?? null,
                    'back_path' => $doc->back_path ?? null,
                    'delivery_date' => $doc->delivery_date ?? null,
                    'delivery_place' => $doc->delivery_place ?? null,
                    'created_at' => $doc->created_at,
                    'updated_at' => $doc->updated_at,
                ]);
            }
        }
        
        // 4. Recréer signatures avec reservation_id au lieu de pre_reservation_id
        if (Schema::hasTable('signatures') && Schema::hasColumn('signatures', 'pre_reservation_id')) {
            // Sauvegarder les données existantes
            $signatures = DB::table('signatures')->get();
            
            // Recréer la table
            Schema::dropIfExists('signatures');
            Schema::create('signatures', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('reservation_id')->nullable();
                $table->text('image_base64');
                $table->timestamps();
                
                $table->index('reservation_id');
            });
            
            // Réinsérer les données
            foreach ($signatures as $sig) {
                DB::table('signatures')->insert([
                    'id' => $sig->id,
                    'reservation_id' => $sig->pre_reservation_id ?? $sig->reservation_id ?? null,
                    'image_base64' => $sig->image_base64,
                    'created_at' => $sig->created_at,
                    'updated_at' => $sig->updated_at,
                ]);
            }
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
