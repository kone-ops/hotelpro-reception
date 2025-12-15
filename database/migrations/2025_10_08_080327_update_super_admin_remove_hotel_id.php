<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Mettre à jour tous les utilisateurs avec le rôle super-admin pour retirer leur hotel_id
        $superAdminRole = Role::where('name', 'super-admin')->first();
        
        if ($superAdminRole) {
            $superAdmins = User::role('super-admin')->get();
            
            foreach ($superAdmins as $admin) {
                $admin->update(['hotel_id' => null]);
            }
        }
        
        // S'assurer qu'un utilisateur ne peut avoir qu'un seul rôle
        $users = User::with('roles')->get();
        
        foreach ($users as $user) {
            if ($user->roles->count() > 1) {
                // Garder seulement le premier rôle
                $firstRole = $user->roles->first();
                $user->syncRoles([$firstRole->name]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Pas de rollback nécessaire pour cette migration
        // car nous ne pouvons pas restaurer les données précédentes de manière sûre
    }
};
