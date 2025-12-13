<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\UserLoginHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        // Check if user exists and is active
        $user = \App\Models\User::where('email', $credentials['email'])->first();

        if ($user && !$user->is_active) {
            throw ValidationException::withMessages([
                'email' => trans_common('account_inactive'),
            ]);
        }

        // Attempt authentication
        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            // Log login history
            $this->logLoginHistory($user, $request);

            // Check IP/MAC blocking if configured
            if (!$this->checkIpMacAccess($user, $request)) {
                Auth::logout();
                throw ValidationException::withMessages([
                    'email' => trans_common('access_denied_ip_mac'),
                ]);
            }

            return redirect()->intended(route('admin.dashboard'));
        }

        throw ValidationException::withMessages([
            'email' => trans_common('invalid_credentials'),
        ]);
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        $user = Auth::user();

        // Update logout time in login history
        if ($user) {
            UserLoginHistory::where('user_id', $user->id)
                ->whereNull('logout_at')
                ->latest('login_at')
                ->first()
                ?->update(['logout_at' => now()]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Log login history
     */
    protected function logLoginHistory($user, Request $request): void
    {
        try {
            UserLoginHistory::create([
                'user_id' => $user->id,
                'branch_id' => $user->defaultBranch()?->id,
                'ip_address' => $request->ip(),
                'mac_address' => $this->getMacAddress($request),
                'user_agent' => $request->userAgent(),
                'login_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log login history: ' . $e->getMessage());
        }
    }

    /**
     * Get MAC address from request (if available)
     */
    protected function getMacAddress(Request $request): ?string
    {
        // MAC address is typically not available in HTTP requests
        // This would need to be implemented via client-side JavaScript or other means
        return $request->header('X-MAC-Address');
    }

    /**
     * Check IP/MAC access restrictions
     */
    protected function checkIpMacAccess($user, Request $request): bool
    {
        // TODO: Implement IP/MAC filtering based on user settings
        // For now, allow all access
        return true;
    }
}


