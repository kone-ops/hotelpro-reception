<x-app-layout>
	<x-slot name="header">
		<h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Éditer le champ</h2>
	</x-slot>
	<div class="py-6">
		<div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
			<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
				<form method="post" action="{{ route('hotel.fields.update', $field) }}" class="space-y-4">
					@csrf @method('PUT')
					<div>
						<label class="block mb-1">Libellé</label>
						<input name="label" class="w-full border-gray-300 rounded-lg" value="{{ $field->label }}" required />
					</div>
					<div>
						<label class="block mb-1">Type</label>
						<select name="type" class="w-full border-gray-300 rounded-lg" value="{{ $field->type }}">
							<option value="text" @selected($field->type==='text')>Texte</option>
							<option value="email" @selected($field->type==='email')>Email</option>
							<option value="tel" @selected($field->type==='tel')>Téléphone</option>
							<option value="date" @selected($field->type==='date')>Date</option>
						</select>
					</div>
					<div class="flex items-center gap-2">
						<input type="checkbox" name="required" value="1" @checked($field->required) />
						<label>Requis</label>
					</div>
					<div>
						<label class="block mb-1">Position</label>
						<input type="number" name="position" class="w-full border-gray-300 rounded-lg" value="{{ $field->position }}" />
					</div>
					<div class="flex gap-2">
						<button class="px-4 py-2 bg-blue-600 text-white rounded">Mettre à jour</button>
						<a href="{{ route('hotel.fields.index') }}" class="px-4 py-2 bg-gray-200 rounded">Annuler</a>
					</div>
				</form>
			</div>
		</div>
	</div>
</x-app-layout>

