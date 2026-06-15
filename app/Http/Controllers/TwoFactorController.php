<?php

namespace App\Http\Controllers;

use App\Models\TwoFactorCode;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends Controller
{
    // ─── Login verification ───────────────────────────────────────────────────

    public function showVerify(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('two_factor_user_id')) {
            return redirect()->route('login');
        }

        $user = User::findOrFail($request->session()->get('two_factor_user_id'));

        if (in_array($user->two_factor_type, ['email', 'sms'], true)) {
            $this->sendOtp($user);
        }

        return view('auth.two-factor', ['type' => $user->two_factor_type]);
    }

    public function verify(Request $request): RedirectResponse
    {
        if (! $request->session()->has('two_factor_user_id')) {
            return redirect()->route('login');
        }

        $user = User::findOrFail($request->session()->get('two_factor_user_id'));

        $request->validate(['code' => ['required', 'string']]);

        $valid = match ($user->two_factor_type) {
            'email', 'sms' => $this->verifyOtp($user, $request->input('code')),
            'totp'         => $this->verifyTotp($user, $request->input('code')),
            default        => false,
        };

        if (! $valid) {
            return back()->withErrors(['code' => 'Invalid or expired code. Please try again.']);
        }

        $remember = $request->session()->pull('two_factor_remember', false);
        $request->session()->forget('two_factor_user_id');

        Auth::loginUsingId($user->id, $remember);
        $request->session()->regenerate();

        return redirect()->intended('/');
    }

    public function resend(Request $request): RedirectResponse
    {
        if (! $request->session()->has('two_factor_user_id')) {
            return redirect()->route('login');
        }

        $user = User::findOrFail($request->session()->get('two_factor_user_id'));

        if (in_array($user->two_factor_type, ['email', 'sms'], true)) {
            TwoFactorCode::where('user_id', $user->id)->delete();
            $this->sendOtp($user);
        }

        return back()->with('resent', true);
    }

    // ─── Settings – 2FA management ───────────────────────────────────────────

    public function showSecurity(): View
    {
        $user = Auth::user();
        $user->load('roles');
        $enabled = (bool) $user->two_factor_confirmed_at;

        return view('settings.security', compact('user', 'enabled'));
    }

    public function enable(Request $request): RedirectResponse
    {
        $request->validate([
            'method' => ['required', 'in:email,sms,totp'],
            'phone'  => ['required_if:method,sms', 'nullable', 'string', 'max:20'],
        ]);

        $user   = Auth::user();
        $method = $request->input('method');

        if ($method === 'totp') {
            $google2fa = new Google2FA();
            $secret    = $google2fa->generateSecretKey();
            $user->forceFill(['two_factor_type' => 'totp', 'two_factor_secret' => $secret])->save();
            return redirect()->route('settings.two-factor.setup-totp');
        }

        if ($method === 'sms') {
            $user->forceFill([
                'two_factor_type'         => 'sms',
                'two_factor_phone'        => $request->input('phone'),
                'two_factor_confirmed_at' => now(),
            ])->save();
        } else {
            $user->forceFill([
                'two_factor_type'         => 'email',
                'two_factor_confirmed_at' => now(),
            ])->save();
        }

        return redirect()->route('settings.security')->with('success', '2FA enabled via ' . strtoupper($method) . '.');
    }

    public function showSetupTotp(): View|RedirectResponse
    {
        $user = Auth::user();

        if ($user->two_factor_type !== 'totp' || ! $user->two_factor_secret) {
            return redirect()->route('settings.security');
        }

        $google2fa = new Google2FA();
        $qrUrl     = $google2fa->getQRCodeUrl(config('app.name'), $user->email, $user->two_factor_secret);
        $qrSvg     = $this->renderQrSvg($qrUrl);

        return view('settings.2fa.setup-totp', [
            'secret' => $user->two_factor_secret,
            'qrSvg'  => $qrSvg,
        ]);
    }

    public function confirmTotp(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'digits:6']]);

        $user = Auth::user();

        if ($user->two_factor_type !== 'totp' || ! $user->two_factor_secret) {
            return redirect()->route('settings.security');
        }

        $google2fa = new Google2FA();
        if (! $google2fa->verifyKey($user->two_factor_secret, $request->input('code'))) {
            return back()->withErrors(['code' => 'Code does not match — try again.']);
        }

        $user->forceFill(['two_factor_confirmed_at' => now()])->save();

        return redirect()->route('settings.security')->with('success', 'Authenticator app configured. 2FA is now active.');
    }

    public function disable(Request $request): RedirectResponse
    {
        $request->validate(['password' => ['required', 'current_password']]);

        $user = Auth::user();
        $user->forceFill([
            'two_factor_type'         => null,
            'two_factor_secret'       => null,
            'two_factor_phone'        => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        TwoFactorCode::where('user_id', $user->id)->delete();

        return redirect()->route('settings.security')->with('success', '2FA has been disabled.');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    protected function sendOtp(User $user): void
    {
        TwoFactorCode::where('user_id', $user->id)->delete();

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        TwoFactorCode::create([
            'user_id'    => $user->id,
            'code'       => $code,
            'expires_at' => now()->addMinutes(10),
        ]);

        if ($user->two_factor_type === 'email') {
            Mail::raw(
                "Your " . config('app.name') . " verification code is: {$code}\n\nThis code expires in 10 minutes.",
                fn ($msg) => $msg->to($user->email)->subject('Your 2FA verification code')
            );
        } elseif ($user->two_factor_type === 'sms') {
            app(SmsService::class)->send(
                $user->two_factor_phone,
                "Your " . config('app.name') . " code: {$code}"
            );
        }
    }

    protected function verifyOtp(User $user, string $code): bool
    {
        $record = TwoFactorCode::where('user_id', $user->id)
            ->where('code', $code)
            ->first();

        if (! $record || $record->isExpired()) {
            return false;
        }

        $record->delete();
        return true;
    }

    protected function verifyTotp(User $user, string $code): bool
    {
        $google2fa = new Google2FA();
        return (bool) $google2fa->verifyKey($user->two_factor_secret, $code);
    }

    protected function renderQrSvg(string $url): string
    {
        $renderer = new \BaconQrCode\Renderer\ImageRenderer(
            new \BaconQrCode\Renderer\RendererStyle\RendererStyle(200),
            new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
        );
        $writer = new \BaconQrCode\Writer($renderer);
        return $writer->writeString($url);
    }
}
