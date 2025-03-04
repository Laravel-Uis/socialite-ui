<?php

namespace SocialiteUi\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Socialite\Contracts\User as ProviderUser;

interface CreatesSocialAccounts
{
    /**
     * Create a social account for a given user.
     */
    public function create(Authenticatable $user, string $provider, ProviderUser $socialiteUser): SocialAccount;
}
