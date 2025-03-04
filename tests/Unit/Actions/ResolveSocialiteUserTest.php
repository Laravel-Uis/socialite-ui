<?php

namespace SocialiteUi\Tests\Unit\Actions;

use Illuminate\Support\Facades\Config;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User;
use SocialiteUi\Actions\ResolveSocialiteUser;
use SocialiteUi\SocialiteUi;

describe('Resolve Socialite User Test', fn () => [
    test('resolves a user', function () {
        Config::set('socialite-ui.features', []);

        Socialite::shouldReceive('driver')->once()->andReturnSelf();
        Socialite::shouldReceive('user')->once()->andReturn(mock(User::class));

        $action = new ResolveSocialiteUser;

        $user = $action->resolve('github');

        expect($user)->toBeInstanceOf(User::class);
    }),
    test('generates missing emails', function () {
        Config::set('app.domain', 'example.com');
        Config::set('socialite-ui.features', [
            SocialiteUi::generatesMissingEmails(),
        ]);

        $user = new User;
        $user->id = $id = fake()->numerify('######');

        Socialite::shouldReceive('driver')->once()->andReturnSelf();
        Socialite::shouldReceive('user')->once()->andReturn($user);

        $action = new ResolveSocialiteUser;

        $user = $action->resolve('github');

        expect($user)->toBeInstanceOf(User::class)
            ->getEmail()->toBe("$id@github.example.com");
    }),
]);
