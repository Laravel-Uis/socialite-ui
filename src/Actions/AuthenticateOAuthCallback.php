<?php

namespace SocialiteUi\Actions;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use SocialiteUi\Contracts\AuthenticatesOAuthCallback;
use SocialiteUi\Contracts\CreatesSocialAccounts;
use SocialiteUi\Contracts\CreatesUserFromProvider;
use SocialiteUi\Contracts\SocialAccount;
use SocialiteUi\Contracts\SocialAccountRepository;
use SocialiteUi\Contracts\UpdatesSocialAccounts;
use SocialiteUi\Contracts\UserRepository;
use SocialiteUi\Events\NewOAuthRegistration;
use SocialiteUi\Events\OAuthFailed;
use SocialiteUi\Events\OAuthLogin;
use SocialiteUi\Events\OAuthProviderLinked;
use SocialiteUi\Events\OAuthProviderLinkFailed;
use SocialiteUi\Features;
use SocialiteUi\Providers;
use SocialiteUi\SocialiteUi;

/**
 * @internal
 */
final readonly class AuthenticateOAuthCallback implements AuthenticatesOAuthCallback
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private StatefulGuard $guard,
        private UserRepository $userRepository,
        private SocialAccountRepository $socialAccountRepository,
        private CreatesUserFromProvider $createsUser,
        private CreatesSocialAccounts $createsSocialAccounts,
        private UpdatesSocialAccounts $updatesSocialAccounts,
    ) {
        //
    }

    /**
     * Handle the authentication of the user.
     */
    public function authenticate(Request $request, string $provider, SocialiteUser $socialiteUser): RedirectResponse
    {
        // User is logged in, prompt the user confirm they wish to link their account.
        if ($user = $request->user()) {
            Cache::put("socialite-ui.{$user->getAuthIdentifier()}:$provider.provider", $socialiteUser, ttl: 600);

            return to_route('oauth.confirm.show', ['provider' => $provider]);
        }

        try {
            return $this->attempt($request, $provider, $socialiteUser);
        } catch (QueryException $exception) {
            report(new \DomainException(
                message: 'Something went wrong while trying to authenticate a user.',
                previous: $exception,
            ));

            event(new OAuthFailed($provider, $socialiteUser));

            return to_route('login')
                ->with('socialite-ui.error', 'Oops! Something went wrong.');
        }
    }

    /**
     * Attempt to authenticate the user.
     */
    protected function attempt(Request $request, string $provider, SocialiteUser $socialiteUser): RedirectResponse
    {
        // Registration...
        $account = $this->socialAccountRepository->find($provider, $socialiteUser->getId());
        $user = $this->userRepository->find($socialiteUser->getEmail());

        if (! $account && ! $user) {
            return $this->register($request, $provider, $socialiteUser);
        }

        // This should never happen...
        if ($account && ! $user) {
            // Notify the developers something went wrong.
            report(new \DomainException(
                message: 'Could not retrieve user information.',
            ));

            event(new OAuthFailed($provider, $socialiteUser));

            // Gracefully handle the error for the user.
            return redirect()->route('login')
                ->with('socialite-ui.error', 'These credentials do not match our records.');
        }

        if ($user && ! $account) {
            if (! Features::authenticatesExistingUnlinkedUsers()) {
                event(new OAuthFailed($provider, $socialiteUser));

                return to_route('login')
                    ->with('socialite-ui.error', 'These credentials do not match our records.');
            }

            $account = $this->createsSocialAccounts->create($user, $provider, $socialiteUser);
        }

        return $this->login($request, $user, $account, $provider, $socialiteUser);
    }

    /**
     * Handle the registration of a new user.
     */
    protected function register(Request $request, string $provider, SocialiteUser $socialiteUser): RedirectResponse
    {
        if (! $this->canRegister($request)) {
            return to_route('login')
                ->with('socialite-ui.error', 'These credentials do not match our records.');
        }

        $user = $this->createsUser->create($provider, $socialiteUser);

        $this->guard->login($user, SocialiteUi::hasRememberSessionFeatures());

        event(new NewOAuthRegistration($user, $provider, $socialiteUser));

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Authenticate the given user and return a login response.
     */
    protected function login(Request $request, Authenticatable $user, SocialAccount $account, string $provider, SocialiteUser $socialiteUser): RedirectResponse
    {
        $account = $this->updatesSocialAccounts->update($user, $account, $provider, $socialiteUser);

        $this->guard->loginUsingId($user->getAuthIdentifier(), SocialiteUi::hasRememberSessionFeatures());

        event(new OAuthLogin($user, $provider, $account, $socialiteUser));

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Handle the linking of a provider account to an already authenticated user.
     */
    public function link(Request $request): RedirectResponse
    {
        $user = $request->user();
        $provider = $request->string('provider')->lower()->toString();

        /** @var ?SocialiteUser $socialiteUser */
        $socialiteUser = Cache::pull("socialite-ui.{$user->getAuthIdentifier()}:$provider.provider");

        $result = $request->input('result');

        if ($result === 'deny') {
            event(new OAuthProviderLinkFailed($user, $provider, null, $socialiteUser));

            return to_route('linked-accounts')
                ->with('socialite-ui.error', __('Failed to link :provider account. User denied the request.', ['provider' => Providers::name($provider)]));
        }

        if (! $socialiteUser) {
            report(new \DomainException(
                message: 'Could not retrieve social provider information.',
            ));

            event(new OAuthProviderLinkFailed($user, $provider, null, $socialiteUser));

            return to_route('linked-accounts')
                ->with('socialite-ui.error', __('Failed to link :provider account. Please try again.', ['provider' => Providers::name($provider)]));
        }

        $account = $this->socialAccountRepository->find($provider, $socialiteUser->getId());

        if ($account && $user->getAuthIdentifier() !== $account->getUserId()) {
            event(new OAuthProviderLinkFailed($user, $provider, $account, $socialiteUser));

            return to_route('linked-accounts')
                ->with('socialite-ui.error', __('Could not link your :provider account. User does not match.', ['provider' => Providers::name($provider)]));
        }

        if (! $account) {
            $this->createsSocialAccounts->create($user, $provider, $socialiteUser);
        }

        event(new OAuthProviderLinked($user, $provider, $account, $socialiteUser));

        return to_route('linked-accounts')
            ->with('status', __(':provider account linked.', ['provider' => Providers::name($provider)]));
    }

    /**
     * Determine if we can register a new user.
     */
    protected function canRegister(Request $request): bool
    {
        if (Route::has('register') && $request->session()->get('socialite-ui.previous_url') === route('register')) {
            return true;
        }

        if (Route::has('login') && $request->session()->get('socialite-ui.previous_url') === route('login')) {
            return Features::hasCreateAccountOnFirstLoginFeatures();
        }

        return Features::hasGlobalLoginFeatures();
    }
}
