<?php


namespace Morningtrain\WP\Route;


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
    private static ?Route $matched_route = null;

    /** Option name for the generated routes hash. If the hash changes rewrite rules will be flushed
     * @var string $hash_option
     */
    private static string $hash_option = 'mtwp_route_hash';

    /**
     * Sets up actions for the routeservice to function
     */
    public static function setup()
    {
        \add_action('init', [static::class, 'registerRoutes']);
        \add_action('parse_request', [static::class, 'matchRequest']);
        \add_action('template_redirect', [static::class, 'onTemplateRedirect']);
    }

    /**
     * Registers the routes on init.
     * Routes must be registered through \Route before this
     */
    public static function registerRoutes()
    {
        $routes = static::$routes;
        static::addMainRewriteTag();
        foreach ($routes as $name => $route) {
            static::addRewriteRule($name, $route);
        }

        $routes_hash = md5(serialize($routes));
        if ($routes_hash != get_option(static::$hash_option)) {
            \flush_rewrite_rules();
            update_option(static::$hash_option, $routes_hash);
        }
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
     * @param string $handle
     * @param Route $route
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
     * @param Route $route
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
     * @param \WP $environment
     */
    public static function matchRequest(\WP $environment)
    {
        $matched_route = static::getRouteByQueryVars($environment->query_vars);

        if ($matched_route instanceof Route) {
            static::$matched_route = $matched_route;
        }
    }

    /**
     * Tries to match query vars and http method to a route and return it
     *
     * @param array $query_vars
     *
     * @return ?Route
     */
    public static function getRouteByQueryVars(array $query_vars): ?Route
    {
        // Checks if the morningtrain route get param is set in Query Vars
        if (empty($query_vars[static::$routeQueryVar])) {
            return null;
        }

        $route_name = \urlencode($query_vars[static::$routeQueryVar]);

        if (!isset(static::$routes[$route_name])) {
            \http_response_code(404);
            return null;
        }

        $requestMethods = static::$routes[$route_name]->getRequestMethods();

        if (!empty($requestMethods) && !in_array($_SERVER['REQUEST_METHOD'], $requestMethods)) {
            \http_response_code(405);
            die;
        }

        return static::$routes[$route_name];
    }

    /**
     * Adds a Route to the service
     * Route wrappes this in Route::get etc.
     *
     * @param Route $route
     */
    public static function addRoute(Route $route)
    {
        static::$routes[urlencode($route->getPath())] = $route;
    }

    /**
     * Updates a route object by name or matching path AND methods
     *
     * @param Route $route
     */
    public static function updateRoute(Route $route)
    {
        foreach (static::$routes as $k => $_route) {
            if ($_route->getName() === $route->getName() || ($_route->getPath() === $route->getPath(
                    ) && $_route->getRequestMethods() === $route->getRequestMethods())) {
                // These routes match! And should be updated
                static::$routes[$k] = $route;
            }
        }
    }

    /**
     * Gets a defined route by name
     *
     * @param string $name
     * @return Route|null
     */
    public static function getRoute(string $name): ?Route
    {
        foreach (static::$routes as $route) {
            if ($route->getName() === $name) {
                return $route;
            }
        }

        return null;
    }

    /**
     * Returns the URL of a named route
     *
     * @param string $name
     * @param ?array $args
     *
     * @return string|null
     */
    public static function getUrl(string $name, $args = []): ?string
    {
        $route = static::getRoute($name);
        if (!$route) {
            return null;
        }

        return $route->getUrl($args);
    }

    /**
     * Returns the currently matched route
     *
     * @return Route|null
     */
    public static function currentRoute(): ?Route
    {
        return static::$matched_route;
    }

    /**
     * Checks if a route is currently matched
     *
     * @param string $name
     * @return bool
     */
    public static function isCurrentRoute(string $name): bool
    {
        return is_a(static::$matched_route, Route::class) && static::$matched_route->getName() === $name;
    }

    /**
     * Redirects to a route callback if a route has been matched
     * Called on the template_redirect action
     */
    public static function onTemplateRedirect()
    {
        if (!static::$matched_route instanceof Route) {
            return;
        }

        static::$matched_route->call();
        exit;
    }
}