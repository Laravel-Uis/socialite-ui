<?php

namespace SocialiteUi\Actions;

use Laravel\Socialite\AbstractUser;
use Laravel\Socialite\Contracts\User;
use Laravel\Socialite\Facades\Socialite;
use SocialiteUi\Contracts\ResolvesSocialiteUsers;
use SocialiteUi\SocialiteUi;

/**
 * @internal
 */
final readonly class ResolveSocialiteUser implements ResolvesSocialiteUsers
{
    /**
     * Resolve the user for a given provider.
     */
    public function resolve(string $provider): User
    {
        /** @var AbstractUser $user */
        $user = Socialite::driver($provider)->user();

        if (SocialiteUi::generatesMissingEmails()) {
            $user->email = $user->getEmail() ?? ("$user->id@$provider.".config('app.domain'));
        }

        return $user;
    }
}
