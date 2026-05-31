<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportController extends Controller
{
    public function index()
    {
        return view('student.support');
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:1000'],
        ]);

        // In production: send email / create support ticket
        return back()->with('success', 'Tu mensaje fue enviado. Te responderemos en menos de 24 horas.');
    }
}
