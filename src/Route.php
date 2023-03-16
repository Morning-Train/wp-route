<?php

namespace Morningtrain\WP\Route;

use Closure;
use Illuminate\Container\Container;
use Morningtrain\PHPLoader\Loader;
use Morningtrain\WP\Route\Classes\Rest\CallbackHandler;
use Morningtrain\WP\Route\Classes\Rewrite\Router as RewriteRouter;
use Morningtrain\WP\Route\Classes\Rest\Router as RestRouter;

class Route
{
    protected static Container $container;

    public static function setup(null|string|array $path = null)
    {
        static::$container = new Container();

        \Morningtrain\WP\Facades\Route::setFacadeApplication(static::getContainer());
        static::$container->singleton('rewrite-router', fn() => new RewriteRouter());

        \Morningtrain\WP\Facades\Rest::setFacadeApplication(static::getContainer());
        static::$container->singleton('rest-router', fn() => new RestRouter(new CallbackHandler()));

        if ($path !== null) {
            static::loadDir($path);
        }
    }

    public static function loadDir(string|array $path)
    {
        if(!isset(static::$container)){
            throw new \Exception('No app container found. Make sure that Route has been setup with Route::setup()');
        }
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

    public static function restRouter(): RestRouter
    {
        return static::getContainer()->make('rest-router');
    }
}
