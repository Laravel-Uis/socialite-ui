<?php

namespace SocialiteUi;

use App\Models\SocialAccount;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use SocialiteUi\Actions\AuthenticateOAuthCallback;
use SocialiteUi\Actions\CreateSocialAccount;
use SocialiteUi\Actions\CreateUserFromProvider;
use SocialiteUi\Actions\GenerateRedirects;
use SocialiteUi\Actions\HandleInvalidState;
use SocialiteUi\Actions\HandleOAuthCallbackErrors;
use SocialiteUi\Actions\ResolveSocialiteUser;
use SocialiteUi\Actions\UpdateSocialAccount;
use SocialiteUi\Contracts\SocialAccountRepository as SocialAccountRepositoryContract;
use SocialiteUi\Contracts\UserRepository as UserRepositoryContract;
use SocialiteUi\Repositories\DatabaseUserRepository;
use SocialiteUi\Repositories\SocialAccountRepository;

class SocialiteUiServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/socialite-ui.php', 'socialite-ui');

        // if there's no fortify, we need to bind a stateful guard to the container
        if (! config('fortify.guard')) {
            $this->app->bind(StatefulGuard::class, function () {
                return Auth::guard(config('socialite-ui.guard'));
            });
        }

        $this->app->bind(UserRepositoryContract::class, DatabaseUserRepository::class);
        $this->app->bind(SocialAccountRepositoryContract::class, SocialAccountRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureRoutes();
        $this->configureCommands();
    }

    /**
     * Sets sensible package defaults.
     */
    private function configureDefaults(): void
    {
        $this->publishes([
            __DIR__.'/../config/socialite-ui.php' => config_path('socialite-ui.php'),
        ], 'socialite-ui-config');

        $this->publishesMigrations([
            __DIR__.'/../database/migrations/2025_02_27_000000_update_users_table.php' => database_path('migrations/2025_02_27_000000_update_users_table.php'),
            __DIR__.'/../database/migrations/2025_02_27_000001_create_social_accounts_table.php' => database_path('migrations/2025_02_27_000001_create_social_accounts_table.php'),
        ], 'socialite-ui-migrations');

        SocialiteUi::useSocialAccountModel(SocialAccount::class);
        Gate::policy(SocialiteUi::socialAccountModel(), Policies\SocialAccountPolicy::class);

        SocialiteUi::authenticatesOAuthCallbackUsing(AuthenticateOAuthCallback::class);
        SocialiteUi::handlesOAuthCallbackErrorsUsing(HandleOAuthCallbackErrors::class);
        SocialiteUi::resolvesSocialiteUsersUsing(ResolveSocialiteUser::class);
        SocialiteUi::createUsersFromProviderUsing(CreateUserFromProvider::class);
        SocialiteUi::createSocialAccountsUsing(CreateSocialAccount::class);
        SocialiteUi::updateSocialAccountsUsing(UpdateSocialAccount::class);
        SocialiteUi::handlesInvalidStateUsing(HandleInvalidState::class);
        SocialiteUi::generatesProvidersRedirectsUsing(GenerateRedirects::class);
    }

    /**
     * Configure the routes offered by the application.
     */
    private function configureRoutes(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../routes/web.php' => base_path('routes/socialite-ui.php'),
            ], 'socialite-ui-routes');
        }

        $this->loadRoutesFrom(path: __DIR__.'/../routes/web.php');
    }

    /**
     * Configure the commands offered by the application.
     */
    protected function configureCommands(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            Console\InstallCommand::class,
        ]);
    }
}
