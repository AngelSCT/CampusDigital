@extends('layouts.auth')
@section('title', 'Crear cuenta')
@section('subtitle', 'Crea tu monedero universitario')

@section('content')
<form method="POST" action="{{ route('register') }}" class="space-y-4">
    @csrf

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Nombre completo</label>
        <input type="text" name="name" value="{{ old('name') }}" placeholder="Ana García López" class="form-input" required />
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Correo electrónico</label>
        <input type="email" name="email" value="{{ old('email') }}" placeholder="tu@universidad.mx" class="form-input" required />
    </div>

    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Matrícula</label>
            <input type="text" name="student_id" value="{{ old('student_id') }}" placeholder="U2024001" class="form-input" required />
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Carrera</label>
            <input type="text" name="career" value="{{ old('career') }}" placeholder="Ing. Sistemas" class="form-input" />
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Contraseña</label>
        <input type="password" name="password" placeholder="Mínimo 8 caracteres" class="form-input" required />
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirmar contraseña</label>
        <input type="password" name="password_confirmation" placeholder="Repite tu contraseña" class="form-input" required />
    </div>

    <button type="submit" class="btn-primary" style="margin-top:.5rem">
        Crear cuenta y monedero
    </button>
</form>

<p class="mt-6 text-center text-sm text-gray-500">
    ¿Ya tienes cuenta?
    <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-700 font-semibold">Inicia sesión</a>
</p>
@endsection
