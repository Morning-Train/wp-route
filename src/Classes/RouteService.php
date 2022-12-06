<?php

namespace Morningtrain\WP\Route\Classes;

use Symfony\Component\HttpFoundation\Request;

class RouteService
{
    /** Contains all registered routes
     * @var Route[] $routes
     */
    private static array $routes = [];

    /** The query var string for route names
     * @var string
     */
    private static string $routeQueryVar = 'mtwp_route';

    /** The currently matched route
     * @var ?Route $route
     */
    private static ?Route $matchedRoute = null;

    /** Option name for the generated routes hash. If the hash changes rewrite rules will be flushed
     * @var string $hashOption
     */
    private static string $hashOption = 'mtwp_route_hash';

    private static bool $actionsHasBeenAdded = false;

    private static array $allowedRequestMethods = [
        'ANY', // This one is special <3
        'GET',
        'POST',
        'PUT',
        'PATCH',
        'DELETE',
        'OPTIONS',
    ];

    private static bool $is404 = false;

    /**
     * Sets up actions for the route service to function
     */
    public static function setup()
    {
        if (static::$actionsHasBeenAdded) {
            return;
        }

        \add_action('init', [static::class, 'registerRoutes']);
        \add_action('parse_request', [static::class, 'matchRequest']);
        \add_action('template_redirect', [static::class, 'onTemplateRedirect']);

        static::$actionsHasBeenAdded = true;
    }

    /**
     * Registers the routes on init.
     * Routes must be registered through \Route before this
     */
    public static function registerRoutes()
    {
        $routes = static::$routes;
        static::addMainRewriteTag();
        foreach ($routes as $route) {
            static::addRewriteRule(\urlencode($route->getPath()), $route);
        }

        $routesHash = md5(json_encode($routes));
        if ($routesHash != get_option(static::$hashOption)) {
            \flush_rewrite_rules();
            update_option(static::$hashOption, $routesHash);
        }
    }

    /**
     * DANGEROUS!!
     * Only use in testing
     */
    public static function __forgetAllRoutes()
    {
        static::$routes = [];
    }

    /**
     * Adds the rewrite tag to WordPress
     */
    public static function addMainRewriteTag()
    {
        \add_rewrite_tag('%' . static::$routeQueryVar . '%', '([^/]+)');
    }

    public static function addRewriteTag(string $tag, string $regex = '([^/]+)')
    {
        \add_rewrite_tag('%' . $tag . '%', $regex);
    }

    /**
     * Adds the rewrite rule for a Route to WordPress
     *
     * @param  string  $handle
     * @param  Route  $route
     */
    public static function addRewriteRule(string $handle, Route $route)
    {
        $path = static::$routeQueryVar . "=" . $handle;
        $i = 1;
        foreach ($route->getParams() as $param) {
            $path .= "&{$param}=\$matches[{$i}]";
            $i++;
        }

        foreach ($route->getParams() as $param) {
            static::addRewriteTag($param, $route->getParamRegex($param));
        }

        \add_rewrite_rule(
            static::generateRouteRegex($route),
            'index.php?' . $path,
            $route->getPosition()
        );
    }

    /**
     * Generates the regex for the service
     *
     * @param  Route  $route
     *
     * @return string
     */
    public static function generateRouteRegex(Route $route): string
    {
        $path = $route->getPath();
        $newPath = str_replace(
            array_map(
                function ($p) {
                    return "{" . $p . "}";
                },
                $route->getParams()
            ),
            array_map([$route, 'getParamRegex'], $route->getParams()),
            $path
        );

        return '^' . ltrim(trim($newPath), '/') . '$';
    }

    /**
     * Attempts to match the current request to a route.
     *
     * @param  \WP  $environment
     */
    public static function matchRequest(\WP $environment)
    {
        $matchedRoute = static::getRouteByQueryVars($environment->query_vars);

        if ($matchedRoute instanceof Route) {
            static::$matchedRoute = $matchedRoute;
        }
    }

    /**
     * Get a list of allowed HTTP Request Methods
     * Note: These are all uppercase
     *
     * @return array|string[]
     */
    public static function getAllowedRequestMethods(): array
    {
        return static::$allowedRequestMethods;
    }

    /**
     * Tries to match query vars and http method to a route and return it
     *
     * @param  array  $query_vars
     *
     * @return ?Route
     */
    public static function getRouteByQueryVars(array $query_vars): ?Route
    {
        // Checks if the morningtrain route get param is set in Query Vars
        if (empty($query_vars[static::$routeQueryVar])) {
            return null;
        }

        $route = static::getRoute(\urlencode($query_vars[static::$routeQueryVar]), $_SERVER['REQUEST_METHOD']);

        if (empty($route)) {
            static::$is404 = true;

            return null;
        }

        return $route;
    }

    /**
     * Adds a Route to the service
     * Route wraps this in Route::get etc.
     *
     * @param  Route  $route
     */
    public static function addRoute(Route $route)
    {
        static::$routes[] = $route;
    }

    /**
     * Updates a route object by name or matching path AND methods
     *
     * @param  Route  $route
     */
    public static function updateRoute(Route $route)
    {
        foreach (static::$routes as $k => $_route) {
            if ($_route->getName() === $route->getName() || ($_route->getPath() === $route->getPath() && $_route->getRequestMethods() === $route->getRequestMethods())) {
                // These routes match! And should be updated
                static::$routes[$k] = $route;
            }
        }
    }

    /**
     * Gets a defined route by path
     *
     * @param  string  $path
     * @return Route|null
     */
    public static function getRoute(string $path, ?string $requestMethod = null): ?Route
    {
        foreach (static::$routes as $route) {
            if (\urlencode($route->getPath()) === $path) {
                if ($requestMethod === null || in_array($requestMethod, $route->getRequestMethods())) {
                    return $route;
                }
            }
        }

        return null;
    }

    /**
     * Gets a defined route by name
     *
     * @param  string  $name
     * @return Route|null
     */
    public static function getRouteByName(string $name)
    {
        foreach (static::$routes as $route) {
            if ($route->getName() === $name) {
                return $route;
            }
        }

        return null;
    }

    /**
     * Whether a named route exists or not
     * For internal use. Use Route::exists instead
     *
     * @param  string  $name
     * @return bool
     * @see Route::exists
     */
    public static function exists(string $name): bool
    {
        return is_a(static::getRouteByName($name), Route::class);
    }

    /**
     * Returns the URL of a named route
     * For internal use. Use Route::route instead
     *
     * @param  string  $name
     * @param ?array  $args
     *
     * @return string|null
     * @see Route::route
     *
     */
    public static function getUrl(string $name, $args = []): ?string
    {
        $route = static::getRouteByName($name);
        if (! $route) {
            return null;
        }

        return trim(\home_url(), '/') . $route->getUrl($args);
    }

    /**
     * Returns the currently matched route
     * For internal use. Use Route::current instead
     *
     * @return Route|null
     * @see Route::current
     */
    public static function currentRoute(): ?Route
    {
        return static::$matchedRoute;
    }

    /**
     * Checks if a route is currently matched
     * For internal use. Use Route::is instead
     *
     * @param  string  $name
     *
     * @return bool
     * @see Route::is
     *
     */
    public static function isCurrentRoute(string $name): bool
    {
        return is_a(static::$matchedRoute, Route::class) && static::$matchedRoute->getName() === $name;
    }

    /**
     * Redirects to a route callback if a route has been matched
     * Called on the template_redirect action
     */
    public static function onTemplateRedirect()
    {
        if (! static::$matchedRoute instanceof Route) {
            if (static::$is404) {
                global $wp_query;
                $wp_query->set_404();
                \status_header(404);
                \get_template_part(404);
            }

            return;
        }

        $request = Request::createFromGlobals();

        static::$matchedRoute->applyMiddleware($request)->call();
        exit;
    }
}
