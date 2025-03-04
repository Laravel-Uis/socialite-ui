<?php

namespace SocialiteUi\Tests\Unit\Actions;

use App\Models\SocialAccount;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Session\SymfonySessionDecorator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Mockery\MockInterface;
use SocialiteUi\Actions\AuthenticateOAuthCallback;
use SocialiteUi\Actions\CreateUserFromProvider;
use SocialiteUi\Contracts\CreatesSocialAccounts;
use SocialiteUi\Contracts\CreatesUserFromProvider;
use SocialiteUi\Contracts\SocialAccountRepository;
use SocialiteUi\Contracts\UpdatesSocialAccounts;
use SocialiteUi\Contracts\UserRepository;
use SocialiteUi\Events\NewOAuthRegistration;
use SocialiteUi\Events\OAuthFailed;
use SocialiteUi\Events\OAuthLogin;
use SocialiteUi\Events\OAuthProviderLinked;
use SocialiteUi\Events\OAuthProviderLinkFailed;
use SocialiteUi\Features;
use SocialiteUi\Tests\Fixtures\User;

use function Pest\Laravel\actingAs;

describe('Authenticate OAuth Callback Test', fn () => [
    beforeEach(function () {
        Event::fake();
        Exceptions::fake();

        Route::get('login', fn () => 'Login')->name('login');
        Route::get('dashboard', fn () => 'Dashboard')->name('dashboard');
    }),
    test('can register a new user', function () {
        Route::get('register', fn () => 'Register')->name('register');
        Config::set('socialite-ui.features', []);

        $user = new User;

        $accountRepository = mock(SocialAccountRepository::class);
        $accountRepository->shouldReceive('find')->once()->andReturnNull();

        $userRepository = mock(UserRepository::class);
        $userRepository->shouldReceive('find')->once()->andReturnNull();
        $userRepository->shouldReceive('create')->once()->andReturn($user);

        $socialAccountCreator = mock(CreatesSocialAccounts::class);
        $socialAccountCreator->shouldReceive('create')->once()->andReturn(mock(SocialAccount::class));

        $guard = mock(StatefulGuard::class);
        $guard->shouldReceive('login')->once()->with($user, false);

        $action = new AuthenticateOAuthCallback(
            $guard,
            $userRepository,
            $accountRepository,
            new CreateUserFromProvider($socialAccountCreator, $userRepository),
            $socialAccountCreator,
            mock(UpdatesSocialAccounts::class),
        );

        /** @var SocialiteUser&MockInterface $socialiteUser */
        $socialiteUser = mock(SocialiteUser::class, function (SocialiteUser&MockInterface $mock) {
            $mock->shouldReceive('getId')->once()->andReturn('123');
            $mock->shouldReceive('getEmail')->once()->andReturn('joel@socialite-ui.dev');
        });

        $session = Session::driver('array');
        $session->put('socialite-ui.previous_url', route('register'));

        $request = Request::create('/');
        $request->setSession(new SymfonySessionDecorator($session));

        $response = $action->authenticate($request, 'github', $socialiteUser);

        Event::assertDispatched(NewOAuthRegistration::class);

        expect($response)->toBeInstanceOf(RedirectResponse::class)
            ->and($response->getTargetUrl())->toBe('http://localhost/dashboard');
    }),
    test('can login an existing user', function () {
        Config::set('socialite-ui.features', []);

        $user = new User;

        $socialiteUser = mock(SocialiteUser::class);
        $socialAccount = new SocialAccount;

        $socialiteUser->shouldReceive('getId')->once()->andReturn(1);
        $socialiteUser->shouldReceive('getEmail')->once()->andReturn('joel@socialite-ui.dev');

        $accountRepository = mock(SocialAccountRepository::class);
        $accountRepository->shouldReceive('find')->once()->andReturn($socialAccount);

        $userRepository = mock(UserRepository::class);
        $userRepository->shouldReceive('find')->once()->andReturn($user);

        $socialAccountUpdater = mock(UpdatesSocialAccounts::class);
        $socialAccountUpdater->shouldReceive('update')->once()->andReturn($socialAccount);

        $guard = mock(StatefulGuard::class);
        $guard->shouldReceive('loginUsingId')->once()->with($user->getAuthIdentifier(), false)->andReturn(true);

        $action = new AuthenticateOAuthCallback(
            $guard,
            $userRepository,
            $accountRepository,
            mock(CreatesUserFromProvider::class),
            mock(CreatesSocialAccounts::class),
            $socialAccountUpdater,
        );

        $request = Request::create('/');
        $request->setSession(new SymfonySessionDecorator(Session::driver('array')));

        $response = $action->authenticate($request, 'github', $socialiteUser);

        Event::assertDispatched(OAuthLogin::class);

        expect($response)->toBeInstanceOf(RedirectResponse::class)
            ->and($response->getTargetUrl())->toBe('http://localhost/dashboard');
    }),
    test('can redirect to login if the user cannot register', function () {
        Config::set('socialite-ui.features', []);

        $accountRepository = mock(SocialAccountRepository::class);
        $accountRepository->shouldReceive('find')->once()->andReturnNull();

        $userRepository = mock(UserRepository::class);
        $userRepository->shouldReceive('find')->once()->andReturnNull();

        $action = new AuthenticateOAuthCallback(
            mock(StatefulGuard::class),
            $userRepository,
            $accountRepository,
            mock(CreatesUserFromProvider::class),
            mock(CreatesSocialAccounts::class),
            mock(UpdatesSocialAccounts::class),
        );

        /** @var SocialiteUser&MockInterface $socialiteUser */
        $socialiteUser = mock(SocialiteUser::class, function (SocialiteUser&MockInterface $mock) {
            $mock->shouldReceive('getId')->once()->andReturn('123');
            $mock->shouldReceive('getEmail')->once()->andReturn('joel@socialite-ui.dev');
        });

        $request = Request::create('/');
        $request->setSession(new SymfonySessionDecorator(Session::driver('array')));

        $response = $action->authenticate($request, 'github', $socialiteUser);

        expect($response)->toBeInstanceOf(RedirectResponse::class)
            ->and($response->getTargetUrl())->toBe('http://localhost/login')
            ->and($response->getSession()->get('socialite-ui.error'))->toBe('These credentials do not match our records.');
    }),
    test('cannot login if the user exists, but the social account does not', function () {
        $user = new User;
        Route::get('login', fn () => 'Login')->name('login');

        $accountRepository = mock(SocialAccountRepository::class);
        $accountRepository->shouldReceive('find')->once()->andReturnNull();

        $userRepository = mock(UserRepository::class);
        $userRepository->shouldReceive('find')->once()->andReturn($user);

        $action = new AuthenticateOAuthCallback(
            mock(StatefulGuard::class),
            $userRepository,
            $accountRepository,
            mock(CreatesUserFromProvider::class),
            mock(CreatesSocialAccounts::class),
            mock(UpdatesSocialAccounts::class),
        );

        /** @var SocialiteUser&MockInterface $socialiteUser */
        $socialiteUser = mock(SocialiteUser::class, function (SocialiteUser&MockInterface $mock) {
            $mock->shouldReceive('getId')->once()->andReturn('123');
            $mock->shouldReceive('getEmail')->once()->andReturn('joel@socialite-ui.dev');
        });

        $request = Request::create('/');
        $request->setSession(new SymfonySessionDecorator(Session::driver('array')));

        $response = $action->authenticate($request, 'github', $socialiteUser);

        Event::assertDispatched(OAuthFailed::class);

        expect($response)->toBeInstanceOf(RedirectResponse::class)
            ->and($response->getTargetUrl())->toBe('http://localhost/login')
            ->and($response->getSession()->get('socialite-ui.error'))->toBe('These credentials do not match our records.');
    }),
    test('can login and link a new account to an existing user', function () {
        $user = new class implements Authenticatable
        {
            public function getAuthIdentifierName(): string
            {
                return 'id';
            }

            public function getAuthIdentifier(): int
            {
                return 1;
            }

            public function getAuthPasswordName(): string
            {
                return 'password';
            }

            public function getAuthPassword(): string
            {
                return 'password';
            }

            public function getRememberToken(): null
            {
                return null;
            }

            public function setRememberToken($value): null
            {
                return null;
            }

            public function getRememberTokenName(): string
            {
                return 'remember_token';
            }
        };

        Route::get('dashboard', fn () => 'Dashboard')->name('dashboard');
        Config::set('socialite-ui.features', [
            Features::authExistingUnlinkedUsers(),
        ]);

        $accountRepository = mock(SocialAccountRepository::class);
        $accountRepository->shouldReceive('find')->once()->andReturnNull();

        $userRepository = mock(UserRepository::class);
        $userRepository->shouldReceive('find')->once()->andReturn($user);

        $socialAccountCreator = mock(CreatesSocialAccounts::class);
        $socialAccountCreator->shouldReceive('create')->once()->andReturn(mock(SocialAccount::class));

        $socialAccountUpdater = mock(UpdatesSocialAccounts::class);
        $socialAccountUpdater->shouldReceive('update')->once()->andReturn(mock(SocialAccount::class));

        $guard = mock(StatefulGuard::class);
        $guard->shouldReceive('loginUsingId')->once()->with(1, false)->andReturnTrue();

        $action = new AuthenticateOAuthCallback(
            $guard,
            $userRepository,
            $accountRepository,
            mock(CreatesUserFromProvider::class),
            $socialAccountCreator,
            $socialAccountUpdater,
        );

        /** @var SocialiteUser&MockInterface $socialiteUser */
        $socialiteUser = mock(SocialiteUser::class, function (SocialiteUser&MockInterface $mock) {
            $mock->shouldReceive('getId')->once()->andReturn('123');
            $mock->shouldReceive('getEmail')->once()->andReturn('joel@socialite-ui.dev');
        });

        $request = Request::create('/');
        $request->setSession(new SymfonySessionDecorator(Session::driver('array')));

        $response = $action->authenticate($request, 'github', $socialiteUser);

        Event::assertDispatched(OAuthLogin::class);

        expect($response)->toBeInstanceOf(RedirectResponse::class)
            ->and($response->getTargetUrl())->toBe('http://localhost/dashboard');
    }),
    test('can redirect to the confirmation prompt screen when authenticated', function () {
        $socialiteUser = mock(SocialiteUser::class);
        actingAs($user = new class implements Authenticatable
        {
            public function getAuthIdentifierName(): string
            {
                return 'id';
            }

            public function getAuthIdentifier(): int
            {
                return 1;
            }

            public function getAuthPasswordName(): string
            {
                return 'password';
            }

            public function getAuthPassword(): string
            {
                return 'password';
            }

            public function getRememberToken(): null
            {
                return null;
            }

            public function setRememberToken($value): null
            {
                return null;
            }

            public function getRememberTokenName(): string
            {
                return 'remember_token';
            }
        });

        Route::get('confirm', fn () => 'Confirm')->name('oauth.confirm.show');

        $action = new AuthenticateOAuthCallback(
            mock(StatefulGuard::class),
            mock(UserRepository::class),
            mock(SocialAccountRepository::class),
            mock(CreatesUserFromProvider::class),
            mock(CreatesSocialAccounts::class),
            mock(UpdatesSocialAccounts::class),
        );

        $request = Request::create('/');
        $request->setUserResolver(fn () => $user);
        $request->setSession(new SymfonySessionDecorator(Session::driver('array')));

        Cache::shouldReceive('put')->once();

        $response = $action->authenticate($request, 'github', $socialiteUser);

        expect($response)->toBeInstanceOf(RedirectResponse::class)
            ->and($response->getTargetUrl())->toBe('http://localhost/confirm?provider=github');
    }),
    test('can handle a query exception', function () {
        /** @var SocialiteUser&MockInterface $socialiteUser */
        $socialiteUser = mock(SocialiteUser::class, function (SocialiteUser&MockInterface $mock) {
            $mock->shouldReceive('getId')->once()->andReturn('123');
        });

        $accountRepository = mock(SocialAccountRepository::class);
        $accountRepository->shouldReceive('find')->once()->andThrow(new QueryException('mysql', '', [], new \Exception));

        $action = new AuthenticateOAuthCallback(
            mock(StatefulGuard::class),
            mock(UserRepository::class),
            $accountRepository,
            mock(CreatesUserFromProvider::class),
            mock(CreatesSocialAccounts::class),
            mock(UpdatesSocialAccounts::class),
        );

        $request = Request::create('/');
        $request->setSession(new SymfonySessionDecorator(Session::driver('array')));

        $response = $action->authenticate($request, 'github', $socialiteUser);

        Event::assertDispatched(OAuthFailed::class);

        expect($response)->toBeInstanceOf(RedirectResponse::class)
            ->and($response->getTargetUrl())->toBe('http://localhost/login')
            ->and($response->getSession()->get('socialite-ui.error'))->toBe('Oops! Something went wrong.');
    }),
    test('can report a domain exception if a Social Account is found without a user', function () {
        $socialiteUser = mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->once()->andReturn(1);
        $socialiteUser->shouldReceive('getEmail')->once()->andReturn('joel@socialite-ui.dev');

        $userRepository = mock(UserRepository::class);
        $userRepository->shouldReceive('find')->once()->andReturnNull();

        $accountRepository = mock(SocialAccountRepository::class);
        $accountRepository->shouldReceive('find')->once()->andReturn(new SocialAccount);

        $action = new AuthenticateOAuthCallback(
            mock(StatefulGuard::class),
            $userRepository,
            $accountRepository,
            mock(CreatesUserFromProvider::class),
            mock(CreatesSocialAccounts::class),
            mock(UpdatesSocialAccounts::class),
        );

        $request = Request::create('/');
        $request->setSession(new SymfonySessionDecorator(Session::driver('array')));

        $response = $action->authenticate($request, 'github', $socialiteUser);

        Event::assertDispatched(OAuthFailed::class);
        Exceptions::assertReported(fn (\DomainException $e): bool => $e->getMessage() === 'Could not retrieve user information.');

        expect($response)->toBeInstanceOf(RedirectResponse::class)
            ->and($response->getTargetUrl())->toBe('http://localhost/login')
            ->and($response->getSession()->get('socialite-ui.error'))->toBe('These credentials do not match our records.');
    }),
    test('can redirect with an error if the user denies a request', function () {
        Route::get('linked-accounts', fn () => 'Linked Accounts')->name('linked-accounts');

        $action = new AuthenticateOAuthCallback(
            mock(StatefulGuard::class),
            mock(UserRepository::class),
            mock(SocialAccountRepository::class),
            mock(CreatesUserFromProvider::class),
            mock(CreatesSocialAccounts::class),
            mock(UpdatesSocialAccounts::class),
        );

        Cache::shouldReceive('pull')->once()->andReturn(mock(SocialiteUser::class));

        $request = Request::create('/', parameters: ['provider' => 'github', 'result' => 'deny']);
        $request->setUserResolver(fn () => new User);

        $response = $action->link($request);

        Event::assertDispatched(OAuthProviderLinkFailed::class);

        expect($response)->toBeInstanceOf(RedirectResponse::class)
            ->and($response->getTargetUrl())->toBe('http://localhost/linked-accounts')
            ->and($response->getSession()->get('socialite-ui.error'))->toBe('Failed to link GitHub account. User denied the request.');
    }),
    test('can report a domain exception if the socialite user is not returned from the cache', function () {
        Route::get('linked-accounts', fn () => 'Linked Accounts')->name('linked-accounts');

        $action = new AuthenticateOAuthCallback(
            mock(StatefulGuard::class),
            mock(UserRepository::class),
            mock(SocialAccountRepository::class),
            mock(CreatesUserFromProvider::class),
            mock(CreatesSocialAccounts::class),
            mock(UpdatesSocialAccounts::class),
        );

        Cache::shouldReceive('pull')->once()->andReturnNull();

        $request = Request::create('/', parameters: ['provider' => 'github']);
        $request->setUserResolver(fn () => new User);

        $response = $action->link($request);

        Event::assertDispatched(OAuthProviderLinkFailed::class);
        Exceptions::assertReported(fn (\DomainException $e) => $e->getMessage() === 'Could not retrieve social provider information.');

        expect($response)->toBeInstanceOf(RedirectResponse::class)
            ->and($response->getTargetUrl())->toBe('http://localhost/linked-accounts')
            ->and($response->getSession()->get('socialite-ui.error'))->toBe('Failed to link GitHub account. Please try again.');
    }),
    test('can redirect if the user attempts to link a provider that is registered to a different user', function () {
        $user = mock(User::class);
        $user->shouldReceive('getAuthIdentifier')->twice()->andReturn(1);

        $socialAccount = mock(SocialAccount::class);
        $socialAccount->shouldReceive('getUserId')->once()->andReturn(2);

        Route::get('linked-accounts', fn () => 'Linked Accounts')->name('linked-accounts');

        $socialiteUser = mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->once()->andReturn(1);

        $socialAccountRepository = mock(SocialAccountRepository::class);
        $socialAccountRepository->shouldReceive('find')->once()->andReturn($socialAccount);

        $action = new AuthenticateOAuthCallback(
            mock(StatefulGuard::class),
            mock(UserRepository::class),
            $socialAccountRepository,
            mock(CreatesUserFromProvider::class),
            mock(CreatesSocialAccounts::class),
            mock(UpdatesSocialAccounts::class),
        );

        Cache::shouldReceive('pull')->once()->andReturn($socialiteUser);

        $request = Request::create('/', parameters: ['provider' => 'github']);
        $request->setUserResolver(fn () => $user);

        $response = $action->link($request);

        Event::assertDispatched(OAuthProviderLinkFailed::class);

        expect($response)->toBeInstanceOf(RedirectResponse::class)
            ->and($response->getTargetUrl())->toBe('http://localhost/linked-accounts')
            ->and($response->getSession()->get('socialite-ui.error'))->toBe('Could not link your GitHub account. User does not match.');
    }),
    test('can create a link an account for an authenticated user', function () {
        $user = mock(User::class);
        $user->shouldReceive('getAuthIdentifier')->once()->andReturn(1);

        Route::get('linked-accounts', fn () => 'Linked Accounts')->name('linked-accounts');

        $socialiteUser = mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->once()->andReturn(1);

        $socialAccountRepository = mock(SocialAccountRepository::class);
        $socialAccountRepository->shouldReceive('find')->once()->andReturnNull();

        $socialAccountCreator = mock(CreatesSocialAccounts::class);
        $socialAccountCreator->shouldReceive('create')->once();

        $action = new AuthenticateOAuthCallback(
            mock(StatefulGuard::class),
            mock(UserRepository::class),
            $socialAccountRepository,
            mock(CreatesUserFromProvider::class),
            $socialAccountCreator,
            mock(UpdatesSocialAccounts::class),
        );

        Cache::shouldReceive('pull')->once()->andReturn($socialiteUser);

        $request = Request::create('/', parameters: ['provider' => 'github']);
        $request->setUserResolver(fn () => $user);

        $response = $action->link($request);

        Event::assertDispatched(OAuthProviderLinked::class);

        expect($response)->toBeInstanceOf(RedirectResponse::class)
            ->and($response->getTargetUrl())->toBe('http://localhost/linked-accounts')
            ->and($response->getSession()->get('status'))->toBe('GitHub account linked.');
    }),
    test('can register a user if they came from the login page', function () {
        Route::get('login', fn () => 'Login')->name('login');
        Config::set('socialite-ui.features', [Features::createAccountOnFirstLogin()]);

        $user = new User;

        $accountRepository = mock(SocialAccountRepository::class);
        $accountRepository->shouldReceive('find')->once()->andReturnNull();

        $userRepository = mock(UserRepository::class);
        $userRepository->shouldReceive('find')->once()->andReturnNull();
        $userRepository->shouldReceive('create')->once()->andReturn($user);

        $socialAccountCreator = mock(CreatesSocialAccounts::class);
        $socialAccountCreator->shouldReceive('create')->once()->andReturn(mock(SocialAccount::class));

        $guard = mock(StatefulGuard::class);
        $guard->shouldReceive('login')->once()->with($user, false);

        $action = new AuthenticateOAuthCallback(
            $guard,
            $userRepository,
            $accountRepository,
            new CreateUserFromProvider($socialAccountCreator, $userRepository),
            $socialAccountCreator,
            mock(UpdatesSocialAccounts::class),
        );

        /** @var SocialiteUser&MockInterface $socialiteUser */
        $socialiteUser = mock(SocialiteUser::class, function (SocialiteUser&MockInterface $mock) {
            $mock->shouldReceive('getId')->once()->andReturn('123');
            $mock->shouldReceive('getEmail')->once()->andReturn('joel@socialite-ui.dev');
        });

        $session = Session::driver('array');
        $session->put('socialite-ui.previous_url', route('login'));

        $request = Request::create('/');
        $request->setSession(new SymfonySessionDecorator($session));

        $response = $action->authenticate($request, 'github', $socialiteUser);

        Event::assertDispatched(NewOAuthRegistration::class);

        expect($response)->toBeInstanceOf(RedirectResponse::class)
            ->and($response->getTargetUrl())->toBe('http://localhost/dashboard');
    }),
]);
