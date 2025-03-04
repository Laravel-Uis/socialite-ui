<?php

namespace SocialiteUi\Tests\Unit\Actions;

use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;
use SocialiteUi\Actions\GenerateRedirects;

describe('Generate Redirect Test', fn () => [
    test('it returns a redirect response', function () {
        $action = new GenerateRedirects;

        Socialite::shouldReceive('driver')->with('github')->andReturnSelf();
        Socialite::shouldReceive('redirect')->andReturn(new RedirectResponse(
            url: 'http://localhost',
            status: 302,
            headers: [],
        ));

        $redirect = $action->generate('github');

        expect($redirect)->toBeInstanceOf(RedirectResponse::class);
    }),
]);
