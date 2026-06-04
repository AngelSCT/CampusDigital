<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $user   = Auth::user();
        $wallet = $user->wallet;

        $query = $wallet->transactions()->latest();

        // Filters
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $transactions = $query->paginate(15)->withQueryString();

        $categories = $wallet->transactions()
            ->distinct()
            ->pluck('category');

        return view('student.movements', compact('transactions', 'categories', 'wallet'));
    }
}
