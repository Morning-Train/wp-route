<?php

use Brain\Monkey;

beforeAll(function () {
    Monkey\setUp();
});

afterAll(function () {
    Monkey\tearDown();
});

it('can make a route', function () {
    \Morningtrain\WP\Route\Route::get('/foo', function () {
        echo 'Foo';
    })->name('foo');
    expect(\Morningtrain\WP\Route\Route::exists('foo'))->toBeTrue();
});
