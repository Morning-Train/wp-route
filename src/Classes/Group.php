<?php

namespace Morningtrain\WP\Route\Classes;

use Illuminate\Pipeline\Pipeline;
use Morningtrain\WP\Route\Responses\ExceptionErrorResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Group
{
    protected static ?self $currentGroup = null;

    protected array $middleware = [];
    protected ?self $group = null;

    public static function getCurrentGroup(): ?static
    {
        return static::$currentGroup;
    }

    public function __construct()
    {
        $this->group = static::getCurrentGroup();
        static::$currentGroup = $this;
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

    public function handleMiddleware(Request $request): void
    {
        try {
            $response = (new Pipeline())
                ->send($request)
                ->through($this->getMiddleware())
                ->thenReturn();
        } catch (\Exception $exception) {
            $response = new ExceptionErrorResponse($exception, 500);
        }

        if (is_a($response, Response::class)) {
            $response->send();
            exit;
        }
    }

    public function group(\Closure $routes): static
    {
        $routes();
        static::$currentGroup = $this->group; // Reset

        return $this;
    }
}
