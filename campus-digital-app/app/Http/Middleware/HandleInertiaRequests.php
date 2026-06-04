<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        // Disable asset version checking in tests to avoid 409 when manifest exists.
        if (app()->environment('testing')) {
            return null;
        }

        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user() ? [
                    'id'       => $request->user()->id,
                    'nombre'   => $request->user()->nombre,
                    'apellido' => $request->user()->apellido,
                    'email'    => $request->user()->email,
                    'foto_url' => $request->user()->foto_url,
                    'roles'    => $request->user()
                                    ->roles()
                                    ->select('rol.id', 'rol.nombre')
                                    ->get(),
                    'saldo'    => $request->user()->saldo?->saldo ?? 0,
                ] : null,
            ],
            'flash' => [
                'resultado'   => fn () => $request->session()->get('resultado'),
                'success'     => fn () => $request->session()->get('success'),
                'error'       => fn () => $request->session()->get('error'),
                'scan_result' => fn () => $request->session()->get('scan_result'),
            ],
        ]);
    }
}
