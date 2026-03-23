<?php

namespace App\Http\Controllers\Recargas;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Controllers\Controller;

class RecargaController extends Controller
{
    public function dashboard()
    {
        return Inertia::render('Recargas/Dashboard');
    }

    public function index()
    {

    }

    public function create()
    {
        return Inertia::render('Recargas/Create');
    }

    public function store(Request $request)
    {

    }

    public function destroy($id)
    {

    }
}