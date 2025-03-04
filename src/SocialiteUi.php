<?php

namespace SocialiteUi;

use Closure;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use SocialiteUi\Contracts\AuthenticatesOAuthCallback;
use SocialiteUi\Contracts\CreatesSocialAccounts;
use SocialiteUi\Contracts\CreatesUserFromProvider;
use SocialiteUi\Contracts\GeneratesRedirects;
use SocialiteUi\Contracts\HandlesInvalidState;
use SocialiteUi\Contracts\HandlesOAuthCallbackErrors;
use SocialiteUi\Contracts\ResolvesSocialiteUsers;
use SocialiteUi\Contracts\SocialAccount;
use SocialiteUi\Contracts\UpdatesSocialAccounts;
use SocialiteUi\Enums\Provider;

/**
 * @phpstan-type TUser (Model&Authenticatable)|(Model&Authenticatable&MustVerifyEmail)
 * @phpstan-type TSocialAccount Model&SocialAccount
 */
class SocialiteUi
{
    /**
     * Determines if the application is using Socialite UI.
     */
    public static bool $enabled = true;

    /**
     * The user model that should be used by Socialite UI.
     *
     * @var class-string<TUser>
     */
    public static string $userModel = 'App\\Models\\User';

    /**
     * The user model that should be used by Socialite UI.
     *
     * @var class-string<TSocialAccount>
     */
    public static string $socialAccountModel = 'App\\Models\\SocialAccount';

    /**
     * The callback that should be used to prompt the user to confirm their OAuth authorization.
     *
     * @var ?(Closure(Provider): (Response|InertiaResponse|RedirectResponse|View))
     */
    public static ?Closure $oAuthConfirmationPrompt = null;

    /**
     * Get the name of the user model used by the application.
     *
     * @return class-string<TUser>
     */
    public static function userModel(): string
    {
        return static::$userModel;
    }

    /**
     * Get a new instance of the user model.
     *
     * @return TUser
     */
    public static function newUserModel(): mixed
    {
        $model = static::userModel();

        return new $model;
    }

    /**
     * Specify the user model that should be used.
     *
     * @param  class-string<TUser>  $model
     */
    public static function useUserModel(string $model): void
    {
        static::$userModel = $model;
    }

    /**
     * Determine whether the UI is enabled.
     */
    public static function enabled(callable|bool|null $callback = null): bool
    {
        if (is_callable($callback)) {
            static::$enabled = $callback();
        }

        if (is_bool($callback)) {
            static::$enabled = $callback;
        }

        return static::$enabled;
    }

    /**
     * Attempt to get a provider for the given input.
     */
    public static function provider(Provider|string $provider): Provider
    {
        return is_string($provider) ? Provider::from($provider) : $provider;
    }

    /**
     * Determine which providers the application supports.
     *
     * @return Collection<int, Provider>
     */
    public static function providers(): Collection
    {
        /** @var list<string> $providers */
        $providers = config('socialite-ui.providers');

        return collect($providers)->map(fn ($provider) => Provider::from($provider));
    }

    /**
     * Determine if the application has the generates missing emails feature enabled.
     */
    public static function generatesMissingEmails(): bool
    {
        return Features::generatesMissingEmails();
    }

    /**
     * Determine if the application should remember the users session om login.
     */
    public static function hasRememberSessionFeatures(): bool
    {
        return Features::hasRememberSessionFeatures();
    }

    /**
     * Determine if the application should refresh the tokens on retrieval.
     */
    public static function refreshesOAuthTokens(): bool
    {
        return Features::refreshesOAuthTokens();
    }

    /**
     * Get the name of the social account model used by the application.
     *
     * @return class-string<Model&SocialAccount>
     */
    public static function socialAccountModel(): string
    {
        return static::$socialAccountModel;
    }

    /**
     * Specify the social account model that should be used.
     *
     * @param  class-string<Model&SocialAccount>  $model
     */
    public static function useSocialAccountModel(string $model): void
    {
        static::$socialAccountModel = $model;
    }

    /**
     * Register a class / callback that should be used to resolve the user for a Socialite Provider.
     */
    public static function resolvesSocialiteUsersUsing(string $class): void
    {
        app()->singleton(ResolvesSocialiteUsers::class, $class);
    }

    /**
     * Register a class / callback that should be used to create users from social providers.
     */
    public static function createUsersFromProviderUsing(string $class): void
    {
        app()->singleton(CreatesUserFromProvider::class, $class);
    }

    /**
     * Register a class / callback that should be used to create social accounts.
     */
    public static function createSocialAccountsUsing(string $class): void
    {
        app()->singleton(CreatesSocialAccounts::class, $class);
    }

    /**
     * Register a class / callback that should be used to update social accounts.
     */
    public static function updateSocialAccountsUsing(string $class): void
    {
        app()->singleton(UpdatesSocialAccounts::class, $class);
    }

    /**
     * Register a class / callback that should be used to set user passwords.
     */
    public static function handlesInvalidStateUsing(Closure|string $callback): void
    {
        app()->singleton(HandlesInvalidState::class, $callback);
    }

    /**
     * Register a class / callback that should be used to authenticate the OAuth callback.
     */
    public static function authenticatesOAuthCallbackUsing(Closure|string $callback): void
    {
        app()->singleton(AuthenticatesOAuthCallback::class, $callback);
    }

    /**
     * Register a class / callback that should be used to handle OAuth callback errors.
     */
    public static function handlesOAuthCallbackErrorsUsing(Closure|string $callback): void
    {
        app()->singleton(HandlesOAuthCallbackErrors::class, $callback);
    }

    /**
     * Register a class / callback that should be used for generating provider redirects.
     */
    public static function generatesProvidersRedirectsUsing(Closure|string $callback): void
    {
        app()->singleton(GeneratesRedirects::class, $callback);
    }

    /**
     * Register a callback that should be used to prompt the user to confirm their OAuth.
     *
     * @param  ?(Closure(Provider): (Response|InertiaResponse|RedirectResponse|View))  $callback
     */
    public static function promptOAuthLinkUsing(?Closure $callback = null): void
    {
        self::$oAuthConfirmationPrompt = $callback;
    }

    public static function getOAuthConfirmationPrompt(): Closure
    {
        return self::$oAuthConfirmationPrompt ?? function (Provider $provider) {
            return Inertia::render('auth/confirm-link-account', [
                'provider' => $provider->toArray(),
            ]);
        };
    }
}
