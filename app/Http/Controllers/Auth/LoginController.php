<?php
namespace App\Http\Controllers\Auth;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class LoginController {
    public function redirectToGitHub()
    {
        return Socialite::driver('github')->redirect();
    }

    public function handleGitHubCallback()
    {
        $githubUser = Socialite::driver('github')->user();
        $userId = $githubUser->getId();
        $foo = '';
        $user = User::updateOrCreate(
            ['github_id' => $githubUser->getId()],
            [
                'name' => $githubUser->getName() ?? $githubUser->getNickname(),
                'email' => $githubUser->getEmail() ?? $githubUser->getNickname() . '@github.local',
                'github_username' => $githubUser->getNickname(),
                'github_id' => $githubUser->getId()
            ]
        );

        Auth::login($user);
        return redirect()->route('home');
    }
}


