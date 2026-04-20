<?php
// app/Http/Controllers/ProfileController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show()
    {
        return view('profile.show', ['user' => Auth::user()]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'email'             => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'birth_date'        => ['nullable', 'date', 'before:today'],
            'gender'            => ['nullable', 'in:male,female'],
            'weight'            => ['nullable', 'numeric', 'min:20', 'max:300'],
            'height'            => ['nullable', 'numeric', 'min:100', 'max:250'],
            'daily_calorie_target' => ['nullable', 'integer', 'min:1000', 'max:10000'],
            'diet_mode'         => ['boolean'],
            'diet_calorie_cut'  => ['nullable', 'integer', 'min:100', 'max:2000'],
        ]);

        $user->update([
            'name'                 => $validated['name'],
            'email'                => $validated['email'],
            'birth_date'           => $validated['birth_date'] ?? null,
            'gender'               => $validated['gender'] ?? null,
            'weight'               => $validated['weight'] ?? null,
            'height'               => $validated['height'] ?? null,
            'daily_calorie_target' => $validated['daily_calorie_target'] ?? 2000,
            'diet_mode'            => $request->boolean('diet_mode'),
            'diet_calorie_cut'     => $validated['diet_calorie_cut'] ?? 500,
        ]);

        return back()->with('success', 'Profil berhasil diperbarui!');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'password'         => ['required', 'confirmed', 'min:8'],
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password lama salah.']);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Password berhasil diubah!');
    }
}
