<?php

namespace SocialiteUi\Actions;

use Illuminate\Http\Response;
use Laravel\Socialite\Two\InvalidStateException;
use SocialiteUi\Contracts\HandlesInvalidState;

/**
 * @internal
 */
final readonly class HandleInvalidState implements HandlesInvalidState
{
    /**
     * Handle an invalid state exception from a Socialite provider.
     */
    public function handle(InvalidStateException $exception): Response
    {
        throw $exception;
    }
}
