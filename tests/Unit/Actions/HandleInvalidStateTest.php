<?php

namespace SocialiteUi\Tests\Unit\Actions;

use Laravel\Socialite\Two\InvalidStateException;
use SocialiteUi\Actions\HandleInvalidState;

describe('Handle Invalid State Test', fn () => [
    test('throws', function () {
        $action = new HandleInvalidState;

        expect(fn () => $action->handle(new InvalidStateException))
            ->toThrow(InvalidStateException::class);
    }),
]);
