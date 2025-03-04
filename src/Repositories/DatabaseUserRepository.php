<?php

namespace SocialiteUi\Repositories;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Laravel\Socialite\Contracts\User as Socialite;
use SocialiteUi\Contracts\UserRepository as Contract;
use SocialiteUi\SocialiteUi;

/**
 * @phpstan-type TUser (Model&Authenticatable)|(Model&Authenticatable&MustVerifyEmail)
 *
 * @internal
 */
final class DatabaseUserRepository implements Contract
{
    public function find(string $email): ?Authenticatable
    {
        /** @var ?TUser */
        return SocialiteUi::userModel()::query()
            ->where('email', $email)
            ->first();
    }

    public function create(Socialite $socialiteUser): Authenticatable
    {
        /** @var TUser $user */
        $user = SocialiteUi::userModel()::query()->create([
            'name' => $socialiteUser->getName() ?? $socialiteUser->getNickname(),
            'email' => $socialiteUser->getEmail(),
            'avatar' => $socialiteUser->getAvatar(),
        ]);

        if ($user instanceof MustVerifyEmail) {
            $user->markEmailAsVerified();
        }

        return $user;
    }
}
