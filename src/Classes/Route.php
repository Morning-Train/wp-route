<?php

namespace Morningtrain\WP\Route\Classes;

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
}
