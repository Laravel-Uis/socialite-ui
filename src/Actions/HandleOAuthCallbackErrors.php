<?php

namespace SocialiteUi\Actions;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use SocialiteUi\Contracts\HandlesOAuthCallbackErrors;

/**
 * @internal
 */
final readonly class HandleOAuthCallbackErrors implements HandlesOAuthCallbackErrors
{
    /**
     * Handles the request if the "errors" key is present.
     */
    public function handle(Request $request): ?RedirectResponse
    {
        if (! $request->has('error')) {
            return null;
        }

        $error = $request->get('error_description', $request->get('error'));

        if (! $request->user()) {
            return to_route('login')
                ->with('socialite-ui.error', $error);
        }

        return to_route('linked-accounts')
            ->with('socialite-ui.error', $error);
    }
}
