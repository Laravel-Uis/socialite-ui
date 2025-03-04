<?php

namespace SocialiteUi\Contracts;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Contracts\User as SocialiteUser;

interface AuthenticatesOAuthCallback
{
    /**
     * Handle the authentication of the user.
     */
    public function authenticate(Request $request, string $provider, SocialiteUser $socialiteUser): RedirectResponse;

    /**
     * Handle the linking of a provider account to an already authenticated user.
     */
    public function link(Request $request): RedirectResponse;
}
