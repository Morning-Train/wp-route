<?php

namespace Morningtrain\WP\Facades;

use Illuminate\Support\Facades\Facade;
use Morningtrain\WP\Route\Abstracts\AbstractRoute;

/**
 * @method static AbstractRoute any(string $path, callable $callback) Add a route that accepts any request type
 * @method static AbstractRoute match(array $requestMethods, string $path, callable $callback) Add a route that accepts multiple request methods
 * @method static AbstractRoute get(string $path, callable $callback) Add a GET route
 * @method static AbstractRoute post(string $path, callable $callback) Add a POST route
 * @method static AbstractRoute put(string $path, callable $callback) Add a PUT route
 * @method static AbstractRoute patch(string $path, callable $callback) Add a PATCH route
 * @method static AbstractRoute delete(string $path, callable $callback) Add a DELETE route
 * @method static AbstractRoute options(string $path, callable $callback) Add a OPTIONS route
 * @method static string route(string $name, ?array $args) Get the URL of a route
 * @method static AbstractRoute|null current() Get the currently matched route. Only accessible after 'parse_request' action
 */
class Route extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'rewrite-router';
    }
}
