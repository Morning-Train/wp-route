<?php

namespace Morningtrain\WP\Route\Classes\Rewrite;

use Morningtrain\WP\Route\Abstracts\AbstractRoute;
use Morningtrain\WP\Route\Route as RouteHandler;
use Symfony\Component\HttpFoundation\Request;

class Route extends AbstractRoute
{
    private string $position = 'top';

    public function __construct(string $path, callable|string $callback)
    {
        parent::__construct($path, $callback);
    }

    public function register(): void
    {
        $path = RouteHandler::rewriteRouter()->getQueryVar() . "=" . \urlencode($this->getPath());
        $i = 1;
        foreach ($this->getParams() as $param) {
            $path .= "&{$param}=\$matches[{$i}]";
            \add_rewrite_tag('%' . $param . '%', '([^/]+)');
            $i++;
        }

        \add_rewrite_rule(
            $this->generateRouteRegex(),
            'index.php?' . $path,
            $this->getPosition()
        );
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

        return \trailingslashit(\home_url()) . str_replace($tokens, array_values($args), $this->getPath());
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
     * @return static
     *
     * @see https://developer.wordpress.org/reference/functions/add_rewrite_rule/
     */
    public function position(string $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getParams(bool $trimBrackets = true): array
    {
        $matches = [];
        \preg_match_all("/{(.*?)}/", $this->getPath(), $matches);

        return $trimBrackets ? $matches[1] : $matches[0];
    }

    protected function generateRouteRegex(): string
    {
        $newPath = str_replace(
            $this->getParams(false),
            '([^/]+)',
            $this->getPath()
        );

        return '^' . ltrim(trim($newPath), '/') . '$';

    }

    /**
     * Get a serialized representation of this route.
     * This is used to determine if route has changed and needs to flush rewrite rules
     *
     * @return array
     */
    public function __serialize(): array
    {
        return [$this->getName(), $this->getPath(), $this->getPosition()];
    }
}
