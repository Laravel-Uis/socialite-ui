<?php

namespace SocialiteUi\Actions;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Gate;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use SocialiteUi\Contracts\SocialAccount;
use SocialiteUi\Contracts\SocialAccountRepository;
use SocialiteUi\Contracts\UpdatesSocialAccounts;

/**
 * @internal
 */
final readonly class UpdateSocialAccount implements UpdatesSocialAccounts
{
    public function __construct(
        private SocialAccountRepository $socialAccounts,
    ) {}

    public function update(Authenticatable $user, SocialAccount $socialAccount, string $provider, SocialiteUser $socialiteUser): SocialAccount
    {
        Gate::forUser($user)->authorize('update', $socialAccount);

        return $this->socialAccounts->update(
            $socialAccount,
            $socialiteUser,
        );
    }
}
