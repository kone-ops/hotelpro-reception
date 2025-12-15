<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['hotel', 'roles']);
        
        // Filtrer par hôtel si spécifié
        if ($request->filled('hotel')) {
            $query->where('hotel_id', $request->hotel);
        }
        
        // Filtrer par rôle si spécifié
        if ($request->filled('role')) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('name', $request->role);
            });
        }
        
        $users = $query->get();
        $hotels = Hotel::all();
        $roles = Role::all();
        
        return view('super.users.index', compact('users', 'hotels', 'roles'));
    }

    public function create()
    {
        $hotels = Hotel::all();
        $roles = Role::all();
        return view('super.users.create', compact('hotels', 'roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'hotel_id' => 'nullable|exists:hotels,id',
            'role' => 'required|string|exists:roles,name',
        ]);

        // Si le rôle est super-admin, on ne doit pas avoir d'hotel_id
        $hotelId = null;
        if ($data['role'] !== 'super-admin') {
            // Pour hotel-admin et receptionist, l'hotel_id est obligatoire
            if (empty($data['hotel_id'])) {
                return back()->withErrors(['hotel_id' => 'L\'hôtel est obligatoire pour ce rôle.'])->withInput();
            }
            $hotelId = $data['hotel_id'];
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'hotel_id' => $hotelId,
        ]);

        $user->assignRole($data['role']);

        return redirect()->route('super.users.index')->with('success', 'Utilisateur créé avec succès');
    }

    public function show(User $user)
    {
        // Si c'est une requête AJAX, retourner JSON
        if (request()->wantsJson() || request()->ajax()) {
            // Nettoyer tout output buffer pour éviter les BOM
            if (ob_get_length()) ob_clean();
            $user->load(['hotel', 'roles']);
            return response()->json($user, 200, [], JSON_UNESCAPED_UNICODE);
        }
        
        $user->load(['hotel', 'roles']);
        return view('super.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $hotels = Hotel::all();
        $roles = Role::all();
        $user->load(['roles']);
        return view('super.users.edit', compact('user', 'hotels', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'nullable|string|min:8',
            'hotel_id' => 'nullable|exists:hotels,id',
            'role' => 'required|string|exists:roles,name',
        ]);

        // Si le rôle est super-admin, on ne doit pas avoir d'hotel_id
        $hotelId = null;
        if ($data['role'] !== 'super-admin') {
            // Pour hotel-admin et receptionist, l'hotel_id est obligatoire
            if (empty($data['hotel_id'])) {
                return back()->withErrors(['hotel_id' => 'L\'hôtel est obligatoire pour ce rôle.'])->withInput();
            }
            $hotelId = $data['hotel_id'];
        }

        $user->update([
            'name' => $data['name'],
            'hotel_id' => $hotelId,
        ]);

        if (!empty($data['password'])) {
            $user->update(['password' => Hash::make($data['password'])]);
        }

        $user->syncRoles([$data['role']]);

        return redirect()->route('super.users.index')->with('success', 'Utilisateur mis à jour');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('super.users.index')->with('success', 'Utilisateur supprimé');
    }
}
