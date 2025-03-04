<?php

namespace SocialiteUi\Actions;

use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Socialite\Contracts\User as Socialite;
use SocialiteUi\Contracts\CreatesSocialAccounts;
use SocialiteUi\Contracts\CreatesUserFromProvider;
use SocialiteUi\Contracts\UserRepository;

/**
 * @internal
 */
final readonly class CreateUserFromProvider implements CreatesUserFromProvider
{
    public function __construct(
        private CreatesSocialAccounts $createsSocialAccounts,
        private UserRepository $repository,
    ) {}

    public function create(string $provider, Socialite $socialiteUser): Authenticatable
    {
        $user = $this->repository->create($socialiteUser);

        $this->createsSocialAccounts->create($user, $provider, $socialiteUser);

        return $user;
    }
}
