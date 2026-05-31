@extends('layouts.app')

@section('content')
{{-- ── Header ─────────────────────────────────────────────────── --}}
<div class="mb-6">
    <p class="text-sm text-gray-500">{{ now()->isoFormat('dddd, D [de] MMMM') }}</p>
    <h1 class="text-2xl font-bold text-gray-900">¡Hola, {{ explode(' ', $user->name)[0] }}! 👋</h1>
</div>

{{-- ── Balance card ────────────────────────────────────────────── --}}
<div class="balance-card mb-6">
    <div class="relative z-10">
        <p class="text-blue-200 text-sm font-medium mb-1">Saldo disponible</p>
        <p class="text-4xl font-bold mb-4">${{ number_format($wallet->balance, 2) }}</p>

        <div class="flex items-center justify-between">
            <div>
                <p class="text-blue-200 text-xs">Tarjeta</p>
                <p class="text-white font-mono text-sm tracking-widest">**** **** **** {{ substr($wallet->card_number, -4) }}</p>
            </div>
            <div class="text-right">
                <p class="text-blue-200 text-xs">Límite mensual</p>
                <p class="text-white text-sm font-semibold">${{ number_format($wallet->monthly_limit, 2) }}</p>
            </div>
        </div>
    </div>
</div>

{{-- ── Quick actions ────────────────────────────────────────────── --}}
<div class="grid grid-cols-4 gap-3 mb-6">
    <a href="{{ route('recharge.index') }}" class="flex flex-col items-center gap-2 p-3 bg-white rounded-xl border border-slate-100 hover:shadow-md transition-all text-center">
        <div class="w-11 h-11 bg-blue-50 rounded-xl flex items-center justify-center">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        </div>
        <span class="text-xs font-medium text-gray-700">Recargar</span>
    </a>

    <a href="{{ route('movements.index') }}" class="flex flex-col items-center gap-2 p-3 bg-white rounded-xl border border-slate-100 hover:shadow-md transition-all text-center">
        <div class="w-11 h-11 bg-purple-50 rounded-xl flex items-center justify-center">
            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        </div>
        <span class="text-xs font-medium text-gray-700">Historial</span>
    </a>

    <a href="{{ route('wallet.index') }}" class="flex flex-col items-center gap-2 p-3 bg-white rounded-xl border border-slate-100 hover:shadow-md transition-all text-center">
        <div class="w-11 h-11 bg-green-50 rounded-xl flex items-center justify-center">
            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
        </div>
        <span class="text-xs font-medium text-gray-700">Monedero</span>
    </a>

    <a href="{{ route('support.index') }}" class="flex flex-col items-center gap-2 p-3 bg-white rounded-xl border border-slate-100 hover:shadow-md transition-all text-center">
        <div class="w-11 h-11 bg-orange-50 rounded-xl flex items-center justify-center">
            <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
        </div>
        <span class="text-xs font-medium text-gray-700">Soporte</span>
    </a>
</div>

{{-- ── Stats row ────────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
    <div class="card p-4 flex items-center gap-4">
        <div class="w-12 h-12 bg-red-50 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/></svg>
        </div>
        <div>
            <p class="text-xs text-gray-500 mb-0.5">Gastos este mes</p>
            <p class="text-xl font-bold text-gray-900">${{ number_format($monthlyCargos, 2) }}</p>
        </div>
    </div>

    <div class="card p-4 flex items-center gap-4">
        <div class="w-12 h-12 bg-green-50 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg>
        </div>
        <div>
            <p class="text-xs text-gray-500 mb-0.5">Recargas este mes</p>
            <p class="text-xl font-bold text-gray-900">${{ number_format($monthlyAbonos, 2) }}</p>
        </div>
    </div>
</div>

{{-- ── Recent movements ─────────────────────────────────────────── --}}
<div class="card">
    <div class="flex items-center justify-between px-5 pt-5 pb-3 border-b border-slate-50">
        <h2 class="text-base font-semibold text-gray-900">Movimientos recientes</h2>
        <a href="{{ route('movements.index') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">Ver todos</a>
    </div>

    <div class="divide-y divide-slate-50">
        @forelse($recentTransactions as $tx)
        <div class="flex items-center justify-between px-5 py-3.5 hover:bg-slate-50 transition-colors">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg flex-shrink-0
                    {{ $tx->type === 'abono' ? 'bg-green-50' : 'bg-slate-50' }}">
                    @if($tx->category === 'Cafetería') ☕
                    @elseif($tx->category === 'Librería') 📚
                    @elseif($tx->category === 'Impresiones') 🖨️
                    @elseif($tx->category === 'Recarga') 💳
                    @elseif($tx->category === 'Laboratorio') 🔬
                    @elseif($tx->category === 'Deportes') ⚽
                    @else 💰
                    @endif
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900">{{ $tx->category }}</p>
                    <p class="text-xs text-gray-400">{{ $tx->formatted_date }} · {{ $tx->reference }}</p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-sm font-semibold {{ $tx->type === 'abono' ? 'text-green-600' : 'text-gray-900' }}">
                    {{ $tx->type === 'abono' ? '+' : '-' }}${{ number_format($tx->amount, 2) }}
                </p>
                <span class="badge-{{ $tx->type === 'abono' ? 'green' : 'blue' }} text-xs">
                    {{ $tx->type === 'abono' ? 'Abono' : 'Cargo' }}
                </span>
            </div>
        </div>
        @empty
        <div class="px-5 py-10 text-center">
            <p class="text-gray-400 text-sm">Sin movimientos aún.</p>
            <a href="{{ route('recharge.index') }}" class="mt-2 inline-block text-blue-600 text-sm font-medium">Haz tu primera recarga →</a>
        </div>
        @endforelse
    </div>
</div>
@endsection
