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

    private static function goToLogin(?Request $request)
    {
        \wp_safe_redirect(\wp_login_url($request?->getPathInfo() ?? ''));
        exit;
    }

    /**
     * @param  Request  $request
     * @param $next
     * @param  string  $nonceField
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
