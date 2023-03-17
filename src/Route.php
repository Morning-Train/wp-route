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

    /**
     * Initialize the router and optionally load all .php files in a directory
     *
     * @param  string|array|null  $path
     */
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

    /**
     * Load all files in a directory
     * If router has not been initialized then it will be done here as well
     *
     * @param  string|array  $path
     */
    public static function loadDir(string|array $path)
    {
        if (! isset(static::$container)) {
            static::setup();
        }
        Loader::create($path);
    }

    /**
     * Get the illuminate app container
     *
     * @return Container
     */
    public static function getContainer(): Container
    {
        return static::$container;
    }

    /**
     * Make rewrite router
     *
     * @return RewriteRouter
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public static function rewriteRouter(): RewriteRouter
    {
        return static::getContainer()->make('rewrite-router');
    }

    /**
     * Make Rest router
     *
     * @return RestRouter
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public static function restRouter(): RestRouter
    {
        return static::getContainer()->make('rest-router');
    }
}
