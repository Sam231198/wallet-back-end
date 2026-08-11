<?php

namespace App\Http\Controllers;

use App\Services\OperationService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

/**
 * @OA\Tag(
 *     name="Operações",
 *     description="API para operações de carteira"
 * )
 */
#[OA\Tag(
    name: 'Operações',
    description: 'API para operações de carteira'
)]
class OperationController extends Controller
{
    protected $user;

    public function __construct(private OperationService $operationService)
    {
        $this->user = auth()->user();
    }

    #[OA\Get(
        path: '/api/wallets/{walletId}/history',
        summary: 'Retorna histórico de operações da carteira',
        tags: ['Operações'],
        parameters: [
            new OA\Parameter(
                name: 'walletId',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Histórico retornado com sucesso'),
            new OA\Response(response: 400, description: 'Erro ao buscar histórico'),
        ]
    )]
    public function getHistory(int $walletId)
    {
        try {
            $result = $this->operationService->getHistory($walletId);

            return response()->json($result['content'], $result['status']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    #[OA\Post(
        path: '/api/operations/deposit',
        summary: 'Depósito em carteira',
        tags: ['Operações'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['wallet_id', 'amount'],
                properties: [
                    new OA\Property(property: 'amount', type: 'number', format: 'float'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Depósito realizado com sucesso'),
            new OA\Response(response: 400, description: 'Erro no depósito'),
        ]
    )]
    public function deposit(Request $request)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        try {
            $result = $this->operationService->deposit($this->user->id,$data['amount']);

            return response()->json($result['content'], $result['status']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    #[OA\Post(
        path: '/api/operations/withdraw',
        summary: 'Saque de carteira',
        tags: ['Operações'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['amount'],
                properties: [
                    new OA\Property(property: 'amount', type: 'number', format: 'float'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Saque realizado com sucesso'),
            new OA\Response(response: 400, description: 'Erro no saque'),
        ]
    )]
    public function withdraw(Request $request)
    {
        $data = $request->validate([
            'wallet_id' => 'required|integer',
            'amount' => 'required|numeric|min:0.01',
        ]);

        try {
            $result = $this->operationService->withdraw($this->user->id,$data['amount']);

            return response()->json($result['content'], $result['status']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    #[OA\Post(
        path: '/api/operations/transfer',
        summary: 'Transferência entre carteiras',
        tags: ['Operações'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['to_wallet_id', 'amount'],
                properties: [
                    new OA\Property(property: 'to_wallet_id', type: 'integer'),
                    new OA\Property(property: 'amount', type: 'number', format: 'float'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Transferência realizada com sucesso'),
            new OA\Response(response: 400, description: 'Erro na transferência'),
        ]
    )]
    public function transfer(Request $request)
    {
        $data = $request->validate([
            'from_wallet_id' => 'required|integer',
            'to_wallet_id' => 'required|integer',
            'amount' => 'required|numeric|min:0.01',
        ]);

        try {
            $result = $this->operationService->transfer($this->user->id,$data['to_wallet_id'], $data['amount']);

            return response()->json($result['content'], $result['status']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
