<?php

namespace App\Http\Controllers;

use App\Services\ContaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Conta",
    description: "API para autenticação e gerenciamento de conta"
)]
class ContaController extends Controller
{
    public function __construct(private ContaService $contaService) {}

    #[OA\Post(
        path: "/api/login",
        summary: "Autentica um usuário",
        tags: ["Conta"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "password"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email"),
                    new OA\Property(property: "password", type: "string")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Login realizado com sucesso"),
            new OA\Response(response: 401, description: "Credenciais inválidas")
        ]
    )]
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

    #[OA\Post(
        path: "/api/contas",
        summary: "Cria nova conta com carteira",
        tags: ["Conta"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "email", "password"],
                properties: [
                    new OA\Property(property: "name", type: "string"),
                    new OA\Property(property: "email", type: "string", format: "email"),
                    new OA\Property(property: "password", type: "string", minLength: 6)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Conta criada com sucesso"),
            new OA\Response(response: 400, description: "Dados inválidos")
        ]
    )]
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

    #[OA\Get(
        path: "/api/conta",
        summary: "Retorna dados da conta autenticada",
        tags: ["Conta"],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: "Dados da conta retornados com sucesso"),
            new OA\Response(response: 401, description: "Não autenticado"),
            new OA\Response(response: 404, description: "Conta não encontrada")
        ]
    )]
    public function getConta(Request $request)
    {
        try {
            $user = $request->user();
            $conta = $this->contaService->getUserById($user->id);
            return response()->json($conta['content'], $conta['status']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    #[OA\Post(
        path: "/api/logout",
        summary: "Faz logout do usuário autenticado",
        tags: ["Conta"],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: "Logout realizado com sucesso"),
            new OA\Response(response: 401, description: "Não autenticado")
        ]
    )]
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