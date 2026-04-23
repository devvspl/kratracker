<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
public function edit(Request $request): View
{
    $backups = [];

    if (auth()->user()->roles->first()?->name === 'Admin') {
        $files = \Storage::disk('local')->files('backups');

        foreach ($files as $file) {
            if (!str_ends_with($file, '.sql') && !str_ends_with($file, '.sql.gz')) continue;

            $size     = \Storage::disk('local')->size($file);
            $modified = \Storage::disk('local')->lastModified($file);

            $bytes = match(true) {
                $size >= 1048576 => number_format($size / 1048576, 2) . ' MB',
                $size >= 1024    => number_format($size / 1024, 2) . ' KB',
                default          => $size . ' B',
            };

            $backups[] = [
                'name'      => basename($file),
                'size'      => $bytes,
                'created'   => \Carbon\Carbon::createFromTimestamp($modified)->format('d M Y, H:i'),
                'timestamp' => $modified,
            ];
        }

        usort($backups, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);
    }

    return view('profile.edit', [
        'user'    => $request->user(),
        'backups' => $backups,
    ]);
}

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::min(8), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return Redirect::route('profile.edit')->with('status', 'password-updated');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        Auth::logout();
        $user->delete();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
