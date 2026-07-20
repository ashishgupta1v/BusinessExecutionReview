<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Actions\ProvisionWorkspace;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleController extends Controller
{
    /** Kick off the OAuth handshake. */
    public function redirect()
    {
        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    /** Handle the callback: match on email, link google_id, provision on first login. */
    public function callback(ProvisionWorkspace $provision)
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            return redirect()->route('login')->withErrors(['google' => 'Google sign-in failed. Please try again.']);
        }

        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if (! $user) {
            $user = User::create([
                'name'              => $googleUser->getName() ?: $googleUser->getNickname() ?: 'New User',
                'email'             => $googleUser->getEmail(),
                'email_verified_at' => now(),
                'password'          => bcrypt(Str::random(40)), // unusable; they log in via Google
                'google_id'         => $googleUser->getId(),
                'avatar'            => $googleUser->getAvatar(),
                'timezone'          => 'Asia/Kolkata',
            ]);
        } elseif (! $user->google_id) {
            $user->update([
                'google_id' => $googleUser->getId(),
                'avatar'    => $user->avatar ?: $googleUser->getAvatar(),
            ]);
        }

        // First login → give them a workspace, roles, seeded KPIs, board, prefs.
        $provision($user);

        Auth::login($user, remember: true);

        return redirect()->intended(route('dcr.show'));
    }
}
