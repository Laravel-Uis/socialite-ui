<?php

namespace SocialiteUi\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Laravel\Socialite\Contracts\User as Socialite;

interface SocialAccountRepository
{
    public function find(string $provider, string $providerId): (Model&SocialAccount)|null;

    public function findForProviderAndEmail(string $provider, string $email): (Model&SocialAccount)|null;

    public function create(Authenticatable $user, string $provider, Socialite $data): SocialAccount;

    public function update(SocialAccount $original, Socialite $data): SocialAccount;
}
