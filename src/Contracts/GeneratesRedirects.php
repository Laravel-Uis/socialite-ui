<?php

namespace SocialiteUi\Contracts;

use Illuminate\Http\RedirectResponse;

interface GeneratesRedirects
{
    /**
     * Generates the redirect for a given provider.
     */
    public function generate(string $provider): RedirectResponse;
}
