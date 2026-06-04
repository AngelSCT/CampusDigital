<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    public function index()
    {
        $user   = Auth::user();
        $wallet = $user->wallet;

        $monthlySpend = $wallet->transactions()
            ->where('type', 'cargo')
            ->whereMonth('created_at', now()->month)
            ->sum('amount');

        $lastRecharge = $wallet->transactions()
            ->where('type', 'abono')
            ->latest()
            ->first();

        $topCategories = $wallet->transactions()
            ->where('type', 'cargo')
            ->selectRaw('category, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('category')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        return view('student.wallet', compact('wallet', 'monthlySpend', 'lastRecharge', 'topCategories'));
    }
}
