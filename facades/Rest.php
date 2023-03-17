<?php

namespace Morningtrain\WP\Facades;

use Illuminate\Support\Facades\Facade;
use Morningtrain\WP\Route\Classes\Rest\Group;
use Morningtrain\WP\Route\Classes\Rest\Route as RestRoute;

/**
 * @method static RestRoute any(string $path, callable $callback) Add a route that accepts any request type
 * @method static RestRoute match(array $requestMethods, string $path, callable $callback) Add a route that accepts multiple request methods
 * @method static RestRoute get(string $path, callable $callback) Add a GET route
 * @method static RestRoute post(string $path, callable $callback) Add a POST route
 * @method static RestRoute put(string $path, callable $callback) Add a PUT route
 * @method static RestRoute patch(string $path, callable $callback) Add a PATCH route
 * @method static RestRoute delete(string $path, callable $callback) Add a DELETE route
 * @method static RestRoute options(string $path, callable $callback) Add a OPTIONS route
 * @method static Group middleware($middleware) Create a group with middleware
 * @method static Group prefix(string $prefix) Create a group with prefix
 * @method static Group namespace(string $namespace) Add a namespace to the group
 * @method static Group group(\Closure $routes) Create a simple group
 * @method static string route(string $name, ?array $args = []) Get the URL of a route
 * @method static RestRoute|null getRouteByName(string $name) Get a route instance by its name
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
