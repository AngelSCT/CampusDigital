<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class RechargeController extends Controller
{
    public function index()
    {
        $wallet = Auth::user()->wallet;
        return view('student.recharge', compact('wallet'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount'         => ['required', 'numeric', 'min:50', 'max:5000'],
            'payment_method' => ['required', 'in:tarjeta,transferencia,efectivo'],
        ], [
            'amount.required' => 'El monto es obligatorio.',
            'amount.min'      => 'El monto mínimo de recarga es $50.',
            'amount.max'      => 'El monto máximo de recarga es $5,000.',
            'payment_method.required' => 'Selecciona un método de pago.',
        ]);

        $user   = Auth::user();
        $wallet = $user->wallet;

        // Deposit to wallet
        $wallet->deposit((float) $request->amount);

        // Record transaction
        Transaction::create([
            'wallet_id'   => $wallet->id,
            'type'        => 'abono',
            'category'    => 'Recarga',
            'amount'      => $request->amount,
            'reference'   => 'REF-' . strtoupper(Str::random(8)),
            'description' => 'Recarga vía ' . ucfirst($request->payment_method),
            'status'      => 'completado',
        ]);

        return redirect()
            ->route('dashboard')
            ->with('success', '¡Recarga exitosa! Se agregaron $' . number_format($request->amount, 2) . ' a tu monedero.');
    }
}
