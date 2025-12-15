<x-app-layout>
	<x-slot name="header">Nouveau champ</x-slot>
	
	<div class="row">
		<div class="col-md-8">
			<div class="card border-0 shadow-sm">
				<div class="card-header bg-transparent">
					<h5 class="mb-0">Ajouter un nouveau champ</h5>
				</div>
				<div class="card-body">
					<form method="post" action="{{ route('hotel.fields.store') }}">
						@csrf
						<div class="row">
							<div class="col-md-6 mb-3">
								<label class="form-label">Clé du champ</label>
								<input name="key" class="form-control" placeholder="ex: nom, email, telephone" required />
								<div class="form-text">Identifiant unique du champ (sans espaces)</div>
							</div>
							<div class="col-md-6 mb-3">
								<label class="form-label">Libellé</label>
								<input name="label" class="form-control" placeholder="ex: Nom complet" required />
								<div class="form-text">Texte affiché à l'utilisateur</div>
							</div>
						</div>
						
						<div class="row">
							<div class="col-md-6 mb-3">
								<label class="form-label">Type de champ</label>
								<select name="type" class="form-select">
									<option value="text">Texte</option>
									<option value="email">Email</option>
									<option value="tel">Téléphone</option>
									<option value="date">Date</option>
									<option value="number">Nombre</option>
									<option value="textarea">Texte long</option>
								</select>
							</div>
							<div class="col-md-6 mb-3">
								<label class="form-label">Position</label>
								<input type="number" name="position" class="form-control" value="0" min="0" />
								<div class="form-text">Ordre d'affichage (0 = premier)</div>
							</div>
						</div>
						
						<div class="mb-3">
							<div class="form-check">
								<input type="checkbox" name="required" value="1" class="form-check-input" id="required" checked />
								<label class="form-check-label" for="required">
									Champ obligatoire
								</label>
							</div>
						</div>
						
						<div class="d-flex gap-2">
							<button type="submit" class="btn btn-primary">
								<i class="bi bi-check-lg me-2"></i>Enregistrer
							</button>
							<a href="{{ route('hotel.fields.index') }}" class="btn btn-outline-secondary">
								<i class="bi bi-arrow-left me-2"></i>Annuler
							</a>
						</div>
					</form>
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="card border-0 shadow-sm">
				<div class="card-header bg-transparent">
					<h6 class="mb-0">Types de champs disponibles</h6>
				</div>
				<div class="card-body">
					<div class="mb-2">
						<strong>Texte</strong><br>
						<small class="text-muted">Champ de saisie libre</small>
					</div>
					<div class="mb-2">
						<strong>Email</strong><br>
						<small class="text-muted">Validation email automatique</small>
					</div>
					<div class="mb-2">
						<strong>Téléphone</strong><br>
						<small class="text-muted">Format téléphone</small>
					</div>
					<div class="mb-2">
						<strong>Date</strong><br>
						<small class="text-muted">Sélecteur de date</small>
					</div>
					<div class="mb-2">
						<strong>Nombre</strong><br>
						<small class="text-muted">Valeurs numériques uniquement</small>
					</div>
					<div class="mb-2">
						<strong>Texte long</strong><br>
						<small class="text-muted">Zone de texte étendue</small>
					</div>
				</div>
			</div>
		</div>
	</div>
</x-app-layout>

