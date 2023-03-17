<?php

namespace Morningtrain\WP\Facades;

use Illuminate\Support\Facades\Facade;
use Morningtrain\WP\Route\Classes\Rewrite\Group;
use Morningtrain\WP\Route\Classes\Rewrite\Route as RewriteRoute;

/**
 * @method static RewriteRoute any(string $path, callable $callback) Add a route that accepts any request type
 * @method static RewriteRoute match(array $requestMethods, string $path, callable $callback) Add a route that accepts multiple request methods
 * @method static RewriteRoute get(string $path, string|callable $callback) Add a GET route
 * @method static RewriteRoute post(string $path, callable $callback) Add a POST route
 * @method static RewriteRoute put(string $path, callable $callback) Add a PUT route
 * @method static RewriteRoute patch(string $path, callable $callback) Add a PATCH route
 * @method static RewriteRoute delete(string $path, callable $callback) Add a DELETE route
 * @method static RewriteRoute options(string $path, callable $callback) Add a OPTIONS route
 * @method static Group middleware($middleware) Create a group with middleware
 * @method static Group prefix(string $prefix) Create a group with prefix
 * @method static Group group(\Closure $routes) Create a simple group
 * @method static string route(string $name, ?array $args = []) Get the URL of a route
 * @method static RewriteRoute|null current() Get the currently matched route. Only accessible after 'parse_request' action
 * @method static RewriteRoute|null getRouteByName(string $name) Get a route instance by its name
 * @method static bool exists(string $name) Check if a route exists by its name
 * @method static array getAllowedRequestMethods() Get a list of all allowed request methods
 * @method static null|RewriteRoute getRouteByPathAndMethod(string $path, string $requestMethod) Get a route by its path and a request method
 */
class Route extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'rewrite-router';
    }
}
