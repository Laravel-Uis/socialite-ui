<?php

namespace SocialiteUi\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Socialite\Contracts\User as Socialite;

interface UpdatesSocialAccounts
{
    public function update(Authenticatable $user, SocialAccount $socialAccount, string $provider, Socialite $socialiteUser): SocialAccount;
}
