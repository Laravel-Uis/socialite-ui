<?php

namespace SocialiteUi\Tests\Unit\Actions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Laravel\Socialite\Contracts\User as Socialite;
use Mockery\MockInterface;
use SocialiteUi\Actions\UpdateSocialAccount;
use SocialiteUi\Contracts\SocialAccount;
use SocialiteUi\Contracts\SocialAccountRepository;
use SocialiteUi\Tests\Fixtures\SocialAccountTemplate;
use SocialiteUi\Tests\Fixtures\User;

describe('Update Social Account Test', fn () => [
    it('updates an account', function () {
        $user = new User;
        $socialiteUser = mock(Socialite::class);
        $account = new SocialAccountTemplate;

        Gate::shouldReceive('forUser')->once()->with($user)->andReturnSelf();
        Gate::shouldReceive('authorize')->with('update', $account)->andReturnTrue();

        $repository = mock(SocialAccountRepository::class, function (SocialAccountRepository&MockInterface $mock) use (

            $account,
            $socialiteUser
        ) {
            $mock->shouldReceive('update')
                ->once()
                ->with($account, $socialiteUser)
                ->andReturn($account);
        });

        $action = new UpdateSocialAccount($repository);

        expect($action->update($user, $account, 'github', $socialiteUser))
            ->toBeInstanceOf(SocialAccount::class);
    }),
    it('throws if not authorized to perform updates', function () {
        $user = new User;
        $socialiteUser = mock(Socialite::class);
        $account = new SocialAccountTemplate;

        $action = new UpdateSocialAccount(mock(SocialAccountRepository::class));

        expect(fn () => $action->update($user, $account, 'github', $socialiteUser))
            ->toThrow(AuthorizationException::class, exceptionMessage: 'This action is unauthorized.');
    }),
]);
