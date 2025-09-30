<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    /**
     * Get all transactions
     */
    public function index(Request $request)
    {
        $query = Transaction::with(['order', 'user']);

        // Filter by user
        $query->where('user_id', $request->user()->id);

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $transactions
        ]);
    }

    /**
     * Get single transaction
     */
    public function show($id)
    {
        $transaction = Transaction::with(['order', 'user'])->find($id);

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found'
            ], 404);
        }

        // Check ownership
        if ($transaction->user_id !== request()->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view this transaction'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $transaction
        ]);
    }

    /**
     * Create new transaction
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'type' => 'required|in:income,expense,refund',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string|max:100',
            'payment_reference' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check order ownership
        $order = Order::find($request->order_id);
        if (!$order || ($order->buyer_id !== $request->user()->id && $order->seller_id !== $request->user()->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to create transaction for this order'
            ], 403);
        }

        $transaction = Transaction::create([
            'transaction_number' => Transaction::generateTransactionNumber(),
            'order_id' => $request->order_id,
            'user_id' => $request->user()->id,
            'type' => $request->type,
            'amount' => $request->amount,
            'status' => 'pending',
            'payment_method' => $request->payment_method,
            'payment_reference' => $request->payment_reference,
            'description' => $request->description
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transaction created successfully',
            'data' => $transaction->load(['order', 'user'])
        ], 201);
    }

    /**
     * Update transaction status
     */
    public function update(Request $request, $id)
    {
        $transaction = Transaction::find($id);

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found'
            ], 404);
        }

        // Check ownership
        if ($transaction->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this transaction'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,completed,failed,cancelled'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $transaction->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Transaction status updated successfully',
            'data' => $transaction->load(['order', 'user'])
        ]);
    }

    /**
     * Get transaction statistics
     */
    public function statistics(Request $request)
    {
        $userId = $request->user()->id;
        
        $stats = [
            'total_transactions' => Transaction::where('user_id', $userId)->count(),
            'total_income' => Transaction::where('user_id', $userId)
                ->where('type', 'income')
                ->where('status', 'completed')
                ->sum('amount'),
            'total_expense' => Transaction::where('user_id', $userId)
                ->where('type', 'expense')
                ->where('status', 'completed')
                ->sum('amount'),
            'total_refund' => Transaction::where('user_id', $userId)
                ->where('type', 'refund')
                ->where('status', 'completed')
                ->sum('amount'),
            'pending_transactions' => Transaction::where('user_id', $userId)
                ->where('status', 'pending')
                ->count(),
            'completed_transactions' => Transaction::where('user_id', $userId)
                ->where('status', 'completed')
                ->count(),
            'failed_transactions' => Transaction::where('user_id', $userId)
                ->where('status', 'failed')
                ->count(),
            'net_income' => Transaction::where('user_id', $userId)
                ->where('status', 'completed')
                ->where('type', 'income')
                ->sum('amount') - Transaction::where('user_id', $userId)
                ->where('status', 'completed')
                ->where('type', 'expense')
                ->sum('amount')
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
