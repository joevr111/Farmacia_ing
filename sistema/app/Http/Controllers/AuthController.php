<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Muestra el formulario de login
    public function showLogin()
    {
        return view('auth.login');
    }

    // Valida las credenciales e inicia la sesión
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => 'Ingresa tu correo.',
            'email.email' => 'Ingresa un correo válido.',
            'password.required' => 'Ingresa tu contraseña.',
        ]);

        // Auth::attempt compara la contraseña con el hash guardado.
        // El segundo parámetro activa "Mantener sesión iniciada".
        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('email', 'remember'))
                ->with('error', 'Correo o contraseña incorrectos.');
        }

        // Genera un nuevo id de sesión tras autenticar (seguridad)
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard.index'));
    }

    // Cierra la sesión
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('login')->with('ok', 'Sesión cerrada correctamente.');
    }
}