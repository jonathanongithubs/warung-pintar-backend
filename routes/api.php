<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\DashboardController;

// Health check for Railway
Route::get('/health', function () {
    return response()->json(['status' => 'ok', 'timestamp' => now()]);
});

// Temporary: Seed demo users (remove after first use)
Route::get('/seed-demo', function () {
    $umkm = \App\Models\User::firstOrCreate(
        ['email' => 'test@warung.com'],
        [
            'name' => 'Warung Test',
            'nama_usaha' => 'Warung Test',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'user_type' => 'umkm',
            'kategori' => 'Makanan',
            'phone' => '081234567890',
            'alamat' => 'Jl. Test No. 123, Jakarta',
        ]
    );
    
    $investor = \App\Models\User::firstOrCreate(
        ['email' => 'investor@test.com'],
        [
            'name' => 'Investor Test',
            'nama_usaha' => 'Investor Test',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'user_type' => 'investor',
            'kategori' => 'Investasi',
            'phone' => '081234567891',
            'alamat' => 'Jl. Investor No. 456, Jakarta',
        ]
    );
    
    return response()->json([
        'success' => true,
        'message' => 'Demo users created!',
        'users' => [
            'umkm' => ['email' => 'test@warung.com', 'password' => 'password123'],
            'investor' => ['email' => 'investor@test.com', 'password' => 'password123'],
        ]
    ]);
});

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::put('/change-password', [AuthController::class, 'changePassword']);

    // Products
    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::get('/products/low-stock', [ProductController::class, 'lowStock']);
    Route::get('/products/{product}', [ProductController::class, 'show']);
    Route::put('/products/{product}', [ProductController::class, 'update']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);
    Route::post('/products/{product}/restock', [ProductController::class, 'restock']);

    // Transactions
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::post('/transactions', [TransactionController::class, 'store']);
    Route::get('/transactions/today-stats', [TransactionController::class, 'todayStats']);
    Route::get('/transactions/recent', [TransactionController::class, 'recent']);
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/reports', [DashboardController::class, 'reports']);
});
