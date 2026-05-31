@extends('layouts.app')

@section('content')
<div class="mb-6">
    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-900 mb-3 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Volver
    </a>
    <h1 class="text-2xl font-bold text-gray-900">Soporte</h1>
    <p class="text-gray-500 text-sm mt-1">¿Necesitas ayuda? Contáctanos</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    @foreach([
        ['📞','Teléfono','442 123 4567','Lun–Vie 8–18 h'],
        ['✉️','Correo','soporte@universidad.mx','Respuesta en 24 h'],
        ['🏢','Ventanilla','Edificio Admin, P.B.','Lun–Vie 9–17 h'],
    ] as [$icon,$title,$value,$sub])
    <div class="card p-4 text-center">
        <div class="text-3xl mb-2">{{ $icon }}</div>
        <p class="text-sm font-semibold text-gray-900">{{ $title }}</p>
        <p class="text-sm text-blue-600 font-medium mt-0.5">{{ $value }}</p>
        <p class="text-xs text-gray-400 mt-0.5">{{ $sub }}</p>
    </div>
    @endforeach
</div>

<div class="card p-5">
    <h2 class="text-base font-semibold text-gray-900 mb-4">Enviar un mensaje</h2>
    <form method="POST" action="{{ route('support.store') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Asunto</label>
            <input type="text" name="subject" value="{{ old('subject') }}" placeholder="¿En qué podemos ayudarte?" class="form-input" required />
            @error('subject')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Mensaje</label>
            <textarea name="message" rows="5" placeholder="Describe tu situación con detalle..." class="form-input resize-none" required>{{ old('message') }}</textarea>
            @error('message')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <button type="submit" class="btn-primary px-6 py-2.5 text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
            Enviar mensaje
        </button>
    </form>
</div>
@endsection
