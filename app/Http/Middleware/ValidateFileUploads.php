<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateFileUploads
{
    /**
     * Extensions de fichiers autorisées
     */
    protected $allowedExtensions = [
        'pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp'
    ];

    /**
     * MIME types autorisés
     */
    protected $allowedMimeTypes = [
        'application/pdf',
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/gif',
        'image/webp',
    ];

    /**
     * Taille maximale en bytes (5MB)
     */
    protected $maxSize = 5242880;

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifier tous les fichiers uploadés
        if ($request->hasFile('piece_identite_recto')) {
            $this->validateFile($request->file('piece_identite_recto'));
        }

        if ($request->hasFile('piece_identite_verso')) {
            $this->validateFile($request->file('piece_identite_verso'));
        }

        if ($request->hasFile('logo')) {
            $this->validateFile($request->file('logo'));
        }

        // Vérifier les données base64 (photos de la caméra)
        if ($request->filled('photo_recto')) {
            $this->validateBase64Image($request->input('photo_recto'));
        }

        if ($request->filled('photo_verso')) {
            $this->validateBase64Image($request->input('photo_verso'));
        }

        if ($request->filled('signature') || $request->filled('signature_data')) {
            $signatureData = $request->input('signature') ?? $request->input('signature_data');
            $this->validateBase64Image($signatureData);
        }

        return $next($request);
    }

    /**
     * Valider un fichier uploadé
     */
    protected function validateFile($file): void
    {
        if (!$file->isValid()) {
            abort(422, 'Le fichier uploadé n\'est pas valide.');
        }

        // Vérifier la taille
        if ($file->getSize() > $this->maxSize) {
            abort(422, 'Le fichier est trop volumineux. Taille maximale : 5MB.');
        }

        // Vérifier l'extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, $this->allowedExtensions)) {
            abort(422, 'Type de fichier non autorisé. Extensions autorisées : ' . implode(', ', $this->allowedExtensions));
        }

        // Vérifier le MIME type réel
        $mimeType = $file->getMimeType();
        if (!in_array($mimeType, $this->allowedMimeTypes)) {
            abort(422, 'Type MIME non autorisé.');
        }

        // Protection supplémentaire : vérifier que c'est vraiment une image/PDF
        if (str_starts_with($mimeType, 'image/')) {
            $this->validateImageContent($file);
        }
    }

    /**
     * Valider une image base64
     */
    protected function validateBase64Image(?string $base64Data): void
    {
        if (empty($base64Data)) {
            return;
        }

        // Vérifier le format base64
        if (!preg_match('/^data:image\/(png|jpg|jpeg|gif|webp);base64,/', $base64Data)) {
            abort(422, 'Format d\'image base64 invalide.');
        }

        // Extraire les données
        $data = substr($base64Data, strpos($base64Data, ',') + 1);
        $data = base64_decode($data);

        // Vérifier la taille décodée
        if (strlen($data) > $this->maxSize) {
            abort(422, 'L\'image est trop volumineuse. Taille maximale : 5MB.');
        }

        // Vérifier que c'est vraiment une image valide
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($data);

        if (!in_array($mimeType, ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'])) {
            abort(422, 'Le contenu de l\'image n\'est pas valide.');
        }
    }

    /**
     * Valider le contenu d'une image
     */
    protected function validateImageContent($file): void
    {
        // Essayer de lire l'image avec GD ou Imagick
        $path = $file->getRealPath();
        
        try {
            $imageInfo = @getimagesize($path);
            if ($imageInfo === false) {
                abort(422, 'Le fichier n\'est pas une image valide.');
            }

            // Vérifier les dimensions (max 10000x10000)
            if ($imageInfo[0] > 10000 || $imageInfo[1] > 10000) {
                abort(422, 'Les dimensions de l\'image sont trop grandes.');
            }

        } catch (\Exception $e) {
            abort(422, 'Impossible de valider l\'image.');
        }
    }
}

