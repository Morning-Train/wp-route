<?php

namespace Morningtrain\WP\Route;

class Route
{
    private ?string $name = null;
    private array $requestMethods = [];
    private string $position = 'top';

    private array $customParamRegexes = [];
    private string $defaultParamRegex = '([^/]+)';
    private array $params = [];

    /**
     * Route constructor.
     *
     * @param  string  $path
     * @param  callable  $callback
     */
    public function __construct(
        private string $path,
        private $callback
    ) {
        $this->extractPathParams();

        return $this;
    }

    public function __destruct()
    {
        $this->save();
    }

    /**
     * Update this route in Service and get  service ready
     *
     * @return $this
     */
    public function save(): static
    {
        RouteService::updateRoute($this);
        RouteService::setup();

        return $this;
    }

    private function extractPathParams()
    {
        $params = [];
        \preg_match_all("/{\w+}/", $this->getPath(), $params);

        $this->params = array_map(
            function ($p) {
                return trim($p, '{}');
            },
            $params[0]
        );
    }

    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * Get the path / relative URL
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Returns the relative URL of a route
     *
     * @param ?array  $args  the values of the route args, if any.
     * Eg. For the route /user/{user_id} -> /user/12
     * $args would be ['user_id'=>12]
     *
     * @return string
     */
    public function getUrl(?array $args = []): string
    {
        $tokens = array_map(
            function ($k) {
                return "{" . $k . "}";
            },
            array_keys($args)
        );

        return str_replace($tokens, array_values($args), $this->getPath());
    }

    /**
     * Get the callback
     *
     * @return callable
     */
    public function getCallback(): callable
    {
        return $this->callback;
    }

    /**
     * Get list of valid request methods
     * Array is empty if all (any) are allowed
     *
     * @return array
     */
    public function getRequestMethods(): array
    {
        return $this->requestMethods;
    }

    /**
     * Set the allowed Request Methods
     *
     * @param  array  $methods  Array of string such as "GET", "POST", "PUT" or "any"
     *
     * @return $this
     */
    public function setRequestMethods(array $methods): static
    {
        if ($methods === ['any']) {
            $methods = [];
        }

        $this->requestMethods = $methods;

        return $this;
    }

    /**
     * Get position
     * Value can be "top" or "bottom"
     *
     * @return string
     */
    public function getPosition(): string
    {
        return $this->position;
    }

    /**
     * Set the rewrite position
     *
     * @param  string  $position
     *
     * @return string
     *
     * @see https://developer.wordpress.org/reference/functions/add_rewrite_rule/
     */
    public function setPosition(string $position): static
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Calls the route callback
     */
    public function call(): static
    {
        $callback = $this->getCallback();
        if (! is_callable($callback)) {
            return $this;
        }
        $callback(...array_values($this->getQueryVars()));

        return $this;
    }

    /**
     * Returns an associative array of the route query vars
     *
     * @return array
     */
    public function getQueryVars(): array
    {
        return array_combine(
            $this->getParams(),
            array_map(
                function ($p) {
                    return \get_query_var($p);
                },
                $this->getParams()
            )
        );
    }

    public function getParamRegex(string $param)
    {
        if (! key_exists($param, $this->customParamRegexes)) {
            return $this->defaultParamRegex;
        }

        return $this->customParamRegexes[$param];
    }

    /**
     * Gets the name
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Sets the name of the route
     *
     * @param  string  $name
     * @return $this
     */
    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public static function loadDir(string|array $path)
    {
        \Morningtrain\PHPLoader\Loader::create($path);
        RouteService::setup();
    }

    /**
     * Register a Route that accepts any HTTP request method
     *
     * @param  string  $path
     * @param  callable  $callback
     *
     * @return Route
     */
    public static function match(array $requestMethods, string $path, callable $callback): Route
    {
        $route = new static($path, $callback);

        $route->setRequestMethods(
            $requestMethods
        ); // TODO: Filter these. But what to do if an invalid method is present? If removed with array_filter then array will be empty and any request type will pass through
        RouteService::addRoute($route);

        return $route;
    }

    /**
     * Register a Route that accepts any HTTP request method
     *
     * @param  string  $path
     * @param  callable  $callback
     *
     * @return Route
     */
    public static function any(string $path, callable $callback): Route
    {
        return static::match([], $path, $callback);
    }

    /**
     * Register a HTTP GET request Route
     *
     * @param  string  $path
     * @param  callable  $callback
     *
     * @return Route
     */
    public static function get(string $path, callable $callback): Route
    {
        return static::match(['GET'], $path, $callback);
    }

    /**
     * Register a HTTP POST request Route
     *
     * @param  string  $path
     * @param  callable  $callback
     *
     * @return Route
     */
    public static function post(string $path, callable $callback): Route
    {
        return static::match(['POST'], $path, $callback);
    }

    /**
     * Register a HTTP PUT request Route
     *
     * @param  string  $path
     * @param  callable  $callback
     *
     * @return Route
     */
    public static function put(string $path, callable $callback): Route
    {
        return static::match(['PUT'], $path, $callback);
    }

    /**
     * Register a HTTP PATCH request Route
     *
     * @param  string  $path
     * @param  callable  $callback
     *
     * @return Route
     */
    public static function patch(string $path, callable $callback): Route
    {
        return static::match(['PATCH'], $path, $callback);
    }

    /**
     * Register a HTTP DELETE request Route
     *
     * @param  string  $path
     * @param  callable  $callback
     *
     * @return Route
     */
    public static function delete(string $path, callable $callback): Route
    {
        return static::match(['DELETE'], $path, $callback);
    }

    /**
     * Register a HTTP OPTIONS request Route
     *
     * @param  string  $path
     * @param  callable  $callback
     *
     * @return Route
     */
    public static function options(string $path, callable $callback): Route
    {
        return static::match(['OPTIONS'], $path, $callback);
    }

    /**
     * Gets a defined route by name
     * Wrapper to allow access through this class
     *
     * @param  string  $name
     * @return bool
     */
    public static function exists(string $name): bool
    {
        return RouteService::exists($name);
    }

    /**
     * Returns the URL of a named route
     * Wrapper to allow access through this class
     *
     * @param  string  $name
     * @param  array  $args
     *
     * @return string|null
     */
    public static function route(string $name, array $args = []): ?string
    {
        return RouteService::getUrl($name, $args);
    }

    /**
     * Checks if a route is currently matched
     *
     * @param  string  $name
     *
     * @return bool
     * @see RouteService::isCurrentRoute
     */
    public static function is(string $name): bool
    {
        return RouteService::isCurrentRoute($name);
    }

    /**
     * Returns the currently matched route
     *
     * @return Route|null
     * @see RouteService::currentRoute
     */
    public static function current(): ?Route
    {
        return RouteService::currentRoute();
    }
}
