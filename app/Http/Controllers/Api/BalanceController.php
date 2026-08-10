<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class BalanceController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $balances = $user->balances()->orderBy('currency')->get();

        return response()->json([
            'success' => true,
            'balances' => $balances,
        ]);
    }

    public function getCryptoPrices()
    {
        try {
            $response = Http::get('https://api.coingecko.com/api/v3/coins/markets', [
                'vs_currency' => 'usd',
                'ids' => 'bitcoin,ethereum,litecoin',
            ]);

            if ($response->failed()) {
                return response()->json(['success' => false, 'error' => 'API externe échouée'], 500);
            }

            $prices = $response->json();

            return response()->json([
                'success' => true,
                'prices' => $prices,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erreur serveur : ' . $e->getMessage(),
            ], 500);
        }
    }

    public function withdraw(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'currency' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $currency = strtoupper($request->input('currency'));
        $amount = $request->input('amount');

        $balance = $user->balances()->where('currency', $currency)->first();

        if (!$balance) {
            return response()->json(['error' => "Solde pour $currency non trouvé."], 404);
        }

        if ($balance->amount < $amount) {
            return response()->json(['error' => 'Solde insuffisant'], 400);
        }

        $balance->amount -= $amount;
        $balance->save();

        return response()->json([
            'message' => "Retrait de $amount $currency effectué avec succès.",
            'balance' => $balance->amount,
        ]);
    }
   
    public function deposit(Request $request)
{
    $validator = Validator::make($request->all(), [
        'currency' => 'required|string',
        'amount' => 'required|numeric|min:0.00000001',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $user = $request->user();
    $currency = strtoupper($request->input('currency'));
    $amount = $request->input('amount');

    // Vérifie si un solde existe déjà
    $balance = $user->balances()->where('currency', $currency)->first();

    if (!$balance) {
        // Crée un nouveau solde
        $balance = $user->balances()->create([
            'currency' => $currency,
            'amount' => $amount,
        ]);
    } else {
        // Ajoute au solde existant
        $balance->amount += $amount;
        $balance->save();
    }

    return response()->json([
        'success' => true,
        'message' => "Dépôt de {$amount} {$currency} effectué avec succès ✅",
        'balance' => $balance,
    ]);
}

}
