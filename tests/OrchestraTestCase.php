<?php

namespace SocialiteUi\Tests;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as BaseTestCase;
use SocialiteUi\SocialiteUi;
use SocialiteUi\Tests\Fixtures\User;

abstract class OrchestraTestCase extends BaseTestCase
{
    use LazilyRefreshDatabase, WithWorkbench;

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.debug', true);
        $app['config']->set('database.default', 'testing');
        $app['config']->set('cache.default', 'file');

        $app['config']->set('services.github', [
            'client_id' => 'github-client-id',
            'client_secret' => 'github-client-secret',
            'redirect' => 'https://example.test/oauth/github/callback',
        ]);

        SocialiteUi::useUserModel(User::class);
    }

    protected function defineRoutes($router)
    {
        $router->get('dashboard', fn () => 'foo')->name('dashboard');
    }
}
