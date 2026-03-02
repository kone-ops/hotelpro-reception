<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DocumentService
{
    /**
     * Sauvegarder une image base64
     *
     * @param string $base64Image
     * @param string $subdirectory Sous-dossier dans images/uploads (ex: 'documents', 'logos')
     * @param string $prefix
     * @return string|null Le chemin relatif depuis public/ (ex: 'images/uploads/documents/xxx.jpg')
     */
    public function saveBase64Image(string $base64Image, string $subdirectory = 'documents', string $prefix = 'img'): ?string
    {
        try {
            // Vérifier si c'est bien une image base64
            if (!preg_match('/^data:image\/(\w+);base64,/', $base64Image, $matches)) {
                return null;
            }

            // Extraire l'extension
            $extension = $matches[1];
            
            // Extraire les données base64
            $imageData = substr($base64Image, strpos($base64Image, ',') + 1);
            $imageData = base64_decode($imageData);

            if ($imageData === false) {
                return null;
            }

            // Générer un nom de fichier unique
            $filename = $prefix . '_' . Str::random(40) . '.' . $extension;
            $directory = public_path('images/uploads/' . $subdirectory);
            $path = 'images/uploads/' . $subdirectory . '/' . $filename;

            // Créer le répertoire s'il n'existe pas
            if (!File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
            }

            // Sauvegarder le fichier
            File::put($directory . '/' . $filename, $imageData);

            return $path;
        } catch (\Exception $e) {
            Log::error('Erreur lors de la sauvegarde de l\'image base64: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Sauvegarder un fichier uploadé
     *
     * @param UploadedFile $file
     * @param string $subdirectory Sous-dossier dans images/uploads (ex: 'documents', 'logos')
     * @param string $prefix
     * @return string|null Le chemin relatif depuis public/ (ex: 'images/uploads/documents/xxx.jpg')
     */
    public function saveUploadedFile(UploadedFile $file, string $subdirectory = 'documents', string $prefix = 'doc'): ?string
    {
        try {
            $extension = $file->getClientOriginalExtension();
            $filename = $prefix . '_' . Str::random(40) . '.' . $extension;
            
            $directory = public_path('images/uploads/' . $subdirectory);
            $path = 'images/uploads/' . $subdirectory . '/' . $filename;

            // Créer le répertoire s'il n'existe pas
            if (!File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
            }

            // Déplacer le fichier
            $file->move($directory, $filename);
            
            return $path;
        } catch (\Exception $e) {
            Log::error('Erreur lors de la sauvegarde du fichier uploadé: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Supprimer un fichier
     *
     * @param string|null $path Chemin relatif depuis public/ (ex: 'images/uploads/xxx.jpg')
     * @return bool
     */
    public function deleteFile(?string $path): bool
    {
        if (!$path) {
            return false;
        }

        try {
            // Nettoyer le chemin (enlever 'storage/' si présent pour compatibilité)
            $path = str_replace('storage/', 'images/', $path);
            $fullPath = public_path($path);
            
            if (File::exists($fullPath)) {
                return File::delete($fullPath);
            }
            
            return false;
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression du fichier: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifier si un fichier existe
     *
     * @param string|null $path Chemin relatif depuis public/ (ex: 'images/uploads/xxx.jpg')
     * @return bool
     */
    public function fileExists(?string $path): bool
    {
        if (!$path) {
            return false;
        }

        // Nettoyer le chemin (enlever 'storage/' si présent pour compatibilité)
        $path = str_replace('storage/', 'images/', $path);
        $fullPath = public_path($path);
        
        return File::exists($fullPath);
    }

    /**
     * Obtenir l'URL publique d'un fichier
     *
     * @param string|null $path Chemin relatif depuis public/ (ex: 'images/uploads/xxx.jpg')
     * @return string|null
     */
    public function getFileUrl(?string $path): ?string
    {
        if (!$path || !$this->fileExists($path)) {
            return null;
        }

        // Nettoyer le chemin (enlever 'storage/' si présent pour compatibilité)
        $path = str_replace('storage/', 'images/', $path);
        return asset($path);
    }

    /**
     * Optimiser une image (réduire la taille si trop grande)
     *
     * @param string $path Chemin relatif depuis public/ (ex: 'images/uploads/xxx.jpg')
     * @param int $maxWidth
     * @param int $maxHeight
     * @return bool
     */
    public function optimizeImage(string $path, int $maxWidth = 1920, int $maxHeight = 1080): bool
    {
        try {
            // Nettoyer le chemin
            $path = str_replace('storage/', 'images/', $path);
            $fullPath = public_path($path);
            
            if (!File::exists($fullPath)) {
                return false;
            }

            $imageInfo = getimagesize($fullPath);
            if (!$imageInfo) {
                return false;
            }

            list($width, $height, $type) = $imageInfo;

            // Si l'image est déjà petite, ne rien faire
            if ($width <= $maxWidth && $height <= $maxHeight) {
                return true;
            }

            // Calculer les nouvelles dimensions
            $ratio = min($maxWidth / $width, $maxHeight / $height);
            $newWidth = (int)($width * $ratio);
            $newHeight = (int)($height * $ratio);

            // Créer une nouvelle image
            $newImage = imagecreatetruecolor($newWidth, $newHeight);

            // Charger l'image source
            switch ($type) {
                case IMAGETYPE_JPEG:
                    $source = imagecreatefromjpeg($fullPath);
                    break;
                case IMAGETYPE_PNG:
                    $source = imagecreatefrompng($fullPath);
                    imagealphablending($newImage, false);
                    imagesavealpha($newImage, true);
                    break;
                case IMAGETYPE_GIF:
                    $source = imagecreatefromgif($fullPath);
                    break;
                default:
                    return false;
            }

            // Redimensionner
            imagecopyresampled($newImage, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            // Sauvegarder
            switch ($type) {
                case IMAGETYPE_JPEG:
                    imagejpeg($newImage, $fullPath, 85);
                    break;
                case IMAGETYPE_PNG:
                    imagepng($newImage, $fullPath, 8);
                    break;
                case IMAGETYPE_GIF:
                    imagegif($newImage, $fullPath);
                    break;
            }

            imagedestroy($source);
            imagedestroy($newImage);

            return true;
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'optimisation de l\'image: ' . $e->getMessage());
            return false;
        }
    }
}
