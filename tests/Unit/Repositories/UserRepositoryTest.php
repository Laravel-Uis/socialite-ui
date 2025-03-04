<?php

namespace SocialiteUi\Tests\Unit\Repositories;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Two\User as SocialiteUser;
use SocialiteUi\Repositories\DatabaseUserRepository;
use SocialiteUi\SocialiteUi;

use function Pest\Laravel\assertDatabaseHas;

uses(RefreshDatabase::class);

describe('User Repository Tests', fn () => [
    test('it creates a user', function () {
        $repository = new DatabaseUserRepository;

        $socialiteUser = (new SocialiteUser);
        $socialiteUser->id = fake()->numerify('######');
        $socialiteUser->name = fake()->name();
        $socialiteUser->email = fake()->safeEmail();
        $socialiteUser->avatar = fake()->imageUrl();

        $user = $repository->create($socialiteUser);

        expect($user)->toBeInstanceOf(Authenticatable::class);

        assertDatabaseHas(table: 'users', data: [
            'name' => $socialiteUser->name,
            'email' => $socialiteUser->email,
            'avatar' => $socialiteUser->avatar,
        ]);
    }),
    test('it marks a users email as verified', function () {
        final class VerifiableUser extends User implements MustVerifyEmail
        {
            protected $table = 'users';
        }

        SocialiteUi::useUserModel(VerifiableUser::class);

        $socialiteUser = (new SocialiteUser);
        $socialiteUser->id = fake()->numerify('######');
        $socialiteUser->name = fake()->name();
        $socialiteUser->email = fake()->safeEmail();
        $socialiteUser->avatar = fake()->imageUrl();

        $repository = new DatabaseUserRepository;

        /** @var MustVerifyEmail $user */
        $user = $repository->create($socialiteUser);

        expect($user)->toBeInstanceOf(MustVerifyEmail::class)
            ->and($user->hasVerifiedEmail())->toBeTruthy();
    }),
]);
