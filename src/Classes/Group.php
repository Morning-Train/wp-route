<?php

namespace Morningtrain\WP\Route\Classes;

use Illuminate\Container\Container;
use Illuminate\Pipeline\Pipeline;

class Group
{
    private static ?self $currentGroup = null;

    private array $middlewares = [];
    private string $prefix = '';
    private ?self $group = null;

    public static function getCurrentGroup(): ?static
    {
        return static::$currentGroup;
    }

    public function __construct()
    {
        $this->group = static::getCurrentGroup();
        static::$currentGroup = $this;
    }

    public function prefix(string $prefix): static
    {
        $this->prefix = trim($prefix, '/');

        return $this;
    }

    public function getPrefix(): string
    {
        return implode('/', array_filter([$this->group?->getPrefix(), $this->prefix]));
    }

    public function addMiddleware(callable $middleware): static
    {
        $this->middlewares[] = $middleware;

        return $this;
    }

    public function getMiddlewares(): array
    {
        return array_filter(array_merge((array) $this->group?->getMiddlewares(), $this->middlewares));
    }

    public function applyMiddlewares(Route $route): void
    {
        $middlewares = $this->getMiddlewares();

        (new Pipeline())
            ->send($route)
            ->through($middlewares)
            ->then(function ($route) {
                $this->afterMiddleware($route);
            });
    }

    public function afterMiddleware(Route $route)
    {

        // return $route;
    }

    public function group(\Closure $routes): void
    {
        $routes();
        static::$currentGroup = $this->group; // Reset
        // Do the middleware stuff here
    }
}
