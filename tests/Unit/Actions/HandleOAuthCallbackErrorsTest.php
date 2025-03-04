<?php

namespace SocialiteUi\Tests\Unit\Actions;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Session\SymfonySessionDecorator;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use SocialiteUi\Actions\HandleOAuthCallbackErrors;
use SocialiteUi\Tests\Fixtures\User;

describe('Handle OAuth Callback Error', fn () => [
    test('does not handle if no error is set on the request', function () {
        expect((new HandleOAuthCallbackErrors)->handle(Request::create('/test')))->toBeNull();
    }),
    test('redirects to login with "error_description" if not authenticated', function () {
        Route::get('login', fn () => 'login')->name('login');

        $request = Request::create('/test', 'GET', ['error' => 'error']);
        $request->setSession(new SymfonySessionDecorator(Session::driver('array')));

        $response = (new HandleOAuthCallbackErrors)->handle($request);
        expect($response)->toBeInstanceOf(RedirectResponse::class)
            ->and($response->getTargetUrl())->toBe('http://localhost/login')
            ->and($request->session()->get('socialite-ui.error'))->toBe('error');
    }),
    test('redirects to login with "error" if not authenticated', function () {
        Route::get('login', fn () => 'login')->name('login');

        $request = Request::create('/test', 'GET', ['error' => 'error']);
        $request->setSession(new SymfonySessionDecorator(Session::driver('array')));

        $response = (new HandleOAuthCallbackErrors)->handle($request);
        expect($response)->toBeInstanceOf(RedirectResponse::class)
            ->and($response->getTargetUrl())->toBe('http://localhost/login')
            ->and($request->session()->get('socialite-ui.error'))->toBe('error');
    }),
    test('redirects to linked-accounts with "error_description" if not authenticated', function () {
        Route::get('linked-accounts', fn () => 'linked-accounts')->name('linked-accounts');

        $request = Request::create('/test', 'GET', ['error' => 'error']);
        $request->setUserResolver(fn () => new User);
        $request->setSession(new SymfonySessionDecorator(Session::driver('array')));

        $response = (new HandleOAuthCallbackErrors)->handle($request);
        expect($response)->toBeInstanceOf(RedirectResponse::class)
            ->and($response->getTargetUrl())->toBe('http://localhost/linked-accounts')
            ->and($request->session()->get('socialite-ui.error'))->toBe('error');
    }),
    test('redirects to linked-accounts with "error" if not authenticated', function () {
        Route::get('linked-accounts', fn () => 'linked-accounts')->name('linked-accounts');

        $request = Request::create('/test', 'GET', ['error' => 'error']);
        $request->setUserResolver(fn () => new User);
        $request->setSession(new SymfonySessionDecorator(Session::driver('array')));

        $response = (new HandleOAuthCallbackErrors)->handle($request);
        expect($response)->toBeInstanceOf(RedirectResponse::class)
            ->and($response->getTargetUrl())->toBe('http://localhost/linked-accounts')
            ->and($request->session()->get('socialite-ui.error'))->toBe('error');
    }),
]);
