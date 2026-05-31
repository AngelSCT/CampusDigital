@extends('layouts.app')

@section('content')
<div class="mb-6">
    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-900 mb-3 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Volver
    </a>
    <h1 class="text-2xl font-bold text-gray-900">Configuración</h1>
    <p class="text-gray-500 text-sm mt-1">Administra tu perfil y preferencias</p>
</div>

{{-- Profile card --}}
<div class="card p-5 mb-5 flex items-center gap-4">
    <div class="avatar" style="width:3.5rem;height:3.5rem;font-size:1.25rem">
        {{ substr($user->name,0,1) }}{{ (strpos($user->name,' ')!==false ? substr($user->name,strpos($user->name,' ')+1,1) : '') }}
    </div>
    <div>
        <p class="font-semibold text-gray-900">{{ $user->name }}</p>
        <p class="text-sm text-gray-500">{{ $user->career ?? 'Sin carrera' }} · {{ $user->student_id }}</p>
        <p class="text-xs text-gray-400">{{ $user->email }}</p>
    </div>
</div>

{{-- Edit profile --}}
<div class="card p-5 mb-5">
    <h2 class="text-base font-semibold text-gray-900 mb-4">Datos personales</h2>
    <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
        @csrf @method('PUT')
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Nombre completo</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-input" required />
            @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Correo electrónico</label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-input" required />
            @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Carrera</label>
            <input type="text" name="career" value="{{ old('career', $user->career) }}" class="form-input" />
        </div>
        <button type="submit" class="btn-primary px-6 py-2.5 text-sm">Guardar cambios</button>
    </form>
</div>

{{-- Change password --}}
<div class="card p-5">
    <h2 class="text-base font-semibold text-gray-900 mb-4">Cambiar contraseña</h2>
    <form method="POST" action="{{ route('profile.password') }}" class="space-y-4">
        @csrf @method('PUT')
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Contraseña actual</label>
            <input type="password" name="current_password" class="form-input" />
            @error('current_password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Nueva contraseña</label>
            <input type="password" name="password" class="form-input" />
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirmar contraseña</label>
            <input type="password" name="password_confirmation" class="form-input" />
        </div>
        <button type="submit" class="btn-secondary px-6 py-2.5 text-sm">Actualizar contraseña</button>
    </form>
</div>

{{-- Logout --}}
<div class="mt-5 pt-5 border-t border-gray-100">
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="inline-flex items-center gap-2 text-sm text-red-600 hover:text-red-700 font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            Cerrar sesión
        </button>
    </form>
</div>
@endsection
