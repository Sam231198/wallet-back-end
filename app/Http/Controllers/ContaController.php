<?php

namespace App\Http\Controllers;

use App\Services\ContaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContaController extends Controller
{
    public function __construct(private ContaService $contaService) {}

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        try {
            $conta = $this->contaService->login($credentials['email'], $credentials['password']);
            return response()->json($conta['content'], $conta['status']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }
    }

    public function createConta(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6'],
            'email' => ['required', 'email'],
        ]);

        try {
            $conta = $this->contaService->createUserWithWallet($data);
            return response()->json($conta['content'], $conta['status']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function getConta(Request $request)
    {
        try {
            $user = $request->user(); // Ensure the user is authenticated
            $conta = $this->contaService->getUserById($user->id);
            return response()->json($conta['content'], $conta['status']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $user->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout realizado com sucesso']);
    }
}
