<?php

namespace Morningtrain\WP\Route\Classes;

use Illuminate\Container\Container;
use Illuminate\Pipeline\Pipeline;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
        try {
            $response = (new Pipeline())
                ->send($request)
                ->through($this->getMiddleware())
                ->thenReturn();

            if (! is_a($response, Response::class)) {
                if (is_string($response)) {
                    $response = new Response($response);
                } else {
                    $response = new Response();
                }
            }
        } catch (\Exception $exception) {
            $response = new Response($exception->getMessage(), 500);
        }

        $this->afterMiddleware($response);
    }

    public function afterMiddleware(Response $response)
    {
        switch ($response->getStatusCode()) {
            case 404:
                global $wp_query;
                $wp_query->set_404();
                \status_header(404);
                // TODO: Gotta figure how this works now. It's changed!! ಥ_ಥ
                \get_template_part(404);
                \block_template_part('404');
                break;
            case 500:
                echo "<p>Uh oh! An exception was thrown</p>";
                echo "<p><strong>{$response->getContent()}</strong></p>";
                break;
        }
    }

    public function group(\Closure $routes): static
    {
        $routes();
        static::$currentGroup = $this->group; // Reset

        return $this;
    }
}
