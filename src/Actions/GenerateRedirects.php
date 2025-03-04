<?php

namespace SocialiteUi\Actions;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Laravel\Socialite\Facades\Socialite;
use SocialiteUi\Contracts\GeneratesRedirects;

/**
 * @internal
 */
final readonly class GenerateRedirects implements GeneratesRedirects
{
    /**
     * Generates the redirect for a given provider.
     */
    public function generate(string $provider): RedirectResponse
    {
        Session::put(
            key: 'socialite-ui.previous',
            value: URL::previous(),
        );

        /** @var RedirectResponse */
        return Socialite::driver($provider)->redirect();
    }
}
