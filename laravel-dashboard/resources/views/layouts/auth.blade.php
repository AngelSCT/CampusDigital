<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ config('app.name') }} — @yield('title', 'Acceso')</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', ui-sans-serif; }
        .form-input { width:100%; padding:0.75rem 1rem; border:1px solid #e5e7eb; border-radius:0.75rem; font-size:0.875rem; color:#111827; outline:none; transition:all .15s; }
        .form-input:focus { border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,.1); }
        .btn-primary { width:100%; padding:0.8rem; background:#2563eb; color:#fff; font-size:0.9rem; font-weight:600; border-radius:0.75rem; border:none; cursor:pointer; transition:background .15s; }
        .btn-primary:hover { background:#1d4ed8; }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-slate-50 flex items-center justify-center p-4">

    <div class="w-full max-w-md">
        {{-- Logo --}}
        <div class="flex flex-col items-center mb-8">
            <div class="w-14 h-14 bg-blue-600 rounded-2xl flex items-center justify-center mb-4 shadow-lg shadow-blue-200">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Monedero Universitario</h1>
            <p class="text-sm text-gray-500 mt-1">@yield('subtitle')</p>
        </div>

        {{-- Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
            @if ($errors->any())
            <div class="mb-5 p-3.5 bg-red-50 border border-red-200 rounded-xl">
                <ul class="text-sm text-red-700 space-y-1">
                    @foreach ($errors->all() as $error)
                    <li class="flex items-start gap-2">
                        <span class="mt-0.5">•</span> {{ $error }}
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            @yield('content')
        </div>
    </div>

</body>
</html>
