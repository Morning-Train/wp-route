<?php

namespace Morningtrain\WP\Route\Abstracts;

use Symfony\Component\HttpFoundation\Request;

abstract class AbstractRoute
{
    protected string $path;
    protected $callback;
    protected ?string $name = null;
    protected array $requestMethods = [];

    protected ?AbstractGroup $group = null;

    abstract public function register(): void;

    abstract public function getUrl(array $args = []): string;

    /**
     * Route constructor.
     *
     * @param  string  $path
     * @param  callable|string  $callback
     */
    public function __construct(
        string $path,
        callable|string $callback
    ) {
        $this->path = trim($path, '/');
        $this->callback = $callback;

        return $this;
    }

    public function setGroup(?AbstractGroup $group): static
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get the path / relative URL
     *
     * @return string
     */
    public function getPath(): string
    {
        $prefix = (string) $this->group?->getPrefix();

        return ! empty($prefix) ? implode('/', [$prefix, $this->path]) : $this->path;
    }

    /**
     * Get the callback
     *
     * @return callable|string
     */
    public function getCallback(): callable|string
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
        $this->requestMethods = array_map('strtoupper', $methods);

        return $this;
    }

    /**
     * Calls the route callback
     */
    public function call(Request $request, ...$args): static
    {
        // If callback is a string and a class, then it must be for invoking
        $callback = $this->getCallback();
        if (is_string($callback) && class_exists($callback)) {
            $callback = new $callback();
        }
        ($callback)($request, ...$args);

        return $this;
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

    /**
     * Get the routes associated group if it has one
     *
     * @return AbstractGroup|null
     */
    public function getGroup(): ?AbstractGroup
    {
        return $this->group;
    }

    /**
     * Handle all middleware for this route
     *
     * @param  Request  $request
     *
     * @return $this
     */
    public function handleMiddleware(Request $request): self
    {
        $this->getGroup()?->handleMiddleware($request);

        return $this;
    }
}
