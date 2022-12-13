<?php

namespace Morningtrain\WP\Route\Classes;

class RouteGroup extends Group
{
    private string $prefix = '';

    /**
     * Add a prefix to a group
     *
     * @param  string  $prefix
     * @return $this
     */
    public function prefix(string $prefix): static
    {
        $this->prefix = trim($prefix, '/');

        return $this;
    }

    /**
     * Get the full prefix for this group including parent groups prefixes
     *
     * @return string
     */
    public function getPrefix(): string
    {
        return implode('/', array_filter([$this->group?->getPrefix(), $this->prefix]));
    }
}
