<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();

        return view('profile', compact('user'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);

        $user->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'name' => $validated['first_name'].' '.$validated['last_name'],
            'email' => $validated['email'] ?? null,
        ]);

        return back()->with('success', 'profile_updated');
    }

    public function updateAvatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'max:2048', 'mimes:jpg,jpeg,png,webp'],
        ]);

        $user = $request->user();

        if ($user->avatar) {
            Storage::disk('public')->delete('avatars/'.$user->avatar);
        }

        $file = $request->file('avatar');
        $filename = $user->id.'.'.$file->getClientOriginalExtension();
        $file->storeAs('avatars', $filename, 'public');

        $user->update(['avatar' => $filename]);

        return back()->with('success', 'avatar_updated');
    }

    public function removeAvatar(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->avatar) {
            Storage::disk('public')->delete('avatars/'.$user->avatar);
            $user->update(['avatar' => null]);
        }

        return back()->with('success', 'avatar_removed');
    }

    public function updateNotificationPreferences(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'task_activity' => ['required', 'boolean'],
            'invitations' => ['required', 'boolean'],
            'project_updates' => ['required', 'boolean'],
            'digest' => ['required', 'boolean'],
        ]);

        $request->user()->update([
            'notification_preferences' => [
                'task_activity' => (bool) $validated['task_activity'],
                'invitations' => (bool) $validated['invitations'],
                'project_updates' => (bool) $validated['project_updates'],
                'digest' => (bool) $validated['digest'],
            ],
        ]);

        return back()->with('success', 'notifications_updated');
    }
}
