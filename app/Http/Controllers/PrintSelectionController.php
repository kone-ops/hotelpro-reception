<?php

namespace App\Http\Controllers;

use App\Models\Printer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PrintSelectionController extends Controller
{
    /**
     * Afficher la page de sélection d'imprimante
     */
    public function show(Request $request)
    {
        $user = Auth::user();
        $hotelId = $user->hotel_id;
        
        // Récupérer les imprimantes de l'hôtel
        $printers = Printer::where('hotel_id', $hotelId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        $printType = $request->get('type', 'qr'); // 'qr' ou 'police'
        $reservationId = $request->get('reservation_id');
        $returnUrl = $request->get('return_url');
        
        return view('admin.print-selection', compact('printers', 'printType', 'reservationId', 'returnUrl'));
    }
    
    /**
     * Traiter l'impression avec l'imprimante sélectionnée
     */
    public function process(Request $request)
    {
        $request->validate([
            'printer_id' => 'required|exists:printers,id',
            'print_type' => 'required|in:qr,police',
            'reservation_id' => 'nullable|exists:reservations,id',
            'return_url' => 'nullable|string'
        ]);
        
        $user = Auth::user();
        $printer = Printer::findOrFail($request->printer_id);
        
        // Vérifier que l'imprimante appartient à l'hôtel de l'utilisateur
        if ($printer->hotel_id !== $user->hotel_id) {
            return response()->json([
                'success' => false,
                'message' => 'Imprimante non autorisée'
            ], 403);
        }
        
        try {
            if ($request->print_type === 'qr') {
                $result = $this->printQRCode($printer, $user->hotel);
            } elseif ($request->print_type === 'police') {
                $reservation = \App\Models\Reservation::findOrFail($request->reservation_id);
                $result = $this->printPoliceForm($printer, $reservation);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Impression envoyée avec succès',
                'printer_name' => $printer->name,
                'result' => $result
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'impression: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Imprimer le QR code
     */
    private function printQRCode(Printer $printer, $hotel)
    {
        $url = route('public.form', $hotel);
        
        // Générer le QR code en image
        try {
            $qrCode = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')
                ->size(300)
                ->margin(2)
                ->errorCorrection('H')
                ->generate($url);
            
            // Essayer d'abord l'impression d'image universelle
            if ($this->printQRCodeImageUniversal($printer, $hotel, $url, $qrCode)) {
                return true;
            }
            
            // Si ça échoue, essayer les méthodes spécifiques
            if ($printer->type === 'ticket') {
                return $this->printQRCodeImageThermal($printer, $hotel, $url, $qrCode);
            }
            
            return $this->printQRCodeImageA4($printer, $hotel, $url, $qrCode);
            
        } catch (\Exception $e) {
            Log::error("Erreur génération QR code", [
                'printer_id' => $printer->id,
                'error' => $e->getMessage()
            ]);
            
            // Fallback vers impression texte simple
            return $this->printQRCodeText($printer, $hotel, $url);
        }
    }
    
    /**
     * Impression universelle du QR code (fonctionne sur la plupart des imprimantes)
     */
    private function printQRCodeImageUniversal(Printer $printer, $hotel, $url, $qrCodeImage)
    {
        try {
            // Créer un document HTML simple avec le QR code
            $html = $this->generateQRCodeHTML($hotel, $url, $qrCodeImage);
            
            // Générer le PDF
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
            $pdf->setPaper('A4', 'portrait');
            $pdfContent = $pdf->output();
            
            // Sauvegarder temporairement le PDF
            $tempFile = tempnam(sys_get_temp_dir(), 'qrcode_') . '.pdf';
            file_put_contents($tempFile, $pdfContent);
            
            // Essayer d'imprimer avec le système d'impression de l'OS
            $success = $this->printPDFWithSystem($printer, $tempFile);
            
            // Nettoyer le fichier temporaire
            unlink($tempFile);
            
            return $success;
            
        } catch (\Exception $e) {
            Log::error("Erreur impression universelle QR code", [
                'printer_id' => $printer->id,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }
    
    /**
     * Imprimer un PDF avec le système d'impression de l'OS
     */
    private function printPDFWithSystem(Printer $printer, $pdfFile)
    {
        try {
            // Pour Windows
            if (PHP_OS_FAMILY === 'Windows') {
                $command = "start /min \"\" \"$pdfFile\"";
                exec($command);
                return true;
            }
            
            // Pour Linux/Unix
            if (PHP_OS_FAMILY === 'Linux') {
                // Essayer avec lpr
                $command = "lpr -P " . escapeshellarg($printer->name) . " " . escapeshellarg($pdfFile);
                exec($command, $output, $returnCode);
                
                if ($returnCode === 0) {
                    return true;
                }
                
                // Essayer avec lp
                $command = "lp -d " . escapeshellarg($printer->name) . " " . escapeshellarg($pdfFile);
                exec($command, $output, $returnCode);
                
                return $returnCode === 0;
            }
            
            return false;
            
        } catch (\Exception $e) {
            Log::error("Erreur impression système", [
                'printer_id' => $printer->id,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }
    
    /**
     * Imprimer QR code image sur imprimante thermique (ESC/POS)
     */
    private function printQRCodeImageThermal(Printer $printer, $hotel, $url, $qrCodeImage)
    {
        try {
            $connector = new \Mike42\Escpos\PrintConnectors\NetworkPrintConnector(
                $printer->ip_address, 
                $printer->port ?? 9100, 
                2
            );
            $escpos = new \Mike42\Escpos\Printer($connector);
            
            $escpos->initialize();
            $escpos->setJustification(\Mike42\Escpos\Printer::JUSTIFY_CENTER);
            
            // En-tête
            $escpos->setEmphasis(true);
            $escpos->text("=================================\n");
            $escpos->text("   QR CODE - {$hotel->name}\n");
            $escpos->text("=================================\n\n");
            $escpos->setEmphasis(false);
            
            // Sauvegarder l'image temporairement
            $tempFile = tempnam(sys_get_temp_dir(), 'qrcode_') . '.png';
            file_put_contents($tempFile, $qrCodeImage);
            
            // Charger et imprimer l'image
            $img = \Mike42\Escpos\EscposImage::load($tempFile);
            $escpos->bitImage($img);
            $escpos->feed(1);
            
            // Nettoyer le fichier temporaire
            unlink($tempFile);
            
            // Informations textuelles
            $escpos->text("URL: {$url}\n");
            $escpos->text("Date: " . now()->format('d/m/Y H:i:s') . "\n\n");
            $escpos->text("Scannez ce QR code pour accéder\n");
            $escpos->text("au formulaire de pré-réservation\n\n");
            
            $escpos->text("=================================\n");
            $escpos->feed(3);
            $escpos->cut();
            
            $escpos->close();
            
            return true;
            
        } catch (\Exception $e) {
            Log::error("Erreur impression QR code image thermique", [
                'printer_id' => $printer->id,
                'error' => $e->getMessage()
            ]);
            
            // Fallback vers impression texte simple
            return $this->printQRCodeText($printer, $hotel, $url);
        }
    }
    
    /**
     * Imprimer QR code image sur imprimante A4
     */
    private function printQRCodeImageA4(Printer $printer, $hotel, $url, $qrCodeImage)
    {
        try {
            // Créer un document HTML avec le QR code
            $html = $this->generateQRCodeHTML($hotel, $url, $qrCodeImage);
            
            // Utiliser DomPDF pour générer le PDF
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
            $pdf->setPaper('A4', 'portrait');
            
            // Générer le PDF
            $pdfContent = $pdf->output();
            
            // Pour l'impression directe, on utilise une approche simple
            // Envoyer le contenu PDF à l'imprimante via le port réseau
            return $this->sendPDFToPrinter($printer, $pdfContent);
            
        } catch (\Exception $e) {
            Log::error("Erreur impression QR code A4", [
                'printer_id' => $printer->id,
                'error' => $e->getMessage()
            ]);
            
            // Fallback vers impression texte simple
            return $this->printQRCodeText($printer, $hotel, $url);
        }
    }
    
    /**
     * Envoyer un PDF à l'imprimante
     */
    private function sendPDFToPrinter(Printer $printer, $pdfContent)
    {
        try {
            // Pour les imprimantes réseau qui supportent PDF
            $socket = @fsockopen($printer->ip_address, $printer->port ?? 9100, $errno, $errstr, 5);
            
            if (!$socket) {
                throw new \Exception("Impossible de se connecter à l'imprimante: $errstr ($errno)");
            }
            
            // Envoyer le PDF
            fwrite($socket, $pdfContent);
            fclose($socket);
            
            Log::info("PDF envoyé avec succès", [
                'printer_id' => $printer->id,
                'ip' => $printer->ip_address,
                'port' => $printer->port ?? 9100
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error("Erreur envoi PDF", [
                'printer_id' => $printer->id,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }
    
    /**
     * Impression texte simple (fallback)
     */
    private function printQRCodeText(Printer $printer, $hotel, $url)
    {
        $content = "=================================\n";
        $content .= "   QR CODE - {$hotel->name}\n";
        $content .= "=================================\n\n";
        $content .= "URL: {$url}\n";
        $content .= "Date: " . now()->format('d/m/Y H:i:s') . "\n\n";
        $content .= "Scannez ce QR code pour accéder\n";
        $content .= "au formulaire de pré-réservation\n\n";
        $content .= "=================================\n";
        
        return $printer->sendToPrinter($content);
    }
    
    /**
     * Générer le HTML pour le QR code
     */
    private function generateQRCodeHTML($hotel, $url, $qrCode)
    {
        $qrCodeBase64 = base64_encode($qrCode);
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <title>QR Code - {$hotel->name}</title>
            <style>
                @page { margin: 20mm; }
                body { 
                    font-family: Arial, sans-serif; 
                    text-align: center; 
                    padding: 0;
                    margin: 0;
                }
                .container {
                    max-width: 100%;
                    margin: 0 auto;
                }
                .header { 
                    font-size: 28px; 
                    font-weight: bold; 
                    margin-bottom: 30px;
                    color: #333;
                }
                .qr-code { 
                    margin: 30px 0;
                    display: flex;
                    justify-content: center;
                }
                .qr-code img {
                    max-width: 100%;
                    height: auto;
                    border: 2px solid #333;
                    padding: 10px;
                    background: white;
                }
                .url { 
                    font-size: 16px; 
                    color: #666; 
                    margin: 30px 0;
                    word-break: break-all;
                    padding: 0 20px;
                }
                .date { 
                    font-size: 14px; 
                    color: #999;
                    margin-top: 20px;
                }
                .instructions {
                    font-size: 18px;
                    color: #333;
                    margin: 20px 0;
                    font-weight: bold;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>{$hotel->name}</div>
                <div class='instructions'>Scannez ce QR code pour accéder au formulaire de pré-réservation</div>
                <div class='qr-code'>
                    <img src='data:image/png;base64,{$qrCodeBase64}' alt='QR Code'>
                </div>
                <div class='url'>{$url}</div>
                <div class='date'>Généré le " . now()->format('d/m/Y H:i:s') . "</div>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Imprimer la fiche de police
     */
    private function printPoliceForm(Printer $printer, $reservation)
    {
        $data = $reservation->data;
        
        $content = "=================================\n";
        $content .= "   FICHE DE POLICE\n";
        $content .= "=================================\n\n";
        $content .= "Hôtel: {$reservation->hotel->name}\n";
        $content .= "Date: " . now()->format('d/m/Y H:i:s') . "\n\n";
        $content .= "INFORMATIONS CLIENT:\n";
        $content .= "Nom: " . ($data['first_name'] ?? '') . " " . ($data['last_name'] ?? '') . "\n";
        $content .= "Email: " . ($data['email'] ?? '') . "\n";
        $content .= "Téléphone: " . ($data['phone'] ?? '') . "\n\n";
        $content .= "RÉSERVATION:\n";
        $content .= "Arrivée: " . ($data['check_in'] ?? '') . "\n";
        $content .= "Départ: " . ($data['check_out'] ?? '') . "\n";
        $content .= "Chambre: " . ($data['room_type'] ?? '') . "\n\n";
        $content .= "=================================\n";
        
        return $printer->sendToPrinter($content);
    }
}
