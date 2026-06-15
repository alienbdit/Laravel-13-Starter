<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SmsGatewaySetting;
use App\Services\SmsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SmsGatewayController extends Controller
{
    public function index(): View
    {
        $settings = SmsGatewaySetting::current();
        return view('admin.sms-gateway.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'provider'     => ['required', 'in:twilio,vonage,aws_sns,custom'],
            'api_key'      => ['nullable', 'string', 'max:500'],
            'api_secret'   => ['nullable', 'string', 'max:500'],
            'from_number'  => ['nullable', 'string', 'max:50'],
            'endpoint_url' => ['nullable', 'url', 'max:500'],
            'is_enabled'   => ['boolean'],
        ]);

        $settings = SmsGatewaySetting::current();
        $settings->update($validated);

        return back()->with('success', 'SMS gateway settings saved.');
    }

    public function test(Request $request): RedirectResponse
    {
        $request->validate(['test_phone' => ['required', 'string', 'max:20']]);

        $sent = app(SmsService::class)->send(
            $request->input('test_phone'),
            'Test message from ' . config('app.name') . '. Your SMS gateway is working!'
        );

        return back()->with(
            $sent ? 'success' : 'error',
            $sent ? 'Test SMS sent successfully.' : 'Failed to send test SMS. Check logs for details.'
        );
    }
}
