<?php

namespace Morningtrain\WP\Route\Classes;

use Symfony\Component\HttpFoundation\Request;

class Middleware
{
    private static array $middleware = [];

    /**
     * Add a global middleware
     *
     * @param  string  $name  The name to refer to this middleware by
     * @param  callable  $callable  The middleware function to call. Must match (Request $request, $next, ...$args)
     */
    public static function add(string $name, callable $callable): void
    {
        static::$middleware[$name] = $callable;
    }

    /**
     * Handles the middleware call. Checks to see if a matching middleware exists and then calls it
     *
     * @param  string  $name
     * @param  array  $arguments
     */
    public static function __callStatic(string $name, array $arguments): void
    {
        // Split middleware with params
        if (str_contains($name, ':')) {
            [$name, $params] = explode(':', $name, 2);
            $arguments = array_merge($arguments, explode(',', $params));
            // If method exists by this name then call it!
            if (method_exists(static::class, $name)) {
                static::$name(...$arguments);

                return;
            }
        }
        // Let's call the registered middleware
        if (array_key_exists($name, static::$middleware)) {
            static::$middleware[$name](...$arguments);
        }
    }

    /**
     * Checks if a user is authorized. If not user is redirected to the login page
     *
     * @param  Request  $request
     * @param $next
     * @param ...$capabilities  - If set then user must match any of these capabilities
     * @return mixed
     */
    public static function auth(Request $request, $next, ...$capabilities)
    {
        $user = \wp_get_current_user();
        if ($user->ID === 0) {
            static::goToLogin($request);
        }

        if (! empty($capabilities) && empty(array_intersect($user->roles, $capabilities))) {
            static::goToLogin($request);
        }

        return $next($request);
    }

    /**
     * Send user to the login page with current URL as return URL
     *
     * @param  Request|null  $request
     */
    private static function goToLogin(?Request $request)
    {
        \wp_safe_redirect(\wp_login_url($request?->getPathInfo() ?? ''));
        exit;
    }

    /**
     * Verifies a nonce. Optionally by name
     * This middleware expects the nonce request param to be named '_wpnonce' if you need to verify a different value, then implement your own validation in your controller
     *
     * @param  Request  $request
     * @param $next
     * @param  int|string  $action
     *
     * @see https://developer.wordpress.org/reference/functions/wp_verify_nonce/
     */
    public static function verifyNonce(
        Request $request,
        $next,
        int|string $action = -1
    ) {
        $store = $request->getMethod() === 'GET' ? 'query' : 'request';
        $nonce = $request->$store->get('_wpnonce');

        if (empty($nonce) || \wp_verify_nonce($nonce, $action) === false) {
            \wp_die(__('You do not have the correct permissions to view this page'));
        }

        return $next($request);
    }
}
