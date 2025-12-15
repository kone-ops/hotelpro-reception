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

		// Permissions minimales (extensibles)
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
		$hotelAdmin->syncRoles([$hotelAdminRole]);

		// Receptionist (avec hotel_id)
		$receptionist = User::firstOrCreate([
			'email' => 'receptioniste@hotelpro.test',
		], [
			'hotel_id' => $hotel->id,
			'name' => 'Receptioniste',
			'password' => Hash::make('password'),
		]);
		$receptionist->syncRoles([$receptionistRole]);
	}
}
