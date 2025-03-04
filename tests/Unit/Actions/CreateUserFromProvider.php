<?php

namespace SocialiteUi\Tests\Unit\Actions;

use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Socialite\Contracts\User as Socialite;
use Mockery\MockInterface;
use SocialiteUi\Actions\CreateUserFromProvider;
use SocialiteUi\Contracts\CreatesSocialAccounts;
use SocialiteUi\Contracts\UserRepository;
use SocialiteUi\Tests\Fixtures\User;

describe('Create User From Provider Test', fn () => [
    test('creates a user', function () {
        $user = new User;

        /** @var Socialite $socialiteUser */
        $socialiteUser = mock(Socialite::class);

        /** @var CreatesSocialAccounts&MockInterface $createsSocialAccounts */
        $createsSocialAccounts = mock(
            CreatesSocialAccounts::class,
            function (CreatesSocialAccounts&MockInterface $mock) use ($user, $socialiteUser) {
                $mock->shouldReceive('create')
                    ->with($user, 'github', $socialiteUser);
            },
        );

        /** @var UserRepository&MockInterface $repository */
        $repository = mock(UserRepository::class, function (UserRepository&MockInterface $mock) use ($user, $socialiteUser) {
            $mock->shouldReceive('create')->with($socialiteUser)->andReturn($user);
        });

        $action = new CreateUserFromProvider($createsSocialAccounts, $repository);

        $user = $action->create('github', $socialiteUser);

        expect($user)->toBeInstanceOf(Authenticatable::class);
    }),
]);
