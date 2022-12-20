<?php

namespace Morningtrain\WP\Route\Classes\Rewrite;

use Morningtrain\WP\Route\Abstracts\AbstractRouteFactory;
use Morningtrain\WP\Route\Classes\Response;
use Symfony\Component\HttpFoundation\Request;

class Router extends AbstractRouteFactory
{
    protected string $routeQueryVar = 'mtwp_route';
    protected string $hashOption = 'mtwp_route_hash';
    protected ?Route $matchedRoute = null;

    public function __construct()
    {
        parent::__construct();
        \add_action('init', [$this, 'registerRoutes']);
    }

    public function __destruct()
    {
        \remove_action('init', [$this, 'registerRoutes']);
        \remove_action('parse_request', [$this, 'matchRequest']);
        \remove_action('template_redirect', [$this, 'onTemplateRedirect']);
    }

    public function current(): ?Route
    {
        return $this->matchedRoute;
    }

    public function registerRoutes()
    {
        $this->addMainRewriteTag();
        parent::registerRoutes();
        $routesHash = md5(serialize($this->routes->toArray()));
        if ($routesHash != get_option($this->hashOption)) {
            \flush_rewrite_rules();
            update_option($this->hashOption, $routesHash);
        }
        \add_action('parse_request', [$this, 'matchRequest']);
    }

    public function newRoute(string $path, callable $callback): Route
    {
        return new Route($path, $callback);
    }

    public function newGroup(): Group
    {
        return new Group();
    }

    public function addMainRewriteTag(): void
    {
        \add_rewrite_tag('%' . $this->routeQueryVar . '%', '([^/]+)');
    }

    public function getQueryVar(): string
    {
        return $this->routeQueryVar;
    }

    public function matchRequest(\WP $environment): void
    {
        if (empty($environment->query_vars[$this->routeQueryVar])) {
            return;
        }

        $matchedRoute = $this->getRouteByQueryVars($environment->query_vars);

        if ($matchedRoute instanceof Route) {
            $this->matchedRoute = $matchedRoute;
        }

        \add_action('template_redirect', [$this, 'onTemplateRedirect']);
    }

    protected function getRouteByQueryVars(array $query_vars): ?Route
    {
        $path = \urlencode($query_vars[$this->routeQueryVar]);
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $matchedRoutes = $this->routes->filter(function (Route $route) use ($requestMethod, $path) {
            if (\urlencode($route->getPath()) === $path) {
                if ($requestMethod === null || in_array($requestMethod, $route->getRequestMethods())) {
                    return true;
                }
            }

            return false;
        });

        return $matchedRoutes->first();
    }

    public function onTemplateRedirect()
    {
        if (! $this->matchedRoute instanceof Route) {
            Response::withWordPressTemplate('404', 404)->send();
            exit;
        }

        $request = Request::createFromGlobals();
        global $wp_query;
        $request->query->add($wp_query->query_vars);
        $this->matchedRoute->handleMiddleware($request)->call($request);
        exit;
    }

}
