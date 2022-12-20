<?php

namespace Morningtrain\WP\Route\Abstracts;

use Illuminate\Support\Collection;

/**
 * @method AbstractRoute $get(string $path, callable $callback)
 */
abstract class AbstractRouteFactory
{
    abstract public function newRoute(string $path, callable $callback): AbstractRoute;

    abstract public function newGroup(): AbstractGroup;

    abstract public function current(): ?AbstractRoute;

    public array $allowedRequestMethods = [
        'GET',
        'POST',
        'PUT',
        'PATCH',
        'DELETE',
        'OPTIONS',
    ];

    public ?AbstractGroup $currentGroup = null;
    protected Collection $routes;

    public function __construct()
    {
        $this->routes = new Collection();
    }

    public function registerRoutes()
    {
        if ($this->routes->isEmpty()) {
            return;
        }
        $this->routes->each(function (AbstractRoute $route) {
            $route->register();
        });
    }

    public function getRoutes(): Collection
    {
        return $this->routes;
    }

    public function getAllowedRequestMethods(): array
    {
        return $this->allowedRequestMethods;
    }

    public function getCurrentGroup(): ?AbstractGroup
    {
        return $this->currentGroup;
    }

    public function setCurrentGroup(?AbstractGroup $group): ?AbstractGroup
    {
        return $this->currentGroup = $group;
    }

    public function getRouteByName(string $name): ?AbstractRoute
    {
        return $this->routes->filter(function (AbstractRoute $route) use ($name) {
            return $route->getName() === $name;
        })->first();
    }

    public function route(string $routeName, array $args = []): ?string
    {
        return $this->getRouteByName($routeName)?->getUrl($args);
    }

    public function match(array $requestMethods, string $path, callable $callback): ?AbstractRoute
    {
        // Remove methods that we either don't recognize or allow
        $requestMethods = array_map('strtoupper', $requestMethods);
        $requestMethods = array_filter($requestMethods,
            fn($method) => in_array($method, $this->getAllowedRequestMethods()));

        // You gotta have at least one request method!
        if (empty($requestMethods)) {
            return null;
        }

        $route = $this->newRoute(ltrim($path, '/'), $callback);
        $route->setRequestMethods(
            $requestMethods
        );
        $route->setGroup($this->getCurrentGroup());
        $this->routes->add($route);

        return $route;
    }

    public function any(string $path, callable $callback): ?AbstractRoute
    {
        return $this->match($this->getAllowedRequestMethods(), $path, $callback);
    }

    public function get(string $path, callable $callback): ?AbstractRoute
    {
        return $this->match(['GET'], $path, $callback);
    }

    public function post(string $path, callable $callback): ?AbstractRoute
    {
        return $this->match(['POST'], $path, $callback);
    }

    public function patch(string $path, callable $callback): ?AbstractRoute
    {
        return $this->match(['PATCH'], $path, $callback);
    }

    public function put(string $path, callable $callback): ?AbstractRoute
    {
        return $this->match(['PUT'], $path, $callback);
    }

    public function options(string $path, callable $callback): ?AbstractRoute
    {
        return $this->match(['OPTIONS'], $path, $callback);
    }

    public function delete(string $path, callable $callback): ?AbstractRoute
    {
        return $this->match(['DELETE'], $path, $callback);
    }

    public function group(\Closure $routes): AbstractGroup
    {
        return $this->newGroup()->setGroup($this->currentGroup)->group($routes);
    }

    public function middleware(array|string|\Closure $middleware): AbstractGroup
    {
        return $this->newGroup()->setGroup($this->currentGroup)->middleware($middleware);
    }

    public function prefix(string $prefix): AbstractGroup
    {
        return $this->newGroup()->setGroup($this->currentGroup)->prefix($prefix);
    }
}
