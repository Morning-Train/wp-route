<?php

use Brain\Monkey;
use Morningtrain\WP\Facades\Route;
use Morningtrain\WP\Route\Classes\Rewrite\Route as RouteInstance;
use Morningtrain\WP\Route\Route as Router;

beforeAll(function () {
    Monkey\setUp();
    Router::setup(); // This resets singletons
    Brain\Monkey\Functions\when('home_url')->justReturn(TESTS_ROUTE_HOMEURL);
});

afterAll(function () {
    Monkey\tearDown();
});

beforeEach(function () {
    Router::getContainer()->forgetInstance('rewrite-router');
    Route::clearResolvedInstances();
});

it('can use path variables in URL', function () {
    Route::get('path/{foo}', function () {
    })->name('path-with-id');
    expect(Route::route('path-with-id', ['foo' => '123']))->toEndWith('path/123');
});

it('set default position to top', function () {
    $route = Route::get('foo', function () {
    });
    expect($route->getPosition())->toBe('top');
});

it('can have a position', function () {
    Route::get('foo', function () {
    })->name('route-position')->position('bottom');
    expect(Route::getRouteByName('route-position')
        ->getPosition())->toBe('bottom');
});

it('can get route by path', function () {
    Route::get('route-path', function () {
    });
    expect(Route::getRouteByPathAndMethod('route-path', 'GET'))->toBeInstanceOf(RouteInstance::class);
});

it('returns null with get route on invalid path', function () {
    expect(Route::getRouteByPathAndMethod('some-route-that-does-not-exist', 'GET'))->toBeNull();
});

it('can find routes by path and method', function () {
    Route::put('route-method-path', function () {
    })->name('put-route');
    Route::get('route-method-path', function () {
    })->name('get-route');
    Route::post('route-method-path', function () {
    })->name('post-route');

    $route = Route::getRouteByPathAndMethod('route-method-path', 'GET');
    expect($route)->toBeInstanceOf(RouteInstance::class);
    expect($route->getName())->toBe('get-route');
});
