<?php

namespace SocialiteUi\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GithubProvider;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Orchestra\Testbench\Concerns\WithWorkbench;
use SocialiteUi\Contracts\GeneratesRedirects;
use SocialiteUi\Enums\Provider;
use SocialiteUi\SocialiteUi;

use function Pest\Laravel\get;
use function Pest\Laravel\post;

uses(RefreshDatabase::class, WithWorkbench::class);

beforeEach(function () {
    Route::middleware('auth')->group(function () {
        Route::get('/dashboard', fn () => 'dashboard')->name('dashboard');
        Route::get('/linked-accounts', fn () => 'linked-accounts')->name('linked-accounts');
    });
    Route::get('/register', fn () => 'register')->name('register');
    Route::get('/login', fn () => 'login')->name('login');
});

it('redirects users', function (): void {
    $response = get('/oauth/github');

    $response->assertRedirect()
        ->assertRedirectContains('github.com');
});

it('generates a redirect using an overriding closure', function (bool $manageRepos): void {
    Config::set('services.github.manage_repos', $manageRepos);

    SocialiteUi::generatesProvidersRedirectsUsing(
        callback: fn () => new class implements GeneratesRedirects
        {
            public function generate(string $provider): \Illuminate\Http\RedirectResponse
            {
                ['provider' => $provider] = Route::current()->parameters();

                $scopes = ['*'];

                $scopes = match ($provider) {
                    'github' => array_merge($scopes, [
                        'repos.manage',
                    ]),
                    default => $scopes,
                };

                return Socialite::driver($provider)
                    ->scopes($scopes)
                    ->with(['response_type' => 'token', 'mobileminimal' => 1])
                    ->redirect();
            }
        }
    );

    $response = get('/oauth/github');

    $response->assertRedirect()
        ->assertRedirectContains('github.com')
        ->assertRedirectContains('mobileminimal=1')
        ->assertRedirectContains('response_type=token');

    if ($manageRepos) {
        $response->assertRedirectContains('repos.manage');
    }
})->with([
    'manage repos' => [true],
    'do not manage repos' => [false],
]);

test('users can register', function (): void {
    $user = (new SocialiteUser)
        ->map([
            'id' => $githubId = fake()->numerify('########'),
            'nickname' => 'joel',
            'name' => 'Joel',
            'email' => 'joel@socialite-ui.dev',
            'avatar' => null,
            'avatar_original' => null,
        ])
        ->setToken('user-token')
        ->setRefreshToken('refresh-token')
        ->setExpiresIn(3600);

    $provider = Mockery::mock(GithubProvider::class);
    $provider->shouldReceive('user')->andReturn($user);

    Socialite::shouldReceive('driver')->with('github')->andReturn($provider);

    Session::put('socialite-ui.previous_url', route('register'));

    $response = get('/oauth/github/callback');

    $response->assertRedirect('/dashboard');

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', ['email' => 'joel@socialite-ui.dev']);
    $this->assertDatabaseHas('social_accounts', [
        'provider' => 'github',
        'provider_id' => $githubId,
        'email' => 'joel@socialite-ui.dev',
    ]);
});

test('existing users can login', function (): void {
    /** @var User $user */
    $user = User::query()->create([
        'name' => 'Joel Butcher',
        'email' => 'joel@socialite-ui.dev',
        'password' => Hash::make('password'),
    ]);

    $user->socialAccounts()->create([
        'provider' => 'github',
        'provider_id' => $githubId = fake()->numerify('########'),
        'email' => 'joel@socialite-ui.dev',
        'token' => Str::random(64),
    ]);

    $this->assertDatabaseHas('users', ['email' => 'joel@socialite-ui.dev']);
    $this->assertDatabaseHas('social_accounts', [
        'provider' => 'github',
        'provider_id' => $githubId,
        'email' => 'joel@socialite-ui.dev',
    ]);

    $user = (new SocialiteUser)
        ->map([
            'id' => $githubId,
            'nickname' => 'joel',
            'name' => 'Joel',
            'email' => 'joel@socialite-ui.dev',
            'avatar' => null,
            'avatar_original' => null,
        ])
        ->setToken('user-token')
        ->setRefreshToken('refresh-token')
        ->setExpiresIn(3600);

    $provider = Mockery::mock(GithubProvider::class);
    $provider->shouldReceive('user')->andReturn($user);

    Socialite::shouldReceive('driver')->with('github')->andReturn($provider);

    Session::put('socialite-ui.previous_url', '/login');

    get('/oauth/github/callback')
        ->assertRedirect('/dashboard');

    $this->assertAuthenticated();
});

test('users can be authenticated with the same provider if they change the email associated with their user', function () {
    /** @var User $user */
    $user = User::query()->create([
        'name' => 'Joel Butcher',
        'email' => 'joel@socialite-ui.dev',
        'password' => Hash::make('password'),
    ]);

    $user->socialAccounts()->create([
        'provider' => 'github',
        'provider_id' => $githubId = fake()->numerify('########'),
        'name' => 'Joel',
        'email' => 'joel@socialite-ui.dev',
        'token' => Str::random(64),
    ]);

    $user = (new SocialiteUser)
        ->map([
            'id' => $githubId,
            'nickname' => 'joel',
            'name' => 'Joel',
            'email' => 'joel@socialite-ui.dev',
            'avatar' => null,
            'avatar_original' => null,
        ])
        ->setToken('user-token')
        ->setRefreshToken('refresh-token')
        ->setExpiresIn(3600);

    $provider = Mockery::mock(GithubProvider::class);
    $provider->shouldReceive('user')->andReturn($user);

    Socialite::shouldReceive('driver')->with('github')->andReturn($provider);

    Session::put('socialite-ui.previous_url', '/login');

    get('/oauth/github/callback')
        ->assertRedirect('/dashboard');

    $this->assertAuthenticated();
});

it('can render the prompt page', function () {
    Config::set('socialite-ui.providers', ['github']);
    SocialiteUi::promptOAuthLinkUsing(fn (Provider $provider) => response('Confirm Your Github OAuth Request (Test)'));

    $this->actingAs(User::create([
        'name' => 'Joel Butcher',
        'email' => 'joel@socialite-ui.dev',
        'password' => Hash::make('password'),
    ]));

    get('/oauth/confirm?provider=github')
        ->assertOk()
        ->assertSee('Confirm Your Github OAuth Request (Test)');
});

it('denies an attempt to link an account', function () {
    $this->actingAs(User::create([
        'name' => 'Joel Butcher',
        'email' => 'joel@socialite-ui.dev',
        'password' => Hash::make('password'),
    ]));

    $user = (new SocialiteUser)
        ->map([
            'id' => fake()->numerify('########'),
            'nickname' => 'joel',
            'name' => 'Joel',
            'email' => 'joel@socialite-ui.dev',
            'avatar' => null,
            'avatar_original' => null,
        ])
        ->setToken('user-token')
        ->setRefreshToken('refresh-token')
        ->setExpiresIn(3600);

    Cache::shouldReceive('pull')->andReturn($user);

    post('/oauth/confirm', data: [
        'provider' => 'github',
        'result' => 'deny',
        'password' => 'password',
    ])
        ->assertRedirect('/linked-accounts')
        ->assertSessionHas('socialite-ui.error', 'Failed to link GitHub account. User denied the request.');
});

it('confirms an attempt to link an account', function () {
    $this->actingAs(User::create([
        'name' => 'Joel Butcher',
        'email' => 'joel@socialite-ui.dev',
        'password' => Hash::make('password'),
    ]));

    $user = (new SocialiteUser)
        ->map([
            'id' => $githubId = fake()->numerify('########'),
            'nickname' => 'joel',
            'name' => 'Joel',
            'email' => 'joel@socialite-ui.dev',
            'avatar' => null,
            'avatar_original' => null,
        ])
        ->setToken('user-token')
        ->setRefreshToken('refresh-token')
        ->setExpiresIn(3600);

    Cache::shouldReceive('pull')->andReturn($user);

    post('/oauth/confirm', data: [
        'provider' => 'github',
        'result' => 'confirm',
        'password' => 'password',
    ])
        ->assertRedirect('/linked-accounts')
        ->assertSessionHas('status', 'GitHub account linked.');

    $this->assertAuthenticated();
    $this->assertDatabaseHas('social_accounts', [
        'provider' => 'github',
        'provider_id' => $githubId,
        'email' => 'joel@socialite-ui.dev',
    ]);
});
