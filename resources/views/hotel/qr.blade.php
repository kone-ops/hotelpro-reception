<x-app-layout>
	<x-slot name="header">QR Code du formulaire public</x-slot>
	
	<!-- Vue écran (non imprimée) -->
	<div class="screen-only">
	<div class="row">
		<div class="col-md-6">
			<div class="card border-0 shadow-sm">
				<div class="card-header bg-transparent">
					<h5 class="mb-0">QR Code à imprimer</h5>
				</div>
				<div class="card-body text-center">
					<div class="mb-4">{!! $qrSvg !!}</div>
						<p class="text-muted">Scannez ce QR code pour accéder au formulaire de Réservation</p>
						<div class="d-flex gap-2 justify-content-center">
					<button class="btn btn-primary" onclick="window.print()">
						<i class="bi bi-printer me-2"></i>Imprimer
					</button>
							<a href="{{ route('hotel.qr.download') }}" class="btn btn-outline-primary" download>
								<i class="bi bi-download me-2"></i>Télécharger SVG
							</a>
						</div>
				</div>
			</div>
		</div>
		<div class="col-md-6">
			<div class="card border-0 shadow-sm">
				<div class="card-header bg-transparent">
					<h5 class="mb-0">Informations du formulaire</h5>
				</div>
				<div class="card-body">
					<div class="mb-3">
						<strong>Hôtel:</strong><br>
						<span class="text-muted">{{ $hotel->name }}</span>
					</div>
						<div class="mb-3">
							<strong>Adresse:</strong><br>
							<span class="text-muted">{{ $hotel->address ?? 'Non renseignée' }}</span>
						</div>
						<div class="mb-3">
							<strong>Téléphone:</strong><br>
							<span class="text-muted">{{ $hotel->phone ?? 'Non renseigné' }}</span>
						</div>
					<div class="mb-3">
						<strong>Lien direct:</strong><br>
						<div class="input-group">
								<input type="text" class="form-control" value="{{ $url }}" readonly id="qrUrl">
								<button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard()">
								<i class="bi bi-clipboard"></i>
							</button>
						</div>
					</div>
					<div class="mb-3">
						<strong>Instructions:</strong><br>
						<small class="text-muted">
							• Placez ce QR code à l'entrée de l'hôtel<br>
							• Les clients peuvent scanner pour remplir le formulaire<br>
								• Les Réservations apparaîtront dans votre tableau de bord
						</small>
					</div>
					<div class="d-grid gap-2">
						<a href="{{ route('hotel.dashboard') }}" class="btn btn-outline-secondary">
							<i class="bi bi-arrow-left me-2"></i>Retour au tableau de bord
						</a>
					</div>
				</div>
			</div>
			</div>
		</div>
	</div>

	<!-- Vue impression (uniquement pour l'impression) -->
	<div class="print-only">
		<div class="print-container">
			<!-- En-tête avec logo et infos hôtel -->
			<div class="print-header">
				@if($hotel->logo)
					<img src="{{ asset('storage/' . $hotel->logo) }}" alt="Logo {{ $hotel->name }}" class="print-logo">
				@else
					<div class="print-logo-placeholder">
						<i class="bi bi-building"></i>
					</div>
				@endif
				<h1 class="print-hotel-name">{{ $hotel->name }}</h1>
				<div class="print-hotel-info">
					@if($hotel->address)
						<p><i class="bi bi-geo-alt-fill"></i> {{ $hotel->address }}</p>
					@endif
					@if($hotel->phone)
						<p><i class="bi bi-telephone-fill"></i> {{ $hotel->phone }}</p>
					@endif
					@if($hotel->email)
						<p><i class="bi bi-envelope-fill"></i> {{ $hotel->email }}</p>
					@endif
				</div>
			</div>

			<!-- Ligne de séparation -->
			<div class="print-divider"></div>

			<!-- Corps principal avec QR code -->
			<div class="print-body">
				<h2 class="print-title">Formulaire de Réservation</h2>
				<p class="print-subtitle">Scannez ce QR code pour accéder au formulaire</p>
				
				<div class="print-qr-container">
					{!! $qrSvg !!}
				</div>

				<div class="print-instructions">
					<h3>Comment utiliser ce QR code ?</h3>
					<ol>
						<li>Ouvrez l'appareil photo de votre smartphone</li>
						<li>Pointez vers ce QR code</li>
						<li>Tapez sur la notification qui apparaît</li>
						<li>Remplissez le formulaire de réservation</li>
					</ol>
				</div>

				<div class="print-url">
					<p>Ou accédez directement via le lien :</p>
					<strong>{{ $url }}</strong>
				</div>
			</div>

			{{-- <!-- Pied de page -->
			<div class="print-footer">
				<p>Document généré le {{ now()->format('d/m/Y à H:i') }}</p>
				<p class="print-powered">Propulsé par {{ config('app.name') }}</p>
			</div> --}}
		</div>
	</div>
</x-app-layout>

<style>
/* ========================================
   STYLES POUR L'ÉCRAN
   ======================================== */
.print-only {
	display: none;
}

.screen-only {
	display: block;
}

/* ========================================
   STYLES POUR L'IMPRESSION
   ======================================== */
@media print {
	/* Masquer tout sauf le contenu d'impression */
	body * {
		visibility: hidden;
	}
	
	.print-only,
	.print-only * {
		visibility: visible;
	}
	
	.screen-only {
		display: none !important;
	}
	
	.print-only {
		display: block;
		position: absolute;
		left: 0;
		top: 0;
		width: 100%;
	}
	
	/* Configuration de la page - Optimisée pour une seule page */
	@page {
		size: A4 portrait;
		margin: 1cm;
	}
	
	/* Empêcher les sauts de page */
	* {
		page-break-inside: avoid !important;
		page-break-after: avoid !important;
		page-break-before: avoid !important;
	}
	
	/* Container principal - Optimisé */
	.print-container {
		width: 100%;
		max-width: 210mm;
		margin: 0 auto;
		font-family: 'Arial', sans-serif;
		color: #333;
		overflow: hidden;
	}
	
	/* En-tête - Compact */
	.print-header {
		text-align: center;
		margin-bottom: 20px;
	}
	
	.print-logo {
		max-width: 150px;
		max-height: 70px;
		margin-bottom: 10px;
		object-fit: contain;
	}
	
	.print-logo-placeholder {
		width: 70px;
		height: 70px;
		margin: 0 auto 10px;
		border: 2px solid #333;
		border-radius: 8px;
		display: flex;
		align-items: center;
		justify-content: center;
		font-size: 36px;
		color: #333;
	}
	
	.print-hotel-name {
		font-size: 26px;
		font-weight: bold;
		margin: 10px 0;
		color: #000;
		text-transform: uppercase;
		letter-spacing: 1.5px;
	}
	
	.print-hotel-info {
		font-size: 12px;
		line-height: 1.6;
		color: #555;
	}
	
	.print-hotel-info p {
		margin: 3px 0;
	}
	
	.print-hotel-info i {
		margin-right: 6px;
		color: #000;
	}
	
	/* Ligne de séparation - Compact */
	.print-divider {
		height: 2px;
		background: linear-gradient(to right, transparent, #333 20%, #333 80%, transparent);
		margin: 15px 0;
	}
	
	/* Corps principal - Compact */
	.print-body {
		text-align: center;
	}
	
	.print-title {
		font-size: 24px;
		font-weight: bold;
		margin: 15px 0 8px;
		color: #000;
	}
	
	.print-subtitle {
		font-size: 14px;
		color: #666;
		margin-bottom: 20px;
	}
	
	/* Container QR code - Optimisé */
	.print-qr-container {
		display: flex;
		justify-content: center;
		align-items: center;
		margin: 20px 0;
		padding: 15px;
		border: 4px solid #000;
		border-radius: 12px;
		background: #fff;
		box-shadow: 0 0 0 8px #f5f5f5;
	}
	
	.print-qr-container svg {
		max-width: 280px !important;
		max-height: 280px !important;
		width: 280px !important;
		height: 280px !important;
	}
	
	/* Instructions - Compact */
	.print-instructions {
		margin: 20px 0;
		text-align: left;
		padding: 15px;
		background: #f9f9f9;
		border-left: 4px solid #000;
		border-radius: 5px;
	}
	
	.print-instructions h3 {
		font-size: 16px;
		margin-bottom: 10px;
		color: #000;
	}
	
	.print-instructions ol {
		font-size: 12px;
		line-height: 1.6;
		padding-left: 20px;
		margin: 0;
	}
	
	.print-instructions li {
		margin: 6px 0;
	}
	
	/* URL - Compact */
	.print-url {
		margin: 20px 0 0 0;
		padding: 12px;
		background: #f0f0f0;
		border-radius: 6px;
		font-size: 11px;
	}
	
	.print-url p {
		margin: 0 0 6px 0;
		color: #666;
	}
	
	.print-url strong {
		font-size: 12px;
		color: #000;
		word-break: break-all;
	}
}
</style>

<script>
function copyToClipboard() {
	const urlInput = document.getElementById('qrUrl');
	urlInput.select();
	document.execCommand('copy');
	
	// Notification visuelle
	const btn = event.target.closest('button');
	const originalHTML = btn.innerHTML;
	btn.innerHTML = '<i class="bi bi-check"></i>';
	btn.classList.add('btn-success');
	btn.classList.remove('btn-outline-secondary');
	
	setTimeout(() => {
		btn.innerHTML = originalHTML;
		btn.classList.remove('btn-success');
		btn.classList.add('btn-outline-secondary');
	}, 2000);
}
</script>
