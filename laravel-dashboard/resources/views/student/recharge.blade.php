@extends('layouts.app')

@section('content')
{{-- ── Header ─────────────────────────────────────────────────── --}}
<div class="mb-6">
    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-900 mb-3 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Volver
    </a>
    <h1 class="text-2xl font-bold text-gray-900">Recargar saldo</h1>
    <p class="text-gray-500 text-sm mt-1">Agrega saldo a tu monedero universitario</p>
</div>

{{-- ── Current balance ─────────────────────────────────────────── --}}
<div class="balance-card mb-6">
    <div class="relative z-10">
        <p class="text-blue-200 text-sm">Saldo actual</p>
        <p class="text-3xl font-bold mt-1">${{ number_format($wallet->balance, 2) }}</p>
        <p class="text-blue-300 text-xs mt-2 font-mono">**** **** **** {{ substr($wallet->card_number, -4) }}</p>
    </div>
</div>

{{-- ── Recharge form ────────────────────────────────────────────── --}}
<div class="card p-5 sm:p-6">
    <form method="POST" action="{{ route('recharge.store') }}" class="space-y-6">
        @csrf

        {{-- Quick amounts --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-3">Monto rápido</label>
            <div class="grid grid-cols-3 gap-2.5">
                @foreach([100, 200, 300, 500, 1000, 2000] as $preset)
                <button type="button" data-amount="{{ $preset }}" class="amount-btn">
                    ${{ number_format($preset, 0) }}
                </button>
                @endforeach
            </div>
        </div>

        {{-- Custom amount --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">O ingresa un monto personalizado</label>
            <div class="relative">
                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 font-medium text-sm">$</span>
                <input
                    id="amount-input"
                    type="number"
                    name="amount"
                    value="{{ old('amount') }}"
                    placeholder="0.00"
                    min="50"
                    max="5000"
                    step="0.01"
                    class="form-input pl-8"
                />
            </div>
            @error('amount')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
            <p class="text-xs text-gray-400 mt-1.5">Mínimo $50 · Máximo $5,000</p>
        </div>

        {{-- Payment method --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-3">Método de pago</label>
            <div class="space-y-2.5">
                @foreach([
                    ['tarjeta',      'Tarjeta de débito / crédito', '💳'],
                    ['transferencia','Transferencia bancaria',       '🏦'],
                    ['efectivo',     'Ventanilla (efectivo)',        '💵'],
                ] as [$val, $label, $icon])
                <label class="flex items-center gap-3 p-3.5 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-blue-300 transition-all has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50">
                    <input type="radio" name="payment_method" value="{{ $val }}" class="w-4 h-4 text-blue-600" {{ old('payment_method') === $val || ($val === 'tarjeta' && !old('payment_method')) ? 'checked' : '' }} />
                    <span class="text-xl">{{ $icon }}</span>
                    <span class="text-sm font-medium text-gray-700">{{ $label }}</span>
                </label>
                @endforeach
            </div>
            @error('payment_method')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="btn-primary w-full text-base py-3.5">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Recargar saldo
        </button>
    </form>
</div>

{{-- ── Info note ────────────────────────────────────────────────── --}}
<div class="mt-4 p-4 bg-blue-50 border border-blue-100 rounded-xl flex gap-3">
    <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <p class="text-xs text-blue-700 leading-relaxed">
        Las recargas son procesadas de inmediato. Para pagos por transferencia o ventanilla, el saldo se refleja en un plazo de hasta 24 horas hábiles.
    </p>
</div>
@endsection
