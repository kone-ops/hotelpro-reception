<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\FormField;
use App\Models\Hotel;
use Illuminate\Http\Request;

class FormFieldController extends Controller
{
    public function index(Request $request)
    {
        $query = FormField::with('hotel');
        
        if ($request->has('hotel') && $request->hotel) {
            $query->where('hotel_id', $request->hotel);
        }
        
        $formFields = $query->orderBy('hotel_id')->orderBy('order')->get();
        $hotels = Hotel::all();
        
        return view('super.forms.index', compact('formFields', 'hotels'));
    }

    public function create()
    {
        $hotels = Hotel::all();
        return view('super.forms.create', compact('hotels'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'hotel_id' => 'required|exists:hotels,id',
            'name' => 'required|string|max:255',
            'label' => 'required|string|max:255',
            'type' => 'required|string|in:text,email,number,date,file,signature,checkbox,textarea,select',
            'is_required' => 'boolean',
            'order' => 'integer',
            'options' => 'nullable|json',
        ]);

        FormField::create($data);
        return redirect()->route('super.forms.index')->with('success', 'Champ de formulaire créé avec succès');
    }

    public function show(FormField $formField)
    {
        $formField->load('hotel');
        return view('super.forms.show', compact('formField'));
    }

    public function edit(FormField $formField)
    {
        $hotels = Hotel::all();
        return view('super.forms.edit', compact('formField', 'hotels'));
    }

    public function update(Request $request, FormField $formField)
    {
        $data = $request->validate([
            'hotel_id' => 'required|exists:hotels,id',
            'name' => 'required|string|max:255',
            'label' => 'required|string|max:255',
            'type' => 'required|string|in:text,email,number,date,file,signature,checkbox,textarea,select',
            'is_required' => 'boolean',
            'order' => 'integer',
            'options' => 'nullable|json',
        ]);

        $formField->update($data);
        return redirect()->route('super.forms.index')->with('success', 'Champ de formulaire mis à jour');
    }

    public function destroy(FormField $formField)
    {
        $formField->delete();
        return redirect()->route('super.forms.index')->with('success', 'Champ de formulaire supprimé');
    }
}
