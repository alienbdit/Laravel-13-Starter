<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProfileController extends Controller
{
    public function show(): View
    {
        return view('profile.index', ['user' => Auth::user()->load('roles')]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255', Rule::unique('users', 'name')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $user->update($validated);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'string', 'confirmed', Password::min(8)],
        ]);

        Auth::user()->update(['password' => $request->input('password')]);

        return back()->with('password_success', 'Password changed successfully.');
    }

    public function updatePhoto(Request $request): RedirectResponse
    {
        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:2048'],
        ]);

        $user = Auth::user();
        $this->deleteStoredPhoto($user->profile_photo);

        $file     = $request->file('photo');
        $filename = 'avatar_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
        Storage::disk('local')->putFileAs('avatars', $file, $filename);

        $user->update(['profile_photo' => $filename]);

        return back()->with('photo_success', 'Profile photo updated.');
    }

    public function removePhoto(): RedirectResponse
    {
        $user = Auth::user();
        $this->deleteStoredPhoto($user->profile_photo);
        $user->update(['profile_photo' => null]);

        return back()->with('photo_success', 'Profile photo removed.');
    }

    public function servePhoto(int $userId): StreamedResponse|Response
    {
        $user = User::findOrFail($userId);
        abort_if(! $user->profile_photo, 404);

        $path = 'avatars/' . $user->profile_photo;
        abort_unless(Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path);
    }

    private function deleteStoredPhoto(?string $filename): void
    {
        if ($filename) {
            Storage::disk('local')->delete('avatars/' . $filename);
        }
    }
}
