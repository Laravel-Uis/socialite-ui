<?php

namespace SocialiteUi\Actions;

use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Socialite\Contracts\User as Socialite;
use SocialiteUi\Contracts\CreatesSocialAccounts;
use SocialiteUi\Contracts\SocialAccount;
use SocialiteUi\Contracts\SocialAccountRepository;

/**
 * @internal
 */
final readonly class CreateSocialAccount implements CreatesSocialAccounts
{
    public function __construct(private SocialAccountRepository $repository) {}

    public function create(Authenticatable $user, string $provider, Socialite $socialiteUser): SocialAccount
    {
        return $this->repository->create($user, $provider, $socialiteUser);
    }
}
