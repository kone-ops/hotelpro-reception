<?php

namespace App\Http\Controllers\HotelAdmin;

use App\Http\Controllers\Controller;
use App\Models\FormField;
use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class FormFieldController extends Controller
{
    public function index()
    {
        $hotel = Auth::user()->hotel;
        $formFields = $hotel->formFields()->orderBy('position')->get();
        return view('hotel.form-fields.index', compact('hotel', 'formFields'));
    }

    public function create()
    {
        $hotel = Auth::user()->hotel;
        return view('hotel.form-fields.create', compact('hotel'));
    }

    public function store(Request $request)
    {
        $hotel = Auth::user()->hotel;

        $validatedData = $request->validate([
            'label' => 'required|string|max:255',
            'type' => 'required|string|in:text,email,tel,date,number,file,textarea,select,checkbox',
            'required' => 'boolean',
            'position' => 'integer',
            'options' => 'nullable|string',
        ]);

        // Générer automatiquement la clé depuis le label
        $validatedData['key'] = Str::slug($validatedData['label'], '_');
        
        // Vérifier l'unicité de la clé pour cet hôtel
        $baseKey = $validatedData['key'];
        $counter = 1;
        while (FormField::where('hotel_id', $hotel->id)->where('key', $validatedData['key'])->exists()) {
            $validatedData['key'] = $baseKey . '_' . $counter;
            $counter++;
        }

        $validatedData['hotel_id'] = $hotel->id;
        $validatedData['active'] = true;
        $validatedData['required'] = $request->has('required');
        
        // Convertir options en JSON si fourni
        if (!empty($validatedData['options'])) {
            $validatedData['options'] = json_decode($validatedData['options'], true);
        }

        FormField::create($validatedData);

        return redirect()->route('hotel.fields.index')->with('success', 'Champ de formulaire ajouté avec succès.');
    }

    public function edit(FormField $field)
    {
        $hotel = Auth::user()->hotel;
        
        // Vérifier que le champ appartient à l'hôtel de l'utilisateur
        if ($field->hotel_id !== $hotel->id) {
            abort(403);
        }
        
        // Si c'est une requête AJAX, retourner JSON
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json($field);
        }
        
        return view('hotel.form-fields.edit', compact('hotel', 'field'));
    }

    public function update(Request $request, FormField $field)
    {
        $hotel = Auth::user()->hotel;
        
        // Vérifier que le champ appartient à l'hôtel de l'utilisateur
        if ($field->hotel_id !== $hotel->id) {
            abort(403);
        }

        $validatedData = $request->validate([
            'label' => 'required|string|max:255',
            'type' => 'required|string|in:text,email,tel,date,number,file,textarea,select,checkbox',
            'required' => 'boolean',
            'position' => 'integer',
            'options' => 'nullable|string',
        ]);

        // Régénérer la clé si le label change
        $newKey = Str::slug($validatedData['label'], '_');
        if ($newKey !== $field->key) {
            // Vérifier l'unicité
            $baseKey = $newKey;
            $counter = 1;
            while (FormField::where('hotel_id', $hotel->id)->where('key', $newKey)->where('id', '!=', $field->id)->exists()) {
                $newKey = $baseKey . '_' . $counter;
                $counter++;
            }
            $validatedData['key'] = $newKey;
        }

        $validatedData['required'] = $request->has('required');
        
        // Convertir options en JSON si fourni
        if (!empty($validatedData['options'])) {
            $validatedData['options'] = json_decode($validatedData['options'], true);
        }

        $field->update($validatedData);

        return redirect()->route('hotel.fields.index')->with('success', 'Champ de formulaire mis à jour avec succès.');
    }

    public function destroy(FormField $field)
    {
        $hotel = Auth::user()->hotel;
        
        // Vérifier que le champ appartient à l'hôtel de l'utilisateur
        if ($field->hotel_id !== $hotel->id) {
            abort(403);
        }
        
        $field->delete();
        return redirect()->route('hotel.fields.index')->with('success', 'Champ de formulaire supprimé avec succès.');
    }

    public function updateOrder(Request $request)
    {
        $hotel = Auth::user()->hotel;
        $order = $request->input('order', []);

        foreach ($order as $index => $fieldId) {
            FormField::where('id', $fieldId)
                ->where('hotel_id', $hotel->id)
                ->update(['position' => $index]);
        }

        return response()->json(['success' => true]);
    }
}
