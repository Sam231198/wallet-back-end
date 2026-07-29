<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OperationService;

class OperationController extends Controller
{
    public function __construct(private OperationService $operationService)
    {
        // Initialization code if needed
    }

    public function getHistory(int $walletId)
    {
        try {
            $result = $this->operationService->getHistory($walletId);
            return response()->json($result['content'], $result['status']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function deposit(Request $request)
    {
        $data = $request->validate([
            'wallet_id' => 'required|integer',
            'amount' => 'required|numeric',
        ]);

        try {
            $result = $this->operationService->deposit($data['wallet_id'], $data['amount']);
            return response()->json($result['content'], $result['status']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function withdraw(Request $request)
    {
        $data = $request->validate([
            'wallet_id' => 'required|integer',
            'amount' => 'required|numeric',
        ]);

        try {
            $result = $this->operationService->withdraw($data['wallet_id'], $data['amount']);
            return response()->json($result['content'], $result['status']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function transfer(Request $request)
    {
        $data = $request->validate([
            'from_wallet_id' => 'required|integer',
            'to_wallet_id' => 'required|integer',
            'amount' => 'required|numeric',
        ]);

        try {
            $result = $this->operationService->transfer($data['from_wallet_id'], $data['to_wallet_id'], $data['amount']);
            return response()->json($result['content'], $result['status']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
