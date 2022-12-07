<?php

namespace Morningtrain\WP\Route\Classes;

use Symfony\Component\HttpFoundation\Request;

class Middleware
{
    private static array $middleware = [];

    public static function addMiddleware(string $name, callable $callable)
    {
        static::$middleware[$name] = $callable;
    }

    public static function __callStatic(string $name, array $arguments)
    {
        if (array_key_exists($name, static::$middleware)) {
            static::$middleware[$name](...$arguments);
        }
    }
}
