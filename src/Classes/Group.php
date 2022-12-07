<?php

namespace Morningtrain\WP\Route\Classes;

use Illuminate\Container\Container;
use Illuminate\Pipeline\Pipeline;
use Symfony\Component\HttpFoundation\Request;

class Group
{
    private static ?self $currentGroup = null;

    private array $middleware = [];
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

    public function middleware(array|callable|string $middleware): static
    {
        // If the middleware is callable then add it
        if (is_callable($middleware)) {
            $this->middleware[] = $middleware;
        } else {
            // If not then make sure it is an array
            $middleware = (array) $middleware;
            foreach ($middleware as $k => $m) {
                // If an item is NOT callable then we assume it is a string and is registered in the Middleware class - so we set that as callback
                if (! is_callable($m)) {
                    $middleware[$k] = [Middleware::class, $m];
                }
            }
            // Add the list of middleware
            $this->middleware = array_merge($this->middleware, $middleware);
        }

        return $this;
    }

    public function getMiddleware(): array
    {
        return array_filter(array_merge((array) $this->group?->getMiddleware(), $this->middleware));
    }

    public function applyMiddleware(Request $request): void
    {
        (new Pipeline())
            ->send($request)
            ->through($this->getMiddleware())
            ->then(function ($request) {
                $this->afterMiddleware($request);
            });
    }

    public function afterMiddleware(Request $request)
    {

        // return $route;
    }

    public function group(\Closure $routes): static
    {
        $routes();
        static::$currentGroup = $this->group; // Reset

        return $this;
    }
}
