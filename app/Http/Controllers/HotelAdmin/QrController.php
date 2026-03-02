<?php

namespace App\Http\Controllers\HotelAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;

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

        // Générer l'URL du formulaire (utilise route() pour une URL dynamique)
        $url = route('public.form', $hotel);

        // Générer le QR code avec logo si imagick est disponible, sinon SVG
        $primaryRgb = $this->hexToRgb($hotel->primary_color ?? '#020220');
        
        // Vérifier si imagick est disponible pour PNG + logo
        $logoPath = null;
        if (extension_loaded('imagick') && $hotel->hasLogo() && $hotel->logo) {
            $logoPath = public_path($hotel->logo);
            // Compatibilité avec anciens chemins
            if (strpos($hotel->logo, 'storage/') === 0 || strpos($hotel->logo, 'hotels/') === 0) {
                $logoPath = public_path('images/logos/' . basename($hotel->logo));
            }
            
            if (!File::exists($logoPath)) {
                $logoPath = null;
            }
        }
        
        if ($logoPath && extension_loaded('imagick')) {
            // Générer en PNG avec logo
            $qrImage = QrCode::format('png')
                ->size(400)
                ->margin(2)
                ->errorCorrection('H')
                ->color($primaryRgb[0], $primaryRgb[1], $primaryRgb[2])
                ->backgroundColor(255, 255, 255)
                ->merge($logoPath, 0.25, true)
                ->generate($url);
            
            // Convertir PNG en base64 pour l'affichage
            $qrSvg = '<img src="data:image/png;base64,' . base64_encode($qrImage) . '" alt="QR Code" style="max-width: 100%; height: auto;">';
        } else {
            // Générer en SVG (fonctionne sans imagick)
            $qrSvg = QrCode::format('svg')
                ->size(400)
                ->margin(2)
                ->errorCorrection('H')
                ->color($primaryRgb[0], $primaryRgb[1], $primaryRgb[2])
                ->backgroundColor(255, 255, 255)
                ->generate($url);
        }

        return view('hotel.qr', [
            'hotel' => $hotel,
            'url' => $url,
            'qrSvg' => $qrSvg,
            'qrPng' => null,
            'customizable' => true,
        ]);
    }

    /**
     * Télécharger le QR code personnalisé (SVG)
     */
    public function download(Request $request)
    {
        $hotel = Auth::user()->hotel;

        if (!$hotel) {
            abort(404, 'Aucun hôtel assigné à cet utilisateur');
        }

        // Récupérer l'IP dynamique du serveur
        $serverIp = gethostbyname(gethostname());

        // Générer l'URL du formulaire
        $url = route('public.form', $hotel);

        // Générer le QR code haute résolution avec logo si imagick est disponible
        $primaryRgb = $this->hexToRgb($hotel->primary_color ?? '#020220');
        
        // Vérifier si imagick est disponible pour PNG + logo
        $logoPath = null;
        if (extension_loaded('imagick') && $hotel->hasLogo() && $hotel->logo) {
            $logoPath = public_path($hotel->logo);
            // Compatibilité avec anciens chemins
            if (strpos($hotel->logo, 'storage/') === 0 || strpos($hotel->logo, 'hotels/') === 0) {
                $logoPath = public_path('images/logos/' . basename($hotel->logo));
            }
            
            if (!File::exists($logoPath)) {
                $logoPath = null;
            }
        }
        
        if ($logoPath && extension_loaded('imagick')) {
            // Générer en PNG avec logo
            $qrContent = QrCode::format('png')
                ->size(1000)
                ->margin(3)
                ->errorCorrection('H')
                ->color($primaryRgb[0], $primaryRgb[1], $primaryRgb[2])
                ->backgroundColor(255, 255, 255)
                ->merge($logoPath, 0.25, true)
                ->generate($url);
            
            $filename = 'qrcode-' . Str::slug($hotel->name) . '.png';
            $contentType = 'image/png';
        } else {
            // Générer en SVG (fonctionne sans imagick)
            $qrContent = QrCode::format('svg')
                ->size(1000)
                ->margin(3)
                ->errorCorrection('H')
                ->color($primaryRgb[0], $primaryRgb[1], $primaryRgb[2])
                ->backgroundColor(255, 255, 255)
                ->generate($url);
            
            $filename = 'qrcode-' . Str::slug($hotel->name) . '.svg';
            $contentType = 'image/svg+xml';
        }

        return response($qrContent, 200)
            ->header('Content-Type', $contentType)
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Convertir une couleur hexadécimale en RGB
     */
    protected function hexToRgb(string $hex): array
    {
        $hex = str_replace('#', '', trim($hex));
        if (!preg_match('/^[0-9A-Fa-f]{3}$|^[0-9A-Fa-f]{6}$/', $hex)) {
            return [34, 34, 34]; // Default dark gray
        }
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
