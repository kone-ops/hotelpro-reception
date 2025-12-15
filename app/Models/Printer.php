<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;

class Printer extends Model
{
    use HasFactory;

    protected $fillable = [
        'hotel_id',
        'name',
        'manufacturer',
        'model',
        'location',
        'ip_address',
        'port',
        'type',
        'module',
        'is_active',
        'is_default',
        'description',
        'connection_status',
        'last_checked_at',
        'response_time_ms',
        'failed_checks_count',
        // Nouvelles fonctionnalités avancées
        'technologie',
        'logo_path',
        'config',
        'disponible',
        'test_statut',
        'last_successful_check',
        'notes'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'port' => 'integer',
        'last_checked_at' => 'datetime',
        'response_time_ms' => 'integer',
        'failed_checks_count' => 'integer',
        // Nouvelles fonctionnalités avancées
        'disponible' => 'boolean',
        'config' => 'array',
        'last_successful_check' => 'datetime',
    ];

    /**
     * Scope pour les imprimantes actives
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope par module
     */
    public function scopeByModule($query, $module)
    {
        return $query->where('module', $module);
    }

    /**
     * Scope par type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Relation avec l'hôtel
     */
    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * Relation avec les logs d'impression
     */
    public function printLogs()
    {
        return $this->hasMany(PrintLog::class);
    }

    /**
     * Scope par hôtel
     */
    public function scopeByHotel($query, $hotelId)
    {
        return $query->where('hotel_id', $hotelId);
    }

    /**
     * Scope pour l'imprimante par défaut
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Obtenir l'imprimante par défaut pour un hôtel
     */
    public static function getDefaultForHotel($hotelId)
    {
        return static::active()
            ->where('is_default', true)
            ->where('hotel_id', $hotelId)
            ->first();
    }
    
    /**
     * Obtenir l'imprimante par défaut pour un module et un hôtel
     */
    public static function getDefaultForModule($module, $hotelId = null)
    {
        $query = static::active()->where('is_default', true);
        
        if ($module) {
            $query->where('module', $module);
        }
        
        if ($hotelId) {
            $query->where('hotel_id', $hotelId);
        }
        
        return $query->first();
    }
    
    /**
     * Définir comme imprimante par défaut (une seule par hôtel)
     */
    public function setAsDefault(): bool
    {
        // Retirer le statut par défaut des autres imprimantes de cet hôtel
        static::where('hotel_id', $this->hotel_id)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);
        
        // Définir cette imprimante comme par défaut
        return $this->update(['is_default' => true]);
    }

    /**
     * Tester la connexion à l'imprimante (Méthode optimisée - fsockopen)
     */
    public function testConnection(): bool
    {
        try {
            // Utiliser fsockopen qui est plus compatible que socket_create
            $port = $this->port ?? 9100;
            
            // Timeout de 2 secondes
            $connection = @fsockopen($this->ip_address, $port, $errno, $errstr, 2);
            
            if ($connection) {
                fclose($connection);
                return true;
            }
            
            Log::info("Test connexion imprimante échoué", [
                'printer_id' => $this->id,
                'ip' => $this->ip_address,
                'port' => $port,
                'errno' => $errno,
                'errstr' => $errstr
            ]);
            
            return false;
            
        } catch (\Exception $e) {
            Log::warning("Erreur test connexion imprimante", [
                'printer_id' => $this->id,
                'ip' => $this->ip_address,
                'port' => $this->port ?? 9100,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Envoyer un document à l'imprimante (Méthode de resto_app adaptée)
     */
    public function sendToPrinter(string $content): bool
    {
        try {
            // Utiliser le port configuré (par défaut 9100)
            $port = $this->port ?? 9100;
            $socket = @fsockopen($this->ip_address, $port, $errno, $errstr, 5);
            
            if (!$socket) {
                throw new \Exception("Impossible de se connecter à l'imprimante: $errstr ($errno)");
            }

            // Envoyer le contenu
            fwrite($socket, $content);
            fclose($socket);

            Log::info("Impression envoyée avec succès", [
                'printer_id' => $this->id,
                'ip' => $this->ip_address,
                'port' => $port
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("Erreur d'impression", [
                'printer_id' => $this->id,
                'ip' => $this->ip_address,
                'port' => $this->port ?? 9100,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }
    
    /**
     * Obtenir l'adresse complète de l'imprimante (comme resto_app)
     */
    public function getAdresseComplete(): string
    {
        return $this->ip_address . ':' . ($this->port ?? 9100);
    }
    
    /**
     * Obtenir les ports par défaut - Liste complète des ports d'imprimantes
     */
    public static function getPortsParDefaut(): array
    {
        return [
            // Ports ESC/POS (Imprimantes thermiques, tickets, reçus)
            9100 => '9100 (ESC/POS Standard)',
            9101 => '9101 (ESC/POS Alternatif)',
            9102 => '9102 (ESC/POS Alternatif)',
            9103 => '9103 (ESC/POS Alternatif)',
            
            // Ports IPP (Internet Printing Protocol)
            631 => '631 (IPP Standard)',
            443 => '443 (IPP Secure/HTTPS)',
            
            // Ports LPD (Line Printer Daemon)
            515 => '515 (LPD Standard)',
            
            // Ports JetDirect (HP)
            9100 => '9100 (HP JetDirect)',
            9101 => '9101 (HP JetDirect)',
            9102 => '9102 (HP JetDirect)',
            
            // Ports Canon
            9100 => '9100 (Canon Network)',
            515 => '515 (Canon LPD)',
            
            // Ports Epson
            9100 => '9100 (Epson ESC/P)',
            515 => '515 (Epson LPD)',
            
            // Ports Brother
            9100 => '9100 (Brother Network)',
            515 => '515 (Brother LPD)',
            
            // Ports Samsung
            9100 => '9100 (Samsung Network)',
            515 => '515 (Samsung LPD)',
            
            // Ports Xerox
            515 => '515 (Xerox LPD)',
            631 => '631 (Xerox IPP)',
            
            // Ports Lexmark
            9100 => '9100 (Lexmark Network)',
            515 => '515 (Lexmark LPD)',
            
            // Ports Ricoh
            515 => '515 (Ricoh LPD)',
            631 => '631 (Ricoh IPP)',
            
            // Ports Kyocera
            515 => '515 (Kyocera LPD)',
            631 => '631 (Kyocera IPP)',
            
            // Ports génériques
            80 => '80 (HTTP Standard)',
            443 => '443 (HTTPS Secure)',
            8080 => '8080 (HTTP Alternatif)',
            8443 => '8443 (HTTPS Alternatif)',
        ];
    }
    
    /**
     * Obtenir les ports par catégorie d'imprimante
     */
    public static function getPortsByCategory(): array
    {
        return [
            'thermique' => [
                9100 => '9100 (ESC/POS Standard)',
                9101 => '9101 (ESC/POS Alternatif)',
                9102 => '9102 (ESC/POS Alternatif)',
            ],
            'laser' => [
                515 => '515 (LPD Standard)',
                631 => '631 (IPP Standard)',
                9100 => '9100 (JetDirect)',
            ],
            'jet_encre' => [
                515 => '515 (LPD Standard)',
                631 => '631 (IPP Standard)',
                9100 => '9100 (Network)',
            ],
            'multifonction' => [
                515 => '515 (LPD Standard)',
                631 => '631 (IPP Standard)',
                9100 => '9100 (Network)',
                80 => '80 (HTTP Web)',
                443 => '443 (HTTPS Secure)',
            ],
            'etiquettes' => [
                9100 => '9100 (ESC/POS)',
                9101 => '9101 (ESC/POS)',
            ],
            'tickets' => [
                9100 => '9100 (ESC/POS)',
                9101 => '9101 (ESC/POS)',
            ],
        ];
    }
    
    /**
     * Obtenir les ports par fabricant
     */
    public static function getPortsByManufacturer(): array
    {
        return [
            'HP' => [
                9100 => '9100 (JetDirect Standard)',
                9101 => '9101 (JetDirect Alternatif)',
                9102 => '9102 (JetDirect Alternatif)',
                515 => '515 (LPD)',
                631 => '631 (IPP)',
            ],
            'Canon' => [
                9100 => '9100 (Canon Network)',
                515 => '515 (Canon LPD)',
                631 => '631 (Canon IPP)',
            ],
            'Epson' => [
                9100 => '9100 (Epson ESC/P)',
                515 => '515 (Epson LPD)',
                631 => '631 (Epson IPP)',
            ],
            'Brother' => [
                9100 => '9100 (Brother Network)',
                515 => '515 (Brother LPD)',
                631 => '631 (Brother IPP)',
            ],
            'Samsung' => [
                9100 => '9100 (Samsung Network)',
                515 => '515 (Samsung LPD)',
                631 => '631 (Samsung IPP)',
            ],
            'Xerox' => [
                515 => '515 (Xerox LPD)',
                631 => '631 (Xerox IPP)',
                80 => '80 (Xerox Web)',
            ],
            'Lexmark' => [
                9100 => '9100 (Lexmark Network)',
                515 => '515 (Lexmark LPD)',
                631 => '631 (Lexmark IPP)',
            ],
            'Ricoh' => [
                515 => '515 (Ricoh LPD)',
                631 => '631 (Ricoh IPP)',
                80 => '80 (Ricoh Web)',
            ],
            'Kyocera' => [
                515 => '515 (Kyocera LPD)',
                631 => '631 (Kyocera IPP)',
                80 => '80 (Kyocera Web)',
            ],
        ];
    }

    /**
     * Obtenir le statut de l'imprimante
     */
    public function getStatusAttribute(): string
    {
        if (!$this->is_active) {
            return 'inactive';
        }
        
        return $this->testConnection() ? 'online' : 'offline';
    }

    // ===== NOUVELLES FONCTIONNALITÉS RÉVOLUTIONNAIRES =====

    /**
     * Vérifier si l'imprimante est disponible
     */
    public function isDisponible(): bool
    {
        return $this->disponible && $this->is_active;
    }

    /**
     * Obtenir la valeur d'une configuration
     */
    public function getConfigValue($key, $default = null)
    {
        return data_get($this->config, $key, $default);
    }

    /**
     * Définir une valeur de configuration
     */
    public function setConfigValue($key, $value)
    {
        $config = $this->config ?? [];
        data_set($config, $key, $value);
        $this->config = $config;
    }

    /**
     * Obtenir le label de la technologie
     */
    public function getTechnologieLabelAttribute()
    {
        $labels = [
            'thermique' => 'Imprimante thermique',
            'laser' => 'Imprimante laser',
            'jet_encre' => 'Imprimante à jet d\'encre',
            'multifonction' => 'Imprimante multifonction'
        ];

        return $labels[$this->technologie] ?? $this->technologie;
    }

    /**
     * Obtenir le label du statut de test
     */
    public function getTestStatutLabelAttribute()
    {
        $labels = [
            'non_teste' => 'Non testé',
            'succes' => 'Test validé',
            'echec' => 'Test échoué'
        ];

        return $labels[$this->test_statut] ?? $this->test_statut;
    }

    /**
     * Test de connexion avancé avec mise à jour du statut
     */
    public function testConnexionAvancee(): bool
    {
        try {
            $isConnected = $this->testConnection();
            
            // Mettre à jour le statut de disponibilité
            $this->update([
                'disponible' => $isConnected,
                'test_statut' => $isConnected ? 'succes' : 'echec',
                'last_successful_check' => $isConnected ? now() : $this->last_successful_check,
                'failed_checks_count' => $isConnected ? 0 : $this->failed_checks_count + 1
            ]);

            return $isConnected;
        } catch (\Exception $e) {
            $this->update([
                'disponible' => false,
                'test_statut' => 'echec',
                'failed_checks_count' => $this->failed_checks_count + 1
            ]);
            
            Log::error("Erreur test connexion imprimante", [
                'printer_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Impression avancée avec ESC/POS et système de retry
     */
    public function imprimerAvancee($contenu, $typeDocument = 'document', $reference = null, $userId = null)
    {
        $maxRetries = 3;
        $retryDelay = 500000; // 0.5 secondes en microsecondes
        
        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                // Créer le log d'impression
                $printLog = PrintLog::create([
                    'printer_id' => $this->id,
                    'user_id' => $userId,
                    'hotel_id' => $this->hotel_id,
                    'type_document' => $typeDocument,
                    'reference' => $reference ?? ('DOC_' . time()),
                    'contenu' => $contenu,
                    'statut' => 'en_cours',
                    'tentatives' => $attempt,
                    'debut_impression' => now(),
                    'metadata' => [
                        'tentative' => $attempt,
                        'max_tentatives' => $maxRetries,
                        'technologie' => $this->technologie
                    ]
                ]);

                // Vérifier la disponibilité
                if (!$this->isDisponible()) {
                    $this->testConnexionAvancee();
                    if (!$this->isDisponible()) {
                        throw new \Exception('Imprimante non disponible (tentative ' . $attempt . '/' . $maxRetries . ')');
                    }
                }

                // Tenter l'impression
                $success = $this->envoyerAvecEscpos($contenu);

                if ($success) {
                    $printLog->marquerSucces();
                    return $printLog;
                } else {
                    throw new \Exception('Échec de l\'impression (tentative ' . $attempt . '/' . $maxRetries . ')');
                }

            } catch (\Throwable $e) {
                $errorMessage = $e->getMessage();
                
                if ($attempt === $maxRetries) {
                    if (isset($printLog)) {
                        $printLog->marquerEchec($errorMessage);
                    }
                    throw new \Exception("Échec définitif après {$maxRetries} tentatives: {$errorMessage}");
                }
                
                // Attendre avant la prochaine tentative
                usleep($retryDelay);
                
                Log::warning("Tentative d'impression échouée", [
                    'printer_id' => $this->id,
                    'tentative' => $attempt,
                    'erreur' => $errorMessage
                ]);
            }
        }
        
        return false;
    }

    /**
     * Envoi avec ESC/POS pour imprimantes thermiques
     */
    private function envoyerAvecEscpos($contenu)
    {
        try {
            // Utiliser Mike42 ESC/POS
            $connector = new \Mike42\Escpos\PrintConnectors\NetworkPrintConnector(
                $this->ip_address, 
                $this->port ?? 9100, 
                2
            );
            $printer = new \Mike42\Escpos\Printer($connector);

            $printer->initialize();
            $printer->setJustification(\Mike42\Escpos\Printer::JUSTIFY_CENTER);

            // Impression du logo si défini
            if (!empty($this->logo_path)) {
                $this->imprimerLogo($printer);
            }

            $printer->setEmphasis(true);
            $printer->text("\n");
            
            // Traiter le contenu ligne par ligne
            $lignes = explode("\n", $contenu);
            
            foreach ($lignes as $ligne) {
                // Détecter les codes-barres
                if (preg_match('/^\s*\*(\d+)\*\s*$/', $ligne, $matches)) {
                    $this->imprimerCodeBarre($printer, $matches[1]);
                } else {
                    $ligneConvertie = $this->convertirTextePourImprimante($ligne);
                    $printer->text($ligneConvertie . "\n");
                }
            }
            
            $printer->setEmphasis(false);
            $printer->feed(3);
            $printer->text("\n\n");
            $printer->cut();
            
            try { 
                $printer->pulse(); 
            } catch (\Throwable $t) {}

            return true;

        } catch (\Throwable $e) {
            Log::error("Erreur impression ESC/POS", [
                'printer_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            return false;
        } finally {
            if (isset($printer)) {
                try { 
                    $printer->close(); 
                } catch (\Throwable $e) {}
            }
        }
    }

    /**
     * Imprimer le logo de l'imprimante
     */
    private function imprimerLogo($printer)
    {
        try {
            $sourcePath = storage_path('app/public/' . ltrim($this->logo_path, '/'));
            if (file_exists($sourcePath)) {
                $mode = $this->getConfigValue('logo_mode', 'transparent');
                $processed = $this->preprocessLogoForThermal($sourcePath, 384, $mode);
                if ($processed && file_exists($processed)) {
                    $img = \Mike42\Escpos\EscposImage::load($processed);
                    $printer->setReverseColors(false);
                    try {
                        $printer->bitImage($img);
                    } catch (\Throwable $b) {
                        try { 
                            $printer->graphics($img); 
                        } catch (\Throwable $g) {}
                    }
                    $printer->feed(1);
                    @unlink($processed);
                }
            }
        } catch (\Throwable $t) {
            // Ignorer erreurs d'image
        }
    }

    /**
     * Imprimer un code-barre
     */
    private function imprimerCodeBarre($printer, $commandeId)
    {
        try {
            // Utiliser la commande barcode native CODE128
            $printer->barcode($commandeId, \Mike42\Escpos\Printer::BARCODE_CODE128);
            $printer->feed(1);
            $printer->text($commandeId . "\n");
            $printer->feed(1);
        } catch (\Exception $e) {
            // Fallback vers Picqer/Barcode
            try {
                $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
                $barcode = $generator->getBarcode($commandeId, $generator::TYPE_CODE_128, 3, 60);
                
                $tempFile = tempnam(sys_get_temp_dir(), 'barcode_') . '.png';
                file_put_contents($tempFile, $barcode);
                
                $image = \Mike42\Escpos\EscposImage::load($tempFile);
                $printer->bitImage($image);
                $printer->feed(1);
                $printer->text($commandeId . "\n");
                $printer->feed(1);
                
                unlink($tempFile);
            } catch (\Exception $e2) {
                // Fallback final - afficher le code en texte
                $printer->text("Code: " . $commandeId . "\n");
            }
        }
    }

    /**
     * Convertir le texte pour éviter les caractères illisibles
     */
    private function convertirTextePourImprimante($texte)
    {
        $remplacements = [
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
            'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
            'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
            'ç' => 'c',
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A',
            'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O',
            'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U',
            'Ç' => 'C',
            '€' => 'EUR', '£' => 'GBP', '¥' => 'YEN',
            '°' => 'deg', '²' => '2', '³' => '3',
            '«' => '"', '»' => '"', '‹' => "'", '›' => "'",
            '–' => '-', '—' => '-', '…' => '...',
        ];
        
        return strtr($texte, $remplacements);
    }

    /**
     * Pré-traiter une image pour imprimante thermique
     */
    private function preprocessLogoForThermal(string $inputPath, int $targetWidth = 384, string $mode = 'transparent'): ?string
    {
        try {
            if (!function_exists('imagecreatefrompng')) {
                return null;
            }

            $mime = mime_content_type($inputPath) ?: '';
            $mime = strtolower($mime);
            $isPng = false;
            
            if (str_contains($mime, 'png')) {
                $src = @imagecreatefrompng($inputPath);
                $isPng = true;
            } elseif (str_contains($mime, 'jpeg') || str_contains($mime, 'jpg')) {
                $src = @imagecreatefromjpeg($inputPath);
            } elseif (str_contains($mime, 'webp')) {
                if (function_exists('imagecreatefromwebp')) {
                    $src = @imagecreatefromwebp($inputPath);
                } else {
                    return null;
                }
            } else {
                $ext = strtolower(pathinfo($inputPath, PATHINFO_EXTENSION));
                if ($ext === 'png') { 
                    $src = @imagecreatefrompng($inputPath); 
                    $isPng = true; 
                }
                elseif (in_array($ext, ['jpg','jpeg'])) $src = @imagecreatefromjpeg($inputPath);
                elseif ($ext === 'webp' && function_exists('imagecreatefromwebp')) $src = @imagecreatefromwebp($inputPath);
                else return null;
            }
            
            if (!$src) return null;

            $srcW = imagesx($src); 
            $srcH = imagesy($src);
            if ($srcW <= 0 || $srcH <= 0) { 
                imagedestroy($src); 
                return null; 
            }
            
            $scale = min(1.0, $targetWidth / max(1, $srcW));
            $newW = max(1, (int) round($srcW * $scale));
            $newH = max(1, (int) round($srcH * $scale));

            $resized = imagecreatetruecolor($newW, $newH);
            if ($isPng) {
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
                $transparent = imagecolorallocatealpha($resized, 255, 255, 255, 127);
                imagefill($resized, 0, 0, $transparent);
                imagealphablending($src, true); 
                imagesavealpha($src, true);
                imagecopyresampled($resized, $src, 0, 0, 0, 0, $newW, $newH, $srcW, $srcH);
            } else {
                $white = imagecolorallocate($resized, 255, 255, 255);
                imagefill($resized, 0, 0, $white);
                imagecopyresampled($resized, $src, 0, 0, 0, 0, $newW, $newH, $srcW, $srcH);
            }
            imagedestroy($src);

            $bw = imagecreatetruecolor($newW, $newH);
            $white = imagecolorallocate($bw, 255, 255, 255);
            $black = imagecolorallocate($bw, 0, 0, 0);
            imagefill($bw, 0, 0, $white);

            for ($y = 0; $y < $newH; $y++) {
                for ($x = 0; $x < $newW; $x++) {
                    $rgba = imagecolorat($resized, $x, $y);
                    $cols = imagecolorsforindex($resized, $rgba);
                    $r = $cols['red']; 
                    $g = $cols['green']; 
                    $b = $cols['blue'];
                    $a = isset($cols['alpha']) ? $cols['alpha'] : 0;

                    if ($mode === 'inverse') {
                        $luma = (int) round(0.299*$r + 0.587*$g + 0.114*$b);
                        imagesetpixel($bw, $x, $y, ($luma > 220) ? $black : $white);
                    } elseif ($mode === 'transparent' && $isPng) {
                        $isTransparent = ($a >= 100);
                        imagesetpixel($bw, $x, $y, $isTransparent ? $white : $black);
                    } else {
                        $luma = (int) round(0.299*$r + 0.587*$g + 0.114*$b);
                        imagesetpixel($bw, $x, $y, ($luma > 220) ? $white : $black);
                    }
                }
            }

            $tmp = tempnam(sys_get_temp_dir(), 'logo_bw_');
            $out = $tmp . '.png';
            @unlink($tmp);
            imagepng($bw, $out, 9);
            imagedestroy($bw);
            return $out;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Obtenir les statistiques d'impression
     */
    public function getStatistiques($dateDebut = null, $dateFin = null): array
    {
        $query = PrintLog::query()
            ->where('hotel_id', $this->hotel_id)
            ->where('printer_id', $this->id);

        if ($dateDebut) {
            $query->where('created_at', '>=', $dateDebut);
        }

        if ($dateFin) {
            $query->where('created_at', '<=', $dateFin);
        }

        $total = $query->count();

        return [
            'total' => $total,
            'succes' => (clone $query)->where('statut', 'succes')->count(),
            'echec' => (clone $query)->where('statut', 'echec')->count(),
            'en_attente' => (clone $query)->where('statut', 'en_attente')->count(),
            'en_cours' => (clone $query)->where('statut', 'en_cours')->count(),
            'annule' => (clone $query)->where('statut', 'annule')->count(),
            'taux_succes' => $total > 0 ? round(((clone $query)->where('statut', 'succes')->count() / $total) * 100, 2) : 0,
            'temps_moyen' => (clone $query)->whereNotNull('debut_impression')
                                          ->whereNotNull('fin_impression')
                                          ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, debut_impression, fin_impression)) as temps_moyen')
                                          ->value('temps_moyen')
        ];
    }

    /**
     * Obtenir les types de technologies disponibles
     */
    public static function getTypesTechnologies()
    {
        return [
            'thermique' => 'Imprimante thermique (ESC/POS)',
            'laser' => 'Imprimante laser',
            'jet_encre' => 'Imprimante à jet d\'encre',
            'multifonction' => 'Imprimante multifonction'
        ];
    }

    /**
     * Obtenir les types de documents supportés
     */
    public static function getTypesDocuments()
    {
        return PrintLog::getTypesDocuments();
    }
}
