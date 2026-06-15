<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteSettingController extends Controller
{
    public function index(Request $request): View
    {
        $s         = SiteSetting::allKeyed();
        $activeTab = in_array($request->query('tab'), ['general', 'email', 'security', 'appearance'])
            ? $request->query('tab')
            : 'general';

        return view('admin.settings.index', compact('s', 'activeTab'));
    }

    public function updateGeneral(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'app_name'        => ['required', 'string', 'max:100'],
            'app_description' => ['nullable', 'string', 'max:255'],
            'app_timezone'    => ['required', 'timezone'],
            'date_format'     => ['required', 'in:Y-m-d,d/m/Y,m/d/Y,d M Y,D d M Y'],
        ]);

        SiteSetting::setMany($validated);

        return redirect()->route('admin.settings.index', ['tab' => 'general'])
            ->with('success', 'General settings saved.');
    }

    public function updateEmail(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mail_from_name'    => ['required', 'string', 'max:100'],
            'mail_from_address' => ['required', 'email', 'max:150'],
        ]);

        SiteSetting::setMany($validated);

        return redirect()->route('admin.settings.index', ['tab' => 'email'])
            ->with('success', 'Email settings saved.');
    }

    public function updateSecurity(Request $request): RedirectResponse
    {
        SiteSetting::setMany([
            'allow_registration' => $request->boolean('allow_registration') ? '1' : '0',
            'require_2fa'        => $request->boolean('require_2fa') ? '1' : '0',
            'session_lifetime'   => $request->validate([
                'session_lifetime' => ['required', 'in:30,60,120,240,480,1440'],
            ])['session_lifetime'],
        ]);

        config(['session.lifetime' => (int) SiteSetting::get('session_lifetime', 120)]);

        return redirect()->route('admin.settings.index', ['tab' => 'security'])
            ->with('success', 'Security settings saved.');
    }

    public function updateAppearance(Request $request): RedirectResponse
    {
        $request->validate([
            'site_logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp,svg', 'max:2048'],
            'login_bg'  => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
        ]);

        $uploadDir = public_path('uploads/site');
        if (! is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $this->handleUpload($request, 'site_logo', $uploadDir, 'logo_');
        $this->handleUpload($request, 'login_bg',  $uploadDir, 'login_bg_');

        return redirect()->route('admin.settings.index', ['tab' => 'appearance'])
            ->with('success', 'Appearance settings saved.');
    }

    private function handleUpload(Request $request, string $field, string $dir, string $prefix): void
    {
        if ($request->boolean("remove_{$field}")) {
            $this->deleteFile(SiteSetting::get($field));
            SiteSetting::set($field, null);
            return;
        }

        if ($request->hasFile($field) && $request->file($field)->isValid()) {
            $this->deleteFile(SiteSetting::get($field));
            $file     = $request->file($field);
            $filename = $prefix . time() . '.' . $file->getClientOriginalExtension();
            $file->move($dir, $filename);
            SiteSetting::set($field, 'uploads/site/' . $filename);
        }
    }

    private function deleteFile(?string $path): void
    {
        if ($path && file_exists(public_path($path))) {
            @unlink(public_path($path));
        }
    }
}
