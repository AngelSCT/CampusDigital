<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ config('app.name', 'Monedero Universitario') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

    <!-- Styles (CDN fallback so it works without `npm run build`) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'ui-sans-serif'] },
                }
            }
        }
    </script>

    <style>
        /* ── Custom components ─────────────────────────── */
        body { font-family: 'Inter', ui-sans-serif; background: #F8FAFC; }

        .sidebar { position:fixed; left:0; top:0; height:100vh; width:16rem; background:#fff; border-right:1px solid #f1f5f9; display:flex; flex-direction:column; z-index:40; }

        .nav-item { display:flex; align-items:center; gap:0.75rem; padding:0.625rem 1rem; border-radius:0.75rem; font-size:0.875rem; font-weight:500; color:#4b5563; transition:all 0.15s; cursor:pointer; text-decoration:none; }
        .nav-item:hover { background:#f8fafc; color:#111827; }
        .nav-item.active { background:#eff6ff; color:#1d4ed8; }
        .nav-item svg { width:1.25rem; height:1.25rem; flex-shrink:0; }

        .balance-card { background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 55%,#3b82f6 100%); border-radius:1.25rem; padding:1.5rem; color:#fff; position:relative; overflow:hidden; }
        .balance-card::before { content:''; position:absolute; top:-40%; right:-10%; width:200px; height:200px; border-radius:50%; background:rgba(255,255,255,0.08); }
        .balance-card::after  { content:''; position:absolute; bottom:-40%; left:-5%;  width:150px; height:150px; border-radius:50%; background:rgba(255,255,255,0.06); }

        .card { background:#fff; border-radius:0.875rem; border:1px solid #f1f5f9; box-shadow:0 1px 3px rgba(0,0,0,.04); }

        .btn-primary { display:inline-flex; align-items:center; justify-content:center; gap:0.5rem; padding:0.75rem 1.25rem; background:#2563eb; color:#fff; font-size:0.875rem; font-weight:600; border-radius:0.75rem; border:none; cursor:pointer; transition:background 0.15s; }
        .btn-primary:hover { background:#1d4ed8; }
        .btn-secondary { display:inline-flex; align-items:center; justify-content:center; gap:0.5rem; padding:0.75rem 1.25rem; background:#fff; color:#374151; font-size:0.875rem; font-weight:600; border-radius:0.75rem; border:1px solid #e5e7eb; cursor:pointer; transition:all 0.15s; }
        .btn-secondary:hover { background:#f9fafb; }

        .form-input { width:100%; padding:0.75rem 1rem; border:1px solid #e5e7eb; border-radius:0.75rem; font-size:0.875rem; color:#111827; background:#fff; outline:none; transition:all 0.15s; }
        .form-input:focus { border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,0.1); }

        .amount-btn { padding:0.75rem; border:2px solid #e5e7eb; border-radius:0.75rem; font-size:0.875rem; font-weight:600; color:#374151; background:#fff; cursor:pointer; transition:all 0.15s; text-align:center; }
        .amount-btn:hover, .amount-btn.selected { border-color:#2563eb; color:#2563eb; background:#eff6ff; }

        .badge-green  { display:inline-flex; align-items:center; padding:0.25rem 0.625rem; border-radius:9999px; font-size:0.75rem; font-weight:500; background:#f0fdf4; color:#15803d; }
        .badge-red    { display:inline-flex; align-items:center; padding:0.25rem 0.625rem; border-radius:9999px; font-size:0.75rem; font-weight:500; background:#fef2f2; color:#dc2626; }
        .badge-blue   { display:inline-flex; align-items:center; padding:0.25rem 0.625rem; border-radius:9999px; font-size:0.75rem; font-weight:500; background:#eff6ff; color:#2563eb; }

        .avatar { width:2.5rem; height:2.5rem; border-radius:9999px; background:linear-gradient(135deg,#3b82f6,#1d4ed8); display:flex; align-items:center; justify-content:center; color:#fff; font-weight:600; font-size:0.875rem; flex-shrink:0; }

        .mobile-nav { position:fixed; bottom:0; left:0; right:0; background:#fff; border-top:1px solid #f1f5f9; display:flex; align-items:center; justify-content:space-around; padding:0.5rem 1rem; z-index:40; }
        .mobile-nav-item { display:flex; flex-direction:column; align-items:center; gap:0.25rem; padding:0.5rem 0.75rem; border-radius:0.75rem; font-size:0.6875rem; color:#6b7280; text-decoration:none; transition:color 0.15s; }
        .mobile-nav-item:hover, .mobile-nav-item.active { color:#2563eb; }
        .mobile-nav-item svg { width:1.25rem; height:1.25rem; }

        .fade-in { animation: fadeIn 0.2s ease-out; }
        @keyframes fadeIn { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:translateY(0); } }

        @media (max-width: 767px) { .sidebar { display:none; } }
        @media (min-width: 768px) { .mobile-nav { display:none; } }
    </style>
</head>
<body class="h-full">

    {{-- ══ Sidebar (desktop) ══════════════════════════════════════ --}}
    <aside class="sidebar">
        {{-- Logo --}}
        <div class="flex items-center gap-3 px-6 py-5 border-b border-slate-100">
            <div class="w-9 h-9 bg-blue-600 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-900 leading-tight">Monedero</p>
                <p class="text-xs text-gray-400 leading-tight">Universitario</p>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            <p class="px-3 py-1 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Principal</p>

            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Inicio
            </a>

            <a href="{{ route('recharge.index') }}" class="nav-item {{ request()->routeIs('recharge.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Recargar
            </a>

            <a href="{{ route('movements.index') }}" class="nav-item {{ request()->routeIs('movements.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Movimientos
            </a>

            <a href="{{ route('wallet.index') }}" class="nav-item {{ request()->routeIs('wallet.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                Mi Monedero
            </a>

            <p class="px-3 py-1 text-xs font-semibold text-gray-400 uppercase tracking-wider mt-4 mb-2">Cuenta</p>

            <a href="{{ route('profile.index') }}" class="nav-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Configuración
            </a>

            <a href="{{ route('support.index') }}" class="nav-item {{ request()->routeIs('support.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                Soporte
            </a>
        </nav>

        {{-- User profile footer --}}
        <div class="px-3 py-4 border-t border-slate-100">
            <div class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-gray-50 transition-colors">
                <div class="avatar text-sm">{{ substr(Auth::user()->name, 0, 1) }}{{ (strpos(Auth::user()->name, ' ') !== false ? substr(Auth::user()->name, strpos(Auth::user()->name, ' ') + 1, 1) : '') }}</div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-gray-900 truncate">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-gray-400 truncate">{{ Auth::user()->student_id }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-gray-400 hover:text-red-500 transition-colors" title="Cerrar sesión">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- ══ Main content ════════════════════════════════════════════ --}}
    <div class="md:ml-64 min-h-screen">
        {{-- Flash messages --}}
        @if(session('success'))
        <div data-flash class="fixed top-4 right-4 z-50 flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 shadow-lg max-w-sm fade-in">
            <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-sm font-medium">{{ session('success') }}</p>
        </div>
        @endif

        @if(session('error'))
        <div data-flash class="fixed top-4 right-4 z-50 flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 shadow-lg max-w-sm fade-in">
            <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-sm font-medium">{{ session('error') }}</p>
        </div>
        @endif

        <main class="p-4 sm:p-6 lg:p-8 pb-24 md:pb-8 max-w-5xl mx-auto fade-in">
            @yield('content')
        </main>
    </div>

    {{-- ══ Mobile bottom nav ══════════════════════════════════════ --}}
    <nav class="mobile-nav">
        <a href="{{ route('dashboard') }}" class="mobile-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Inicio
        </a>
        <a href="{{ route('recharge.index') }}" class="mobile-nav-item {{ request()->routeIs('recharge.*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Recargar
        </a>
        <a href="{{ route('movements.index') }}" class="mobile-nav-item {{ request()->routeIs('movements.*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            Historial
        </a>
        <a href="{{ route('wallet.index') }}" class="mobile-nav-item {{ request()->routeIs('wallet.*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            Monedero
        </a>
        <a href="{{ route('profile.index') }}" class="mobile-nav-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Config
        </a>
    </nav>

    <script>
        // Flash auto-hide
        document.querySelectorAll('[data-flash]').forEach(el => {
            setTimeout(() => { el.style.transition='opacity .4s'; el.style.opacity='0'; setTimeout(()=>el.remove(),400); }, 4000);
        });
        // Amount selector
        document.querySelectorAll('[data-amount]').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('[data-amount]').forEach(b => b.classList.remove('selected'));
                btn.classList.add('selected');
                const inp = document.getElementById('amount-input');
                if (inp) inp.value = btn.dataset.amount;
            });
        });
        document.getElementById('amount-input')?.addEventListener('input', () => {
            document.querySelectorAll('[data-amount]').forEach(b => b.classList.remove('selected'));
        });
    </script>
</body>
</html>
