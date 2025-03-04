<?php

namespace SocialiteUi\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Inertia\Response as InertiaResponse;
use Laravel\Socialite\Two\InvalidStateException;
use SocialiteUi\Contracts\AuthenticatesOAuthCallback;
use SocialiteUi\Contracts\GeneratesRedirects;
use SocialiteUi\Contracts\HandlesInvalidState;
use SocialiteUi\Contracts\HandlesOAuthCallbackErrors;
use SocialiteUi\Contracts\ResolvesSocialiteUsers;
use SocialiteUi\Enums\Provider;
use SocialiteUi\SocialiteUi;

class OAuthController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected HandlesOAuthCallbackErrors $errorHandler,
        protected ResolvesSocialiteUsers $userResolver,
        protected AuthenticatesOAuthCallback $authenticator,
        protected HandlesInvalidState $invalidStateHandler,
    ) {
        //
    }

    /**
     * Get the redirect for the given Socialite provider.
     */
    public function redirect(Request $request, string $provider, GeneratesRedirects $generator): RedirectResponse
    {
        $request->session()->put('socialite-ui.previous_url', back()->getTargetUrl());

        return $generator->generate($provider);
    }

    /**
     * Attempt to log the user in via the provider user returned from Socialite.
     */
    public function callback(Request $request, string $provider): RedirectResponse|Response
    {
        $redirect = $this->errorHandler->handle($request);

        if ($redirect instanceof RedirectResponse) {
            return $redirect;
        }

        try {
            $providerAccount = $this->userResolver->resolve($provider);
        } catch (InvalidStateException $e) {
            return $this->invalidStateHandler->handle($e);
        }

        return $this->authenticator->authenticate($request, $provider, $providerAccount);
    }

    /**
     * Show the oauth confirmation page.
     */
    public function prompt(Request $request): View|RedirectResponse|InertiaResponse|Response
    {
        $request->validate([
            'provider' => ['required', Rule::in(config('socialite-ui.providers'))],
        ]);

        $provider = $request->enum('provider', Provider::class);

        return app()->call(SocialiteUi::getOAuthConfirmationPrompt(), ['provider' => $provider]);
    }

    public function confirm(Request $request): RedirectResponse
    {
        $request->validate([
            'result' => ['required', 'in:confirm,deny'],
            'provider' => ['required', 'string'],
        ]);

        if ($request->string('result')->lower()->toString() === 'confirm') {
            $request->validate([
                'password' => ['required_if:result,confirm', 'current_password'],
            ]);
        }

        return $this->authenticator->link($request);
    }
}
