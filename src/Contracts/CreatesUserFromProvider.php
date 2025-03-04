<?php

namespace SocialiteUi\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Socialite\Contracts\User as Socialite;

interface CreatesUserFromProvider
{
    /**
     * Create a new user from a social provider user.
     */
    public function create(string $provider, Socialite $socialiteUser): Authenticatable;
}
