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
        // Vérifier si la table existe
        if (!Schema::hasTable('notification_logs')) {
            // Créer la table si elle n'existe pas
            Schema::create('notification_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('reservation_id')->nullable()->constrained('reservations')->nullOnDelete();
                $table->string('type'); // reservation_created, new_reservation_hotel, etc.
                $table->string('recipient_type'); // client, hotel_staff
                $table->string('recipient_email');
                $table->string('subject')->nullable();
                $table->string('status')->default('pending'); // pending, success, failed
                $table->timestamp('sent_at')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamps();
                
                $table->index('reservation_id');
                $table->index('status');
                $table->index('type');
            });
        } else {
            $driver = DB::getDriverName();
            
            // SQLite ne supporte pas DROP COLUMN, il faut recréer la table
            if ($driver === 'sqlite') {
                // Sauvegarder les données existantes
                $existingData = DB::table('notification_logs')->get();
                
                // Supprimer l'ancienne table
                Schema::dropIfExists('notification_logs');
                
                // Recréer la table avec la nouvelle structure
                Schema::create('notification_logs', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('reservation_id')->nullable()->constrained('reservations')->nullOnDelete();
                    $table->string('type');
                    $table->string('recipient_type');
                    $table->string('recipient_email');
                    $table->string('subject')->nullable();
                    $table->string('status')->default('pending');
                    $table->timestamp('sent_at')->nullable();
                    $table->text('error_message')->nullable();
                    $table->timestamps();
                    
                    $table->index('reservation_id');
                    $table->index('status');
                    $table->index('type');
                });
                
                // Réinsérer les données si possible (migration des anciennes colonnes vers les nouvelles)
                if ($existingData->isNotEmpty()) {
                    $insertData = [];
                    foreach ($existingData as $row) {
                        $insertData[] = [
                            'id' => $row->id,
                            'reservation_id' => $row->pre_reservation_id ?? null,
                            'type' => 'unknown', // Valeur par défaut car on ne peut pas mapper depuis channel
                            'recipient_type' => 'unknown', // Valeur par défaut
                            'recipient_email' => $row->recipient ?? '',
                            'subject' => null,
                            'status' => $row->status === 'queued' ? 'pending' : $row->status,
                            'sent_at' => null,
                            'error_message' => null,
                            'created_at' => $row->created_at ?? now(),
                            'updated_at' => $row->updated_at ?? now(),
                        ];
                    }
                    DB::table('notification_logs')->insert($insertData);
                }
            } else {
                // Pour les autres bases de données (MySQL, PostgreSQL, Oracle)
                Schema::table('notification_logs', function (Blueprint $table) use ($driver) {
                    // Supprimer l'ancienne colonne si elle existe
                    if (Schema::hasColumn('notification_logs', 'pre_reservation_id')) {
                        $table->dropForeign(['pre_reservation_id']);
                        $table->dropColumn('pre_reservation_id');
                    }
                    
                    // Ajouter la nouvelle colonne si elle n'existe pas
                    if (!Schema::hasColumn('notification_logs', 'reservation_id')) {
                        $column = $table->foreignId('reservation_id')->nullable();
                        if ($driver !== 'oracle') {
                            $column->after('id');
                        }
                        $column->constrained('reservations')->nullOnDelete();
                    }
                    
                    // Ajouter les nouvelles colonnes si elles n'existent pas
                    if (!Schema::hasColumn('notification_logs', 'type')) {
                        $column = $table->string('type');
                        if ($driver !== 'oracle') {
                            $column->after('reservation_id');
                        }
                    }
                    if (!Schema::hasColumn('notification_logs', 'recipient_type')) {
                        $column = $table->string('recipient_type');
                        if ($driver !== 'oracle') {
                            $column->after('type');
                        }
                    }
                    if (!Schema::hasColumn('notification_logs', 'recipient_email')) {
                        $column = $table->string('recipient_email');
                        if ($driver !== 'oracle') {
                            $column->after('recipient_type');
                        }
                    }
                    if (!Schema::hasColumn('notification_logs', 'subject')) {
                        $column = $table->string('subject')->nullable();
                        if ($driver !== 'oracle') {
                            $column->after('recipient_email');
                        }
                    }
                    if (!Schema::hasColumn('notification_logs', 'sent_at')) {
                        $column = $table->timestamp('sent_at')->nullable();
                        if ($driver !== 'oracle') {
                            $column->after('status');
                        }
                    }
                    if (!Schema::hasColumn('notification_logs', 'error_message')) {
                        $column = $table->text('error_message')->nullable();
                        if ($driver !== 'oracle') {
                            $column->after('sent_at');
                        }
                    }
                    
                    // Supprimer les anciennes colonnes si elles existent
                    if (Schema::hasColumn('notification_logs', 'channel')) {
                        $table->dropColumn('channel');
                    }
                    if (Schema::hasColumn('notification_logs', 'recipient')) {
                        $table->dropColumn('recipient');
                    }
                    if (Schema::hasColumn('notification_logs', 'payload')) {
                        $table->dropColumn('payload');
                    }
                    
                    // Mettre à jour le statut par défaut
                    if (Schema::hasColumn('notification_logs', 'status')) {
                        if ($driver === 'oracle') {
                            // Oracle utilise MODIFY au lieu de ALTER COLUMN
                            DB::statement("ALTER TABLE notification_logs MODIFY status DEFAULT 'pending'");
                        } else {
                            // Pour MySQL, PostgreSQL, etc.
                            DB::statement("ALTER TABLE notification_logs ALTER COLUMN status SET DEFAULT 'pending'");
                        }
                    }
                });
                
                // Ajouter les index si nécessaire
                Schema::table('notification_logs', function (Blueprint $table) {
                    if (!Schema::hasColumn('notification_logs', 'reservation_id')) {
                        $table->index('reservation_id');
                    }
                    if (!Schema::hasColumn('notification_logs', 'status')) {
                        $table->index('status');
                    }
                    if (!Schema::hasColumn('notification_logs', 'type')) {
                        $table->index('type');
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Pas de rollback - migration one-way pour éviter la perte de données
    }
};
