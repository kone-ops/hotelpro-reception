<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use App\Services\ClientService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ClientController extends Controller
{
    protected $clientService;

    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    /**
     * Rechercher un client pour pré-remplir le formulaire
     */
    public function search(Hotel $hotel, Request $request): JsonResponse
    {
        $query = $request->input('q', '');
        
        if (empty($query)) {
            return response()->json([
                'found' => false,
                'message' => 'Veuillez saisir un email, un téléphone ou un numéro de pièce d\'identité'
            ]);
        }

        $client = $this->clientService->searchClient($hotel, $query);

        if ($client) {
            $formData = $this->clientService->getClientFormData($client);
            return response()->json([
                'found' => true,
                'client' => [
                    'id' => $client->id,
                    'full_name' => $client->full_name,
                    'email' => $client->email,
                    'telephone' => $client->telephone,
                    'reservations_count' => $client->reservations_count,
                ],
                'form_data' => $formData
            ]);
        }

        return response()->json([
            'found' => false,
            'message' => 'Aucun client trouvé avec ces informations'
        ]);
    }

    /**
     * Ancienne méthode pour compatibilité (dépréciée)
     * @deprecated Utiliser search() à la place
     */
    public function checkClient(Request $request)
    {
        $email = $request->input('email');
        
        if (!$email) {
            return response()->json(['found' => false]);
        }
        
        // Essayer de trouver via le service
        $hotel = $request->route('hotel') ?? Hotel::first();
        
        if ($hotel) {
            $client = $this->clientService->searchClient($hotel, $email);
            
            if ($client) {
                $formData = $this->clientService->getClientFormData($client);
                return response()->json([
                    'found' => true,
                    'data' => $formData
                ]);
            }
        }
        
        return response()->json(['found' => false]);
    }
}













