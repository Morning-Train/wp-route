<?php

use Brain\Monkey;
use Morningtrain\WP\Facades\Route;
use Morningtrain\WP\Route\Classes\Rewrite\Route as RouteInstance;
use Morningtrain\WP\Route\Route as Router;
use Symfony\Component\HttpFoundation\Request;

const TESTS_ROUTE_HOMEURL_GROUP = 'http://testsite.local';

beforeAll(function () {
    Monkey\setUp();
    Router::setup(); // This resets singletons
    Brain\Monkey\Functions\when('home_url')->justReturn(TESTS_ROUTE_HOMEURL_GROUP);
});

afterAll(function () {
    Monkey\tearDown();
});

beforeEach(function () {
    Router::getContainer()->forgetInstance('rewrite-router');
    Route::clearResolvedInstances();
});

it('can group two routes', function () {
    Route::group(function () {
        Route::get('foo', function () {
        })->name('foo');
        Route::get('bar', function () {
        })->name('bar');
    });
    expect(Route::getRouteByName('foo'))->toBeInstanceOf(RouteInstance::class);
    expect(Route::getRouteByName('bar'))->toBeInstanceOf(RouteInstance::class);
});

it('can prefix two routes', function () {
    Route::prefix('baz')->group(function () {
        Route::get('foo', function () {
        })->name('foo');
        Route::get('bar', function () {
        })->name('bar');
    });

    expect(Route::getRouteByName('foo'))->toBeInstanceOf(RouteInstance::class);
    expect(Route::getRouteByName('bar'))->toBeInstanceOf(RouteInstance::class);
    expect(Route::route('foo'))->toBe(TESTS_ROUTE_HOMEURL_GROUP . '/baz/foo');
    expect(Route::route('bar'))->toBe(TESTS_ROUTE_HOMEURL_GROUP . '/baz/bar');
});

it('can middleware two routes', function () {
    function mw(Request $request, $next)
    {
        return $next($request);
    }

    Route::middleware('mw')->group(function () {
        Route::get('foo', function () {
        })->name('foo');
        Route::get('bar', function () {
        })->name('bar');
    });
    expect(Route::getRouteByName('foo'))->toBeInstanceOf(RouteInstance::class);
    expect(Route::getRouteByName('bar'))->toBeInstanceOf(RouteInstance::class);
});
