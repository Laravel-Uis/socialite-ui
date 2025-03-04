<?php

namespace SocialiteUi\Tests\Unit\Actions;

use Laravel\Socialite\Contracts\User as Socialite;
use Mockery\MockInterface;
use SocialiteUi\Actions\CreateSocialAccount;
use SocialiteUi\Contracts\SocialAccount;
use SocialiteUi\Contracts\SocialAccountRepository;
use SocialiteUi\Tests\Fixtures\SocialAccountTemplate;
use SocialiteUi\Tests\Fixtures\User;

describe('Create Social Account Action Test', fn () => [
    test('creates an account', function () {
        $user = new User;

        /** @var Socialite&MockInterface $socialiteUserMock */
        $socialiteUserMock = mock(Socialite::class);

        /** @var SocialAccountRepository&MockInterface $repository */
        $repository = mock(SocialAccountRepository::class, function (SocialAccountRepository&MockInterface $mock) use (
            $socialiteUserMock,
            $user
        ) {
            $mock->shouldReceive('create')
                ->once()
                ->with($user, 'github', $socialiteUserMock)
                ->andReturn(new SocialAccountTemplate);
        });

        $action = new CreateSocialAccount($repository);

        $account = $action->create($user, 'github', $socialiteUserMock);

        expect($account)->toBeInstanceOf(SocialAccount::class);
    }),
]);
