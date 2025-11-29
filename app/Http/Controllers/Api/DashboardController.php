<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        // Today's stats
        $todayTransactions = $user->transactions()->whereDate('created_at', $today)->get();
        $transaksiHariIni = $todayTransactions->sum('total');

        // This month stats
        $monthTransactions = $user->transactions()
            ->where('created_at', '>=', $thisMonth)
            ->get();
        $omset = $monthTransactions->sum('total');

        // Simple laba kotor (assuming 40% profit margin for demo)
        $labaKotor = $omset * 0.4;

        // Last month for comparison
        $lastMonthTransactions = $user->transactions()
            ->whereBetween('created_at', [$lastMonth, $thisMonth])
            ->get();
        $lastMonthOmset = $lastMonthTransactions->sum('total');
        $omzetPercentage = $lastMonthOmset > 0 
            ? round((($omset - $lastMonthOmset) / $lastMonthOmset) * 100, 1) 
            : 0;

        // Chart data - last 7 months
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $monthStart = Carbon::now()->subMonths($i)->startOfMonth();
            $monthEnd = Carbon::now()->subMonths($i)->endOfMonth();
            $monthOmset = $user->transactions()
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->sum('total');
            
            $chartData[] = [
                'month' => $monthStart->format('M'),
                'value' => $monthOmset,
            ];
        }

        // Monthly summary
        $totalTransaksi = $monthTransactions->count();
        $produkTerjual = $monthTransactions->sum('quantity');
        
        // Count unique days with transactions as "new customers" approximation
        $pelangganBaru = $monthTransactions->groupBy(function($t) {
            return $t->created_at->format('Y-m-d');
        })->count();

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => [
                    'transaksi_hari_ini' => $transaksiHariIni,
                    'omset' => $omset,
                    'laba_kotor' => $labaKotor,
                    'omzet_target' => 10000000, // Target could be configurable
                    'omzet_percentage' => $omzetPercentage,
                ],
                'chart_data' => $chartData,
                'monthly_summary' => [
                    'total_transaksi' => $totalTransaksi,
                    'produk_terjual' => $produkTerjual,
                    'pelanggan_baru' => $pelangganBaru,
                ],
            ]
        ]);
    }

    public function reports(Request $request)
    {
        $user = $request->user();
        
        $startDate = $request->has('start_date') 
            ? Carbon::parse($request->start_date)->startOfDay()
            : Carbon::now()->startOfMonth();
        $endDate = $request->has('end_date')
            ? Carbon::parse($request->end_date)->endOfDay()
            : Carbon::now()->endOfDay();

        $transactions = $user->transactions()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $totalPendapatan = $transactions->sum('total');
        // Assuming 60% cost for demo purposes
        $totalPengeluaran = $totalPendapatan * 0.6;
        $labaKotor = $totalPendapatan - $totalPengeluaran;
        $totalTransaksi = $transactions->count();

        // Monthly breakdown
        $monthlyData = [];
        $months = $transactions->groupBy(function($t) {
            return $t->created_at->format('Y-m');
        });

        foreach ($months as $month => $monthTransactions) {
            $pendapatan = $monthTransactions->sum('total');
            $pengeluaran = $pendapatan * 0.6;
            $monthlyData[] = [
                'month' => Carbon::parse($month)->format('F Y'),
                'pendapatan' => $pendapatan,
                'pengeluaran' => $pengeluaran,
                'laba' => $pendapatan - $pengeluaran,
                'transaksi' => $monthTransactions->count(),
            ];
        }

        // Top products
        $topProducts = $transactions->groupBy('product_name')
            ->map(function($items, $name) {
                return [
                    'name' => $name,
                    'sold' => $items->sum('quantity'),
                    'revenue' => $items->sum('total'),
                ];
            })
            ->sortByDesc('revenue')
            ->take(5)
            ->values();

        // Calculate margin percentage
        $marginPercentage = $totalPendapatan > 0 
            ? round(($labaKotor / $totalPendapatan) * 100, 1)
            : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => [
                    'total_pendapatan' => $totalPendapatan,
                    'total_pengeluaran' => $totalPengeluaran,
                    'laba_kotor' => $labaKotor,
                    'total_transaksi' => $totalTransaksi,
                    'margin_percentage' => $marginPercentage,
                ],
                'monthly_data' => $monthlyData,
                'top_products' => $topProducts,
            ]
        ]);
    }
}

