<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password as PasswordBroker;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function showRegister(): View|RedirectResponse
    {
        if (SiteSetting::get('allow_registration', '1') !== '1') {
            abort(403, 'Registration is currently disabled.');
        }

        return view('auth.register');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // The login form accepts either an email address or a username.
        $field = filter_var($credentials['email'], FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        $attempt = [$field => $credentials['email'], 'password' => $credentials['password']];

        if (! Auth::validate($attempt)) {
            return back()
                ->withErrors(['email' => __('auth.failed')])
                ->onlyInput('email');
        }

        $user = User::where($field, $credentials['email'])->first();

        if ($user->twoFactorEnabled()) {
            $request->session()->put('two_factor_user_id', $user->id);
            $request->session()->put('two_factor_remember', $request->boolean('remember'));
            return redirect()->route('two-factor.verify');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        if (SiteSetting::get('require_2fa', '0') === '1' && ! $user->twoFactorEnabled()) {
            return redirect()->route('settings.security')
                ->with('warning', '2FA is required for all accounts. Please set it up to continue.');
        }

        return redirect()->intended('/');
    }

    public function register(Request $request): RedirectResponse
    {
        if (SiteSetting::get('allow_registration', '1') !== '1') {
            abort(403, 'Registration is currently disabled.');
        }

        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255', 'unique:users,name'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::min(8)],
            'terms' => ['accepted'],
        ]);

        $user = User::create([
            'name' => $validated['username'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/');
    }

    public function showForgotPassword(): View
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = PasswordBroker::sendResetLink($request->only('email'));

        return $status === PasswordBroker::ResetLinkSent
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)])->onlyInput('email');
    }

    public function showResetPassword(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
        ]);

        $status = PasswordBroker::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $status === PasswordBroker::PasswordReset
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => __($status)])->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
