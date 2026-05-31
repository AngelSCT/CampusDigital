<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user   = Auth::user();
        $wallet = $user->wallet;

        $recentTransactions = $wallet
            ->transactions()
            ->latest()
            ->take(5)
            ->get();

        $monthlyCargos = $wallet->transactions()
            ->where('type', 'cargo')
            ->whereMonth('created_at', now()->month)
            ->sum('amount');

        $monthlyAbonos = $wallet->transactions()
            ->where('type', 'abono')
            ->whereMonth('created_at', now()->month)
            ->sum('amount');

        $categorySpends = $wallet->transactions()
            ->where('type', 'cargo')
            ->whereMonth('created_at', now()->month)
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        return view('student.home', compact(
            'user',
            'wallet',
            'recentTransactions',
            'monthlyCargos',
            'monthlyAbonos',
            'categorySpends',
        ));
    }
}
