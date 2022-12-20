<?php

namespace Morningtrain\WP\Route;

use Closure;
use Illuminate\Container\Container;
use Morningtrain\PHPLoader\Loader;
use Morningtrain\WP\Route\Classes\Rewrite\Router as RewriteRouter;

class Route
{
    protected static Container $container;

    public static function loadDir(string|array $path)
    {
        static::$container = new Container();
        \Morningtrain\WP\Facades\Route::setFacadeApplication(static::getContainer());
        static::$container->singleton('rewrite-router', fn() => new RewriteRouter());
        Loader::create($path);
    }

    public static function getContainer(): Container
    {
        return static::$container;
    }

    public static function rewriteRouter(): RewriteRouter
    {
        return static::getContainer()->make('rewrite-router');
    }
}
