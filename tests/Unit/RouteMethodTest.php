<?php

use Brain\Monkey;
const TESTS_ROUTE_HOMEURL = 'http://testsite.local';

beforeAll(function () {
    Monkey\setUp();
    Brain\Monkey\Functions\when('home_url')->justReturn(TESTS_ROUTE_HOMEURL);
});

afterAll(function () {
    Monkey\tearDown();
});

beforeEach(function(){
    \Morningtrain\WP\Route\Classes\RouteService::__forgetAllRoutes();
});

function _testRequestMethod($method)
{
    $route = \Morningtrain\WP\Route\Route::$method('/foo-' . $method, function () {
    });
    expect($route)->toBeInstanceOf(\Morningtrain\WP\Route\Classes\Route::class);
    expect($route->getRequestMethods())->toBe([$method]);
}

it('can make a route', function () {
    $route = \Morningtrain\WP\Route\Route::get('/foo', function () {
    });
    expect($route)->toBeInstanceOf(\Morningtrain\WP\Route\Classes\Route::class);
});

it('can make a "any" route', function () {
    $route = \Morningtrain\WP\Route\Route::any('/foo-any', function () {
    });
    expect($route)->toBeInstanceOf(\Morningtrain\WP\Route\Classes\Route::class);
    expect($route->getRequestMethods())->toBe([]);
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
    $route = \Morningtrain\WP\Route\Route::match(['foo'], '/foo-foo', function () {
    });
    expect($route)->toBeNull();
});

it('can check if a named route exists', function () {
    \Morningtrain\WP\Route\Route::get('named-exists', function () {
    })->name('named-exists-route');
    expect(\Morningtrain\WP\Route\Route::exists('named-exists-route'))->toBeTrue();
});

it('can get a named route', function () {
    \Morningtrain\WP\Route\Route::get('named', function () {
    })->name('named-route');
    expect(\Morningtrain\WP\Route\Classes\RouteService::getRouteByName('named-route'))->toBeInstanceOf(\Morningtrain\WP\Route\Classes\Route::class);
});

it('returns null when a named route doesn\'t exist', function () {
    expect(\Morningtrain\WP\Route\Classes\RouteService::getRouteByName('some-route-that-does-not-exist'))->toBeNull();
});

it('can use path variables in URL', function () {
    \Morningtrain\WP\Route\Route::get('path/{foo}',function(){})->name('path-with-id');
    expect(\Morningtrain\WP\Route\Route::route('path-with-id',['foo' => '123']))->toEndWith('path/123');
});

it('return null as URL for unknown route', function () {
    expect(\Morningtrain\WP\Route\Route::route('some-route-that-does-not-exist',['foo' => '123']))->toBeNull();
});

it('lets routes return their params', function () {
    $route = \Morningtrain\WP\Route\Route::get('path/{foo}',function(){});
    expect($route->getParams())->toBe(['foo']);
});

it('can call route callbacks', function () {
    $route = \Morningtrain\WP\Route\Route::get('path}',function(){echo "hello";});
    ob_start();
    $route->call();
    $obj = ob_get_clean();
    expect($obj)->toBe('hello');
});

it('set default position to top', function () {
    $route = \Morningtrain\WP\Route\Route::get('foo',function(){});
    expect($route->getPosition())->toBe('top');
});

it('can have a position', function () {
    \Morningtrain\WP\Route\Route::get('foo',function(){})->name('route-position')->position('bottom');
    expect(\Morningtrain\WP\Route\Classes\RouteService::getRouteByName('route-position')->getPosition())->toBe('bottom');
});

it('can use invokable controllers', function () {
    class InvokableRouteController{
        public function __invoke()
        {
            echo "hello";
        }
    }
    $route = \Morningtrain\WP\Route\Route::get('foo', InvokableRouteController::class);
    ob_start();
    $route->call();
    $obj = ob_get_clean();
    expect($obj)->toBe('hello');
});

it('can get route by path', function () {
    \Morningtrain\WP\Route\Route::get('route-path',function(){});
    expect(\Morningtrain\WP\Route\Classes\RouteService::getRoute('route-path'))->toBeInstanceOf(\Morningtrain\WP\Route\Classes\Route::class);
});

it('returns null with get route on invalid path', function () {
    expect(\Morningtrain\WP\Route\Classes\RouteService::getRoute('some-route-that-does-not-exist'))->toBeNull();
});

it('can find routes by path and method', function () {
    \Morningtrain\WP\Route\Route::put('route-method-path',function(){})->name('put-route');
    \Morningtrain\WP\Route\Route::get('route-method-path',function(){})->name('get-route');
    \Morningtrain\WP\Route\Route::post('route-method-path',function(){})->name('post-route');

    $route = \Morningtrain\WP\Route\Classes\RouteService::getRoute('route-method-path','GET');
    expect($route)->toBeInstanceOf(\Morningtrain\WP\Route\Classes\Route::class);
    expect($route->getName())->toBe('get-route');
});

it('returns default param regex on unrecognized param', function () {
    $route = \Morningtrain\WP\Route\Route::get('route-path',function(){});
    expect($route->getParamRegex('foo'))->toBe('([^/]+)');
});
