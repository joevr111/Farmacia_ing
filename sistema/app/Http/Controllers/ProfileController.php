<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    // Formulario de Mi Perfil
        public function edit(Request $request)
    {
        $user = $request->user()->load('role');

        return view('profile.edit', compact('user'));
    }

    // Actualiza los datos personales (el rol, DNI y colegiatura los administra el admin en Usuarios)
    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|max:255',
            'phone' => 'nullable|max:15',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        ]);

        $user->update($validated);

        return to_route('profile.edit')->with('ok', 'Perfil actualizado');
    }

    // Formulario de Cambiar Contraseña
    public function editPassword()
    {
        return view('profile.password');
    }

    // Guarda la nueva contraseña
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|current_password', // verifica contra la contraseña actual
            'password' => 'required|min:8|confirmed|different:current_password',
        ], [
            'current_password.current_password' => 'La contraseña actual no es correcta.',
            'password.different' => 'La nueva contraseña debe ser distinta a la actual.',
        ]);

        $request->user()->update(['password' => $validated['password']]); // el cast 'hashed' la encripta

        return to_route('profile.password.edit')->with('ok', 'Contraseña actualizada');
    }
}