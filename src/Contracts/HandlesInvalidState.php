<?php

namespace SocialiteUi\Contracts;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Laravel\Socialite\Two\InvalidStateException;

interface HandlesInvalidState
{
    /**
     * Handle an invalid state exception from a Socialite provider.
     */
    public function handle(InvalidStateException $exception): Response|RedirectResponse;
}
