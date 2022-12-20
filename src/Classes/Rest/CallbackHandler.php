<?php

namespace Morningtrain\WP\Route\Classes\Rest;

use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Request;

class CallbackHandler
{
    protected array $callbacks = [];

    public function addCallback(string $handle, Route $route): void
    {
        $this->callbacks[$handle] = $route;
    }

    public function __call(string $name, array $arguments)
    {
        $request = Request::createFromGlobals();
        global $wp_query;
        $request->query->add($wp_query->query_vars);

        if (key_exists($name, $this->callbacks)) {
            $route = $this->callbacks[$name];
            /** @var Route $route */
            $route->handleMiddleware($request)->call($request, ...$arguments);
        }
    }
}
