<?php

namespace SocialiteUi\Tests\Unit\Repositories;

use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Socialite\Two\User as SocialiteUser;
use SocialiteUi\Contracts\SocialAccount;
use SocialiteUi\Repositories\SocialAccountRepository;

use function Pest\Laravel\assertDatabaseHas;

describe('Social Account Repository Test', fn () => [
    test('can create a social account', function () {
        $user = new class implements Authenticatable
        {
            public function getAuthIdentifierName(): string
            {
                return 'id';
            }

            public function getAuthIdentifier(): int
            {
                return 1;
            }

            public function getAuthPasswordName(): string
            {
                return 'password';
            }

            public function getAuthPassword(): string
            {
                return 'password';
            }

            public function getRememberToken(): null
            {
                return null;
            }

            public function setRememberToken($value): null
            {
                return null;
            }

            public function getRememberTokenName(): string
            {
                return 'remember_token';
            }
        };

        $socialiteUser = (new SocialiteUser);
        $socialiteUser->id = fake()->numerify('######');
        $socialiteUser->name = fake()->name();
        $socialiteUser->email = fake()->safeEmail();
        $socialiteUser->avatar = fake()->imageUrl();
        $socialiteUser->setToken(fake()->sha256());

        $repository = new SocialAccountRepository;
        $account = $repository->create($user, 'github', $socialiteUser);

        expect($account)->toBeInstanceOf(SocialAccount::class);

        assertDatabaseHas('social_accounts', [
            'user_id' => 1,
            'provider' => 'github',
            'provider_id' => $socialiteUser->id,
            'name' => $socialiteUser->name,
            'email' => $socialiteUser->email,
            'avatar' => $socialiteUser->avatar,
            'token' => $socialiteUser->token,
        ]);
    }),
    test('can update a user', function () {
        $user = new class implements Authenticatable
        {
            public function getAuthIdentifierName(): string
            {
                return 'id';
            }

            public function getAuthIdentifier(): int
            {
                return 1;
            }

            public function getAuthPasswordName(): string
            {
                return 'password';
            }

            public function getAuthPassword(): string
            {
                return 'password';
            }

            public function getRememberToken(): null
            {
                return null;
            }

            public function setRememberToken($value): null
            {
                return null;
            }

            public function getRememberTokenName(): string
            {
                return 'remember_token';
            }
        };

        $socialiteUser = (new SocialiteUser);
        $socialiteUser->id = fake()->numerify('######');
        $socialiteUser->name = 'Foo';
        $socialiteUser->email = fake()->safeEmail();
        $socialiteUser->avatar = fake()->imageUrl();
        $socialiteUser->setToken(fake()->sha256());

        $socialiteUser->name = 'Bar';

        $repository = new SocialAccountRepository;
        $original = $repository->create($user, 'github', $socialiteUser);

        $account = $repository->update($original, $socialiteUser);

        expect($account)->toBeInstanceOf(SocialAccount::class);

        assertDatabaseHas('social_accounts', [
            'user_id' => 1,
            'provider' => 'github',
            'provider_id' => $socialiteUser->id,
            'name' => 'Bar',
            'email' => $socialiteUser->email,
            'avatar' => $socialiteUser->avatar,
            'token' => $socialiteUser->token,
        ]);
    }),
]);
