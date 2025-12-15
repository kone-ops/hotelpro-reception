<?php

namespace App\Http\Controllers\HotelAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrController extends Controller
{
	/**
	 * Afficher le QR code personnalisé de l'hôtel
	 */
	public function show(Request $request)
	{
		$hotel = Auth::user()->hotel;
		
		if (!$hotel) {
			abort(404, 'Aucun hôtel assigné à cet utilisateur');
		}

		$url = route('public.form', $hotel);
		
	// Générer le QR code avec personnalisation
	$primaryRgb = $this->hexToRgb($hotel->primary_color ?? '#020220');
	$qrSvg = QrCode::format('svg')
		->size(400)
		->margin(2)
		->errorCorrection('H') // Haute correction d'erreur pour permettre le logo
		->color($primaryRgb[0], $primaryRgb[1], $primaryRgb[2])
		->backgroundColor(255, 255, 255)
		->generate($url);
		
	// Pour le moment, on utilise uniquement SVG (pas de dépendance imagick)
	// Le SVG peut être facilement converti en PNG par le navigateur si nécessaire

	return view('hotel.qr', [
		'hotel' => $hotel,
		'url' => $url,
		'qrSvg' => $qrSvg,
		'qrPng' => null, // Désactivé temporairement (nécessite imagick)
		'customizable' => true,
	]);
	}
	
	/**
	 * Télécharger le QR code personnalisé (PNG)
	 */
	public function download(Request $request)
	{
		$hotel = Auth::user()->hotel;
		
		if (!$hotel) {
			abort(404);
		}
		
	$url = route('public.form', $hotel);
	
	// Générer QR code en SVG (pas de dépendance imagick)
	$primaryRgb = $this->hexToRgb($hotel->primary_color ?? '#020220');
	$qrCode = QrCode::format('svg')
		->size(1000)
		->margin(3)
		->errorCorrection('H')
		->color($primaryRgb[0], $primaryRgb[1], $primaryRgb[2])
		->backgroundColor(255, 255, 255)
		->generate($url);
	
	$filename = 'qrcode-' . \Illuminate\Support\Str::slug($hotel->name) . '.svg';
	
	return response($qrCode)
		->header('Content-Type', 'image/svg+xml')
		->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
	}
	
	/**
	 * Convertir couleur HEX en RGB
	 */
	protected function hexToRgb(string $hex): array
	{
		$hex = str_replace('#', '', $hex);
		
		if (strlen($hex) == 3) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}
		
		return [
			hexdec(substr($hex, 0, 2)),
			hexdec(substr($hex, 2, 2)),
			hexdec(substr($hex, 4, 2))
		];
	}
}
