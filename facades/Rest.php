<?php

namespace Morningtrain\WP\Facades;

use Illuminate\Support\Facades\Facade;
use Morningtrain\WP\Route\Abstracts\AbstractGroup;
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
 * @method static AbstractGroup middleware($middleware) Create a group with middleware
 * @method static AbstractGroup prefix(string $prefix) Create a group with prefix
 * @method static AbstractGroup group(\Closure $routes) Create a simple group
 * @method static string route(string $name, ?array $args = []) Get the URL of a route
 * @method static AbstractRoute|null getRouteByName(string $name) Get a route instance by its name
 * @method static bool exists(string $name) Check if a route exists by its name
 * @method static array getAllowedRequestMethods() Get a list of all allowed request methods
 */
class Rest extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'rest-router';
    }
}
