<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->user()->transactions()->with('product');

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('created_at', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ]);
        }

        if ($request->has('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $transactions = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $transactions
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'nullable|exists:products,id',
            'product_name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'payment_method' => 'required|in:Cash,QRIS,Transfer',
            'notes' => 'nullable|string',
        ]);

        $total = $request->price * $request->quantity;

        // If product_id is provided, decrease stock
        if ($request->product_id) {
            $product = Product::find($request->product_id);
            if ($product && $product->user_id === $request->user()->id) {
                if ($product->stock < $request->quantity) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Stok tidak mencukupi'
                    ], 400);
                }
                $product->decrement('stock', $request->quantity);
            }
        }

        $transaction = $request->user()->transactions()->create([
            'product_id' => $request->product_id,
            'product_name' => $request->product_name,
            'quantity' => $request->quantity,
            'price' => $request->price,
            'total' => $total,
            'payment_method' => $request->payment_method,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transaksi berhasil disimpan',
            'data' => $transaction
        ], 201);
    }

    public function show(Request $request, Transaction $transaction)
    {
        if ($transaction->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $transaction->load('product')
        ]);
    }

    public function todayStats(Request $request)
    {
        $today = Carbon::today();
        $transactions = $request->user()->transactions()
            ->whereDate('created_at', $today)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total_transaksi' => $transactions->count(),
                'pendapatan' => $transactions->sum('total'),
                'item_terjual' => $transactions->sum('quantity'),
            ]
        ]);
    }

    public function recent(Request $request)
    {
        $transactions = $request->user()->transactions()
            ->with('product')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $transactions
        ]);
    }
}

