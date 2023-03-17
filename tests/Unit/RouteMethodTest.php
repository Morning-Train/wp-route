<?php

use Brain\Monkey;
use Morningtrain\WP\Facades\Route;
use Morningtrain\WP\Route\Classes\Rewrite\Route as RouteInstance;
use Morningtrain\WP\Route\Route as Router;

const TESTS_ROUTE_HOMEURL = 'http://testsite.local';

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

function _testRequestMethod($method)
{
    $route = Route::$method('/foo-' . $method, function () {
    });
    expect($route)->toBeInstanceOf(RouteInstance::class);
    expect($route->getRequestMethods())->toBe([$method]);
}

it('can make a route', function () {
    $route = Route::get('/foo', function () {
    });
    expect($route)->toBeInstanceOf(RouteInstance::class);
});

it('can make a "any" route', function () {
    $route = Route::any('/foo-any', function () {
    });
    expect($route)->toBeInstanceOf(RouteInstance::class);
    expect($route->getRequestMethods())->toBe(Route::getAllowedRequestMethods());
});

it('can make a GET route', function () {
    _testRequestMethod('GET');
});
it('can make a POST route', function () {
    _testRequestMethod('POST');
});
it('can make a PUT route', function () {
    _testRequestMethod('PUT');
});
it('can make a PATCH route', function () {
    _testRequestMethod('PATCH');
});
it('can make a DELETE route', function () {
    _testRequestMethod('DELETE');
});
it('can make a OPTIONS route', function () {
    _testRequestMethod('OPTIONS');
});

it('cannot make use an invalid request method', function () {
    $route = Route::match(['foo'], '/foo-foo', function () {
    });
    expect($route)->toBeNull();
});

it('can check if a named route exists', function () {
    Route::get('named-exists', function () {
    })->name('named-exists-route');
    expect(Route::exists('named-exists-route'))->toBeTrue();
});

it('can get a named route', function () {
    Route::get('named', function () {
    })->name('named-route');
    expect(Route::getRouteByName('named-route'))->toBeInstanceOf(RouteInstance::class);
});

it('returns null when a named route doesn\'t exist', function () {
    expect(Route::getRouteByName('some-route-that-does-not-exist'))->toBeNull();
});

it('return null as URL for unknown route', function () {
    expect(Route::route('some-route-that-does-not-exist', ['foo' => '123']))->toBeNull();
});

it('lets routes return their params', function () {
    $route = Route::get('path/{foo}', function () {
    });
    expect($route->getParams())->toBe(['foo']);
});

it('can call route callbacks', function () {
    $route = Route::get('path', function () {
        echo "hello";
    });
    ob_start();
    $route->call(new \Symfony\Component\HttpFoundation\Request());
    $obj = ob_get_clean();
    expect($obj)->toBe('hello');
});

it('can use invokable controllers', function () {
    class InvokableRouteController
    {
        public function __invoke()
        {
            echo "hello";
        }
    }

    $route = Route::get('foo', InvokableRouteController::class);
    ob_start();
    $route->call(new \Symfony\Component\HttpFoundation\Request());
    $obj = ob_get_clean();
    expect($obj)->toBe('hello');
});
