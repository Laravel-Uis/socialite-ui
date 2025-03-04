<?php

namespace SocialiteUi\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Socialite\Contracts\User as Socialite;

interface UserRepository
{
    public function find(string $email): ?Authenticatable;

    public function create(Socialite $socialiteUser): Authenticatable;
}
