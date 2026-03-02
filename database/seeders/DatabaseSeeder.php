<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
	/**
	 * Seed the application's database.
	 * Pointe vers TOUS les seeders pour charger : rôles, utilisateurs (tous les rôles),
	 * paramètres, imprimantes, champs de formulaire, réglages UI et impression, etc.
	 * Ordre respecté pour les dépendances (rôles/hôtels avant paramètres par hôtel).
	 */
	public function run(): void
	{
		$this->call([
			// 1. Rôles, permissions et utilisateurs par défaut (Super Admin, Hotel Admin, Réceptionniste, Service des étages, Buanderie)
			RolesAndAdminSeeder::class,
			// 2. Données de démo : hôtels supplémentaires, admins, réceptionnistes
			CompleteDataSeeder::class,
			// 3. Paramètres applicatifs (features, etc.)
			SettingsSeeder::class,
			// 4. Paramètres d'interface (polices, couleurs, etc.)
			UiSettingSeeder::class,
			// 5. Paramètres d'impression (signature, auto-print, etc.) — n'utilise pas le modèle Printer
			PrinterSettingsSeeder::class,
			// 6. Paramètres d'impression par hôtel (nécessite des hôtels)
			ImpressionSettingsSeeder::class,
			// 7. Champs de formulaire prédéfinis par hôtel (nécessite des hôtels)
			PredefinedFormFieldsSeeder::class,
			// Note : PrinterSeeder (module imprimantes) a été retiré ; ne pas le rappeler ici.
		]);
	}
}
