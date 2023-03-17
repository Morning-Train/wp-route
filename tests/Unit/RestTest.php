<?php

use Brain\Monkey;
use Morningtrain\WP\Facades\Rest;
use Morningtrain\WP\Route\Classes\Rest\Route as RouteInstance;
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
    Router::getContainer()->forgetInstance('rest-router');
    Rest::clearResolvedInstances();
});

it('can be constructed', function () {
    $route = Rest::get('foo', function () {
    });
    expect($route)->toBeInstanceOf(RouteInstance::class);
});
