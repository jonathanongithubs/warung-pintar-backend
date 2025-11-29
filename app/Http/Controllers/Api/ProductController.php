<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->user()->products();

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        $products = $query->orderBy('created_at', 'desc')->get();

        $totalProducts = $products->count();
        $lowStockProducts = $products->where('stock', '<', 15)->count();
        $totalValue = $products->sum(function ($product) {
            return $product->price * $product->stock;
        });

        return response()->json([
            'success' => true,
            'data' => [
                'products' => $products,
                'stats' => [
                    'total_products' => $totalProducts,
                    'low_stock_products' => $lowStockProducts,
                    'total_value' => $totalValue,
                ]
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $product = $request->user()->products()->create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil ditambahkan',
            'data' => $product
        ], 201);
    }

    public function show(Request $request, Product $product)
    {
        if ($product->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $product
        ]);
    }

    public function update(Request $request, Product $product)
    {
        if ($product->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan'
            ], 404);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'price' => 'sometimes|numeric|min:0',
            'stock' => 'sometimes|integer|min:0',
            'category' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
        ]);

        $product->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil diperbarui',
            'data' => $product
        ]);
    }

    public function destroy(Request $request, Product $product)
    {
        if ($product->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan'
            ], 404);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil dihapus'
        ]);
    }

    public function restock(Request $request, Product $product)
    {
        if ($product->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan'
            ], 404);
        }

        $request->validate([
            'amount' => 'required|integer|min:1',
        ]);

        $product->increment('stock', $request->amount);

        return response()->json([
            'success' => true,
            'message' => 'Stok berhasil ditambahkan',
            'data' => $product->fresh()
        ]);
    }

    public function lowStock(Request $request)
    {
        $products = $request->user()->products()
            ->where('stock', '<', 15)
            ->orderBy('stock', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }
}

