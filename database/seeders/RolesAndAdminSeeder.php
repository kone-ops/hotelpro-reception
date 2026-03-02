<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\Hotel;
use App\Models\User;

class RolesAndAdminSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 */
	public function run(): void
	{
		// Rôles de base
		$superAdminRole = Role::firstOrCreate(['name' => 'super-admin']);
		$hotelAdminRole = Role::firstOrCreate(['name' => 'hotel-admin']);
		$receptionistRole = Role::firstOrCreate(['name' => 'receptionist']);
		$housekeepingRole = Role::firstOrCreate(['name' => 'housekeeping']);
		$laundryRole = Role::firstOrCreate(['name' => 'laundry']);
		$maintenanceRole = Role::firstOrCreate(['name' => 'maintenance']);

		// Permissions minimales (extensibles)
		$permissions = [
			'manage-hotels',
			'manage-users',
			'manage-forms',
			'view-reservations',
			'validate-reservations',
			'print-police-form',
			'view-housekeeping',
			'update-cleaning-state',
			'complete-cleaning',
			'view-laundry',
			'update-laundry-collection',
			'manage-laundry-item-types',
			'view-maintenance',
			'update-technical-state',
		];
		foreach ($permissions as $perm) {
			Permission::firstOrCreate(['name' => $perm]);
		}

		$superAdminRole->givePermissionTo(Permission::all());
		$hotelAdminRole->givePermissionTo([
			'manage-users', 'manage-forms', 'view-reservations', 'validate-reservations', 'print-police-form',
		]);
		$receptionistRole->givePermissionTo(['view-reservations', 'validate-reservations', 'print-police-form']);
		$housekeepingRole->givePermissionTo(['view-housekeeping', 'update-cleaning-state', 'complete-cleaning']);
		$laundryRole->givePermissionTo(['view-laundry', 'update-laundry-collection', 'manage-laundry-item-types']);
		$maintenanceRole->givePermissionTo(['view-maintenance', 'update-technical-state']);

		// Hôtel par défaut
		$hotel = Hotel::firstOrCreate([
			'name' => 'HotelPro',
		], [
			'address' => 'Adresse démo',
			'city' => 'Ville',
			'country' => 'Pays',
			'primary_color' => '#020220',
			'secondary_color' => '#293820',
		]);

		// Super admin par défaut (sans hotel_id)
		$superAdmin = User::firstOrCreate([
			'email' => 'admin@hotelpro.test',
		], [
			'hotel_id' => null, // Super admin n'appartient à aucun hôtel
			'name' => 'Super Admin',
			'password' => Hash::make('password'),
		]);
		$superAdmin->syncRoles([$superAdminRole]);

		// Hotel admin (avec hotel_id)
		$hotelAdmin = User::firstOrCreate([
			'email' => 'gerant@hotelpro.test',
		], [
			'hotel_id' => $hotel->id,
			'name' => 'Hotel Admin',
			'password' => Hash::make('password'),
		]);
		if (!$hotelAdmin->hotel_id) {
			$hotelAdmin->update(['hotel_id' => $hotel->id]);
		}
		$hotelAdmin->syncRoles([$hotelAdminRole]);

		// Receptionist (avec hotel_id)
		$receptionist = User::firstOrCreate([
			'email' => 'receptioniste@hotelpro.test',
		], [
			'hotel_id' => $hotel->id,
			'name' => 'Receptioniste',
			'password' => Hash::make('password'),
		]);
		if (!$receptionist->hotel_id) {
			$receptionist->update(['hotel_id' => $hotel->id]);
		}
		$receptionist->syncRoles([$receptionistRole]);

		// Housekeeping (service des étages) - utilisateur démo
		$housekeeping = User::firstOrCreate([
			'email' => 'housekeeping@hotelpro.test',
		], [
			'hotel_id' => $hotel->id,
			'name' => 'Service des étages',
			'password' => Hash::make('password'),
		]);
		if (!$housekeeping->hotel_id) {
			$housekeeping->update(['hotel_id' => $hotel->id]);
		}
		$housekeeping->syncRoles([$housekeepingRole]);

		// Laundry (buanderie) - utilisateur démo
		$laundry = User::firstOrCreate([
			'email' => 'laundry@hotelpro.test',
		], [
			'hotel_id' => $hotel->id,
			'name' => 'Buanderie',
			'password' => Hash::make('password'),
		]);
		if (!$laundry->hotel_id) {
			$laundry->update(['hotel_id' => $hotel->id]);
		}
		$laundry->syncRoles([$laundryRole]);

		// Maintenance (service technique) - utilisateur démo (hotel_id obligatoire pour /maintenance/areas)
		$maintenance = User::firstOrCreate([
			'email' => 'technicien@hotelpro.test',
		], [
			'hotel_id' => $hotel->id,
			'name' => 'Service technique',
			'password' => Hash::make('password'),
		]);
		if (!$maintenance->hotel_id) {
			$maintenance->update(['hotel_id' => $hotel->id]);
		}
		$maintenance->syncRoles([$maintenanceRole]);
	}
}
