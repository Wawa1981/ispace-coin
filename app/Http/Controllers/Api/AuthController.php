<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function test()
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Contrôleur AuthController opérationnel ✅'
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Création d’un token Sanctum
        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Utilisateur créé avec succès ✅',
            'user' => $user,
            'token' => $token,
        ]);
    }
    public function login(Request $request)
   {
   
        $request->validate([
        'email' => 'required|email',
        'password' => 'required'
    ]);

    $user = \App\Models\User::where('email', $request->email)->first();

    if (! $user || ! \Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
        return response()->json([
            'status' => 'error',
            'message' => 'Identifiants invalides ❌'
        ], 401);
    }

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'status' => 'success',
        'message' => 'Connexion réussie ✅',
        'user' => $user,
        'token' => $token,
    ]);
 }

    public function logout(Request $request)
{
    $request->user()->currentAccessToken()->delete();
    return response()->json(['status' => 'success', 'message' => 'Déconnexion réussie ✅']);
}

}
