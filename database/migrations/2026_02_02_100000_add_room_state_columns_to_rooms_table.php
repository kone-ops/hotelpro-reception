<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Ajoute les états indépendants (occupation, nettoyage, technique) et migre les données existantes.
     */
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->string('occupation_state', 50)->default('free')->after('status');
            $table->string('cleaning_state', 50)->default('none')->after('occupation_state');
            $table->string('technical_state', 50)->default('normal')->after('cleaning_state');
        });

        // Remplir les nouveaux champs à partir de l'ancien status
        DB::table('rooms')->update([
            'occupation_state' => DB::raw("CASE
                WHEN status IN ('occupied', 'reserved') THEN 'occupied'
                ELSE 'free'
            END"),
            'cleaning_state' => 'none',
            'technical_state' => DB::raw("CASE WHEN status = 'maintenance' THEN 'maintenance' ELSE 'normal' END"),
        ]);

        // Changer la colonne status en string pour accepter 'cleaning' (état dérivé)
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE rooms MODIFY status VARCHAR(50) DEFAULT \'available\'');
        } else {
            Schema::table('rooms', function (Blueprint $table) {
                $table->string('status', 50)->default('available')->change();
            });
        }

        // Recalculer status = état global (priorité : technique > occupation > cleaning)
        DB::statement("
            UPDATE rooms SET status = CASE
                WHEN technical_state != 'normal' THEN technical_state
                WHEN occupation_state = 'occupied' THEN 'occupied'
                WHEN cleaning_state IN ('pending', 'in_progress') THEN 'cleaning'
                ELSE 'available'
            END
        ");

        Schema::table('rooms', function (Blueprint $table) {
            $table->index(['hotel_id', 'occupation_state']);
            $table->index(['hotel_id', 'cleaning_state']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropIndex(['hotel_id', 'occupation_state']);
            $table->dropIndex(['hotel_id', 'cleaning_state']);
        });

        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn(['occupation_state', 'cleaning_state', 'technical_state']);
        });

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE rooms MODIFY status ENUM('available', 'occupied', 'maintenance', 'reserved') DEFAULT 'available'");
        } else {
            Schema::table('rooms', function (Blueprint $table) {
                $table->enum('status', ['available', 'occupied', 'maintenance', 'reserved'])->default('available')->change();
            });
        }
    }
};
