<?php

namespace Morningtrain\WP\Route\Classes;

use Illuminate\Container\Container;
use Illuminate\Pipeline\Pipeline;
use Symfony\Component\HttpFoundation\Request;

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

    public function getMiddleware(): array
    {
        return array_filter(array_merge((array) $this->group?->getMiddleware(), $this->middlewares));
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
