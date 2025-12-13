<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    /**
     * Show the registration form
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Handle registration request
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        // Assign default role (Cashier or Manager based on first user)
        $defaultRole = User::count() === 1 
            ? \Spatie\Permission\Models\Role::where('name', 'Super Admin')->first()
            : \Spatie\Permission\Models\Role::where('name', 'Cashier')->first();
        
        if ($defaultRole) {
            $user->assignRole($defaultRole);
        }

        Auth::login($user);

        return redirect()->route('admin.dashboard');
    }
}


